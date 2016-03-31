<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestAffCampaign extends C11UnitTests {

    public function testAffCOREGPartnerRedirection() {
        $campaignId = SetupTestData::$cids['UTC17'];
        $campaign   = SingletonRegistry::getModelCampaign()->get($campaignId);
        $engine     = SetupTestData::debugEngineSubmission();
        $testData   = array('email' => 'ribicb@yahoo.com', 'username' => 'konoko');

        $_SERVER['REMOTE_ADDR'] = '85.222.222.232';
        $engine->setTestIncomingData($testData)->flags->fakeUrlResponses = array();

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), 'http://xxx.localhost/COREG/login?docket=NzgxK3MnYydzJ3kgVzZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnY%3D&ip_address=85.222.222.232');
        }
    }
    //--------------------------------------------------------------------------
}
