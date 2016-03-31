<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

class TestFillingInMissingDataFromProfiles extends MyUT {

    /**
     * @var Model_Profile
     */
    private $modelProfile;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    /**
     * @var Engine_Submission
     */
    private $engine;

    private $campaign;
    const EMAIL = 'joe@yahoo.com';
    const AGE   = '24';


    public function __construct() {
        parent::__construct();
        $this->modelProfile  = SingletonRegistry::getSingleInstance('Model_Profile');
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------


    public function testFillInMissing() {
        try {
            $this->processTestData(array('email' => self::EMAIL));
        } catch (EFWUT $e) {
            $this->assertEqual(self::AGE, $this->engine->getData()->inputData[Model_CampaignField::FIELD_TYPE_AGE]);
        }
    }
    //--------------------------------------------------------------------------


    public function testFillInInvalid() {
        try {
            $this->processTestData(array('email' => self::EMAIL, 'age' => 'a lil young'));
        } catch (EFWUT $e) {
            $this->assertEqual(self::AGE, $this->engine->getData()->inputData[Model_CampaignField::FIELD_TYPE_AGE]);
        }
    }
    //--------------------------------------------------------------------------


    private function processTestData(array $testData) {
        $this->engine->setTestIncomingData($testData);
        $this->engine->processIncomingFormSubmission($this->campaign);
        $this->fail("Should not have arrived here");
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        parent::setUp();
        SetupTestData::resetCampaign('UTC00');

        $this->createProfile();

        $this->engine = $this->newEngineSubmission(false, $this->cbFinish, Engine::ACTION_GATHERED);
        $this->engine->flags->useProfilesForMissingData = true;
    }
    //--------------------------------------------------------------------------


    private function createProfile() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age', true);

        $this->campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);

        $this->modelProfile->deleteWhere(array('email' => self::EMAIL));
        $testData = array('email' => self::EMAIL, 'age' => self::AGE);
        $engine = $this->newEngineSubmission($testData);

        try {
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }
    }
    //--------------------------------------------------------------------------

}
