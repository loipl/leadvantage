<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestInferingWeight extends MyUT {

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    public function setUp() {
        parent::setUp();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------
    
    public function testInferringSystemWeightFromWeightKGS() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WEIGHT_KGS, 'weight_kgs');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'weight_kgs', 'value' => '[:weight_kgs:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'weight_lbs', 'value' => '[:weight_lbs:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'weight_kgs' => '200'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "weight_kgs" => "200",
                "weight_lbs" => "440.92"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     // ------------------------------------------------------------------------
     
     public function testInferringSystemWeightFromWeightLBS() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WEIGHT_LBS, 'weight_lbs');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'weight_kgs', 'value' => '[:weight_kgs:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'weight_lbs', 'value' => '[:weight_lbs:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'weight_lbs' => '200'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "weight_kgs" => "90.72",
                "weight_lbs" => "200"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     // ------------------------------------------------------------------------
}
