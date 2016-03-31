<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestInferingValues extends MyUT {

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    public function setUp() {
        parent::setUp();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------


    public function testInferringSystemFieldNameFromFullname() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_FULL_NAME, 'full_name');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'first_name', 'value' => '[:first_name:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'middle_name', 'value' => '[:middle_name:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'last_name', 'value' => '[:last_name:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'first_initial', 'value' => '[:first_initial:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'middle_initial', 'value' => '[:middle_initial:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'last_initial', 'value' => '[:last_initial:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe2@yahoo.com', 'full_name' => 'Lionel Brockman Richie, Jr'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "first_name" => "Lionel",
                "middle_name" => "Brockman",
                "last_name" => "Richie, Jr",
                "first_initial" => "L",
                "middle_initial" => "B",
                "last_initial" => "R"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    //--------------------------------------------------------------------------

    public function testInferringSystemFieldFullnameFromName() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_FIRST_NAME, 'first_name');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_MIDDLE_NAME, 'middle_name');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_LAST_NAME, 'last_name');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'full_name', 'value' => '[:full_name:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'joe3@yahoo.com',
                'first_name' => "Pham",
                'middle_name' => "Le",
                'last_name' => "Loi"
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->gathered[0]["full_name"], "Pham Le Loi");
        }
    }
    //--------------------------------------------------------------------------
    
     public function testInferringSystemCellPhoneFromCellPhone() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE, 'cell_phone');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'cell_phone_area_code', 'value' => '[:cell_phone_area_code:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'cell_phone_prefix', 'value' => '[:cell_phone_prefix:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'cell_phone_suffix', 'value' => '[:cell_phone_suffix:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'cell_phone' => '800-555-5555'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "cell_phone_area_code" => "800",
                "cell_phone_prefix" => "555",
                "cell_phone_suffix" => "5555"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemWorkPhoneFromWorkPhone() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WORK_PHONE, 'work_phone');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'work_phone_area_code', 'value' => '[:work_phone_area_code:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'work_phone_prefix', 'value' => '[:work_phone_prefix:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'work_phone_suffix', 'value' => '[:work_phone_suffix:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'work_phone' => '(123) 456-7890 '), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "work_phone_area_code" => "123",
                "work_phone_prefix" => "456",
                "work_phone_suffix" => "7890"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldCellPhoneFromCellPhoneAttributes() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE, 'cell_phone_area_code');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX, 'cell_phone_prefix');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX, 'cell_phone_suffix');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'cell_phone', 'value' => '[:cell_phone:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com',
                'cell_phone_area_code' => "800",
                'cell_phone_prefix' => "555",
                'cell_phone_suffix' => "5555"
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->gathered[0]["cell_phone"], "800-555-5555");
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldWorkPhoneFromWorkPhoneAttributes() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WORK_PHONE_AREA_CODE, 'work_phone_area_code');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WORK_PHONE_PREFIX, 'work_phone_prefix');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WORK_PHONE_SUFFIX, 'work_phone_suffix');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'work_phone', 'value' => '[:work_phone:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com',
                'work_phone_area_code' => "800",
                'work_phone_prefix' => "555",
                'work_phone_suffix' => "5555"
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->gathered[0]["work_phone"], "800-555-5555");
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldPhoneFromPhone() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_PHONE, 'phone');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone', 'value' => '[:phone:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_area_code', 'value' => '[:phone_area_code:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_prefix', 'value' => '[:phone_prefix:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_suffix', 'value' => '[:phone_suffix:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'phone' => '800-555-5555'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "phone" => "800-555-5555",
                "phone_area_code" => "800",
                "phone_prefix" => "555",
                "phone_suffix" => "5555"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldPhoneFromCellPhone() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE, 'cell_phone');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone', 'value' => '[:phone:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_area_code', 'value' => '[:phone_area_code:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_prefix', 'value' => '[:phone_prefix:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone_suffix', 'value' => '[:phone_suffix:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'cell_phone' => '800-555-5555'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "phone" => "800-555-5555",
                "phone_area_code" => "800",
                "phone_prefix" => "555",
                "phone_suffix" => "5555"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldPhoneFromPhoneAttributes() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_PHONE_AREA_CODE, 'phone_area_code');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_PHONE_PREFIX, 'phone_prefix');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_PHONE_SUFFIX, 'phone_suffix');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone', 'value' => '[:phone:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com',
                'phone_area_code' => "800",
                'phone_prefix' => "555",
                'phone_suffix' => "5555"
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->gathered[0]["phone"], "800-555-5555");
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldPhoneFromCellPhoneAttributes() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE, 'cell_phone_area_code');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX, 'cell_phone_prefix');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX, 'cell_phone_suffix');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'phone', 'value' => '[:phone:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com',
                'cell_phone_area_code' => "800",
                'cell_phone_prefix' => "555",
                'cell_phone_suffix' => "5555"
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->gathered[0]["phone"], "800-555-5555");
        }
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemFieldUsernameFromEmail() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'uname', 'value' => '[:username:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('joe', $this->gathered[0]['uname']);
        }
    }
    //--------------------------------------------------------------------------


    public function testInferringSystemFieldUsernameFromUsername() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_USERNAME, 'username');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'uname', 'value' => '[:username:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'username' => 'jack'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('jack', $this->gathered[0]['uname']);
        }
    }
    //--------------------------------------------------------------------------


    public function testInferingWindowsVersionFromUserAgent() {
        $oldUa = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)';

        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'system_field_os', 'value' => '[:operating_system:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('Windows 7', $this->gathered[0]['system_field_os']);
        }

        $this->gathered = array();
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2)';
        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('Windows 8', $this->gathered[0]['system_field_os']);
        }

        if ($oldUa) {
            $_SERVER['HTTP_USER_AGENT'] = $oldUa;
        } else {
            unset($_SERVER['HTTP_USER_AGENT']);
        }
    }
    //--------------------------------------------------------------------------


    public function testInfering() {
        SetupTestData::deleteEverythingForTestUser();
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC20']);
        $_SERVER['REMOTE_ADDR'] = '69.64.34.113';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'utc20@unit-tests.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertTrue(preg_match('/^[0-9]{5}$/', $this->lastData['test_zipcode']));
        }
    }
    //--------------------------------------------------------------------------


    public function testInferringCountryIsUSViaCountryCode() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_COUNTRY_CODE, 'cc');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_POSTAL_CODE,  'zip');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'system_field_country', 'value' => '[:country:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'cc' => 'US', 'zip' => 90210), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('United States', $this->gathered[0]['system_field_country']);
        }
    }
    //--------------------------------------------------------------------------


    public function testInferringCountryIsUSViaCountryName() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_COUNTRY, 'country');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_POSTAL_CODE,  'zip');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'system_field_country', 'value' => '[:country:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'country' => 'America', 'zip' => 90210), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('America', $this->gathered[0]['system_field_country']);
        }
    }
    //--------------------------------------------------------------------------

}
