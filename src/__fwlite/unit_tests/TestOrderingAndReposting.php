<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestOrderingAndReposting extends C11UnitTests {

    public function __construct() {
        parent::__construct();
        SetupTestData::deleteEverythingForTestUser();
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        parent::setUp();
        $this->assertEqual('', $this->campaign['email_field_verify']);
    }
    //--------------------------------------------------------------------------


    public function testNoDeliveryToPartnerAddedAfterOriginalSubmission() {
        $pid = SetupTestData::$pids['C11-P03'];
        $partner = $oldPartnerState = $this->modelPartner->get($pid);
        $partner['created_at'] = date('Y-m-d H:i:s', strtotime("+ 5 days"));
        $this->modelPartner->updateDiff($pid, $partner, $oldPartnerState);

        $this->checkDeliveryOrderAndTurnRepostOn($this->fullList, array(1, 2), 2, 0);
        $this->deliveredToPartnerIDs = array();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->repostAllForTestUser(array($this, 'callbackDelivered'));

        // Since C11-P03 is now pushed to the future it should be ignored for reposting, so instead P04 and P05 will be reposted to
        $this->assertEqual(array(SetupTestData::$pids['C11-P04'], SetupTestData::$pids['C11-P05']), $this->deliveredToPartnerIDs);

        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->modelPartner->updateDiff($pid, $oldPartnerState, $partner);
    }
    //--------------------------------------------------------------------------


    public function testReplaceSystemToken() {
        $this->disableRepostingAndSetRepostValuesForCampaign();
        $this->setupDelivery(array('1-http://w1.localhost/[:ip_address:]-n'));
        $_SERVER['REMOTE_ADDR'] = '11.22.33.44';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'meysam_z_a@yahoo.com'), $this->cbFinish, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (EFWUT $e) {
            $url = implode('', $this->lastData);
            $this->assertEqual('http://w1.localhost/11.22.33.44', $url);
        }
    }
    //--------------------------------------------------------------------------


    private function disableRepostingAndSetRepostValuesForCampaign() {
        $this->campaign['should_repost']   = 0;
        $this->campaign['repost_hours']    = 0;
        $this->campaign['is_oob']          = 0;
        $this->campaign['leads_per']       = 2;
        $this->campaign['partner_retries'] = $this->partnerRetries;
    }
    //--------------------------------------------------------------------------


    public function testReplaceUnrecognizedSystemTokenLeftUnchanged() {
        $this->disableRepostingAndSetRepostValuesForCampaign();
        $this->setupDelivery(array('1-http://w1.localhost/[:ip_zzz_address:]-n'));
        $_SERVER['REMOTE_ADDR'] = '11.22.33.44';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'meysam_z_a@yahoo.com'), $this->cbFinish, Engine::ACTION_URL_PROC);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (EFWUT $e) {
            $url = implode('', $this->lastData);
            $this->assertEqual('http://w1.localhost/[:ip_zzz_address:]', $url);
        }
    }
    //--------------------------------------------------------------------------


    public function testAppendSuccessUrls() {
        $this->disableRepostingAndSetRepostValuesForCampaign();
        $this->campaign['append_succ_url'] = '1';
        $this->setupDelivery(array('19-y-n', '20-http://w1.localhost/succ.php-n'));

        try {
            $engine = $this->newEngineSubmission(array('email' => 'meysam_z_a@yahoo.com'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {
            $redirectionUrl = $e->getMessage();
            $this->assertTrue(strpos($redirectionUrl, $this->campaign['success_url']) === 0);
            $this->assertEqual(2, sizeof($this->deliveredToPartnerIDs));
            $this->assertEqual(array(SetupTestData::$pids['C11-P19'], SetupTestData::$pids['C11-P20']), $this->deliveredToPartnerIDs);
        }

        $query = parse_url($redirectionUrl, PHP_URL_QUERY);
        parse_str($query, $arr);

        $this->assertTrue(isset($arr['success_url']));
        $this->assertTrue(is_array($arr['success_url']));
        $this->assertEqual(2, sizeof($arr['success_url']));
        $this->assertEqual('http://w1.locahlhost/succ11-o1', $arr['success_url'][0]);
        $this->assertEqual('http://w1.locahlhost/succ11-o2', $arr['success_url'][1]);
    }
    //--------------------------------------------------------------------------


    public function testValidationOnInvalidEmail() {
        if (!SetupTestData::$runValidationTests) {
            return;
        }

        $this->campaign['email_field_verify'] = 1;
        $this->testData['email'] = 'meysam_z_a2yahoo.com';
        try {
            $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n'), array(1), 1, 1, true);
            $this->fail("Should have complained about invalid email");
        } catch (EExplainableError $e) {
            //
        }

        $arr = Validator_Registry::getTriedValidatorsOnLastRun();
        $this->assertEqual(0, sizeof($arr));

        Config::$validatorCache = false;
        $this->testData['email'] = 'axo8347569237456873450823945723845720@yah.com';
        try {
            $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n'), array(1), 1, 1, true);
            $this->fail("Should have complained about invalid email");
        } catch (EExplainableError $e) {
            //
        }

        $arr = Validator_Registry::getTriedValidatorsOnLastRun();
        $this->assertEqual(1, sizeof($arr));
    }
    //--------------------------------------------------------------------------


    public function testCurlFailure() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-f-n'), array(), 1, 1, false);
        $dids = $this->engine->getDeliveryIDs();
        $this->assertEqual(2, sizeof($dids));

        $del = SingletonRegistry::getModelLogDelivery()->get($dids[0]);
        $this->assertEqual('curl', $del['reject_reason']);
        $this->assertEqual('0', $del['is_accepted']);

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-f-n', '2-y-n'), array(2), 1, 1, true);
        $dids = $this->engine->getDeliveryIDs();
        $this->assertEqual(3, sizeof($dids));
    }
    //--------------------------------------------------------------------------


    public function testCampaignRepostWithRepostMax2() {
        $this->checkDeliveryOrderAndTurnRepostOn($this->fullList, array(1, 2), 2, 2, true);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(2, $repostQueue[0]['remaining_count']);

        $this->repostAllForTestUser();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(1, $repostQueue[0]['remaining_count']);

        $this->repostAllForTestUser(false, true);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(0, $repostQueue[0]['remaining_count']);

        $this->repostAll();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testDeliveriesWithLeadsPerLimits() {
        $list = $this->fullList;
        $list[8] = '9-y-y';
        $list[2] = '3-n-n';
        $ids = $this->ids;
        $this->checkDeliveryOrderNoRepost($list, array(1, 2, 9), 2, true);
        // First two and 9 because it is always=yes
        $this->assertEqual(array($ids[1], $ids[2], $ids[9]), $this->deliveredToPartnerIDs);

        // Check that always=yes is not counted in repost because it succeeded in original post
        // and that partner 3 failed
        $this->repostLastSubmission();
        $this->assertEqual(array($ids[3]), $this->deliveryFailedToPartnerIDs);
        $this->assertEqual(array($ids[4],  $ids[5]), $this->deliveredToPartnerIDs);

        // Check third repost took next two
        $this->repostLastSubmission();
        $this->assertEqual(array($ids[6], $ids[7]), $this->deliveredToPartnerIDs);
    }
    //--------------------------------------------------------------------------


    public function testUsernameFailure() {
        $this->checkDeliveryOrderNoRepost(array('10-u-n'), array(), 2, false);
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Basic delivery order tests - just check that it happens in right order and right number
     */
    public function testSubmissionDeliveryOrderBasics() {
        // Check that script uses leads_per correctly
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '2-y-n'),          array(1),       1);
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2),    2);
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '6-y-n', '9-y-n'), array(1, 6),    2);
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '6-y-n', '9-y-n'), array(1, 6, 9), 3);
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '9-y-n', '6-y-n'), array(1, 9, 6), 3);
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Check that script doesn't count failures toward leads_per
     */
    public function testSubmissionOrderFailuresDontCount() {
        $this->checkDeliveryOrderNoRepost(array('1-n-n', '2-n-n'),          array(),        1, false);

        $this->checkDeliveryOrderNoRepost(array('1-n-n', '2-y-n'),          array(2),       1);
        $this->checkDeliveryOrderNoRepost(array('1-n-n', '2-y-n', '3-y-n'), array(2, 3),    2);
        $this->checkDeliveryOrderNoRepost(array('1-y-n', '2-n-n', '3-y-n'), array(1, 3),    2);
        $this->checkDeliveryOrderNoRepost(array('1-n-n', '2-n-n', '3-y-n'), array(3),       1);
    }
    //--------------------------------------------------------------------------


    public function testLongDeliveryList() {
        $list = array(
            '1-n-n',
            '2-n-n',
            '3-n-n',
            '4-n-n',
            '5-n-n',
            '6-n-n',
            '7-n-n',
            '8-n-n',
            '9-y-n',
        );
        $this->checkDeliveryOrderNoRepost($list, array(9), 2, true);
        $this->assertEqual(8, sizeof($this->deliveryFailedToPartnerIDs));
        $this->assertEqual(1, sizeof($this->deliveredToPartnerIDs));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Count Always=yes Partners Against Leads Per
     */
    public function testCapalp() {
        $this->campaign['capalp'] = 0;
        $this->checkDeliveryOrderNoRepost(array('1-y-y', '2-y-n'),          array(1, 2),     1);

        $this->campaign['capalp'] = 1;
        $this->checkDeliveryOrderNoRepost(array('1-y-y', '2-y-n'),          array(1),        1);

        $this->campaign['capalp'] = 0;
        $this->checkDeliveryOrderNoRepost(array('1-y-y', '2-y-n', '3-y-n'),          array(1, 2, 3),     2);

        $this->campaign['capalp'] = 1;
        $this->checkDeliveryOrderNoRepost(array('1-y-y', '2-y-n', '3-y-n'),          array(1, 2),        2);
    }
    //--------------------------------------------------------------------------


    public function testAddingToRepostQueue() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-n-n', '2-n-n'), array(), 2, 1, false);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-n-n', '2-y-n'), array(2), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testAddingToRepostQueueWithOneHourDelay() {
        $this->repostHours = 1;
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $now = DB::$db->getTopLeft("SELECT NOW()");
        $repostQueue = $this->logIncoming->listRepostQueuesForIncomingId($this->engine->getIncomingId());
        $diff = strtotime($repostQueue[0]['repost_at']) - strtotime($now);

        // Time should be about an hour after now() so 10 seconds should be enough tolerance
        $this->assertTrue(abs(3600 - $diff) < 10);
    }
    //--------------------------------------------------------------------------


    public function testThatCompletelySuccessfulDeliveryIsNotReposted() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-y'), array(1, 2, 3), 2, 1);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2, 3), 3, 1);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testRepostingLastSubmission() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 0);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        // Change delivery settings so that repost sends everything
        $this->setupDelivery(array('1-y-n', '2-y-n'));
        $this->repostLastSubmission();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(0, sizeof($this->deliveredToPartnerIDs));
    }
    //--------------------------------------------------------------------------


    public function testRepostingEverythingForTestUser() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 0);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));


        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testRepostingWithSuccessOnRepost() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testPartnerRetries() {
        $p1Id = SetupTestData::$pids['C11-P01'];

        $this->checkDeliveryOrderNoRepost(array('1-n-n'), array(), 1, false);
        $this->assertEqual(array($p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        $this->assertEqual(array($p1Id), $this->deliveryFailedToPartnerIDs);

        $this->partnerRetries = 1;
        $this->checkDeliveryOrderNoRepost(array('1-n-n'), array(), 1, false);
        $this->assertEqual(array($p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        // Retries is 1, so we should have one more failed P1
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
        $this->repostLastSubmission();
        // But not another after reposting again since the one retry was already used up
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->partnerRetries = 2;
        $this->checkDeliveryOrderNoRepost(array('1-n-n'), array(), 1, false);
        $this->assertEqual(array($p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        // One more after first repost
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
        $this->repostLastSubmission();
        // One more after second repost
        $this->assertEqual(array($p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
        $this->repostLastSubmission();
        // No change after third repost since partner_retries is 2
        $this->assertEqual(array($p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
    }
    //--------------------------------------------------------------------------


    public function testPartnerUsernameRetriesHandling() {
        $this->testData = array('email' => 'c11@campaign.com', 'un' => 'joey');
        $p1Id = SetupTestData::$pids['C11-P01'];

        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id), $this->deliveryFailedToPartnerIDs);

        $this->partnerRetries = 1;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $p2Id = SetupTestData::$pids['C11-P02'];
        $this->partnerRetries = 1;
        $this->checkDeliveryOrderNoRepost(array('1-u-n', '2-y-n'), array(2), 1, true);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
        $this->assertEqual(array($p2Id), $this->deliveredToPartnerIDs);
    }
    //--------------------------------------------------------------------------


    public function testPartnerUsernameRetriesWithRepost() {
        $this->testData = array('email' => 'c11@campaign.com', 'un' => 'joey');
        $p1Id = SetupTestData::$pids['C11-P01'];


        $this->partnerRetries = 1;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
        $this->repostLastSubmission();
        // No new failures after repost because we already used up retry count
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->partnerRetries = 2;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        $this->assertEqual(array($p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->partnerRetries = 3;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        $this->assertEqual(array($p1Id, $p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        // 4 retries means 5 attempts total
        $this->partnerRetries = 4;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        // Should be 4 times failed partner 1 after first repost
        $this->assertEqual(array($p1Id, $p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $this->repostLastSubmission();
        // Should be 5 times failed partner 1 after second repost
        $this->assertEqual(array($p1Id, $p1Id, $p1Id, $p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);
    }
    //--------------------------------------------------------------------------


    public function testEveryUsernameRetryMarkedAsUsername() {
        $this->testData = array('email' => 'c11@campaign.com', 'un' => 'joey');
        $p1Id = SetupTestData::$pids['C11-P01'];

        $this->partnerRetries = 1;
        $this->checkDeliveryOrderNoRepost(array('1-u-n'), array(), 1, false);
        $this->assertEqual(array($p1Id, $p1Id), $this->deliveryFailedToPartnerIDs);

        $ids = $this->engine->getDeliveryIDs();
        $this->assertEqual(2, sizeof($ids));

        $logDelivery = SingletonRegistry::getModelLogDelivery();
        $d1 = $logDelivery->get($ids[0]);
        $d2 = $logDelivery->get($ids[1]);
        $this->assertEqual('username', $d1['reject_reason']);
        $this->assertEqual('username', $d2['reject_reason']);
    }
    //--------------------------------------------------------------------------


    public function testDeliveryToCappedPartnerWithAlwaysYes() {
        SetupTestData::deleteEverythingForTestUser();
        $partnerId = SetupTestData::$pids['C11-P01'];
        $partner = $this->modelPartner->get($partnerId);

        $p2 = $partner;
        $p2['delivery_cap']   = 1;
        $p2['delivery_ctype'] = 1; // per hour
        $this->modelPartner->updateDiff($partnerId, $p2, $partner);

        $this->setupDelivery(array('1-y-y'));
        try {
            $engine = $this->newEngineSubmission(array('email' => __FUNCTION__ . '@test.com'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $this->assertEqual($this->deliveredToPartnerIDs, array($partnerId));
        }
        $this->deliveredToPartnerIDs      = array();
        $this->deliveryFailedToPartnerIDs = array();


        try {
            $engine = $this->newEngineSubmission(array('email' => __FUNCTION__ . '@test.com'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($this->campaign);
        } catch (Exception $e) {
            $this->assertEqual($this->deliveredToPartnerIDs, array());
        }

        $p3 = $p2;
        $p3['delivery_cap']   = 0;
        $p3['delivery_ctype'] = 0;
        $this->modelPartner->updateDiff($partnerId, $p3, $p2);
    }
    //--------------------------------------------------------------------------
}
