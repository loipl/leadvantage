<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestSkimming extends C11UnitTests {
    private $shadowId;
    private $shadow;


    public function __construct() {
        parent::__construct();
        $this->logIncoming = SingletonRegistry::getModelLogIncoming();
        $this->shadowId = SetupTestData::$cids['UTC11 - Shadow Copy 3'];
        $_SERVER['REMOTE_ADDR'] = '85.222.134.142';
    }
    //--------------------------------------------------------------------------


    public function testBasicSkimming() {
        $this->initializeShadow(10);

        try {
            $this->engine->flags->alwaysSkim = true;
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), $this->campaign['success_url']);
            $this->assertEqual(2, sizeof($this->deliveredToPartnerIDs));
        }
        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure we processed the shadow campaign, not source campaign
        $this->assertEqual($this->shadowId, $incRow['campaign_id']);
    }
    //--------------------------------------------------------------------------


    private function initializeShadow($skimPercentage) {
        $shadow = $this->modelCampaign->get($this->shadowId);
        if ($shadow) {
            $shadow['should_repost'] = '1';
            $shadow['repost_hours'] = 0;
            $shadow['repost_max'] = 2;
            $shadow['leads_per'] = 2;
            $shadow['is_active'] = '1';
            $shadow['skim_percentage'] = $skimPercentage;
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $shadow['shadow_delay_hours'] = 0;
            $this->modelCampaign->update($shadow['id'], $shadow);
        }
        $this->shadow = $shadow;

        $this->setupDelivery($this->fullList);
        $this->setupDelivery(array('10-y-n', '11-y-n'), $this->shadowId);

        $this->campaign['should_repost']   = 0;
        $this->campaign['repost_hours']    = 0;
        $this->campaign['is_oob']          = 0;
        $this->campaign['leads_per']       = 2;
        $this->campaign['partner_retries'] = $this->partnerRetries;

        $this->engine = $this->newEngineSubmission($this->testData, $this->cbDelivered);

        // Debug version of Engine has neverSkim = true, we have to turn it off
        // when testing skimming
        $this->engine->flags->neverSkim = false;
    }
    //--------------------------------------------------------------------------


    public function testSkimming2() {
        $this->initializeShadow(100);

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), $this->campaign['success_url']);
            $this->assertEqual(2, sizeof($this->deliveredToPartnerIDs));
        }
        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure we processed the shadow campaign, not source campaign
        $this->assertEqual($this->shadowId, $incRow['campaign_id']);
    }
    //--------------------------------------------------------------------------


    public function testSkimmingNoSkim() {
        SetupTestData::deleteEverythingForTestUser();
        $this->initializeShadow(0);

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), $this->campaign['success_url']);
            $this->assertEqual(2, sizeof($this->deliveredToPartnerIDs));
        }
        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure incoming row is of source campaign because skim_percentage is 0
        $this->assertEqual(SetupTestData::$cids['UTC11'], $incRow['campaign_id']);
    }
    //--------------------------------------------------------------------------


}