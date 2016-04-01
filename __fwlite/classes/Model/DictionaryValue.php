<?php

class Model_DictionaryValue extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_dictionary;

    protected $t_dictionary_columns;

    protected $t_dictionary_column_values;

    protected $t_dictionary_values;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_dictionary_values);
    }
    //--------------------------------------------------------------------------


    public function listForDictionary($dictionaryId, $page, $perPage) {
        $arr = $this->listPageWhere(array('dictionary_id' => (int)$dictionaryId), $page, $perPage);
        if (!$arr) {
            return array();
        }

        $ids = array();
        $map = array();
        foreach ($arr as $index => & $row) {
            $ids[] = (int)$row['id'];
            $map[(int)$row['id']] = $index;

            $row['values'] = array();
        }
        unset($row);

        $values = $this->db->getArrayAssoc("
        SELECT `value_id`, `column_id`, `value`
        FROM `$this->t_dictionary_column_values`
        WHERE `value_id` IN (" . self::implodeInts($ids) . ') ORDER BY 1, 2');

        foreach ($values as $valueId => $colValues) {
            $arr[$map[$valueId]]['values'] = $colValues;
        }

        return $arr;
    }
    //--------------------------------------------------------------------------


    public function get($pk, $mysql_mode = MYSQL_BOTH) {
        $arr = parent::get($pk);
        if ($arr) {
            $arr['values'] = $this->db->getArrayAssoc("SELECT `column_id`, `value` FROM `$this->t_dictionary_column_values` WHERE `value_id` = ? ORDER BY 1", array((int)$pk));
        }
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function writeValues($valueId, array $valuesAssoc) {
        $this->db->query("DELETE FROM `$this->t_dictionary_column_values` WHERE `value_id` = ?", array((int)$valueId));
        if (!$valuesAssoc) {
            return;
        }

        $sql     = "INSERT INTO `$this->t_dictionary_column_values` (`column_id`, `value_id`, `value`) VALUES";
        $params  = array();
        $addComa = false;

        foreach ($valuesAssoc as $columnId => $value) {
            $params[] = (int)$columnId;
            $params[] = (int)$valueId;
            $params[] = $value;
            if ($addComa) {
                $sql .= ",";
            } else {
                $addComa = true;
            };
            $sql .= "\n(?, ?, ?)";
        }
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function getValueForKey($dictionaryId, $keyStr, $columnId) {
        $sql = "
      SELECT `value` FROM `$this->t_dictionary_column_values` WHERE `column_id` = ? AND `value_id` = (
        SELECT `id` FROM `$this->tableName` WHERE `dictionary_id` = ? AND `key_value` = ?
      )";
        return $this->db->getTopLeft($sql, array((int)$columnId, (int)$dictionaryId, $keyStr));
    }
}
