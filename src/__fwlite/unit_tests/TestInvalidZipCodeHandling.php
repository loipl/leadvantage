<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestInvalidZipCodeHandling extends MyUT {

    public function testInferingipZipWithInvalidValue() {
        $_SERVER['REMOTE_ADDR'] = '69.64.34.113';
        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC23']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 't1@unittest.com', 'zip' => '501'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['pc_test'], '63101');
        }
    }
    //--------------------------------------------------------------------------


    public function testInferingZipWithCorrectValue() {
        $_SERVER['REMOTE_ADDR'] = '69.64.34.113';
        $campaign   = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC23']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 't2@unittest.com', 'zip' => '90210'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['pc_test'], '90210');
        }
    }
    //--------------------------------------------------------------------------
}
