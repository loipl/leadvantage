<?php

class Model_Dictionary extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_dictionary;

    protected $t_dictionary_columns;

    protected $t_dictionary_values;

    const VDICT_ZIP_CODES    = -1;
    const VDICT_STATE_NAMES  = -2;
    const VDICT_STATE_ABBRS  = -3;

    const COL_ZIP_CODES_CITY       = -1;
    const COL_ZIP_CODES_STATE_ABBR = -2;
    const COL_ZIP_CODES_STATE      = -3;

    const COL_STNAMES_ABBR         = -4;
    const COL_STABBRS_NAME         = -5;

    protected static $virtualDictionaries = array (
        self::VDICT_ZIP_CODES      => 'ZIP Codes',
        self::VDICT_STATE_NAMES    => 'State Names',
        self::VDICT_STATE_ABBRS    => 'State Abbreviations',
    );

    protected static $dicts2Columns = array (

        self::VDICT_ZIP_CODES   => array (
            self::COL_ZIP_CODES_CITY       => 'City',
            self::COL_ZIP_CODES_STATE_ABBR => 'State Abbreviation',
            self::COL_ZIP_CODES_STATE      => 'State',
        ),

        self::VDICT_STATE_NAMES => array (
            self::COL_STNAMES_ABBR => 'State Abbreviation',
        ),

        self::VDICT_STATE_ABBRS => array (
            self::COL_STABBRS_NAME => 'State Name'
        ),

    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_dictionary);
    }
    //--------------------------------------------------------------------------


    public function listNamesAssoc() {
        return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` ORDER BY 2");
    }
    //--------------------------------------------------------------------------


    public function listVirtualDictionaries() {
        return self::$virtualDictionaries;
    }
    //--------------------------------------------------------------------------


    public function listColumnsForVDict($vDictId) {
        return isset(self::$dicts2Columns[$vDictId]) ? self::$dicts2Columns[$vDictId] : array();
    }
    //--------------------------------------------------------------------------


    public function listVirtualDictionaryColumns() {
        return self::$dicts2Columns;
    }
    //--------------------------------------------------------------------------
}
