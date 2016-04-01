<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

class TestInvalidDataNotWrittenToProfiles extends MyUT {

    /**
     * @var Model_Profile
     */
    private $modelProfile;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    private $campaign;

    private $testData_InvalidEmail = array('email' => 'invalid@@test.com');
    private $testData_InvalidAge   = array('email' => 'joe@yahoo.com', 'age' => 'none of your business');


    public function __construct() {
        parent::__construct();
        $this->modelProfile  = SingletonRegistry::getSingleInstance('Model_Profile');
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        parent::setUp();
        SetupTestData::resetCampaign('UTC00');
    }
    //--------------------------------------------------------------------------


    public function testInvalidEmail_NotWrittenToProfilesTable() {
        $this->campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);

        $this->assertFalse($this->modelProfile->getWhere($this->testData_InvalidEmail));

        try {
            $this->newEngineSubmission($this->testData_InvalidEmail)->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }

        $this->assertFalse($this->modelProfile->getWhere($this->testData_InvalidEmail));
    }
    //--------------------------------------------------------------------------


    public function testValidAge_IsWrittenToProfilesTable() {
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');
        $this->campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);

        $testData = $this->testData_InvalidAge;
        $testData['age'] = 25;

        try {
            $this->newEngineSubmission($testData)->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }

        $data = $this->modelProfile->getProfileDataForEmail($testData['email']);
        $this->assertEqual($data[Model_CampaignField::FIELD_TYPE_AGE], $testData['age']);
    }
    //--------------------------------------------------------------------------


    public function testInvalidAge_NotWrittenToProfilesTable() {
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');
        $this->campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);

        try {
            $this->newEngineSubmission($this->testData_InvalidAge)->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }

        $data = $this->modelProfile->getProfileDataForEmail($this->testData_InvalidAge['email']);
        $this->assertNotEqual($data[Model_CampaignField::FIELD_TYPE_AGE], $this->testData_InvalidAge['age']);
    }
    //--------------------------------------------------------------------------
}
