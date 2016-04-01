<?php

$skipOtherTests = 1;
defined('RUN_ALL_TESTS') or require_once '../../runAllTests.php';

class TestChildCampaignDelivery extends MyUT {
    private $masterCampaign;
    private $masterCampaignId;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    public function setUp() {
        $this->masterCampaignId = (int)SetupTestData::$cids['UTC37 Master'];
        $this->modelCampaign    = SingletonRegistry::getModelCampaign();
        $this->masterCampaign   = $this->modelCampaign->get($this->masterCampaignId, MYSQL_ASSOC);

        $this->assertEqual('UTC37 Master', $this->masterCampaign['name']);
    }
    //--------------------------------------------------------------------------


    public function test() {
        $testData = array(
            'email'      => 'joe@yahoo.com',
            'first_name' => 'Joe',
            'age'        => '24',
        );
        $engine   = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_CHILD_CAMPAIGN);

        try {
            $engine->processIncomingFormSubmission($this->masterCampaign);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    //--------------------------------------------------------------------------
}
