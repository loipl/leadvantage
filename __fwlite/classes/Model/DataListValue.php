<?php

class Model_DataListValue extends CrudModelCaching {
    
    protected $db;

    protected $t_data_list_value;
    
    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_data_list_value);

    }
    
    public function insertMultiple($id, $valueList) {
        $sql = "INSERT INTO `data_list_value`(`data_list_id`, `value`) VALUES ";
        
        foreach ($valueList as $value) {
            $sql .= '(' . $id . ',\'' . mysql_real_escape_string($value) . '\'),';
        }
        $sql = rtrim($sql, ',');
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------
    
    public function checkValueExistInDataList($dataListID, $value) {
        $result = $this->listAllWhere(array('data_list_id' => $dataListID));
        foreach ($result as $row) {
            if ($row['value'] === $value) {
                return true;
            }
        }
        return false;
    }
}

