<?php

class Model_UsZipCode extends CrudModel {

    /**
     * @var DB
     */
    protected $db;

    protected $t_us_zip_codes;

    protected $t_us_states;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_us_zip_codes);
    }
    //--------------------------------------------------------------------------


    public function getCityFromZip($zipCode) {
        $sql = "SELECT `city_name` FROM `$this->tableName` WHERE `zip_code` = ?";
        return $this->db->getTopLeft($sql, array($zipCode));
    }
    //--------------------------------------------------------------------------


    public function getStateAbbrFromZip($zipCode) {
        $sql = "SELECT `state_abbr` FROM `$this->tableName` WHERE `zip_code` = ?";
        return $this->db->getTopLeft($sql, array($zipCode));
    }
    //--------------------------------------------------------------------------


    public function getStateFromZip($zipCode) {
        $sql = "SELECT `s`.`name` FROM `$this->tableName` AS `z`, `$this->t_us_states` AS `s`
        WHERE
        `z`.`zip_code`   = ? AND
        `z`.`state_abbr` = `s`.`abbr`";
        return $this->db->getTopLeft($sql, array($zipCode));
    }
    //--------------------------------------------------------------------------


    public function getRowFromZip($zipCode) {
        $sql = "SELECT `z`.*, `s`.`name` AS `state_name` FROM `$this->tableName` AS `z`, `$this->t_us_states` AS `s`
        WHERE `s`.`abbr` = `z`.`state_abbr` AND `z`.`zip_code`   = ?";
        return $this->db->getTopArray($sql, array($zipCode));
    }
    //--------------------------------------------------------------------------
}
