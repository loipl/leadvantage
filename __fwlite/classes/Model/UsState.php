<?php

class Model_UsState extends CrudModel {

    /**
     * @var DB
     */
    protected $db;

    protected $t_us_states;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_us_states);
    }
    //--------------------------------------------------------------------------


    public function listStatesAssoc() {
        return $this->db->getArrayAssoc("SELECT `abbr`, `name` FROM `$this->tableName` ORDER BY 2");
    }
    //--------------------------------------------------------------------------


    public function getStateAbbrFromState($state) {
        $sql = "SELECT `abbr` FROM `$this->tableName` WHERE `name` = ?";
        return $this->db->getTopLeft($sql, array($state));
    }
    //--------------------------------------------------------------------------


    public function getStateFromStateAbbr($stateAbbr) {
        $sql = "SELECT `name` FROM `$this->tableName` WHERE `state_abbr` = ?";
        return $this->db->getTopLeft($sql, array($stateAbbr));
    }
    //--------------------------------------------------------------------------
}
