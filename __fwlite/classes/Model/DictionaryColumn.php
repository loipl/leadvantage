<?php

class Model_DictionaryColumn extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_dictionary;

    protected $t_dictionary_columns;

    protected $t_dictionary_values;
    

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_dictionary_columns);
    }
    //--------------------------------------------------------------------------


    public function listColumnsForDictionary($dictionaryId) {
        return $this->listAllWhere(array('dictionary_id' => (int)$dictionaryId));
    }
    //--------------------------------------------------------------------------


    public function listColumnsAssoc($dictionaryId) {
        return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` WHERE `dictionary_id` = ? ORDER BY 2", array((int)$dictionaryId));
    }
    //--------------------------------------------------------------------------


    public function listAllColumnsAssoc() {
        return $this->db->getArrayAssoc("SELECT `dictionary_id`, `id`, `name` FROM `$this->tableName` ORDER BY 1, 3");
    }
    //--------------------------------------------------------------------------
}
