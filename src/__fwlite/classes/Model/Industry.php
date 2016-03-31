<?php

class Model_Industry extends CrudModelCaching {
    protected $t_industries;

    protected $cachedAssocList = false;
    protected $cachedAbbrList = false;

    protected $defaultArguments = array (
        'listAssoc' => array (
            0 => true,
        ),
        'listAbbreviationsAssoc' => array (
            0 => true,
        ),
    );

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_industries);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAssoc($cached = true) {
        if (!$cached || ($this->cachedAssocList === false)) {
            $this->cachedAssocList = $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` ORDER BY 2");
        }
        return $this->cachedAssocList;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAbbreviationsAssoc($cached = true) {
        if (!$cached || ($this->cachedAbbrList === false)) {
            $this->cachedAbbrList = $this->db->getArrayAssoc("SELECT `id`, `abbreviation` FROM `$this->tableName` ORDER BY 2");
        }
        return $this->cachedAbbrList;
    }
    //--------------------------------------------------------------------------
}
