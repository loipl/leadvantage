<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestInferingHeight extends MyUT {

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    public function setUp() {
        parent::setUp();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemMobileFromBrowser() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'mobile', 'value' => '[:mobile_lead:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "mobile" => "N"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     // ------------------------------------------------------------------------
        
    public function testInferringSystemMobileFromMobile() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_MOBILE, 'mobile_lead');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'mobile_lead', 'value' => '[:mobile_lead:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'mobile_lead' => 'y'),
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "mobile_lead" => "Y"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    

    // ------------------------------------------------------------------------
        
    public function testInferringSystemMobileRegex() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_MOBILE, 'mobile_lead');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'mobile_lead', 'value' => '[:mobile_lead:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'mobile_lead' => 'abc'),
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "mobile_lead" => "N"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
}
