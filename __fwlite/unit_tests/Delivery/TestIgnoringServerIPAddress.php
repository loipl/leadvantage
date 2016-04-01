<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

class TestIgnoringServerIPAddress extends MyUT {

    private $oldIPs     = array();
    private $campaignId = 0;
    private $email      = 'joe@yahoo.com';
    private $testIP     = '85.222.222.234';

    /**
     * @var Model_Profile
     */
    private $modelProfile;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;


    public function __construct() {
        parent::__construct();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $this->modelProfile  = SingletonRegistry::getSingleInstance('Model_Profile');

    }
    //--------------------------------------------------------------------------


    public function setUp() {
        parent::setUp();
        $this->oldIPs = Config::$serverIPAddresses;
        Config::$serverIPAddresses = array('127.0.0.1');

        SetupTestData::resetCampaign('UTC00');
        $this->campaignId = SetupTestData::$cids['UTC00'];

		$this->modelProfile->deleteWhere(array('email' => $this->email));
    }
    //--------------------------------------------------------------------------


    public function tearDown() {
        parent::tearDown();
        Config::$serverIPAddresses = $this->oldIPs;
		$this->modelProfile->deleteWhere(array('email' => $this->email));
    }
    //--------------------------------------------------------------------------


    public function testProcessSubmissionWorksAsExpected() {
		$this->processSubmission($this->testIP);

        $profile = $this->modelProfile->getWhere(array('email' => $this->email));

        $this->assertEqual($this->testIP, long2ip($profile['last_ip']));
        $this->assertEqual($this->email,  $profile['email']);

        $inferred = $this->modelProfile->getProfileInferredDataForEmail($this->email);
        $this->assertEqual($this->testIP, $inferred['ip_address']);
    }
    //--------------------------------------------------------------------------


    public function testOverridingOldIpWorksWithEmptyConfigSetting() {
        Config::$serverIPAddresses = array();
        $this->processSubmission($this->testIP);
        $this->processSubmission('127.0.0.1');

        $inferred = $this->modelProfile->getProfileInferredDataForEmail($this->email);
        $this->assertEqual('127.0.0.1', $inferred['ip_address']);
    }
    //--------------------------------------------------------------------------


    public function testNotOverridingOldIpWithInvalidOne() {
        $this->processSubmission($this->testIP);
        $this->processSubmission('127.0.0.1');

        $inferred = $this->modelProfile->getProfileInferredDataForEmail($this->email);
        $this->assertEqual($this->testIP, $inferred['ip_address']);
    }
    //--------------------------------------------------------------------------


    public function testRetrievingOldIpWhenGatheringInputData() {
        $this->processSubmission($this->testIP);
        $this->processSubmission('127.0.0.1', $this->cbFinish, Engine::ACTION_GATHERED);

        $this->assertEqual($this->testIP, $_SERVER['REMOTE_ADDR']);
    }
    //--------------------------------------------------------------------------


    public function testGeoipUsesRestoredIp() {
        // US IP address, will be treated as that of our server, ie invalid
        Config::$serverIPAddresses = array('69.64.34.113');

        // First submission is with Serbian IP address
        $this->processSubmission($this->testIP);


        $partnerId = SetupTestData::$pids['C00-P01'];
        $modelPartnerField = SingletonRegistry::getModelPartnerField();
        $modelPartnerField->deleteForPartner($partnerId);
        $modelPartnerField->insert(array('partner_id' => $partnerId, 'name' => 'country', 'value' => '[:country_code:] [:country:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaign = $this->modelCampaign->get($this->campaignId);
            $engine = $this->newEngineSubmission(array('email' => $this->email), $this->cbFinish, Engine::ACTION_SENT_VALS);

            // Re submit with invalid US ip
            $_SERVER['REMOTE_ADDR'] = '69.64.34.113';
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            // Geoip should still use original IP
            $this->assertEqual('RS Serbia', $this->lastData['country']);
        }
    }
    //--------------------------------------------------------------------------


    public function testGeoipFieldsEmptyForInvalidAddress() {
        Config::$serverIPAddresses = array('69.64.34.113'); // US IP address

        $partnerId = SetupTestData::$pids['C00-P01'];
        $modelPartnerField = SingletonRegistry::getModelPartnerField();
        $modelPartnerField->deleteForPartner($partnerId);
        $modelPartnerField->insert(array('partner_id' => $partnerId, 'name' => 'country', 'value' => '[:country_code:] [:country:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $_SERVER['REMOTE_ADDR'] = '69.64.34.113';
            $campaign = $this->modelCampaign->get($this->campaignId);
            $engine = $this->newEngineSubmission(array('email' => $this->email), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            // Geoip should return empty strings because IP is invalid.
            // Comment out first line in this function and result below would be "US United States"
            $this->assertEqual(' ', $this->lastData['country']);
        }

        $inferred = $this->modelProfile->getProfileInferredDataForEmail($this->email);

        // IP address should not be written to inferred fields
        $this->assertTrue(empty($inferred['ip_address']));

        // Nor geoip fields
        $this->assertTrue(empty($inferred['country']));
        $this->assertTrue(empty($inferred['country_code']));
    }
    //--------------------------------------------------------------------------


    public function testExplicitIpAddressOverridesInvalidOne() {
        Config::$serverIPAddresses = array('69.64.34.113'); // US IP address

        $partnerId = SetupTestData::$pids['C00-P01'];
        $modelPartnerField = SingletonRegistry::getModelPartnerField();
        $modelPartnerField->deleteForPartner($partnerId);
        $modelPartnerField->insert(array('partner_id' => $partnerId, 'name' => 'country', 'value' => '[:country_code:] [:country:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_IP_ADDRESS, 'explicit_ip', true);

        try {
            $_SERVER['REMOTE_ADDR'] = '69.64.34.113';
            $campaign = $this->modelCampaign->get($this->campaignId);
            $engine = $this->newEngineSubmission(array('email' => $this->email, 'explicit_ip' => $this->testIP), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('RS Serbia', $this->lastData['country']);
        }

        $inferred = $this->modelProfile->getProfileInferredDataForEmail($this->email);

        $this->assertEqual($this->testIP, $inferred['ip_address']);

        $this->assertEqual('Serbia', $inferred['country']);
        $this->assertEqual('RS',     $inferred['country_code']);
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Submission
     */
    private function processSubmission($ipAddress, $callback = false, $waitAction = false) {
        $_SERVER['REMOTE_ADDR'] = $ipAddress;

        try {
            $campaign = $this->modelCampaign->get($this->campaignId);
            $engine = $this->newEngineSubmission(array('email' => $this->email), $callback, $waitAction);
            $engine->processIncomingFormSubmission($campaign);
        } catch (Exception $e) {
            //
        }
        return $engine;
    }
    //--------------------------------------------------------------------------
}
