<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestBasics extends UnitTestCase {
    private $campaign   = array();
    private $campaignId = 0;


    public function setUp() {
        parent::setUp();

        SetupTestData::resetCampaign('UTC00');

        $this->campaignId = SetupTestData::$cids['UTC00'];
        $this->campaign   = SingletonRegistry::getModelCampaign()->get($this->campaignId, MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function testJobKinds() {
        $engine = SetupTestData::debugEngineSubmission();
        $this->assertIdentical(Engine_Job::KIND_UNINITIALIZED, $engine->getJob()->getKind());

        try {
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }
        $this->assertIdentical(Engine_Job::KIND_SUBMISSION, $engine->getJob()->getKind());

        $engine = SetupTestData::debugEngineRepost();
        try {
            $engine->processRepost(array(), $this->campaign);
        } catch (Exception $e) {
            //
        }
        $this->assertIdentical(Engine_Job::KIND_REPOSTING, $engine->getJob()->getKind());
    }
    //--------------------------------------------------------------------------


    public function testIsReposting() {
        $engine = SetupTestData::debugEngineSubmission();

        try {
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }
        $this->assertFalse($engine->getJob()->isReposting());

        $engine = SetupTestData::debugEngineRepost();
        try {
            $engine->processRepost(array(), $this->campaign);
        } catch (Exception $e) {
            //
        }
        $this->assertTrue($engine->getJob()->isReposting());
    }
    //--------------------------------------------------------------------------


    public function testCampaignWrapperInitializedWithSameCampaign() {
        $engine = SetupTestData::debugEngineSubmission();
        $this->assertIdentical(Engine_Job::KIND_UNINITIALIZED, $engine->getJob()->getKind());

        try {
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            //
        }
        $campaign = $engine->getJob()->campaign->row;

        $this->assertEqual($campaign, $this->campaign);
    }
    //--------------------------------------------------------------------------
}
