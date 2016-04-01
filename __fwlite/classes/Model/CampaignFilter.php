<?php

class Model_CampaignFilter extends CrudModelCaching {
    /**
     * @var DB
     */
    protected $db;

    protected $t_campaign_filters;

    protected static $filterActions = array(
        Model_PartnerFilter::FILTER_ACTION_DONT_DELIVER       => "Do not accept"
    );

    protected $defaultArguments = array (
        'listAllForCampaign' => array (
            1 => 'id',
        ),
        'listAssocForCampaignGroup' => array (
            1 => false,
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_campaign_filters);
    }
    //--------------------------------------------------------------------------


    public function listFilterActions() {
        return self::$filterActions;
    }
    //--------------------------------------------------------------------------


    public function listFilterMatches() {
        $result = Model_PartnerFilter::listFilterMatches();
        
        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAllForCampaign($campaignId, $orderBy = 'id') {
        $sql = "SELECT * FROM `$this->tableName` WHERE `campaign_id` = ?" . $this->order($orderBy);
        return $this->db->getArrayIndexed($sql, array((int)$campaignId), 'id');
    }
    //--------------------------------------------------------------------------

    
    /**
     * @Cached
     */
    public function listActiveFiltersForCampaign($campaignId, $orderBy = 'id') {
        $sql = "SELECT * FROM `$this->tableName` WHERE `campaign_id` = ? AND `is_active` = '1'" . $this->order($orderBy);
        return $this->db->getArrayIndexed($sql, array((int)$campaignId), 'id');
    }
    //--------------------------------------------------------------------------
    

    /**
     * @Cached
     */
    public function listAssocForCampaignGroup(array $ids, $onlyActive = false) {
        if (!$ids) {
            return array();
        }
        $sql = "SELECT * FROM `$this->tableName` WHERE `campaign_id` IN (" . self::implodeInts($ids) . ')';
        if ($onlyActive) {
            $sql .= " AND `is_active` = '1'";
        }
        $sql .= ' ORDER BY `campaign_id`, `id` ';
        return $this->db->getArrayGrouped($sql, array(), 'campaign_id');
    }
    //--------------------------------------------------------------------------
    
    public function countForUser($userId) {
        $sql = "SELECT count(*) "
                . "FROM `$this->tableName` cr "
                . "INNER JOIN campaigns c ON cr.campaign_id = c.id "
                . "WHERE c.user_id = ?";
        return $this->db->getTopLeft($sql, array((int)$userId));
    }
}
