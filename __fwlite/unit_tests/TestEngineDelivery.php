<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestEngineDelivery extends MyUT {

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;


    public function setUp() {
        parent::setUp();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------


    public function testRapleafAge() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }

        SetupTestData::resetCampaign('UTC00');

        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'rf_age', 'value' => '[:age:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'rf_gender', 'value' => '[:gender:]'));

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        /* @var $melc Model_ExternalLookupCache */
        $melc = SingletonRegistry::getSingleInstance('Model_ExternalLookupCache');

        $melc->deleteEntry('RapLeaf', 'saliero@yahoo.com');

        try {
            $engine = $this->newEngineSubmission(array('email' => 'saliero@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertTrue(is_numeric($this->gathered[0]['rf_age']));
            $this->assertEqual('Female', $this->gathered[0]['rf_gender']);
        }

        // Second time around the value should be cached
        try {
            $engine = $this->newEngineSubmission(array('email' => 'saliero@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertTrue(is_numeric($this->gathered[0]['rf_age']));
            $this->assertEqual('Female', $this->gathered[0]['rf_gender']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverrideAgeWithBirthYear() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_YEAR, 'year');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'age', 'value' => '[:age:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'year' => '1990');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(date('Y') - 1990, $this->lastData['age']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverrideAgeWithAge() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'age', 'value' => '[:age:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'age' => '33');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(33, $this->lastData['age']);
        }
    }
    //--------------------------------------------------------------------------


    public function testDeduceBirthYearFromBirthYear() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_YEAR, 'year');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'byear', 'value' => '[:birth_year:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'year' => '1990');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(1990, $this->lastData['byear']);
        }
    }
    //--------------------------------------------------------------------------


    public function testDeduceBirthYearFromAge() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'byear', 'value' => '[:birth_year:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'age' => '23');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual(date('Y') - 23, $this->lastData['byear']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverridePostalSystemFieldsFromCountryValue() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_COUNTRY, 'country');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'country', 'value' => '[:postal_country:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'cc', 'value' => '[:postal_country_code:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'country' => 'france');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('FRANCE', $this->lastData['country']);
            $this->assertEqual('FR', $this->lastData['cc']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverridePostalSystemFieldsFromZipValue() {
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_POSTAL_CODE, 'zip');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_COUNTRY, 'country');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'pr',  'value' => '[:postal_region:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'prc', 'value' => '[:postal_region_code:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'pc',  'value' => '[:postal_city:]'));
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'pac', 'value' => '[:postal_area_code:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'zip' => '90210', 'country' => 'America');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('California',    $this->lastData['pr']);
            $this->assertEqual('CA',            $this->lastData['prc']);
            $this->assertEqual('Beverly Hills', $this->lastData['pc']);
            $this->assertEqual('310',           $this->lastData['pac']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverrideGenderNegative() {
        SetupTestData::resetCampaign('UTC00');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'out', 'value' => '[:gender:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);

            // Disable rapleaf external lookup for gender
            $engine->flags->test_OwnerUserCaps = array('subscriber');
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('', $this->lastData['out']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOverrideGender() {
        SetupTestData::resetCampaign('UTC00');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_GENDER, 'gender');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'out', 'value' => '[:gender:]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'gender' => 1);
            $engine   = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);

            // Disable rapleaf external lookup for gender
            $engine->flags->test_OwnerUserCaps = array('subscriber');
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('1', $this->lastData['out']);
        }
    }
    //--------------------------------------------------------------------------


    public function testTokenReplacement() {
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'dict', 'value' => '[Age]', 'modifier' => "dlg-modifier-replacements\nAge\n1\n8\n%5BEmail%5D"));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'age' => 8);
            $engine   = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('joe@yahoo.com', $this->lastData['dict']);
        }
    }
    //--------------------------------------------------------------------------


    public function testDictionaryReplacement() {
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'dict', 'value' => '[Age]', 'modifier' => "dlg-modifier-dictionary\nAge\n2\n3"));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'age' => 8);
            $engine   = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('Eight', $this->lastData['dict']);
        }
    }
    //--------------------------------------------------------------------------


    public function testChangeSuccessUrl() {
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P03'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'email', 'value' => '[Email]'));

        $partner = SingletonRegistry::getModelPartner()->get($pid);
        $partner['price_per_lead'] = '0';
        $partner['parse_response'] = '0';
        SingletonRegistry::getModelPartner()->update($pid, $partner);

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P03'));

        try {
            $this->newEngineSubmission(array('email' => 'joe@yahoo.com'))->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('http://www.changed.com/', $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function testRevenueCalculationFromParseResponse() {
        SetupTestData::resetCampaign('UTC00');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P02'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'email', 'value' => '[Email]'));

        $partner = SingletonRegistry::getModelPartner()->get($pid);
        $partner['price_per_lead'] = '$1';
        $partner['parse_response'] = '1';
        $partner['delimiter']      = '|';
        SingletonRegistry::getModelPartner()->update($pid, $partner);

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P02'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'cc' => 'US', 'zip' => 90210);
            $engine   = $this->newEngineSubmission($testData, $this->cbGatherData, Engine::ACTION_DELIVERY_LOGGED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('0.08', $this->gathered[0]['revenue']);
        }
    }
    //--------------------------------------------------------------------------


    public function testRevenueCalculation() {
        SetupTestData::resetCampaign('UTC00');
        $cid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($cid, MYSQL_ASSOC);

        $pid = SetupTestData::$pids['C00-P02'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'email', 'value' => '[Email]'));

        $partner = SingletonRegistry::getModelPartner()->get($pid);
        $partner['price_per_lead'] = '0.12';
        $partner['parse_response'] = '0';
        SingletonRegistry::getModelPartner()->update($pid, $partner);

        SetupTestData::setCampaignDelivery('UTC00', array('C00-P02'));

        try {
            $testData = array('email' => 'joe@yahoo.com', 'cc' => 'US', 'zip' => 90210);
            $engine   = $this->newEngineSubmission($testData, $this->cbGatherData, Engine::ACTION_DELIVERY_LOGGED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('0.12', $this->gathered[0]['revenue']);
        }
    }
    //--------------------------------------------------------------------------


    public function testMissingOwnerCapsThrows404() {
        try {
            SingletonRegistry::getModelUser()->clearUserCapCache();
            SingletonRegistry::getModelUser()->deleteUserCapCache(TEST_USER_ID);

            $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC01']);

            $engine = $this->newEngineSubmission(array('email' => 'axo@yahoo.com', 'first_name' => 'Name'));
            $engine->processIncomingFormSubmission($campaign);

            $this->fail('Should have thrown EError404');
        } catch (EError404 $e) {
            //
        }

        SingletonRegistry::getModelUser()->recreateUserCapCache();
    }
    //--------------------------------------------------------------------------


    public function testDeliveryOrder() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC01']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual(SetupTestData::$expectedRedirect, $e->getMessage());
        }

        $this->assertEqual(3, sizeof($this->deliveredToPartnerIDs));
        $expectedOrder = array(
            SetupTestData::$pids['C01-P1'],
            SetupTestData::$pids['C01-P2'],
            SetupTestData::$pids['C01-P3'],
        );

        $this->assertEqual($expectedOrder, $this->deliveredToPartnerIDs);
    }
    //--------------------------------------------------------------------------


    public function testMultipleSuccessKeywordsSuccess() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC14']);
        $modelPartner = SingletonRegistry::getModelPartner();
        $partner = $modelPartner->get(SetupTestData::$pids['C14-P1']);

        $partner['delivery_addr'] = 'http://w1.localhost/?succ';
        $partner['success_url'] = '$2';
        $modelPartner->update($partner['id'], $partner);

        try {
            $engine = $this->newEngineSubmission(array('email' => __FUNCTION__ . '@test.com'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['success_url'], $e->getMessage());
        }

        $partner['delivery_addr'] = 'http://w1.localhost/?0';
        $modelPartner->update($partner['id'], $partner);
        try {
            $engine = $this->newEngineSubmission(array('email' => __FUNCTION__ . '@test.com'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['failure_url'], $e->getMessage());
        }

        $partner['delivery_addr'] = 'http://w1.localhost/?fail';
        $modelPartner->update($partner['id'], $partner);
        try {
            $engine = $this->newEngineSubmission(array('email' => __FUNCTION__ . '@test.com'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['failure_url'], $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function testPartnerMaxDeliveries() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC10']);
        $deliverySettings = $this->modelCampaign->listDeliverySettings($campaign['id']);
        $this->assertEqual(1, sizeof($deliverySettings));

        $index   = array_search(Model_Partner::DELIVERY_CTYPE_MONTH, Model_Partner::$deliveryTypes);
        $partner = SingletonRegistry::getModelPartner()->get($deliverySettings[0]['partner_id']);

        $this->assertEqual(2, $partner['delivery_cap']);
        $this->assertEqual($index, $partner['delivery_ctype']);

        $count = 0;

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo1@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $count++;
        }
        $this->assertEqual(1, sizeof($this->gathered));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo2@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $count++;
        }
        $this->assertEqual(2, sizeof($this->gathered));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo2@yahoo.com'), $this->cbGatherData, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $count++;
        }

        $this->assertEqual(2, sizeof($this->gathered));
        $this->assertEqual(3, $count);
    }
    //--------------------------------------------------------------------------


    public function testEmptyReplacements() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC17']);
        $_SERVER['REMOTE_ADDR'] = '174.18.221.140';

        try {
            $testData = array('email' => 'ribicb@yahoo.com', 'username' => 'konoko', 'city' => '', 'state_code' => '', 'state' => '');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual('Tucson', $this->lastData['city']);
            $this->assertEqual('AZ', $this->lastData['state_code']);
            $this->assertEqual('Arizona', $this->lastData['state']);
        }
    }
    //--------------------------------------------------------------------------


    public function testChangePartnerDeliveryAddrUsingCampaignFieldTokens() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC29']);
        $_SERVER['REMOTE_ADDR'] = '11.22.33.44';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'utc29@unit-tests.com'), $this->cbFinish, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $url = implode('', $this->lastData);
            $expectedUrl = 'http://w1.localhost/?mail=' . urlencode('utc29@unit-tests.com') . '&ip=' . urlencode($_SERVER['REMOTE_ADDR']);
            $this->assertEqual($expectedUrl, $url);
        }
    }
    //--------------------------------------------------------------------------


    public function testChangePartnerDeliveryAddrUsingCampaignFieldTokensUnrecognizedTokensAreLeftUnchanged() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC30']);
        $_SERVER['REMOTE_ADDR'] = '11.22.33.44';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'utc29@unit-tests.com'), $this->cbFinish, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $url = implode('', $this->lastData);
            $expectedUrl = 'http://w1.localhost/?mail=' . urlencode('utc29@unit-tests.com') . '&ip=' . urlencode($_SERVER['REMOTE_ADDR']) . '&x=[xxx]';
            $this->assertEqual($expectedUrl, $url);
        }
    }
    //--------------------------------------------------------------------------


    public function testNoRepostIfAllPartnersSkipped() {
        $logIncoming = SingletonRegistry::getModelLogIncoming();
        $oldCount = $logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC33']);
        $this->assertEqual($campaign['should_repost'], 1);
        $this->assertEqual($campaign['repost_max'], 2);

        $_SERVER['REMOTE_ADDR'] = '11.22.33.44';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'utc29@unit-tests.com', 'fname' => 'Kokomo'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $url = $e->getMessage();
            $this->assertEqual($campaign['failure_url'], $url);
        }
        $count = $logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual($oldCount, $count);
    }
    //--------------------------------------------------------------------------


    public function testEngineMaxDeliveries() {
        $this->checkEngineMax(-1,  array());
        $this->checkEngineMax(-1,  array('unknown'));
        $this->checkEngineMax(10,  array('visitor'));
        $this->checkEngineMax(10,  array('visitor', 'unknown'));
        $this->checkEngineMax(100, array('user'));
        $this->checkEngineMax(100, array('user', 'visitor'));
        $this->checkEngineMax(0,   array('admin'));
        $this->checkEngineMax(0,   array('admin', 'user'));
    }
    //--------------------------------------------------------------------------


    private function checkEngineMax($expected, array $userCaps) {
        $levelLimitsAssoc = array('visitor' => 10, 'user' => 100, 'admin' => 0);
        $result = Engine_Utilities::getMaxDeliveriesForUserLevel($levelLimitsAssoc, $userCaps);
        $this->assertEqual($expected, $result);
    }
    //--------------------------------------------------------------------------
}