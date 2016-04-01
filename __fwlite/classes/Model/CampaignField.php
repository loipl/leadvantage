<?php

class Model_CampaignField extends CrudModelCaching {

    protected $t_campaign_fields;

    protected $t_campaign_fields_shadow_validation;

    protected $t_campaigns;

    protected $t_field_types;

    protected $defaultArguments = array (
        'listAllForCampaign' => array (
            1 => 'id',
        ),
    );
    
    const DUMMY_EMAIL                    = 'dummpy@gmail.com';

    const FIELD_TYPE_OTHER               = 1;
    const FIELD_TYPE_EMAIL               = 2;
    const FIELD_TYPE_PHONE               = 3;
    const FIELD_TYPE_MONTH               = 4;
    const FIELD_TYPE_DAY                 = 5;
    const FIELD_TYPE_YEAR                = 6;
    const FIELD_TYPE_FIRST_NAME          = 7;
    const FIELD_TYPE_LAST_NAME           = 8;
    const FIELD_TYPE_WEBSITE_URL         = 10;
    const FIELD_TYPE_MIDDLE_NAME         = 11;
    const FIELD_TYPE_MIDDLE_INITIAL      = 12;
    const FIELD_TYPE_FIRST_INITIAL       = 13;
    const FIELD_TYPE_LAST_INITIAL        = 14;
    const FIELD_TYPE_GENDER              = 18;
    const FIELD_TYPE_USERNAME            = 19;
    const FIELD_TYPE_COUNTRY             = 20;
    const FIELD_TYPE_COUNTRY_CODE        = 21;
    const FIELD_TYPE_POSTAL_CODE         = 22;
    const FIELD_TYPE_IP_ADDRESS          = 24;
    const FIELD_TYPE_STATE               = 25;
    const FIELD_TYPE_CAMPAIGN            = 26;
    const FIELD_TYPE_CITY                = 27;
    const FIELD_TYPE_STATE_CODE          = 28;
    const FIELD_TYPE_AGE                 = 30;
    const FIELD_TYPE_SUBMIT_DATE         = 31;
    const FIELD_TYPE_ADDRESS_1           = 33;
    const FIELD_TYPE_FULL_NAME           = 46;
    const FIELD_TYPE_EDUCATION           = 43;
    const FIELD_TYPE_HOME_OWNER          = 39;
    const FIELD_TYPE_YEARS_AT_ADDRESS    = 121;
    const FIELD_TYPE_MONTHS_AT_ADDRESS   = 72;
    const FIELD_TYPE_MARRIED             = 38;
    const FIELD_TYPE_OCCUPATION          = 44;
    const FIELD_TYPE_CHILDREN            = 119;
    const FIELD_TYPE_HOME_VALUE          = 120;
    const FIELD_TYPE_ANNUAL_INCOME       = 118;
    const FIELD_TYPE_MONTHLY_INCOME      = 66;
    const FIELD_TYPE_INTERESTS           = 122;
    const FIELD_TYPE_HIGH_NET_WORTH      = 123;
    const FIELD_TYPE_CELL_PHONE          = 64;
    const FIELD_TYPE_CELL_PHONE_AREA_CODE= 112;
    const FIELD_TYPE_CELL_PHONE_PREFIX   = 113;
    const FIELD_TYPE_CELL_PHONE_SUFFIX   = 114;
    const FIELD_TYPE_PHONE_AREA_CODE     = 47;
    const FIELD_TYPE_PHONE_PREFIX        = 48;
    const FIELD_TYPE_PHONE_SUFFIX        = 49;
    const FIELD_TYPE_WORK_PHONE          = 63;
    const FIELD_TYPE_WORK_PHONE_AREA_CODE= 109;
    const FIELD_TYPE_WORK_PHONE_PREFIX   = 110;
    const FIELD_TYPE_WORK_PHONE_SUFFIX   = 111;
    const FIELD_TYPE_HEIGHT_FEET         = 90;
    const FIELD_TYPE_HEIGHT_METERS       = 91;
    const FIELD_TYPE_HEIGHT_INCHES       = 92;
    const FIELD_TYPE_HEIGHT_CENTIMETERS  = 93;
    const FIELD_TYPE_WEIGHT_LBS          = 88;
    const FIELD_TYPE_WEIGHT_KGS          = 89;
    const FIELD_TYPE_MOBILE              = 166;
    const FIELD_TYPE_LEAD_ID             = 167;

    const SYS_FIELD_IPADDRESS    = ':ip_address:';
    const SYS_FIELD_SUBMIT_YEAR  = ':submit_year:';
    const SYS_FIELD_SUBMIT_MONTH = ':submit_month:';
    const SYS_FIELD_SUBMIT_DAY   = ':submit_day:';
    const SYS_FIELD_SUBMIT_DATE  = ':submit_date:';
    const SYS_FIELD_SUBMIT_TIME  = ':submit_time:';
    const SYS_FIELD_USER_AGENT   = ':user_agent:';
    const SYS_FIELD_OS           = ':operating_system:';
    const SYS_FIELD_BROWSER      = ':web_browser:';
    const SYS_FIELD_MOBILE       = ':mobile_lead:';
    const SYS_FIELD_LIVE         = ':live_lead:';
    const SYS_LEAD_ID            = ':lead_id:';
    const SYS_TRANSACTION_ID     = ':transaction_id:';
    const SYS_FIELD_SUBMIT_DAY_OF_WEEK = ':submit_day_of_week:';
    const SYS_FIELD_BID_PRICE_INDEX = 14;
    const SYS_FIELD_BID_PRICE    = ':bid_price:';
    
    const MANDATORY_NONE         = 0;
    const MANDATORY_BOTH         = 1;
    const MANDATORY_PING         = 2;
    const MANDATORY_POST         = 3;

    const SETTING_VERIFY_FIELD   = 'verify_field';

    protected static $systemFields = array(
        1  => self::SYS_FIELD_IPADDRESS,
        2  => self::SYS_FIELD_SUBMIT_TIME,
        3  => self::SYS_FIELD_USER_AGENT,
        4  => self::SYS_FIELD_OS,
        5  => self::SYS_FIELD_BROWSER,
        6  => self::SYS_LEAD_ID,
        7  => self::SYS_FIELD_SUBMIT_DATE,
        8  => self::SYS_FIELD_SUBMIT_DAY,
        9  => self::SYS_FIELD_SUBMIT_MONTH,
        10 => self::SYS_FIELD_SUBMIT_YEAR,
        11 => self::SYS_FIELD_MOBILE,
        12 => self::SYS_FIELD_LIVE,
        13 => self::SYS_FIELD_SUBMIT_DAY_OF_WEEK,
        self::SYS_FIELD_BID_PRICE_INDEX => self::SYS_FIELD_BID_PRICE
    );


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_campaign_fields);

        $this->zeroOneFields = array();
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
    public function listValidationOverrides($shadowId) {
        $sql = "SELECT `field_id`, `validate` FROM `$this->t_campaign_fields_shadow_validation` WHERE `shadow_id` = ? ORDER BY 1";
        return $this->db->getArrayAssoc($sql, array((int)$shadowId));
    }
    //--------------------------------------------------------------------------


    public function addValidationOverride($shadowId, $fieldId) {
        $sql = "INSERT IGNORE INTO `$this->t_campaign_fields_shadow_validation` (`shadow_id`, `field_id`, `validate`) VALUES (?, ?, '1')";
        $this->db->query($sql, array((int)$shadowId, (int)$fieldId));
    }
    //--------------------------------------------------------------------------


    public function removeValidationOverride($shadowId, $fieldId) {
        $sql = "DELETE FROM `$this->t_campaign_fields_shadow_validation` WHERE `shadow_id` = ? AND `field_id` = ?";
        $this->db->query($sql, array((int)$shadowId, (int)$fieldId));
    }
    //--------------------------------------------------------------------------


    public function removeAllValidationOverrides($shadowId) {
        $sql = "DELETE FROM `$this->t_campaign_fields_shadow_validation` WHERE `shadow_id` = ?";
        $this->db->query($sql, array((int)$shadowId));
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listFieldTypesAssoc() {
        return $this->db->getArrayAssoc("SELECT `id`, `name` FROM `$this->t_field_types` ORDER BY `name`");
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function getFieldIdFromName($fieldName) {
        // Handle our non-standard field/token translations (token names don't match field names)
        switch($fieldName) {
            case 'state_or_region' :
                return '25';
            break;

            case 'state_or_region_code' :
                return '28';
            break;
        }

        $sql = "SELECT `id` FROM `$this->t_field_types` WHERE UPPER(`name`) = UPPER(?)";
        return $this->db->getTopLeft($sql, array($fieldName));
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listFieldTypesAssocFull() {
        return $this->db->getArrayIndexed("SELECT * FROM `$this->t_field_types` ORDER BY `name`", array(), 'id');
    }
    //--------------------------------------------------------------------------


    public function listSystemFields() {
        $arr = self::$systemFields;
        unset($arr[3]);
        $arr = $arr + SingletonRegistry::getModelGeoipLocation()->listGeoipFields();
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function listSystemFieldsFull() {
        $arr = self::$systemFields;
        unset($arr[3]);
        $arr = $arr + SingletonRegistry::getModelGeoipLocation()->listGeoipFieldsFull();
        return $arr;
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listFieldTypesForUser($userId) {
        $sql = "
      SELECT
        DISTINCT `cf`.`field_type`
      FROM
        `$this->t_campaign_fields` AS `cf`, `$this->t_campaigns` AS `c`
      WHERE
        `c`.`user_id` = ? AND
        `c`.`id`      = `cf`.`campaign_id`
      ORDER BY 1";
        return $this->db->getArray1v($sql, array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public static function listBuiltInSystemFields() {
        return self::$systemFields;
    }
    //--------------------------------------------------------------------------
    
    
    public function insertLeadIdForPingAndPostCampaign ($campaignId) 
    {
        $data = array (
            'campaign_id' => $campaignId,
            'name'        => 'lead_id',
            'description' => 'Lead ID',
            'field_type'  => self::FIELD_TYPE_LEAD_ID,
            'is_mandatory'=> self::MANDATORY_POST
        );
        
        return $this->insert($data, true);
    }
    //--------------------------------------------------------------------------
    
    
    public function deleteLeadIdOfPingAndPostCampaign ($campaignId) 
    {
        $data = array (
            'campaign_id' => $campaignId,
            'field_type'  => self::FIELD_TYPE_LEAD_ID
        );
        
        return $this->deleteWhere($data);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkFieldMatch($campaign, $fields) {
        
        $campaignFieldNames     = array ();
        $campaignFieldNames[]   = $campaign['email_field_name'];
        
        $campaignFields         = $this->listAllForCampaign($campaign['id']);
        foreach ($campaignFields as $campaignField) {
            $campaignFieldNames[] = $campaignField['name'];
        }
        
        $mismatchFields     = array();
        foreach ($fields as $field) {
            if (!in_array($field, $campaignFieldNames) && trim($field) !== '') {
                $mismatchFields[] = $field;
            }
        }

        if (empty($mismatchFields)) {
            return true;
        } else {
            return "This campaign doesn't contain these fields: " . implode(',', $mismatchFields);
        }
    }
    // -------------------------------------------------------------------------
    
    
    public function getEmailFieldName ($campaignId) {
        $sql = "SELECT `email_field_name` FROM `campaigns` WHERE id = ?";
        return $this->db->getTopLeft($sql, array($campaignId));
    }
    // -------------------------------------------------------------------------
    
    /**
     * Sort campaign field in order ping - both - post - none 
     * to make it easier to see in apiDetail page
     * @param type $field1
     * @param type $field2
     */
    public static function sortFields($field1, $field2) {
        if ($field1['is_mandatory'] == self::MANDATORY_PING) {
            return -1;
        } else if ($field2['is_mandatory'] == self::MANDATORY_PING) {
            return 1;
        }
        
        if ($field1['is_mandatory'] == self::MANDATORY_BOTH) {
            return -1;
        } else if ($field2['is_mandatory'] == self::MANDATORY_BOTH) {
            return 1;
        }
        
        if ($field1['is_mandatory'] == self::MANDATORY_POST) {
            return -1;
        } else if ($field2['is_mandatory'] == self::MANDATORY_POST) {
            return 1;
        }
        
        return -1;
    }
}
