<?php

class Model_Country extends CrudModelCaching {
    protected $t_countries;

    protected $t_cc2_2_cc3;

    protected $countriesAssoc = array();

    protected $defaultArguments = array (
        'listCountriesAssoc' => array (
            0 => true,
        ),
    );

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_countries);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listCountriesAssoc($cached = true) {
        if (!$this->countriesAssoc || !$cached) {
            $this->countriesAssoc = $this->db->getArrayAssoc("SELECT `abbreviation`, `name` FROM `$this->tableName` ORDER BY 2");
        }
        return $this->countriesAssoc;
    }
    //--------------------------------------------------------------------------


    public function countryCode2charsTo3Chars($code2chars) {
        return $this->db->getTopLeft("SELECT `code3` FROM `$this->t_cc2_2_cc3` WHERE `code2` = ?", array($code2chars));
    }
    //--------------------------------------------------------------------------
}
