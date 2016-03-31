<?php

class Model_Partner extends CrudModelCaching {

    const POST_TYPE_POST  = 1;
    const POST_TYPE_GET   = 2;
    const POST_TYPE_EMAIL = 3;
    const POST_TYPE_AFF   = 4;
    const POST_TYPE_XML   = 5;
    const POST_TYPE_JSON  = 6;
    const POST_TYPE_XML_FIELD = 7;
    
    const RESPONSE_TYPE_TEXT = 0;
    const RESPONSE_TYPE_JSON = 1;
    const RESPONSE_TYPE_XML  = 2;
    
    const PARTNER_TYPE_POST      = 0;
    const PARTNER_TYPE_PING_POST = 1;
    
    const PING_POST_TYPE_PING = 1;
    const PING_POST_TYPE_POST = 0;

    const AFF_URL_REGEX   = '^http://[a-z0-9\\-]{1,}(.[a-z0-9\\-]{1,}){0,}/COREG/';

    const DELIVERY_CTYPE_MINUTE          = 'per Minute';
    const DELIVERY_CTYPE_HOUR            = 'per 60 Minutes';
    const DELIVERY_CTYPE_DAY             = 'per 24 Hours';
    const DELIVERY_CTYPE_WEEK            = 'per 7 Days';
    const DELIVERY_CTYPE_MONTH           = 'per 30 Days';
    const DELIVERY_CTYPE_HOUR_CALENDAR   = 'per Hour';
    const DELIVERY_CTYPE_DAY_CALENDAR    = 'per Day';
    const DELIVERY_CTYPE_WEEK_CALENDAR   = 'per Week';
    const DELIVERY_CTYPE_MONTH_CALENDAR  = 'per Month';

    const DELIVERY_CTYPE_EVER            = 'per 365 Days';
    const DELIVERY_CTYPE_DAY_OF_WEEK     = 'Day of Week';
    const DELIVERY_CTYPE_TIME_OF_DAY     = 'Time of Day';
    
    const PING_SUFFIX   = ' (ping)';
    const POST_SUFFIX   = ' (post)';
    
    const TEMPLATE_VALID    = '1';
    const TEMPLATE_INVALID  = '0';
    
    const LOCK_THRESHOLD    = 0.5;
    const NUMBER_OF_THREAD  = 50;
    
    protected static $postTypes = array (
        self::POST_TYPE_POST        => 'POST',
        self::POST_TYPE_GET         => 'GET',

//        self::POST_TYPE_EMAIL       => 'Email',
        self::POST_TYPE_XML         => 'XML payload',
        self::POST_TYPE_XML_FIELD   => 'XML field',
        self::POST_TYPE_JSON        => 'JSON',

        self::POST_TYPE_AFF   => 'Adult FriendFinder',
    );
    
    public static $priceJsonPaths = array (
        '$.price',
        '$.Price',
        '$.*.price',
        '$.*.Price'
    );
    
    public static $priceXmlPaths = array (
        '/price',
        '/Price',
        '/*/price',
        '/*/Price',
        '/*/*/price',
        '/*/*/Price'
    );

    // order in this array decides order of Delivery cap type in partner screen.
    // that's why 5,6,7,8 appear before 1,2,3,4
    public static $deliveryTypes = array(
        5 => self::DELIVERY_CTYPE_HOUR_CALENDAR,
        6 => self::DELIVERY_CTYPE_DAY_CALENDAR,
        7 => self::DELIVERY_CTYPE_WEEK_CALENDAR,
        8 => self::DELIVERY_CTYPE_MONTH_CALENDAR,
        1 => self::DELIVERY_CTYPE_HOUR,
        2 => self::DELIVERY_CTYPE_DAY,
        3 => self::DELIVERY_CTYPE_WEEK,
        4 => self::DELIVERY_CTYPE_MONTH,
        11 => self::DELIVERY_CTYPE_EVER,
    );

    public static $deliveryTypesWithMinute = array(
        0 => self::DELIVERY_CTYPE_MINUTE,
        1 => self::DELIVERY_CTYPE_HOUR,
        2 => self::DELIVERY_CTYPE_DAY,
        3 => self::DELIVERY_CTYPE_WEEK,
        4 => self::DELIVERY_CTYPE_MONTH,
    );
    
    public static $partnerTypes = array (
        self::PARTNER_TYPE_POST      => 'Post-only',
        self::PARTNER_TYPE_PING_POST => 'Ping-Post'
    );

    /**
     * @var DB
     */
    protected $db;

    protected $t_campaigns;
    protected $t_campaign_delivery;
    protected $t_campaign_fields;
    protected $t_field_types;
    protected $t_partners;
    protected $t_partner_fields;
    protected $t_sh_delivery;
    protected $t_sh_response_times;
    protected $t_summary_acceptance;
    protected $t_template_partners;


    protected $defaultArguments = array (
        'listPageForUser' => array (
            3 => 'name',
        ),
        'listAllPartnerNamesAssoc' => array (
            0 => false,
        ),
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_partners);

        $this->timestampForInsert = array('created_at');
        $this->zeroOneFields      = array('parse_response', 'should_retry');
    }
    //--------------------------------------------------------------------------

    
    public function updateCampaignsWithExistingWrapperPartner($userId) {
        // Find campaigns with existing wrapper partners, then update them
        $sql = "
            SELECT `$this->t_partners`.`id` FROM `$this->t_partners`
            INNER JOIN `$this->t_campaigns`
                ON `$this->t_campaigns`.`id` = `$this->t_partners`.`wrapped_campaign_id`
                AND `$this->t_campaigns`.`user_id` = `$this->t_partners`.`user_id`
            INNER JOIN `$this->t_campaign_delivery` 
                ON `$this->t_partners`.`id` = `$this->t_campaign_delivery`.`partner_id`
            WHERE `$this->t_partners`.`user_id` = ? AND `wrapped_campaign_id` IS NOT NULL
            GROUP BY `$this->t_partners`.`id`
        ";

        $wrappedIDs = $this->db->getArray1v($sql, array((int)$userId));

        foreach ($wrappedIDs AS $wid) {
            $this->updateCampaignWrapper($wid);
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function removeUnassignedWrapperPartner($userId) {
        $sql = "DELETE FROM `$this->t_partners`
                WHERE `user_id` = ?
                AND `wrapped_campaign_id` IS NOT NULL;
                ";
        return $this->db->query($sql,array((int)$userId));
    }
    //--------------------------------------------------------------------------
    
    
    public function makeSureAllCampaignsHaveWrapperPartners($userId) {
        // Find campaigns without wrapper partners
        $sql = "
        SELECT `id` FROM `$this->t_campaigns` WHERE
        `user_id` = ?
        AND NOT EXISTS (
            SELECT * FROM `$this->t_partners` WHERE `user_id` = ? AND `wrapped_campaign_id` = `$this->t_campaigns`.`id`
        )
        AND `is_master` = '0'
        ORDER BY 1";

        $campaignIDs = $this->db->getArray1v($sql, array((int)$userId, (int)$userId));
        foreach ($campaignIDs as $cid) {
            $this->createWrapperAroundCampaign($cid);
        }
    }
    //--------------------------------------------------------------------------


    public function getCampaignIdFromWrapperPartnerId($wrapperId) {
        $sql = "
        SELECT `wrapped_campaign_id` FROM `$this->t_partners` WHERE
        `id` = ?
        LIMIT 1
        ";

        $result = $this->db->getTopLeftInt($sql, array((int)$wrapperId));

        return $result;
    }
    //--------------------------------------------------------------------------


    public function createChildIpFieldForWrapperPartner($wrapperId) {
        $sql = "
        INSERT IGNORE INTO `$this->t_partner_fields` SET
        `partner_id` = ?,
        `name` = ?,
        `value` = ?,
        `modifier` = ''
        ";
        $this->db->query($sql,array($wrapperId, 'lw_child_ip_pass', '[:ip_address:]'));
    }
    //--------------------------------------------------------------------------


    public function createWrapperAroundCampaign($campaignId) {
        $sql = "SELECT `user_id`,`name` FROM `$this->t_campaigns` WHERE `id` = ?";
        $campaignData = $this->db->getTopArray($sql, array((int)$campaignId));

    $hash = (int)$campaignId . 'e' . md5('troglodyte' . $campaignData['user_id'] . $campaignId);
        $campaignUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/' . $hash . '/';

        if ($campaignData['user_id']) {
            $sql = "INSERT INTO `$this->tableName` SET
            `user_id`             = ?,
            `created_at`          = NOW(),
            `wrapped_campaign_id` = ?,
            `name`                = ?,
            `posting_type`        = '1',
            `success_keyword`     = 'success',
            `failure_keyword`     = 'failure',
            `delivery_addr`       = ?,
            `delivery_cap`        = 0,
            `success_url`         = '',
            `price_per_lead`      = 0    ";

            $this->db->query($sql, array($campaignData['user_id'], (int)$campaignId, $campaignData['name'] . " (campaign wrapper)", $campaignUrl));
            return $this->db->lastInsertId();
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------


    public function updateCampaignWrapper($wrapperId) {
        $sql = "
        SELECT `name`,`id` FROM `$this->t_partner_fields` WHERE
        `partner_id` = ?
        ";
        $partnerFields = $this->db->getArrayAssoc($sql, array($wrapperId));

        $sql = "
        SELECT `wrapped_campaign_id` FROM `$this->tableName` WHERE
        `id` = ?
        LIMIT 1
        ";
        $campaignId = $this->db->getTopLeftInt($sql,array($wrapperId));

        $sql    = "SELECT `user_id` FROM `$this->t_campaigns` WHERE `id` = ?";
        $userId = $this->db->getTopLeftInt($sql, array((int)$campaignId));

        $hash = (int)$campaignId . 'e' . md5('troglodyte' . $userId . $campaignId);
        $campaignUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/' . $hash . '/';

        $sql = "
        UPDATE `$this->tableName` SET `delivery_addr` = ?
        WHERE `id` = ?
        LIMIT 1
        ";

        $this->db->query($sql, array($campaignUrl, $wrapperId));

        // Update partner fields from campaign fields
        $sql = "
        SELECT `name`,`field_type` FROM `$this->t_campaign_fields` WHERE
        `campaign_id` = ?
        ";

        $campaignFields   = $this->db->getArrayAssoc($sql, array($campaignId));

        $sql = "SELECT `email_field_name` FROM `$this->t_campaigns` WHERE `id` = ?";
        $emailFieldName = $this->db->getTopLeft($sql, array((int)$campaignId));

        $campaignFields[$emailFieldName] = '2';

        foreach ($campaignFields AS $field => $type) {
            $sql = "
            SELECT `name` FROM `$this->t_field_types` WHERE
            `id` = ?
            ";
            $typeName = $this->db->getTopLeft($sql,array($type));

            if (array_key_exists($field,$partnerFields)) {
                if ($field == 'lw_child_ip_pass') {
                    continue;
                }

                $sql = "
                UPDATE `$this->t_partner_fields` SET
                `name` = ?,
                `value` = ?
                WHERE `id` = ?
                LIMIT 1
                ";
                $this->db->query($sql,array($field,'[' . $typeName . ']',(int)$partnerFields[$field]));
            } else {
                $sql = "
                INSERT INTO `$this->t_partner_fields` SET
                `partner_id` = ?,
                `name` = ?,
                `value` = ?,
                `modifier` = ''
                ";
                $this->db->query($sql,array($wrapperId,$field,'[' . $typeName . ']'));
            }
        }

        foreach ($partnerFields AS $field => $id) {
            if (!array_key_exists($field,$campaignFields)) {
                $sql = "
                DELETE FROM `$this->t_partner_fields`
                WHERE `id` = ?
                ";
                $this->db->query($sql,array((int)$id));
            }
        }
    }
    //--------------------------------------------------------------------------


    public function markAsDeleted($id) {
        $sql = "UPDATE `$this->tableName` SET `name` = CONCAT('Deleted #', ?, ' - ', `name`), `user_id` = NULL WHERE `id` = ?";
        $this->db->query($sql, array($id . '', (int)$id));
        $this->deletePartnerFromAllRelatedSmallTables($id);
    }
    //--------------------------------------------------------------------------


    /**
     * @desc When marking partner as deleted, also delete its data from related small
     * tables - ones not generated by traffic - visible in the UI
     */
    public function deletePartnerFromAllRelatedSmallTables($campaignId) {

        $dependantTables = array(
            $this->t_campaign_delivery,
            $this->t_sh_delivery,
            $this->t_sh_response_times,
            $this->t_summary_acceptance,
            $this->t_template_partners,
        );

        foreach ($dependantTables as $row) {
            if (!is_array($row)) {
                $row = array($row, 'partner_id');
            }
            $sql = "DELETE FROM `$row[0]` WHERE `$row[1]` = ?";
            $this->db->query($sql, array((int)$campaignId));
        }
    }
    //--------------------------------------------------------------------------


    public function listPostTypesAssoc() {
        return self::$postTypes;
    }
    //--------------------------------------------------------------------------


    public function listDeliveryCapTypesAssoc() {
        return self::$deliveryTypes;
    }
    //--------------------------------------------------------------------------

    public function listResponseTypesAssoc() {
        return array(
            self::RESPONSE_TYPE_TEXT => 'Text',
            self::RESPONSE_TYPE_JSON => 'JSON',
            self::RESPONSE_TYPE_XML  => 'XML'
        );
    }
    //--------------------------------------------------------------------------

    public function listDeliveryCapTypesAssocWithMinute() {
        return array(
            0 => self::DELIVERY_CTYPE_MINUTE,
            1 => self::DELIVERY_CTYPE_HOUR,
            2 => self::DELIVERY_CTYPE_DAY,
            3 => self::DELIVERY_CTYPE_WEEK,
            4 => self::DELIVERY_CTYPE_MONTH
        );
    }
    //--------------------------------------------------------------------------


    public function listPageForUser($whereUserId, $page, $perPage, $orderBy = 'name') {

        $sql = "SELECT *
                FROM `$this->tableName` p1
                WHERE $whereUserId
                    AND `wrapped_campaign_id` IS NULL 
                    AND (`partner_type` = ? 
                        OR `ping_post_type` = ?
                        OR NOT EXISTS (
                            SELECT * FROM `partners` 
                            WHERE $whereUserId
                                AND post_partner_id = p1.id
                        )
                    ) " 
                . $this->orderLimit($orderBy, $page, $perPage);
        
        return $this->db->getArray($sql, array(strval(self::PARTNER_TYPE_POST), strval(self::PING_POST_TYPE_PING)));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns position where given id is found when rows are sorted by name.
     * Used to redirect user to correct page for the partner he just edited
     */
    public function indexOfIdSortedByName($userId, $id) {
        $arr = $this->db->getArray1v("SELECT `id` FROM `$this->tableName` WHERE `user_id` AND `wrapped_campaign_id` IS NULL = ? ORDER BY `name`", array((int)$userId));
        return array_search((int)$id, $arr);
    }
    //--------------------------------------------------------------------------


    public function countForUser($whereUserId, $getPingPostPartner = null) {
        $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE $whereUserId AND `wrapped_campaign_id` IS NULL";
        
        if (empty($getPingPostPartner)) {
            $sql .= " AND `ping_post_type` = '" . self::PARTNER_TYPE_POST . "'";
        } else {
            $sql .= " AND `ping_post_type` = '" . self::PARTNER_TYPE_PING_POST . "'";
        }
        
        return $this->db->getTopLeftInt($sql);
    }
    //--------------------------------------------------------------------------


    public function listPartnerNamesAssoc($userId, $subAccountPartnerIds = null) { 
        $whereUserId = 'user_id = ' . $userId;
        if (!empty($subAccountPartnerIds)) {
            $whereUserId = '(' . $whereUserId . ' OR `id` IN (' . implode(',', $subAccountPartnerIds) . '))';
        }
        
        $sql = "SELECT `id`, `name`
                FROM `$this->tableName`
                WHERE $whereUserId
                    AND `wrapped_campaign_id` IS NULL
                ORDER BY `name`";
        
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------
    
    
    public function listPostOnlyPartnerNames($userId, $subAccountPartnerIds = null) {
        $whereUserId = 'WHERE `user_id` = ? ';
        if (!empty($subAccountPartnerIds)) {
            $whereUserId = 'WHERE (`user_id` = ? OR `id` IN (' . implode(',', $subAccountPartnerIds) . '))';
        }
        
        $sql = "SELECT `id`, `name`
                FROM `$this->tableName`
                $whereUserId
                    AND `wrapped_campaign_id` IS NULL
                    AND `partner_type` = ?
                ORDER BY `name`";
        $params = array((int)$userId, strval(self::PARTNER_TYPE_POST));
        
        return $this->db->getArrayAssoc($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    public function listPingPartnerNames($userId, $subAccountPartnerIds = null) {
        $whereUserId = 'WHERE `user_id` = ? ';
        if (!empty($subAccountPartnerIds)) {
            $whereUserId = 'WHERE (`user_id` = ? OR `id` IN (' . implode(',', $subAccountPartnerIds) . '))';
        }
        
        $sql = "SELECT `id`, `name`
                FROM `$this->tableName`
                $whereUserId
                    AND `wrapped_campaign_id` IS NULL 
                    AND `partner_type` = ?
                    AND `ping_post_type` = ?
                    AND `post_partner_id` IS NOT NULL
                ORDER BY `name`";
        $params = array((int)$userId, strval(self::PARTNER_TYPE_PING_POST), strval(self::PING_POST_TYPE_PING));
        
        return $this->db->getArrayAssoc($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listWrappedCampaigns($userId) {
        $sql = "
        SELECT
          `p`.`id`,
          `c`.`id` AS `wrapped_id`,
          `c`.`name`,
          `c`.`is_active`
        FROM
          `$this->t_partners`  AS `p`,
          `$this->t_campaigns` AS `c`
        WHERE
          `p`.`wrapped_campaign_id` = `c`.`id`
          AND `c`.`is_master` = '0'
          AND `p`.`user_id`   = ?
          AND `c`.`user_id`   = ?
        ORDER BY
          `c`.`name`";

        $arr = $this->db->getArray($sql, array((int)$userId, (int)$userId), MYSQL_ASSOC);
        $res = array();
        foreach ($arr as $row) {
            $res[$row['id']] = $row;
        }
        return $res;
    }
    //--------------------------------------------------------------------------


    public function listPartnerIDsForUser($userId) {
        return $this->db->getArray1v("SELECT `id` FROM `$this->tableName` WHERE `user_id` = ? AND `wrapped_campaign_id` IS NULL ORDER BY 1", array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function listAllPartnerNamesAssoc($flat = false) {
        if ($flat) {
            return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` WHERE `wrapped_campaign_id` IS NULL ORDER BY `name`");
        } else {
            return $this->db->getArrayAssoc("SELECT `user_id`, `id`, `name` FROM `$this->tableName` WHERE `wrapped_campaign_id` IS NULL ORDER BY `user_id`, `name`");
        }
    }
    //--------------------------------------------------------------------------


    public function listPartnerNamesWithWrappersAssoc($flat = false) {
        if ($flat) {
            return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->tableName` ORDER BY `name`");
        } else {
            return $this->db->getArrayAssoc("SELECT `user_id`, `id`, `name` FROM `$this->tableName` ORDER BY `user_id`, `name`");
        }
    }
    //--------------------------------------------------------------------------


    public function copyPartner($id, $newName, $copyFields, $copyRules, $copyCaps, $userId = false, $templatePartnerId = false) {
        if (trim($newName) == '') {
            throw new EExplainableError("Name cannot be empty");
        }

        $row = $this->get($id, MYSQL_ASSOC);
        $existing = $this->getExistingFieldValuesForKey(0, array('name' => $newName), array('user_id' => $userId ? $userId : (int)$row['user_id']));
        if ($existing) {
            throw new EExplainableError("Name already taken");
        }

        unset($row['id']);
        unset($row['created_at']);
        $row['name'] = $newName;
        if ($userId !== false) {
            $row['user_id'] = (int)$userId;
        }

        if ($templatePartnerId) {
            $tplId = $this->db->getTopLeftInt("SELECT `id` FROM `$this->t_template_partners` WHERE `partner_id` = ?", array((int)$templatePartnerId));
            if ($tplId) {
                $row['create_from_tpl_id'] = $tplId;
            }
        }

        $newId = $this->insert($row);
        if ($newId) {
            if ($copyFields) {
                $odelPartnerField = SingletonRegistry::getModelPartnerField();
                foreach ($odelPartnerField->listAllForPartner($id) as $row) {
                    unset($row['id']);
                    $row['partner_id'] = $newId;
                    Lib::removeIntKeys($row);
                    $odelPartnerField->insert($row);
                }
            }

            if ($copyRules) {
                $modelPartnerFilter = SingletonRegistry::getModelPartnerFilter();
                foreach ($modelPartnerFilter->listAllForPartner($id) as $row) {
                    unset($row['id']);
                    Lib::removeIntKeys($row);
                    $row['partner_id'] = $newId;
                    $modelPartnerFilter->insert($row);
                }
            }
            
            if ($copyCaps) {
                $modelPartnerCap = SingletonRegistry::getModelPartnerCap();
                foreach ($modelPartnerCap->listAllForPartner($id) as $row) {
                    unset($row['id']);
                    Lib::removeIntKeys($row);
                    $row['partner_id'] = $newId;
                    $modelPartnerCap->insert($row);
                }
            }
            
            return $newId;
        } else {
            throw new EExplainableError('Cannot copy partner');
        }
    }
    //--------------------------------------------------------------------------


    public function listAllPartnersCreatedFromTemplate($templateId) {
        $sql = "SELECT * FROM `$this->tableName` WHERE `user_id` IS NOT NULL AND `wrapped_campaign_id` IS NULL AND `create_from_tpl_id` = ?";
        return $this->db->getArray($sql, array((int)$templateId));
    }
    //--------------------------------------------------------------------------


    public function setPartnerDeliveryTemplate($partnerId, $templateText) {
        $sql = "UPDATE `$this->tableName` SET `template` = ? WHERE `id` = ?";
        $params = array($templateText, (int)$partnerId);

        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    public function getPingPartnerId($postPartnerId) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE `post_partner_id` = ? LIMIT 1";
        $params = array($postPartnerId);

        return $this->db->getTopLeft($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    
    public function getPingPostInfo($id) {
        $sql = "SELECT `id`, `partner_type`, `ping_post_type`, `post_partner_id` FROM `$this->tableName` WHERE `id` = ? LIMIT 1";
        $params = array($id);

        return $this->db->getArray($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    
    public function updatePostPartnerId($id, $postPartnerId) {
        $sql = "UPDATE `$this->tableName` SET `post_partner_id` = ? WHERE `id` = ?";
        $params = array($postPartnerId, (int)$id);

        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    
    public static function isValidCapType($capType) {
        return isset(Model_Partner::$deliveryTypes[$capType]);
    }
    //--------------------------------------------------------------------------


    public static function isValidCapTypeWithMinutes($capType) {
        return isset(Model_Partner::$deliveryTypesWithMinute[$capType]);
    }
    //--------------------------------------------------------------------------
        
    public static function getDayStart($time, $timezone = null)
    {

        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }
        // calculate partner time;
        $dayStart = strtotime('today', $time);

        // convert back to Server time
        date_default_timezone_set(Config::$timeZone);
        return date('Y-m-d H:i:s', $dayStart);
    }
    //--------------------------------------------------------------------------

    public static function getWeekStart($time, $timezone = null)
    {
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        // calculate partner time;
        if (date('w', $time) === '0') { // on sunday
            $lastSunday = strtotime('today', $time);
        } else {
            $lastSunday = strtotime('last Sunday', $time);
        }

        // convert back to server tiem
        date_default_timezone_set(Config::$timeZone);
        return date('Y-m-d H:i:s', $lastSunday);
    }
    //--------------------------------------------------------------------------

    public static function getMonthStart($time, $timezone = null)
    {
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }
        
        // calculate partner time;
        $monthStart = strtotime(date('Y-m', $time));

        // convert back to server tiem
        date_default_timezone_set(Config::$timeZone);
        return date('Y-m-d H:i:s', $monthStart);
    }
    //--------------------------------------------------------------------------
    
    
    public static function getDayOfWeekCondition($cap, $time, $timezone = null)
    {
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }
        
        if ($cap['day_of_week'] !== date('D', $time)) {
            return "";
        }
        
        // get start of that Day of Week
        $dayStart = strtotime('today', $time);

        // convert back to server tiem
        date_default_timezone_set(Config::$timeZone);
        return "'" . date('Y-m-d H:i:s', $dayStart) . "'";
    }
    //--------------------------------------------------------------------------
    
    
    public static function getTimeOfDayCondition($cap, $time, $timezone = null)
    {
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        if ($cap['day_of_week'] !== date('D', $time)) {
            return "";
        }
        
        $currentTime = date('H:i', $time);
        $timeRange = explode('-', $cap['time_of_day']);
        $fromTime = !empty($timeRange[0]) ? $timeRange[0] : '00:00';
        $toTime = !empty($timeRange[1]) ? $timeRange[1] : '23:59';
        
        if ($currentTime < $fromTime || $currentTime > $toTime) {
            return "";
        }
        
        // get start of that Day of Week
        $dayStart = strtotime('today', $time);
        $fromDate = strtotime(date('Y-m-d', $dayStart) . ' ' . $fromTime);
        
        // convert back to server tiem
        date_default_timezone_set(Config::$timeZone);
        
        return "'" . date('Y-m-d H:i', $fromDate) . "'";
    }
    //--------------------------------------------------------------------------
    
    
    public static function isValidJsonPathExpression($path) 
    {
        // if path start with $, it should be followed by . or [
        if (substr($path,0,1) === '$' && substr($path,1,1) !== '.' && substr($path,1,1) !== '[') {
            return false;
        }
        
        $regex = '/^[a-zA-Z0-9\$\.\*@()\[\]+_-]+$/';
        return preg_match($regex, $path);
    }
    //--------------------------------------------------------------------------
    
    public static function isValidXPathExpression($path) 
    {
        // path cannot start with a number or [ character
        // character / inside [] is also not valid
        
        if (preg_match('/(^[0-9]|^\[|\[[^\]]+\/)/', $path)) {
            return false;
        }
        
        $regex = '/^[a-zA-Z0-9\/\.\*@()\[\]+_-]+$/';
        return preg_match($regex, $path);
    }
    //--------------------------------------------------------------------------
    
    public function syncPingPostPartner($id, $data, $direction) 
    {
        if ($direction === 'pingToPost') {
            $name = preg_replace('/( ?\(ping\))?$/', ' (post)', $data['name'], 1);
            $condition = " id = " . mysql_real_escape_string($data['post_partner_id']);
        } else {
            $name = preg_replace('/( ?\(post\))?$/', ' (ping)', $data['name'], 1);
            $condition = " post_partner_id = " . mysql_real_escape_string($id);
        }
        
        $sql = "UPDATE $this->tableName "
             . "SET name = '" . mysql_real_escape_string($name) . "' "
                . ",posting_type = '" . mysql_real_escape_string($data['posting_type']) . "' "
                . ",xml_field_name = '" . mysql_real_escape_string($data['xml_field_name']) . "' "
             . "WHERE " . $condition;
         
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------
    
    public function removePingPostSuffix($partnerList) 
    {
        foreach ($partnerList as $index => $partner) {
            if (gettype($partner) === 'array' && isset($partner['name'])) {
                $partnerList[$index]['name'] = preg_replace('/ ?\((ping|post)\)$/', '', $partner['name']);
            } else if (gettype($partner) === 'string') {
                $partnerList[$index] = preg_replace('/ ?\((ping|post)\)$/', '', $partner);
            }   
        }
        return $partnerList;
    }
    // -------------------------------------------------------------------------
    
    
    public static function getTimeFrameStart($capTypeName, $upToTimestamp, $timezone = null, $cap = null) {
        switch ($capTypeName) {
            case Model_Partner::DELIVERY_CTYPE_DAY_OF_WEEK:
                return Model_Partner::getDayOfWeekCondition($cap, time(), $timezone);
            case Model_Partner::DELIVERY_CTYPE_TIME_OF_DAY:
                return Model_Partner::getTimeOfDayCondition($cap, time(), $timezone);
            case Model_Partner::DELIVERY_CTYPE_HOUR:
                return "DATE_SUB($upToTimestamp, INTERVAL 1 HOUR)";
            case Model_Partner::DELIVERY_CTYPE_DAY:
                return "DATE_SUB($upToTimestamp, INTERVAL 1 DAY)";
            case Model_Partner::DELIVERY_CTYPE_WEEK:
                return "DATE_SUB($upToTimestamp, INTERVAL 1 WEEK)";
            case Model_Partner::DELIVERY_CTYPE_MONTH:
                return "DATE_SUB($upToTimestamp, INTERVAL 1 MONTH)";
            case Model_Partner::DELIVERY_CTYPE_EVER:
                return "DATE_SUB($upToTimestamp, INTERVAL 1 YEAR)";
            case Model_Partner::DELIVERY_CTYPE_HOUR_CALENDAR:
                return  "'" . date('Y-m-d H:00:00') . "'";
            case Model_Partner::DELIVERY_CTYPE_DAY_CALENDAR:
                return "'" . Model_Partner::getDayStart(time(), $timezone) . "'";
            case Model_Partner::DELIVERY_CTYPE_WEEK_CALENDAR:
                return "'" . Model_Partner::getWeekStart(time(), $timezone) . "'";
            case Model_Partner::DELIVERY_CTYPE_MONTH_CALENDAR:
                return "'" . Model_Partner::getMonthStart(time(), $timezone) . "'";
            default:
                return "";
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function listPartnersWithInvalidTemplate($userId) {
        $postingTypesWithTemplate = implode(',', array(self::POST_TYPE_XML, self::POST_TYPE_JSON, self::POST_TYPE_XML_FIELD));
        
        $sql =    "SELECT * "
                . "FROM `$this->tableName` "
                . "WHERE `user_id` = ? "
                . "AND `posting_type` IN ($postingTypesWithTemplate) "
                . "AND `valid_template` = ? ";
        
        return $this->db->getArray($sql, array($userId, self::TEMPLATE_INVALID));
    }
    //--------------------------------------------------------------------------
    
    
    public function listPartnersWithExistingCaps() {
        
        $sql =    "SELECT * "
                . "FROM `$this->tableName` "
                . "WHERE `user_id` IS NOT NULL "
                . "AND `wrapped_campaign_id` IS NULL "
                . "AND delivery_cap != '0' ";
        
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------
    
    
    public function countLockedPartner($partnerIds) {
        $sql =    "SELECT count(*) "
                . "FROM `$this->tableName` "
                . "WHERE `id` IN (" . implode(',', $partnerIds) . ") "
                . "AND `lock_status` = '1' "
                . "AND lock_time > DATE_SUB(NOW(), INTERVAL 2 MINUTE) "
                . "AND " . Engine_Utilities::getQueryRandomModifier();
        $count = $this->db->getTopLeft($sql);
        return intval($count);
    }
    //--------------------------------------------------------------------------
    
    
    public function lockPartner($partnerId) {
        if ($this->countLockedPartner(array($partnerId)) > 0) {
            return false;
        }
        
        $threadId = mt_rand(1, 10000);
        
        $sql =   "UPDATE `$this->tableName` "
                . "SET `lock_status`    = '1', "
                . "    `lock_time`      = NOW(), "
                . "    `lock_thread_id` = " . $threadId
                . " WHERE `id` = '$partnerId'";
        
        $this->db->query($sql);
        
        // check if current thread is the last thread that lock the partner
        usleep(100000);
        $sql =    " SELECT * "
                . " FROM `$this->tableName`"
                . " WHERE `id` = " . $partnerId
                . " AND `lock_status` = '1' "
                . " AND `lock_thread_id` = " . $threadId
                . " AND " . Engine_Utilities::getQueryRandomModifier();
        $result = $this->db->getArray($sql);
        return (count($result) !== 0);
    }
    //--------------------------------------------------------------------------
    
    
    public function unlockPartners($partnerIds) {
        if (empty($partnerIds) || !is_array($partnerIds)) {
            return false;
        }
        
        $sql =   "UPDATE `$this->tableName` "
                . "SET `lock_status` = '0' "
                . "WHERE `id` IN (" . implode(',', $partnerIds) . ')';
        
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------
    
    
    public function listPartnersWithIDs($ids, $getPingPostPartner = null) {
        if (empty($ids)) {
            return array();
        }
        
        $sql = "SELECT * FROM `$this->tableName` WHERE `id` IN (" . self::implodeInts($ids) . ")";
        if (!empty($getPingPostPartner)) {
            $sql .= " AND `ping_post_type` = '" . self::PARTNER_TYPE_PING_POST . "'";
        }
        $result = $this->db->getArray($sql);
        return $result;
    }
    //--------------------------------------------------------------------------
}

