<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

class TestXmlAndJsonDelivery extends MyUT {

    private $restoreTemplate = false;
    private $campaign;

    /**
     * @var Model_Partner
     */
    private $modelPartner;


    public function __construct() {
        parent::__construct();
        $this->modelPartner = SingletonRegistry::getModelPartner();
    }
    //--------------------------------------------------------------------------


    public function testXMLDelivery() {
        $this->modelPartner->setPartnerDeliveryTemplate(SetupTestData::$pids['C16-P1'], 'testing with %f1% and %f2%');
        $this->checkResponseMatchesExpected('UTC16', array('email' => 'ribicb@yahoo.com', 'first_name' => 'xxx'), 'testing with ribicb@yahoo.com and xxx');
    }
    //--------------------------------------------------------------------------


    public function testJSONDelivery() {
        $this->modelPartner->setPartnerDeliveryTemplate(SetupTestData::$pids['C35-P1'], 'testing with %f1% and %f2%');
        $this->checkResponseMatchesExpected('UTC35', array('email' => 'ribicb@yahoo.com', 'first_name' => 'xxx'), 'testing with ribicb@yahoo.com and xxx');
    }
    //--------------------------------------------------------------------------


    public function testJSONDeliveryWithQuotes() {
        $this->modelPartner->setPartnerDeliveryTemplate(SetupTestData::$pids['C35-P1'], 'testing with "%f1%" and "%f2%"');
        $this->checkResponseMatchesExpected('UTC35', array('email' => 'ribicb@yahoo.com', 'first_name' => 'xxx'), 'testing with "ribicb@yahoo.com" and "xxx"');
    }
    //--------------------------------------------------------------------------


    public function testJSONDeliveryWithQuotesTwoValues() {
        $this->modelPartner->setPartnerDeliveryTemplate(SetupTestData::$pids['C35-P1'], 'testing with "%f1% %f2%"');
        $this->checkResponseMatchesExpected('UTC35', array('email' => 'ribicb@yahoo.com', 'first_name' => 'xxx'), 'testing with "ribicb@yahoo.com xxx"');
    }
    //--------------------------------------------------------------------------


    public function testBirthdayFields() {
        SetupTestData::resetCampaign('UTC00');
//        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_DAY,   'bday');
//        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_MONTH, 'bmonth');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_YEAR,  'byear');

        $this->modelPartner->setPartnerDeliveryTemplate(SetupTestData::$pids['C16-P2'], '<value>%birthday_year%</value>');
        SetupTestData::setCampaignDelivery('UTC00', array('C16-P2'));

        $this->checkResponseMatchesExpected('UTC00', array('email' => 'ribicb@yahoo.com', 'byear' => '1972'), '<value>1972</value>');
    }
    //--------------------------------------------------------------------------


    private function checkResponseMatchesExpected($campaignName, array $testData, $expectedDelivery) {
        try {
            $this->sendData($campaignName, $testData);
        } catch (ERedirectException $e) {
            $this->assertEqual($this->campaign['failure_url'], $e->getMessage());
            $this->assertEqual(1, sizeof($this->gathered));
            $this->assertEqual($expectedDelivery, $this->gathered[0]['result']);
        }
    }
    //--------------------------------------------------------------------------


    private function sendData($campaignName, array $testData) {
        $this->campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids[$campaignName]);
        $engine = $this->newEngineSubmission($testData, $this->cbGatherData, Engine::ACTION_PARTNER_RESPONSE);
        $engine->flags->fakeUrlResponses = array ();
        $engine->processIncomingFormSubmission($this->campaign);
    }
    //--------------------------------------------------------------------------
}
