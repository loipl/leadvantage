<?php

class Model_FieldType extends CrudModelCaching {
    /**
     * @var DB
     */
    protected $db;

    protected $t_field_types;
    protected $t_field_types_industries;

    const VALIDATION_NONE  = 0;
    const VALIDATION_EMAIL = 1;
    const VALIDATION_REGEX = 2;
    const VALIDATION_URL   = 3;
    const VALIDATION_DATE  = 4;

    protected static $validationTypes = array (
        self::VALIDATION_NONE  => 'None (valid UTF-8)',
        self::VALIDATION_EMAIL => 'Email Syntax',
        self::VALIDATION_URL   => 'URL Syntax',
        self::VALIDATION_DATE  => 'Valid Date',
        self::VALIDATION_REGEX => 'Regular Expression'
    );

    protected $defaultArguments = array (
        'listAssoc' => array (
            0 => 'name',
        ),
        'listFieldTypesAssoc' => array (
            0 => false,
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_field_types);

        $this->zeroOneFields = array('force_uppercase');
    }
    //--------------------------------------------------------------------------


    public function listValidationTypesAssoc() {
        return self::$validationTypes;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAssoc($orderBy = 'name') {
        $sql  = "SELECT * FROM `$this->tableName` ORDER BY `$orderBy`";
        $rows = $this->db->getArrayIndexed($sql, array(), 'id');
        $list  = $this->listIndustriesGroup(array_keys($rows));

        foreach ($rows as $id => & $row) {
            $row['industries'] = isset($list[$id]) ? $list[$id] : array();
        }
        unset($row);
        return $rows;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listFieldTypesAssoc($lowerCase = false) {
        if ($lowerCase) {
            return $this->db->getArrayAssoc("SELECT `id`, LOWER(`name`) AS `name` FROM `$this->tableName` ORDER BY 2");
        } else {
            return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` ORDER BY 2");
        }
    }
    //--------------------------------------------------------------------------


    public function setIndustries($ftId, array $industryIds) {
        $ftId = (int)$ftId;
        $this->db->query("DELETE FROM `$this->t_field_types_industries` WHERE `ft_id` = ?", array($ftId));
        if ($industryIds) {
            foreach ($industryIds as & $x) {
                $x = (int)$x;
            }
            unset($x);
            sort($industryIds);

            $sql = "INSERT INTO `$this->t_field_types_industries` (`ft_id`, `industry_id`) VALUES ($ftId, ";
            $sql .= implode("), ($ftId, ", $industryIds);
            $sql .= ')';
            $this->db->query($sql);
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listIndustries($ftId) {
        return $this->db->getArray1v("SELECT `industry_id` FROM `$this->t_field_types_industries` WHERE `ft_id` = ? ORDER BY 1", array((int)$ftId));
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listIndustriesGroup(array $fieldTypeIDs) {
        if (!$fieldTypeIDs) {
            return array();
        }

        $list = $this->db->getArray("SELECT `ft_id`, `industry_id` FROM `$this->t_field_types_industries` WHERE `ft_id` IN (" . self::implodeInts($fieldTypeIDs, ', ') . ') ORDER BY 1, 2', array(), MYSQL_NUM);
        $result = array();
        foreach ($list as $row) {
            $result[$row[0]][] = $row[1];
        }
        return $result;
    }
    //--------------------------------------------------------------------------
}
