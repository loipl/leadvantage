<?php

class Model_ValidationCache extends CrudModel {

    protected $t_validation_cache;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_validation_cache);

        $this->timestampForInsert = array('check_time');
        $this->zeroOneFields      = array('is_valid');
    }
    //--------------------------------------------------------------------------


    public function isCached($fieldType, $data, & $isValid, & $message, $timeoutHours) {
        $sql = "SELECT `t`.* FROM `$this->tableName` AS `t`
        WHERE `ftype` = ? AND `data` = ?";

        $row = $this->db->getTopArray($sql, array((int)$fieldType, $data));
        if (!$row) {
            return false;
        }

        $message = $row['message'];
        $isValid = ($row['is_valid'] == '1');

        return true;
    }
    //--------------------------------------------------------------------------


    public function store($value, $fieldType, $error, $result) {
        $data = array(
            'ftype'    => $fieldType,
            'data'     => $value,
            'is_valid' => $result,
            'message'  => $error,
        );
        $this->insert($data, true);
    }
    //--------------------------------------------------------------------------


    public function deleteOldData($timeoutHours) {
        $this->db->query("DELETE FROM `$this->tableName` WHERE `check_time` < DATE_SUB(NOW(), INTERVAL ? HOUR)", array((int)$timeoutHours));
    }
    //--------------------------------------------------------------------------
}