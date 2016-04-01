<?php

/**
 * @desc Supports CRUD operations with multiple tables, guiding idea being to keep<br>
 * all of the project's SQL code and all DB tables in one model file instead of <br>
 * chasing what model file it's in.
 */
class CrudModelMulti extends Model {

    /**
     * @var DB
     */
    public $db;


    public function __construct(DB $db = null) {
        $this->db = $db ? $db : DB::$db;
    }
    //--------------------------------------------------------------------------


    public function get(CM__Table $table, $pk, $mysql_mode = MYSQL_ASSOC) {
        $params = array();
        $sql = "SELECT * FROM `$table` WHERE " . $this->pkCondition($table, $pk, $params);
        $arr = $this->db->getTopArray($sql, $params, $mysql_mode);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function getWhere(CM__Table $table, array $where, $mysql_mode = MYSQL_ASSOC) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $arr = $this->db->getTopArray("SELECT * FROM `$table`$cond", $params, $mysql_mode);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function insert(CM__Table $table, array $data, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            $params[] = in_array($fieldName, $table->zeroOneFields) ? (empty($value) ? '0' : '1') : $value;
            $fields[] = $fieldName;
        }
        foreach ($table->zeroOneFields as $fieldName) {
            if (!in_array($fieldName, $fields)) {
                $params[] = '0';
                $fields[] = $fieldName;
            }
        }
        $count = 0;
        foreach($table->timestampForInsert as $fn) {
            if (!isset($data[$fn])) {
                $fields[] = $fn;
                $count++;
            }
        }
        $sql = "INSERT " . ($ignore ? 'IGNORE ' : '') . "INTO `$table` (`" . implode("`, `", $fields) . "`) VALUES(" . implode(', ', array_fill(0, sizeof($params), '?'))
        . str_repeat(', NOW()', $count) . ')';

        if ($this->db->query($sql, $params)) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function update(CM__Table $table, $pk, array $data, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            if (($fieldName != $table->primaryKey) && array_key_exists($fieldName, $data) && (!is_numeric($fieldName))) {
                $params[] = in_array($fieldName, $table->zeroOneFields) ? (empty($data[$fieldName]) ? '0' : '1') : $data[$fieldName];
                $fields[] = $fieldName;
            }
        }
        foreach ($table->zeroOneFields as $fieldName) {
            if (!in_array($fieldName, $fields)) {
                $params[] = '0';
                $fields[] = $fieldName;
            }
        }
        if (!$fields) {
            return true;
        }
        $ignore = $ignore ? 'IGNORE ' : '';
        $sql = "
      UPDATE $ignore`$table` SET
        `" . implode("` = ?,\n        `", $fields) . "` = ?
      WHERE
        " . $this->pkCondition($table, $pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function updateDiff(CM__Table $table, $pk, array $data, array $oldData, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            if (($fieldName != $table->primaryKey) && (!array_key_exists($fieldName, $data) || ($oldData[$fieldName] !== $data[$fieldName]))) {
                $params[] = in_array($fieldName, $table->zeroOneFields) ? (empty($data[$fieldName]) ? '0' : '1') : $data[$fieldName];
                $fields[] = $fieldName;
            }
        }
        if (!$fields) {
            return true;
        }
        $ignore = $ignore ? 'IGNORE ' : '';
        $sql = "
      UPDATE $ignore`$table` SET
        `" . implode("` = ?,\n        `", $fields) . "` = ?
      WHERE
        " . $this->pkCondition($table, $pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function updateListCautiously(CM__Table $table, array $list, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            if (!$this->update($table, $pk, $row)) {
                $conflicts[$pk] = $row;
                $cvalues[$pk]   = $row[$uniqueField];
            }
        }
        if (empty($conflicts)) {
            return;
        }

        $remaining = array();
        do {
            while (!empty($conflicts)) {
                $keys = array_keys($conflicts);
                $pk = $keys[0];
                $row = $conflicts[$pk];
                $row[$uniqueField] = mt_rand();
                if ($this->update($table, $pk, $row)) {
                    $remaining[$pk] = $conflicts[$pk];
                    unset($conflicts[$pk]);
                }
            }
            foreach ($remaining as $pk => $row) {
                if (!$this->update($table, $pk, $row)) {
                    $conflicts[$pk] = $row;
                }
            }
        } while (!empty($conflicts));
    }
    //--------------------------------------------------------------------------


    public function updateDiffListCautiously(CM__Table $table, array $list, array $oldList, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array(); $old = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            if (!$this->updateDiff($table, $pk, $row, $oldList[$k])) {
                $conflicts[$pk] = $row;
                $old[$pk]       = $oldList[$k];
                $cvalues[$pk]   = $row[$uniqueField];
            }
        }
        if (empty($conflicts)) {
            return;
        }

        $remaining = array();
        do {
            while (!empty($conflicts)) {
                $keys = array_keys($conflicts);
                $pk = $keys[0];
                $row = $conflicts[$pk];
                $row[$uniqueField] = mt_rand();
                if ($this->updateDiff($table, $pk, $row, $old[$pk])) {
                    $remaining[$pk] = $conflicts[$pk];
                    unset($conflicts[$pk]);
                }
            }
            foreach ($remaining as $pk => $row) {
                if (!$this->updateDiff($table, $pk, $row, $old[$pk])) {
                    $conflicts[$pk] = $row;
                }
            }
        } while (!empty($conflicts));
    }
    //--------------------------------------------------------------------------


    public function updateDiffListCautiouslyTwoSteps(CM__Table $table, array $list, array $oldList, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array(); $old = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            $r2 = $row;
            for(;;) {
                $r2[$uniqueField] = $pk . '-' . mt_rand();
                if ($this->updateDiff($table, $pk, $r2, $oldList[$k])) {
                    $oldList[$k] = $r2;
                    break;
                }
            }
        }
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            $this->updateDiff($table, $pk, $row, $oldList[$k]);
        }
    }
    //--------------------------------------------------------------------------


    public function delete(CM__Table $table, $pk) {
        $params = array();
        $sql = "DELETE FROM `$table` WHERE " . $this->pkCondition($table, $pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function deleteWhere(CM__Table $table, array $where) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        return $this->db->query("DELETE FROM `$table`$cond", $params);
    }
    //--------------------------------------------------------------------------


    public function deleteGroup(CM__Table $table, array $pks) {
        if (!$pks) {
            return;
        }
        foreach ($pks as & $id) {
            $id = (int)$id;
        }
        unset($id);

        $sql = "DELETE FROM `$table` WHERE `$table->primaryKey` IN (" . self::implodeInts($pks) . ')';
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------


    public function listAll(CM__Table $table, $orderBy = '') {
        $sql = "SELECT * FROM `$table`" . $this->order($orderBy);
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function listPage(CM__Table $table, $page, $perPage, $orderBy = '') {
        $sql = "SELECT * FROM `$table`" . $this->orderLimit($orderBy, $page, $perPage);
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function listAllWhere(CM__Table $table, array $where, $orderBy = '') {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $sql = "SELECT * FROM `$table`$cond" . $this->order($orderBy);
        return $this->db->getArray($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listPageWhere(CM__Table $table, array $where, $page, $perPage, $orderBy = '') {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $sql = "SELECT * FROM `$table`$cond" . $this->orderLimit($orderBy, $page, $perPage);
        return $this->db->getArray($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listGroup(CM__Table $table, array $pks, $orderBy = '', $isAssoc = true) {
        if (empty($pks)) {
            return array();
        }
        $sql = "SELECT * FROM `$table` WHERE `$table->primaryKey` IN (" . self::implodeInts($pks) . ')';
        return $isAssoc ? $this->db->getArrayIndexed($sql, array(), $table->primaryKey) : $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function count(CM__Table $table) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$table`");
    }
    //--------------------------------------------------------------------------


    public function countWhere(CM__Table $table, array $where) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$table`$cond", $params);
    }
    //--------------------------------------------------------------------------


    public function getExistingFieldValuesForKey(CM__Table $table, $key, array $valuesAssoc, array $condition = array()) {
        if (!is_array($key)) {
            $key = array($table->primaryKey => $key);
        }
        $addAnd = false;
        $where  = 'NOT (';
        $params = array();
        foreach ($key as $field => $value) {
            if ($addAnd) {
                $where .= ' AND ';
            } else {
                $addAnd = true;
            }
            $where .= "(`$field` = ?)";
            $params[] = $value;
        }
        $where .= ')';
        $where = $this->db->processParams($where, $params);
        $addUnion = false;

        $extraSql = '';
        foreach ($condition as $fld => $val) {
            $extraSql .= $this->db->processParams(" AND `$fld` = ?", array($val));
        }

        $sql = ""; $params = array();
        foreach ($valuesAssoc as $field => $value) {
            if ($addUnion) {
                $sql .= "\nUNION ALL \n";
            } else {
                $addUnion = true;
            }
            $sql .= "SELECT '$field' as `field`, `$field` AS `val` FROM `$table` " .
            "WHERE $where AND `$field` = '" . mysql_real_escape_string($value, $this->db->getLink(true)) . "'$extraSql";
        }
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    public function truncate(CM__Table $table) {
        return $this->db->query("TRUNCATE `$table`");
    }
    //--------------------------------------------------------------------------


    public function pkCondition(CM__Table $table, $pk, array & $params) {
        $params[] = (int)$pk;
        return "`$table->primaryKey` = ?";
    }
    //--------------------------------------------------------------------------


    public static function implodeInts(array $ints, $delimiter = ', ') {
        $result   = '';
        $addDelim = false;
        foreach ($ints as $int) {
            if ($addDelim) {
                $result .= $delimiter;
            } else {
                $addDelim = true;
            }
            $result .= (int)$int;
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    protected function convertWhereToParamsAndSql(array $where, & $params, & $cond, $prependWhere = true) {
        $params = array();
        $cond = '';
        foreach ($where as $k => $v) {
            $cond .= ($cond ? ' AND ' : ($prependWhere ? ' WHERE ' : ' ')) . (is_int($k) ? "($v)" : "(`$k` = ?)");
            if (!is_int($k)) {
                $params[] = $v;
            }
        }
    }
    //--------------------------------------------------------------------------
}


class CM__Table {

    public $tableName;

    /**
     * @var CrudModelMulti
     */
    public $ownerModel;

    public $primaryKey = 'id';

    /**
     * @desc These fields will be filled with NOW() on insert, but only if
     * there is no index for them in incoming data
     *
     * @var array
     */
    public $timestampForInsert = array();

    /**
     * @desc These fields will be transformed to empty($data['field']) ? 0 : 1;
     *
     * @var array
     */
    public $zeroOneFields = array();


    public function __construct(CrudModelMulti $owner, $tableName, $primaryKey = 'id', array $timestampForInsert = array(), array $zeroOneFields = array()) {
        $this->ownerModel = $owner;
        $this->setParams($tableName, $primaryKey, $timestampForInsert, $zeroOneFields);
    }
    //--------------------------------------------------------------------------


    public function setParams($tableName, $primaryKey = 'id', array $timestampForInsert = array(), array $zeroOneFields = array()) {
        $this->tableName  = $tableName;
        $this->primaryKey = $primaryKey;

        $this->timestampForInsert = $timestampForInsert;
        $this->zeroOneFields      = $zeroOneFields;
    }
    //--------------------------------------------------------------------------


    public function __toString() {
        return $this->ownerModel->db->getTablePrefix() . $this->tableName;
    }
    //--------------------------------------------------------------------------
}
