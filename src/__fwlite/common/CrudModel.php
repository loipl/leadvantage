<?php

class CrudModel extends Model {

    /**
     * @var DB
     */
    protected $db = null;

    protected $tableName = '';

    protected $primaryKey = 'id';

    protected $intPK = true;

    protected $relatedData = array();

    /**
     * @desc These fields will be filled with NOW() on insert, but only if
     * there is no index for them in incoming data
     *
     * @var array
     */
    protected $timestampForInsert = array();

    /**
     * @desc These fields will be transformed to empty($data['field']) ? 0 : 1;
     *
     * @var array
     */
    protected $zeroOneFields = array();


    public function __construct(DB $db, $tableName, $pk = 'id', array $related = array()) {
        $this->db = $db;
        $this->setTableName($tableName, $related);
        $this->primaryKey  = $pk;
    }
    //--------------------------------------------------------------------------


    public function get($pk, $mysql_mode = MYSQL_BOTH) {
        $params = array();
        $sql = "SELECT * FROM `$this->tableName` WHERE " . $this->pkCondition($pk, $params);
        $arr = $this->db->getTopArray($sql, $params, $mysql_mode);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function getWhere(array $where, $mysql_mode = MYSQL_BOTH) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $arr = $this->db->getTopArray("SELECT * FROM `$this->tableName`$cond", $params, $mysql_mode);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function insert(array $data, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            $params[] = in_array($fieldName, $this->zeroOneFields) ? (empty($value) ? '0' : '1') : $value;
            $fields[] = $fieldName;
        }
        foreach ($this->zeroOneFields as $fieldName) {
            if (!in_array($fieldName, $fields)) {
                $params[] = '0';
                $fields[] = $fieldName;
            }
        }
        $count = 0;
        foreach($this->timestampForInsert as $fn) {
            if (!isset($data[$fn])) {
                $fields[] = $fn;
                $count++;
            }
        }
        $sql = "INSERT " . ($ignore ? 'IGNORE ' : '') . "INTO `$this->tableName` (`" . implode("`, `", $fields) . "`) VALUES(" . implode(', ', array_fill(0, sizeof($params), '?'))
        . str_repeat(', NOW()', $count) . ')';

        if ($this->db->query($sql, $params)) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function update($pk, array $data, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            if (($fieldName != $this->primaryKey) && array_key_exists($fieldName, $data) && (!is_numeric($fieldName))) {
                $params[] = in_array($fieldName, $this->zeroOneFields) ? (empty($data[$fieldName]) ? '0' : '1') : $data[$fieldName];
                $fields[] = $fieldName;
            }
        }
        foreach ($this->zeroOneFields as $fieldName) {
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
      UPDATE $ignore `$this->tableName` SET
        `" . implode("` = ?,\n        `", $fields) . "` = ?
      WHERE
        " . $this->pkCondition($pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function updateDiff($pk, array $data, array $oldData, $ignore = false) {
        $params = array(); $fields = array();
        foreach ($data as $fieldName => $value) {
            if (($fieldName != $this->primaryKey) && (!array_key_exists($fieldName, $data) || ($oldData[$fieldName] !== $data[$fieldName]))) {
                $params[] = in_array($fieldName, $this->zeroOneFields) ? (empty($data[$fieldName]) ? '0' : '1') : $data[$fieldName];
                $fields[] = $fieldName;
            }
        }
        if (!$fields) {
            return true;
        }
        $ignore = $ignore ? 'IGNORE ' : '';
        $sql = "
      UPDATE $ignore`$this->tableName` SET
        `" . implode("` = ?,\n        `", $fields) . "` = ?
      WHERE
        " . $this->pkCondition($pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function updateListCautiously(array $list, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            if (!$this->update($pk, $row)) {
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
                if ($this->update($pk, $row)) {
                    $remaining[$pk] = $conflicts[$pk];
                    unset($conflicts[$pk]);
                }
            }
            foreach ($remaining as $pk => $row) {
                if (!$this->update($pk, $row)) {
                    $conflicts[$pk] = $row;
                }
            }
        } while (!empty($conflicts));
    }
    //--------------------------------------------------------------------------


    public function updateDiffListCautiously(array $list, array $oldList, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array(); $old = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            if (!$this->updateDiff($pk, $row, $oldList[$k])) {
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
                if ($this->updateDiff($pk, $row, $old[$pk])) {
                    $remaining[$pk] = $conflicts[$pk];
                    unset($conflicts[$pk]);
                }
            }
            foreach ($remaining as $pk => $row) {
                if (!$this->updateDiff($pk, $row, $old[$pk])) {
                    $conflicts[$pk] = $row;
                }
            }
        } while (!empty($conflicts));
    }
    //--------------------------------------------------------------------------


    public function updateDiffListCautiouslyTwoSteps(array $list, array $oldList, $pkField, $uniqueField) {
        $conflicts = array(); $cvalues = array(); $old = array();
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            $r2 = $row;
            for(;;) {
                $r2[$uniqueField] = $pk . '-' . mt_rand();
                if ($this->updateDiff($pk, $r2, $oldList[$k])) {
                    $oldList[$k] = $r2;
                    break;
                }
            }
        }
        foreach ($list as $k => $row) {
            $pk = $row[$pkField];
            $this->updateDiff($pk, $row, $oldList[$k]);
        }
    }
    //--------------------------------------------------------------------------


    public function delete($pk) {
        $params = array();
        $sql = "DELETE FROM `$this->tableName` WHERE " . $this->pkCondition($pk, $params);
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function deleteWhere(array $where) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        return $this->db->query("DELETE FROM `$this->tableName`$cond", $params);
    }
    //--------------------------------------------------------------------------


    public function deleteGroup(array $pks) {
        if (!$pks) {
            return;
        }
        foreach ($pks as & $id) {
            $id = (int)$id;
        }
        unset($id);

        $sql = "DELETE FROM `$this->tableName` WHERE `$this->primaryKey` IN (" . implode(',', $pks) . ')';
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------


    public function listAll($orderBy = '') {
        $sql = "SELECT * FROM `$this->tableName`" . $this->order($orderBy);
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function listPage($page, $perPage, $orderBy = '') {
        $sql = "SELECT * FROM `$this->tableName`" . $this->orderLimit($orderBy, $page, $perPage);
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function listAllWhere(array $where, $orderBy = '') {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $sql = "SELECT * FROM `$this->tableName`$cond" . $this->order($orderBy);
        return $this->db->getArray($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listPageWhere(array $where, $page, $perPage, $orderBy = '') {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        $sql = "SELECT * FROM `$this->tableName`$cond" . $this->orderLimit($orderBy, $page, $perPage);
        return $this->db->getArray($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listGroup(array $pks, $orderBy = '', $isAssoc = true) {
        if (empty($pks)) {
            return array();
        }
        $sql = "SELECT * FROM `$this->tableName` WHERE `$this->primaryKey` IN(" . self::implodeInts($pks) . ')';
        return $isAssoc ? $this->db->getArrayIndexed($sql, array(), $this->primaryKey) : $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------


    public function count() {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function countWhere(array $where) {
        $this->convertWhereToParamsAndSql($where, $params, $cond);
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->tableName`$cond", $params);
    }
    //--------------------------------------------------------------------------


    public function setPrimaryKey($pk = 'id') {
        $this->primaryKey = $pk;
    }
    //--------------------------------------------------------------------------


    public function getPrimaryKey() {
        return $this->primaryKey;
    }
    //--------------------------------------------------------------------------


    public function setTableName($tableName, array $related = array()) {
        $this->tableName   = $tableName;
        $this->relatedData = $related;
    }
    //--------------------------------------------------------------------------


    public function getTableName() {
        return $this->tableName;
    }
    //--------------------------------------------------------------------------


    public function pkCondition($pk, array & $params) {
        $params[] = (int)$pk;
        return "`$this->primaryKey` = ?";
    }
    //--------------------------------------------------------------------------


    public function getExistingFieldValuesForKey($key, array $valuesAssoc, array $condition = array()) {
        if (!is_array($key)) {
            $key = array($this->primaryKey => $key);
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
        $where = DB::$db->processParams($where, $params);
        $addUnion = false;

        $extraSql = '';
        foreach ($condition as $fld => $val) {
            $extraSql .= DB::$db->processParams(" AND `$fld` = ?", array($val));
        }

        $sql = ""; $params = array();
        foreach ($valuesAssoc as $field => $value) {
            if ($addUnion) {
                $sql .= "\nUNION ALL \n";
            } else {
                $addUnion = true;
            }
            $sql .= "SELECT '$field' as `field`, `$field` AS `val` FROM `$this->tableName` " .
            "WHERE $where AND `$field` = '" . mysql_real_escape_string($value, $this->db->getLink(true)) . "'$extraSql";
        }
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    public function truncate() {
        return $this->db->query("TRUNCATE `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function startTransaction() {
        return $this->db->query("START TRANSACTION");
    }
    //--------------------------------------------------------------------------


    public function commit() {
        return $this->db->query("COMMIT");
    }
    //--------------------------------------------------------------------------


    public function rollback() {
        return $this->db->query("ROLLBACK");
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
