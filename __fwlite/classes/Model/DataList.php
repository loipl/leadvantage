<?php

class Model_DataList extends CrudModelCaching {
    
    protected $db;

    protected $t_data_list;
    
    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->$t_data_list);

    }
    //--------------------------------------------------------------------------
}

