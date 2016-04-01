<?php

class Model_Conversion extends CrudModel {

    protected $t_conversions;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_conversions);

        $this->timestampForInsert = array('conversion_time');
    }
    //--------------------------------------------------------------------------


    public function getLast() {
        $sql = "SELECT * FROM `$this->tableName` ORDER BY `id` DESC LIMIT 1";
        return $this->db->getTopArray($sql, array(), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------
}
