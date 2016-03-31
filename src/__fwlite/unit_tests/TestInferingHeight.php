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
    
    /**
     * campaign input centimeter = 182cm
     * delivery to partner value meters = 1m, centimeters = 82cm.
     */
    public function testInferringSystemHeight_182cm_1m_82cm() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS, 'height_centimeters');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_meters', 'value' => '[:height_meters:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_centimeters', 'value' => '[:height_centimeters:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'height_centimeters' => '182'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_meters" => "1",
                "height_centimeters" => "82"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     
     // ------------------------------------------------------------------------
        
     /**
      * Input 200 cm
      * Delivery 2m 0cm
      */
    public function testInferringSystemHeight_200cm_2m_0cm() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS, 'height_centimeters');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_meters', 'value' => '[:height_meters:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_centimeters', 'value' => '[:height_centimeters:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'loiphamle@gmail.com', 'height_centimeters' => '200'), 
                                                    $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_meters" => "2",
                "height_centimeters" => ""
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
    }
    

    //--------------------------------------------------------------------------
    
    /**
     * input 1m82cm
     * delivery 182cm
     */
   public function testInferringSystemHeight_1m_82cm_182cm() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_METERS, 'height_meters');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS, 'height_centimeters');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_centimeters', 'value' => '[:height_centimeters:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com', 
                'height_meters' => '1',
                'height_centimeters' => '82'
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_centimeters" => "182"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     
     // ------------------------------------------------------------------------
     
     /**
      * input 1m82cm
      * delivery 1m82cm
      */
    public function testInferringSystemHeight_1m_82cm_1m_82cm() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_METERS, 'height_meters');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS, 'height_centimeters');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_meters', 'value' => '[:height_meters:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_centimeters', 'value' => '[:height_centimeters:]')); 

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com', 
                'height_meters' => '1',
                'height_centimeters' => '82'
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_meters" => "1",
                "height_centimeters" => "82"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
   //--------------------------------------------------------------------------
     
     /**
      * input 28 inches
      * delivery 2 feet 2 inches
      */
   public function testInferringSystemHeight_28I_2F_4I() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_INCHES, 'height_inches');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_feet', 'value' => '[:height_feet:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_inches', 'value' => '[:height_inches:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com', 
                'height_inches' => '28'
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_inches" => "4",
                "height_feet" => "2"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     // ------------------------------------------------------------------------
    
     /**
      * input 2 feet 2 inches
      * delivery 26 inches
      */
   public function testInferringSystemHeight_2F_2I_26I() {
        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_FEET, 'height_feet');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_HEIGHT_INCHES, 'height_inches');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'height_inches', 'value' => '[:height_inches:]'));
        

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $campaignData = array(
                'email' => 'loiphamle@gmail.com', 
                'height_feet' => '2',
                'height_inches' => '2'
            );
            $engine = $this->newEngineSubmission($campaignData, $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $deliveryValues = array(
                "height_inches" => "26"
            );
            $this->assertEqual($this->gathered[0], $deliveryValues);
        }
     }
     // ------------------------------------------------------------------------
}
