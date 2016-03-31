<?php

/**
 * @desc Common data shared by all Engine classes
 *
 */
class Engine_Data {

    /**
     * @var Model_Campaign
     */
    public $model;

    /**
     * @var Model_Partner
     */
    public $modelPartner;

    /**
     * @var Model_PartnerField
     */
    public $modelPartnerField;

    /**
     * @var Model_PartnerFilter
     */
    public $modelPartnerFilter;

    /**
     * @var Model_Dictionary
     */
    public $modelDictionary;

    /**
     * @var Model_DictionaryColumn
     */
    public $modelDictionaryColumn;

    /**
     * @var Model_DictionaryValue
     */
    public $modelDictionaryValue;

    /**
     * @var Model_Profile
     */
    public $modelProfile;

    /**
     * @var Model_CampaignField
     */
    public $modelCampaignFields;

    /**
     * @var Model_User
     */
    public $modelUser;

    /**
     * @var Model_LogIncoming
     */
    public $logIncoming;

    /**
     * @var Model_LogIncomingRepost
     */
    public $logIncomingRepost;

    /**
     * @var Model_LogDelivery
     */
    public $logDelivery;
    
    /**
     * @var Model_LogPing
     */
    public $modelLogPing;

    /**
     * @desc array (ftId => ftName)
     */
    public $fieldTypes = array();

    /**
     * @desc array (ftId => strtolower(ftName))
     */
    public $fieldTypesLCase = array();

    /**
     * @desc array (ftId => $rowFromDbForThatid)
     */
    public $fieldTypesFull = array();

    /**
     * @desc US states in CA => California form
     */
    public $statesAssoc = array();

    /**
     * @desc Tokens (like magic vars) will be replaced in outgoing data with their value.
     * These are the ones that are derived from others, like GeoIP values etc
     */
    public $derivedTokens = array();

    /**
     * @desc Tokens that can be overridden with user-supplied data. This is used for instance
     * if we figure out ZIP from IP address but user supplied his zip code then we override the token
     */
    public $overridableTokens = array();

    /**
     * @desc array of Field_Type_Id => data from submitted form. Not that it is keyed
     * on field type, not on the name from original form
     */
    public $inputData = array();

    /**
     * @desc Capabilities from WordPress for current user. Current user is the owner of campaign
     * we are processing right now. This will have string like "administrator" or "subscriber"
     */
    public $ownerUserCaps = array();

    /**
     * @desc Field types used by this campaign, ft_id => ft_name
     */
    public $usedFieldTypes = array();

    /**
     * @desc Can Validate Field Types - array of field types (int) that user
     * can validate with validation API
     *
     * @var array
     */
    public $cvFTypes = array();

    public $profileId = 0;

    /**
     * @desc Whether submission was accepted, 0 or 1
     */
    public $incomingStatus;

    /**
     * @desc When form is submitted we write the data to DB, this var will hold primary key of that row
     */
    public $incomingId;

    /**
     * @desc This is the raw incoming data, keyed on incoming form field names. It is only used for reposting
     */
    public $incRow = false;

    /**
     * @desc The timestamp with which submission is written to DB, in SQL format
     */
    public $incomingTimestamp = '';

    /**
     * @desc assoc array, how many times have we tried delivering to each partner
     */
    public $attemptsPerPartnerAssoc = array();

    /**
     * @desc Last partner that we sent the data to
     */
    public $lastPartnerDeliveredTo = array();

    /**
     * @desc last data (assoc array) that we sent to a partner
     */
    public $lastDataDeliveredToPartner = array();

    public $deliveryIDs = array();

    /**
     * @desc Full appended list of wrapper success url returns
     */
    public $successUrlAppend = '';

    /**
     * @desc Most recent successful wrapper success url
     */
    public $successUrl = '';

    /**
     * @desc Stores overall (any) partner success true/false
     */
    public $success = false;


    public function __construct() {
        $this->model               = SingletonRegistry::getModelCampaign();
        $this->modelUser           = SingletonRegistry::getModelUser();
        $this->modelPartner        = SingletonRegistry::getModelPartner();
        $this->modelPartnerField   = SingletonRegistry::getModelPartnerField();
        $this->modelPartnerFilter  = SingletonRegistry::getModelPartnerFilter();
        $this->logIncoming         = SingletonRegistry::getModelLogIncoming();
        $this->logIncomingRepost   = SingletonRegistry::getModelLogIncomingRepost();
        $this->logDelivery         = SingletonRegistry::getModelLogDelivery();
        $this->modelProfile        = SingletonRegistry::getSingleInstance('Model_Profile');
        $this->modelDictionary     = SingletonRegistry::getSingleInstance('Model_Dictionary');
        $this->modelLogPing        = SingletonRegistry::getSingleInstance('Model_LogPing');

        $this->modelDictionaryColumn = SingletonRegistry::getSingleInstance('Model_DictionaryColumn');
        $this->modelDictionaryValue  = SingletonRegistry::getSingleInstance('Model_DictionaryValue');
        $this->modelCampaignFields   = SingletonRegistry::getModelCampaignField();

        /* @var $mft Model_FieldType */
        $mft = SingletonRegistry::getSingleInstance('Model_FieldType');
        $this->fieldTypesFull  = $mft->listAssoc();
        $this->fieldTypes      = array();
        $this->fieldTypesLCase = array();
        foreach ($this->fieldTypesFull as $id => $row) {
            $this->fieldTypes[$id]      = $row['name'];
            $this->fieldTypesLCase[$id] = strtolower($row['name']);
        }
        DerivedField_Registry::$fieldTypesAssoc = $this->fieldTypesLCase;

        /* @var $mus Model_UsState */
        $mus = SingletonRegistry::getSingleInstance('Model_UsState');
        $this->statesAssoc = $mus->listStatesAssoc();

        $this->derivedTokens      = DerivedField_Registry::listTokens();
        $this->overridableTokens  = DerivedField_Registry::listOverridableTokens();
    }
    //--------------------------------------------------------------------------


    public function isCountryUSA() {
        if (!empty($this->inputData[Model_CampaignField::FIELD_TYPE_COUNTRY])) {
            $country  = trim(strtolower($this->inputData[Model_CampaignField::FIELD_TYPE_COUNTRY]));
            $usaNames = array('america', 'united states', 'united states of america');
            $isUSA = in_array($country, $usaNames);
            return $isUSA;
        }
        if (!empty($this->inputData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE])) {
            return (strtolower($this->inputData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE]) == 'us');
        }

        $location = SingletonRegistry::getModelGeoipLocation()->getLocationForIP($_SERVER['REMOTE_ADDR']);
        if ($location) {
            $isUSA = ($location['country'] == 'US');
        } else {
            $isUSA = true;
        }
        return $isUSA;
    }
    //--------------------------------------------------------------------------
}
