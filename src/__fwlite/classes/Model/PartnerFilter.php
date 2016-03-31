<?php

class Model_PartnerFilter extends CrudModelCaching {
    /**
     * @var DB
     */
    protected $db;

    protected $t_partner_filters;

    const FILTER_ACTION_DONT_DELIVER       = 1;
    const FILTER_ACTION_CHANGE_SUCCESS_URL = 2;
    const FILTER_ACTION_CHANGE_DELIVERY_URL= 3;

    const FILTER_MATCH_EQUALS              = 1;
    const FILTER_MATCH_STARTS_WITH         = 2;
    const FILTER_MATCH_ENDS_WITH           = 3;
    const FILTER_MATCH_CONTAINS            = 4;
    const FILTER_MATCH_GREATER_THAN        = 5;
    const FILTER_MATCH_LESS_THAN           = 6;
    const FILTER_MATCH_STRLEN              = 7;
    const FILTER_MATCH_REGEX               = 8;

    protected static $filterActions = array(
        self::FILTER_ACTION_DONT_DELIVER       => "Do not deliver",
        self::FILTER_ACTION_CHANGE_SUCCESS_URL => "Change success url to ",
        self::FILTER_ACTION_CHANGE_DELIVERY_URL=> "Change delivery url to "
    );

    protected static $filterMatches = array (
        self::FILTER_MATCH_EQUALS        => 'is equal to',
        -1                               => 'is not equal to',

        self::FILTER_MATCH_STARTS_WITH   => 'starts with',
        -2                               => 'does not start with',

        self::FILTER_MATCH_ENDS_WITH     => 'ends with',
        -3                               => 'does not end with',

        self::FILTER_MATCH_CONTAINS      => 'contains',
        -4                               => 'does not contain',

        self::FILTER_MATCH_GREATER_THAN  => 'is greater than',
        -5                               => 'is not greater than',

        self::FILTER_MATCH_LESS_THAN     => 'is less than',
        -6                               => 'is not less than',

        self::FILTER_MATCH_STRLEN        => 'length is at least',
        -7                               => 'length is less than',
        
        self::FILTER_MATCH_REGEX         => 'matches REGEX',
        -8                               => 'does not match REGEX',

    );

    protected $defaultArguments = array (
        'listAllForPartner' => array (
            1 => 'id',
        ),
        'listAssocForPartnerGroup' => array (
            1 => false,
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_partner_filters);
    }
    //--------------------------------------------------------------------------


    public function listFilterActions() {
        return self::$filterActions;
    }
    //--------------------------------------------------------------------------


    public static function listFilterMatches() {
        return self::$filterMatches;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAllForPartner($partnerId, $orderBy = 'id') {
        $sql = "SELECT * FROM `$this->tableName` WHERE `partner_id` = ?" . $this->order($orderBy);
        return $this->db->getArrayIndexed($sql, array((int)$partnerId), 'id');
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAssocForPartnerGroup(array $ids, $onlyActive = false) {
        if (!$ids) {
            return array();
        }
        $sql = "SELECT * FROM `$this->tableName` WHERE `partner_id` IN (" . self::implodeInts($ids) . ')';
        if ($onlyActive) {
            $sql .= " AND `is_active` = '1'";
        }
        $sql .= ' ORDER BY `partner_id`, `id` ';
        return $this->db->getArrayGrouped($sql, array(), 'partner_id');
    }
    //--------------------------------------------------------------------------
}
