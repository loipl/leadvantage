<?php

class Model_AdminTip extends CrudModel {
    protected $t_admin_tips;

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_admin_tips);
    }
    //--------------------------------------------------------------------------


    public function getRandomTip() {
        $sql = "SELECT `content` FROM `$this->t_admin_tips` ORDER BY RAND() LIMIT 1";
        return $this->db->getTopLeft($sql);
    }
    //--------------------------------------------------------------------------

}