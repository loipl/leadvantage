<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestOverrideFieldValidation extends C11UnitTests {
    private $oldCapability = false;
    private $shadow = array();


    public function setUp() {
        $this->oldCapability = DB::$db->getTopLeft("SELECT `capability` FROM `user_rights_cache` WHERE `user_id` = " . TEST_USER_ID);
        $sql = "UPDATE `user_rights_cache` SET `capability` = 'subscriber' WHERE `user_id` = " . TEST_USER_ID;
        DB::$db->query($sql);
        SingletonRegistry::getModelUser()->clearUserCapCache();
        parent::setUp();
    }
    //--------------------------------------------------------------------------


    public function tearDown() {
        $this->restoreOldUserRights();
        parent::tearDown();
    }
    //--------------------------------------------------------------------------


    private function restoreOldUserRights() {
        $sql = "UPDATE `user_rights_cache` SET `capability` = '$this->oldCapability' WHERE `user_id` = " . TEST_USER_ID;
        DB::$db->query($sql);
        SingletonRegistry::getModelUser()->clearUserCapCache();
    }
    //--------------------------------------------------------------------------


    public function testValidateTwoFieldsAtSameTime() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }

        // This one test is not about overriding validation, so we need to undo user rights change
        $this->restoreOldUserRights();
        $this->campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC34']);

        try {
            $this->testData = array ('email' => 'zzsedfkjgiwerups87dfto2w34hn5rgsk7yrtikhn534gk@yahoo.com', 'phone' => '99999999999999999999999999999999');
            $engine = $this->newEngineSubmission($this->testData, $this->cbFinish, Engine::ACTION_GATHERED);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(2, sizeof($this->lastData));
            $this->assertEqual($this->testData['email'] . ' is an invalid email address', $this->lastData[0]);
            $this->assertEqual($this->testData['phone'] . ' is in an invalid phone number format', $this->lastData[1]);

            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(1, sizeof($arr));
            $this->assertEqual(Validator_Registry::VAL_BRITE, $arr[0]);
        }
    }
    //--------------------------------------------------------------------------


    public function testValidationOverrideByShadowCampaignRedistribute() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->initialize(0);
        SingletonRegistry::getModelCampaignField()->removeAllValidationOverrides($this->campaignId);

        try {
            $this->engine->flags->neverSkim  = true;
            $this->engine->flags->alwaysSkim = false;
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = $this->logIncoming->listRepostQueuesForIncomingId($this->engine->getIncomingId());
            $this->assertEqual(1, sizeof($arr));
        }

        $this->shadow['email_field_verify'] = '1';
        $this->modelCampaign->update($this->shadow['id'], $this->shadow);

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = $this->logIncoming->listRepostQueuesForIncomingId($this->engine->getIncomingId());
            $this->assertEqual(0, sizeof($arr));
        }

        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure we processed the UTC11 campaign
        $this->assertEqual(SetupTestData::$cids['UTC11'], $incRow['campaign_id']);

        // Restore old value
        $this->shadow['email_field_verify'] = '';
        $this->modelCampaign->update($this->shadow['id'], $this->shadow);
    }
    //--------------------------------------------------------------------------


    private function initialize($skimPercentage) {
        $this->restoreOldUserRights();

        $this->campaignId = SetupTestData::$cids['UTC11 - Shadow Copy 5'];
        $shadow = $this->modelCampaign->get($this->campaignId);
        if (!$shadow) {
            $this->fail('Campaign "UTC11 - Shadow Copy 5" is missing');
        }
        $shadow['should_repost']      = '1';
        $shadow['repost_hours']       = 0;
        $shadow['repost_max']         = 2;
        $shadow['leads_per']          = 2;
        $shadow['is_active']          = '1';
        $shadow['skim_percentage']    = $skimPercentage;
        $shadow['shadow_of']          = SetupTestData::$cids['UTC11'];
        $shadow['shadow_delay_hours'] = 0;
        $shadow['email_field_verify'] = '';
        $this->modelCampaign->update($shadow['id'], $shadow);

        $this->campaign['should_repost']      = 0;
        $this->campaign['repost_hours']       = 0;
        $this->campaign['is_oob']             = 0;
        $this->campaign['leads_per']          = 2;
        $this->campaign['partner_retries']    = $this->partnerRetries;
        $this->campaign['email_field_verify'] = '';

        $this->shadow = $shadow;

        $this->setupDelivery(array('10-y-n', '11-y-n'), $this->campaignId);
        $this->engine->setTestIncomingData(array('email' => 'kzhxcgkwhsgfbkgadfsjgnslehdfgikshndfkgbhwuerghs@yahoo.com'));
        $this->engine->setListenerCallback($this->cbDelivered);
    }
    //--------------------------------------------------------------------------


    public function testValidationOverrideByShadowCampaignRedistributePhone() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->initialize(0);
        SingletonRegistry::getModelCampaignField()->removeAllValidationOverrides($this->campaignId);

        try {
            $this->engine->flags->neverSkim  = true;
            $this->engine->flags->alwaysSkim = false;
            $this->engine->setTestIncomingData(array('email' => 'kzhxcgkwhsgfbkgadfsjgnslehdfgikshndfkgbhwuerghs@yahoo.com', 'ph' => '3456'));
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = $this->logIncoming->listRepostQueuesForIncomingId($this->engine->getIncomingId());
            $this->assertEqual(1, sizeof($arr));
        }

        $fields = SingletonRegistry::getModelCampaignField()->listAllForCampaign($this->shadow['shadow_of']);
        foreach ($fields as $fld) {
            if ($fld['field_type'] == Model_CampaignField::FIELD_TYPE_PHONE) {
                SingletonRegistry::getModelCampaignField()->addValidationOverride($this->campaignId, $fld['id']);
            }
        }

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = $this->logIncoming->listRepostQueuesForIncomingId($this->engine->getIncomingId());
            $this->assertEqual(0, sizeof($arr));
        }
    }
    //--------------------------------------------------------------------------


    public function testValidationOverrideByShadowCampaignSkim() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->initialize(10);
        SingletonRegistry::getModelCampaignField()->removeAllValidationOverrides($this->campaignId);

        try {
            $this->engine->flags->neverSkim  = false;
            $this->engine->flags->alwaysSkim = true;
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(0, sizeof($arr));
        }

        $this->shadow['email_field_verify'] = '1';
        $this->modelCampaign->update($this->shadow['id'], $this->shadow);

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(1, sizeof($arr));
        }

        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure we processed the shadow campaign, not source campaign
        $this->assertEqual($this->campaignId, $incRow['campaign_id']);

        $this->shadow['email_field_verify'] = '';
        $this->modelCampaign->update($this->shadow['id'], $this->shadow);
    }
    //--------------------------------------------------------------------------


    public function testValidationOverrideByShadowCampaignSkimPhone() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->initialize(10);

        try {
            $this->engine->flags->neverSkim  = false;
            $this->engine->flags->alwaysSkim = true;
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(0, sizeof($arr));
        }

        $fields = SingletonRegistry::getModelCampaignField()->listAllForCampaign($this->shadow['shadow_of']);
        foreach ($fields as $fld) {
            if ($fld['field_type'] == Model_CampaignField::FIELD_TYPE_PHONE) {
                SingletonRegistry::getModelCampaignField()->addValidationOverride($this->campaignId, $fld['id']);
            }
        }

        try {
            $this->engine->setTestIncomingData(array('email' => 'kzhxcgkwhsgfbkgadfsjgnslehdfgikshndfkgbhwuerghs@yahoo.com', 'ph' => '65654'));
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(1, sizeof($arr));
        }

        $incRow = $this->logIncoming->get($this->engine->getIncomingId());

        // Make sure we processed the shadow campaign, not source campaign
        $this->assertEqual($this->campaignId, $incRow['campaign_id']);
    }
    //--------------------------------------------------------------------------


    public function testValidationOnInvalidEmailDoesntHappenForSubscriber() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC26']);

        try {
            $this->engine->setTestIncomingData(array('email' => 'zzsedfkjgiwerups87dfto2w34hn5rgsk7yrtikhn534gk@yahoo.com'));
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(0, sizeof($arr));
        }
    }
    //--------------------------------------------------------------------------


    public function testValidationOnInvalidEmailValidationOverrideByAdmin() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }
        $this->campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC27']);

        try {
            $this->engine->setTestIncomingData(array('email' => 'zzsedfkjgiwerups87dfto2w34hn5rgsk7yrtikhn534gk@yahoo.com'));
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $arr = Validator_Registry::getTriedValidatorsOnLastRun();
            $this->assertEqual(1, sizeof($arr));
        }
    }
    //--------------------------------------------------------------------------
}
