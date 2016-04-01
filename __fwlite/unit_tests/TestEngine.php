<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestEngine extends MyUT {

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;


    public function setUp() {
        $this->modelCampaign   = SingletonRegistry::getModelCampaign();
        $this->actionsReported = array();
        $this->lastData        = array();
        $this->gathered        = array();
        $this->waitAction      = false;

        $this->deliveredToPartnerIDs = array();
    }
    //--------------------------------------------------------------------------


    public function testNoPartnersAtSubmissionTime() {
        SetupTestData::deleteEverythingForTestUser();

        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::setCampaignDelivery('UTC00', array());

        $testData = array('email' => 'testNoPartnersAtSubmissionTime@unit-tests.com');

        try {
            $engine = SetupTestData::debugEngineSubmission($testData);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['failure_url'], $e->getMessage());
            $iid = $engine->getIncomingId();
            $arr = SingletonRegistry::getModelLogIncoming()->listFailedNoPartners(array($iid));
            $this->assertEqual(array($iid), $arr);
        }
    }
    //--------------------------------------------------------------------------


    public function testTooManySubmissions() {
        SetupTestData::deleteEverythingForTestUser();

        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'x', 'value' => '1'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));
        $partner = SingletonRegistry::getModelPartner()->get($pid);

        $testData = array('email' => 'testTooManySubmissions@unit-tests.com');

        try {
            SetupTestData::debugEngineSubmission($testData)->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($partner['success_url'], $e->getMessage());
        }

        try {
            $engine = $this->newEngineSubmission($testData);
            $engine->flags->test_MaxSubmissions = 1;
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($partner['success_url'], $e->getMessage());
        }


        try {
            $engine = $this->newEngineSubmission($testData, $this->cbActionList);
            $engine->flags->test_MaxSubmissions = 1;
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['failure_url'], $e->getMessage());
            $this->assertTrue(in_array(Engine::ACTION_TOO_MANY, $this->actionsReported));
        }
    }
    //--------------------------------------------------------------------------


    public function testDetectingDuplicateSubmission() {
        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_AGE, 'age');
        $testData = array('email' => "testDetectingDuplicateSubmission@unit-test.com", 'age' => 23);

        try {
            $engine = $this->newEngineSubmission($testData);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $redirectedTo = $e->getMessage();
        }

        $incomingId = $engine->getData()->incomingId;

        try {
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_INCOMING_DUPLICATE);
            $engine->flags->checkForDuplicates = true;
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($e->getMessage(), Engine::ACTION_INCOMING_DUPLICATE);
            $this->assertEqual(1, sizeof($this->lastData));
            $keys = array_keys($this->lastData);
            $this->assertEqual($incomingId, $keys[0]);
            $this->assertEqual($this->lastData[$incomingId][Model_CampaignField::FIELD_TYPE_AGE], 23);
        }

        // Second retry will let the code run all the way instead of stopping at detecting duplicate
        try {
            $engine = $this->newEngineSubmission($testData);
            $engine->flags->checkForDuplicates = true;
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($redirectedTo, $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function testInvalidUrlFormat() {
        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_WEBSITE_URL, 'url');

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'url' => 'zzzz'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), "zzzz is not a valid URL") !== false);
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'url' => 'http://-'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), "http://- is not a valid URL") !== false);
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'url' => 'http%3a%2f%2f-'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), "http://- is not a valid URL") !== false);
        }
    }
    //--------------------------------------------------------------------------


    public function testInvalidDateFormat() {
        $campaign = SingletonRegistry::getModelCampaign()->get(SetupTestData::$cids['UTC00'], MYSQL_ASSOC);
        SetupTestData::resetCampaign('UTC00');
        SetupTestData::addCampaignField('UTC00', Model_CampaignField::FIELD_TYPE_SUBMIT_DATE, 'submit_date');

        $pid = SetupTestData::$pids['C00-P01'];
        SingletonRegistry::getModelPartnerField()->deleteForPartner($pid);
        SingletonRegistry::getModelPartnerField()->insert(array('partner_id' => $pid, 'name' => 'dict', 'value' => '[Submit_Date]'));
        SetupTestData::setCampaignDelivery('UTC00', array('C00-P01'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'submit_date' => '2011-45-45 89:89:89'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), "Invalid date: 2011-45-45 89:89:89") !== false);
        }
    }
    //--------------------------------------------------------------------------


    public function testAutoConvertingNumericToStringIpAddress() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC25']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failure';
        if (!$campaign) {
            $this->fail("Campaign UTC25 missing");
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'ip' => '44556677'), $this->cbFinish, Engine::ACTION_GATHERED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($engine->getData()->inputData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS], long2ip(44556677));
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'joe@yahoo.com', 'ip' => '33.44.55.66'), $this->cbFinish, Engine::ACTION_GATHERED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($engine->getData()->inputData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS], '33.44.55.66');
        }
    }
    //--------------------------------------------------------------------------


    public function testNoDuplicateSkips() {
        SetupTestData::deleteEverythingForTestUser();
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC19']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failure';
        $campaign['should_repost'] = '1';
        $campaign['repost_hours'] = '0';

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            $engine = $this->newEngineSubmission(array('first_name' => '6chars', 'email' => 'cjow@joes.com', 'username' => '4444'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), 'failure');
        }

        $logDelivery = SingletonRegistry::getModelLogDelivery();
        $count = $logDelivery->countOfSkippedDeliveries($engine->getData()->incomingId, SetupTestData::$pids['C19-P1'], $campaign['id']);
        $this->assertEqual(1, $count);

        $this->repostAllForTestUser();
        $count = $logDelivery->countOfSkippedDeliveries($engine->getData()->incomingId, SetupTestData::$pids['C19-P1'], $campaign['id']);
        $this->assertEqual(1, $count);
    }
    //--------------------------------------------------------------------------


    public function testFilteringByStrLen() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC19']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failure';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            $engine = $this->newEngineSubmission(array('first_name' => '6chars', 'email' => 'cjow@joes.com', 'username' => '4444'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), 'failure');
        }

        try {
            $engine = $this->newEngineSubmission(array('first_name' => '999999999', 'email' => 'cjow@joes.com', 'username' => '4444'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), 'http://w1.localhost/');
        }
    }
    //--------------------------------------------------------------------------


    public function testModifyByStrlen() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC19']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failure';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            $engine = $this->newEngineSubmission(array('first_name' => '999999999', 'email' => 'cjow@joes.com', 'username' => '4444'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['strlen2'], 'bla-bla-bla');
        }

        try {
            $engine = $this->newEngineSubmission(array('first_name' => '999999999', 'email' => 'cjow@joes.com', 'username' => '999999999'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['strlen2'], '999999999');
        }
    }
    //--------------------------------------------------------------------------


    public function testRejectedCampaign() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC01']);
        $campaign['should_repost'] = 1;

        try {
            $engine = $this->newEngineSubmission(array('email' => ''));
            $engine->processIncomingFormSubmission($campaign);
            $this->fail('Should have thrown an EExplainableError that email is required');
        } catch (EExplainableError $e) {
            //
        }

        $arr = SingletonRegistry::getModelLogIncoming()->listRepostQueuesForIncomingId($engine->getIncomingId());
        $this->assertEqual(0, sizeof($arr));
    }
    //--------------------------------------------------------------------------


    public function testOrProcessingNoMatch() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC08']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failed';

        $_SERVER['REMOTE_ADDR'] = '85.222.231.45';// Serbian IP

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo@yahoo.com'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('failed', $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function testOrProcessingMatch() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC08']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failed';
        $_SERVER['REMOTE_ADDR'] = '2.20.183.0';// UK IP

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo@yahoo.com'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('failed', $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function testOrProcessingMatch2() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC08']);
        $campaign['success_url'] = 'success';
        $campaign['failure_url'] = 'failed';
        $_SERVER['REMOTE_ADDR'] = '2.20.183.0';// UK IP

        try {
            $engine = $this->newEngineSubmission(array('email' => 'axo@yahoo.com', 'cc' => 'GB'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('success', $e->getMessage());
        }
    }
    //--------------------------------------------------------------------------


    public function x_testPakistan() {
        $osa = $_SERVER['REMOTE_ADDR'];
        $_SERVER['REMOTE_ADDR'] = '182.177.171.227';
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC06']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'ribicb@yahoo.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $_SERVER['REMOTE_ADDR'] = $osa;
            $this->assertEqual($this->lastData['cc3'], 'PAK');
        }

        $_SERVER['REMOTE_ADDR'] = $osa;
    }
    //--------------------------------------------------------------------------


    public function _testRapleafOverrideAge() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC07']);
        $engine   = $this->newEngineSubmission(false, $this->cbFinish, Engine::ACTION_SENT_VALS);

        try {
            // Inferring from birth year field
            $engine->setTestIncomingData(array('email' => 'ribicb@yahoo.com', 'by' => '1980'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['rapleaf_age'], date('Y') - 1980);
        }

        try {
            // Value of Age field
            $engine->setTestIncomingData(array('email' => 'ribicb@yahoo.com', 'age' => '23'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['rapleaf_age'], 23);
        }

        try {
            // Now test that Age overrides Birthday_Year if both are entered
            $engine->setTestIncomingData(array('email' => 'ribicb@yahoo.com', 'age' => '23', 'by' => '1980'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['rapleaf_age'], 23);
        }
    }
    //--------------------------------------------------------------------------


    public function _testRapleafOverrideGender() {
        $oldIP    = $_SERVER['REMOTE_ADDR'];
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC07']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'ribicb@yahoo.com', 'gender' => 'Dude'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['rapleaf_gender'], 'Dude');
        }

        $_SERVER['REMOTE_ADDR'] = $oldIP;
    }
    //--------------------------------------------------------------------------


    public function _testRapleafDataAnd3CharCountryCode() {
        $oldIP = $_SERVER['REMOTE_ADDR'];
        $_SERVER['REMOTE_ADDR'] = '69.64.34.116';
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC06']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'ribicb@yahoo.com', 'by' => '1980'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $_SERVER['REMOTE_ADDR'] = $oldIP;
            $this->assertEqual($this->lastData['rapleaf_gender'], 'Male');
            $this->assertEqual($this->lastData['cc3'], 'USA');
            $this->assertEqual($this->lastData['byr'], '1980');
        }

        $_SERVER['REMOTE_ADDR'] = $oldIP;
    }
    //--------------------------------------------------------------------------


    public function testMissingDataMessage() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC01']);
        $campaign['email_field_description'] = 'Email Address :)';

        try {
            $engine = $this->newEngineSubmission(array('first_name' => 'Name'), $this->cbFinish, Engine::ACTION_GATHERED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData[0], 'Email Address :) is a mandatory field');
            $this->assertEqual(1, sizeof($this->lastData));
        }
    }
    //--------------------------------------------------------------------------


    public function testFilteringBySystemField() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC02']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            $engine = $this->newEngineSubmission(array('first_name' => 'Name', 'email' => 'cjow@joes.com'), $this->cbFinish, Engine::ACTION_FILTERED);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData[0], false);
            $this->assertEqual(1, sizeof($this->lastData));
        }
    }
    //--------------------------------------------------------------------------


    public function testInferingStateFromZip() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC03']);
        $_SERVER['REMOTE_ADDR'] = '69.64.34.113';

        try {
            $engine = $this->newEngineSubmission(array('zip' => '90210', 'email' => 'jow@joes.com'), $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['state'], 'California');
            $this->assertEqual(1, sizeof($this->lastData));
        }
    }
    //--------------------------------------------------------------------------


    public function testUrlReplacement() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC04']);
        $campaign['success_url'] = 'http://www.xxx.com/?x=$1&y=$3';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'jow@joes.com'), $this->cbGatherData, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('http://www.xxx.com/?x=OK&y=x%2By', $e->getMessage());
        }
        // 2 because both campaign success url and partner delivery url were processed
        $this->assertEqual(2, sizeof($this->gathered));


        $campaign['success_url'] = '$2';
        try {
            $engine = $this->newEngineSubmission(array('email' => 'jow@joes.com'), $this->cbGatherData, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual('http://wiki.localhost/', $e->getMessage());
        }
        $this->assertEqual(4, sizeof($this->gathered));
    }
    //--------------------------------------------------------------------------


    public function testInactiveCampaign() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC12']);
        $this->assertEqual($campaign['is_active'], '0');

        try {
            $engine = $this->newEngineSubmission(array('email' => 'jow@joes.com'), $this->cbActionList);
            $engine->processIncomingFormSubmission($campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($campaign['failure_url'], $e->getMessage());
            $this->assertEqual(array(Engine::ACTION_INCOMING), $this->actionsReported);
        }
    }
    //--------------------------------------------------------------------------


    public function testModifyValueTokenProcessing() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC05']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        try {
            $testData = array('email' => 'testModifyValueTokenProcessing@testing.com', 'fn' => 'Joe', 'f4mod1' => 'a', 'f4mod2' => 'b');
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['i'], '+127.0.0.1+');
            $this->assertEqual($this->lastData['mod2_1'], 'b');
            $this->assertEqual($this->lastData['mod2_2'], 'a');
        }
    }
    //--------------------------------------------------------------------------


    public function testBlankReplacementRules() {
        // This should match partner settings for partner C05-P1
        $EXPECTED_BLANK = 'it was blank';

        $this->checkUTC05BlankRule('', $EXPECTED_BLANK);
        $this->checkUTC05BlankRule(' ', $EXPECTED_BLANK);
        $this->checkUTC05BlankRule('*', '*');
        $this->checkUTC05BlankRule(false, $EXPECTED_BLANK);
        $this->checkUTC05BlankRule(null, $EXPECTED_BLANK);
    }
    //--------------------------------------------------------------------------


    private function checkUTC05BlankRule($input, $expectedOutput) {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC05']);

        $testData = (array('email' => 'checkUtc05BlankRule@testing.com', 'fn' => 'Joe'));
        if ($input !== false) {
            $testData['blank_field'] = $input;
        }

        try {
            $engine = $this->newEngineSubmission($testData, $this->cbFinish, Engine::ACTION_SENT_VALS);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EFWUT $e) {
            $this->assertEqual($this->lastData['test_blank'], $expectedOutput);
        }
    }
    //--------------------------------------------------------------------------

}
