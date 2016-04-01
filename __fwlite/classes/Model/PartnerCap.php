<?php

class Model_PartnerCap extends CrudModelCaching {
    /**
     * @var DB
     */
    protected $db;

    protected $t_partner_caps;

    const CAP_TYPE_DELIVERY = '1';
    const CAP_TYPE_BUDGET   = '2';
    const ACTIVE_CAP = '1';
    const INACTIVE_CAP = '0';
    
    const INTERVAL_HOUR     = '1';
    const INTERVAL_DAY      = '2';
    const INTERVAL_WEEK     = '3';
    const INTERVAL_MONTH    = '4';
    const INTERVAL_HOUR_CALENDAR     = '5';
    const INTERVAL_DAY_CALENDAR      = '6';
    const INTERVAL_WEEK_CALENDAR     = '7';
    const INTERVAL_MONTH_CALENDAR    = '8';
    
    const INTERVAL_DAY_OF_WEEK        = '9';
    const INTERVAL_TIME_OF_DAY        = '10';
    
    const INTERVAL_EVER               = '11';
    
    public static function getIntervals() {
        return array (
            self::INTERVAL_DAY_OF_WEEK      => 'Day of Week',
            self::INTERVAL_TIME_OF_DAY      => 'Time of Day',
            self::INTERVAL_HOUR_CALENDAR   => 'per Hour',
            self::INTERVAL_DAY_CALENDAR    => 'per Day',
            self::INTERVAL_WEEK_CALENDAR   => 'per Week',
            self::INTERVAL_MONTH_CALENDAR  => 'per Month',
            self::INTERVAL_HOUR             => 'per 60 Minutes',
            self::INTERVAL_DAY              => 'per 24 Hours',
            self::INTERVAL_WEEK             => 'per 7 Days',
            self::INTERVAL_MONTH            => 'per 30 Days',
            self::INTERVAL_EVER             => 'per 365 Days'
        );
    }
    
    public static function getCapTypes() {
        return array (
            self::CAP_TYPE_DELIVERY => 'Lead Delivery Cap',
            self::CAP_TYPE_BUDGET   => 'Budget Cap'
        );
    }

    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_partner_caps);
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
    
    
    public function addPartnerCap($row) {
        $sql = "INSERT INTO `$this->tableName` SET
                `partner_id`    = ?,
                `is_active`     = ?,
                `cap_type`      = ?,
                `cap_value`     = ?,
                `interval`      = ?,
                `day_of_week`   = ?,
                `time_of_day`   = ?";

        $this->db->query($sql, array($row['id'], self::ACTIVE_CAP, self::CAP_TYPE_DELIVERY, $row['delivery_cap'], $row['delivery_ctype'], null, null));
        return $this->db->lastInsertId();
        
    }
    //--------------------------------------------------------------------------
    
    
    public function checkOldCapExists($row) {
        $sql = "SELECT * FROM `$this->tableName` 
                WHERE `partner_id`    = ?
                AND `cap_value`     = ?
                AND `interval`      = ?";
        
        return $this->db->getTopLeft($sql, array($row['id'], $row['delivery_cap'], $row['delivery_ctype']));
    }
}
