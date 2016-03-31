<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestOOBCampaign extends MyUT {

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming;

    private $oldIp = false;


    public function setUp() {
        SetupTestData::deleteEverythingForTestUser();
        $this->modelCampaign or $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $this->logIncoming   = SingletonRegistry::getModelLogIncoming();

        $this->oldIp = $_SERVER['REMOTE_ADDR'];
    }
    //--------------------------------------------------------------------------


    public function tearDown() {
        $_SERVER['REMOTE_ADDR'] = $this->oldIp;
    }
    //--------------------------------------------------------------------------


    public function testSysFieldIpAddressOverride() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC21']);
        $_SERVER['REMOTE_ADDR'] = '22.22.22.22';

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@unit-tests.com', 'ipadd' => '55.66.77.88'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
            $this->assertEqual(1, sizeof($repostQueue));
            $this->assertEqual($campaign['repost_hours'], $repostQueue[0]['interval_hours']);
        }

        try {
            $this->waitAction = Engine::ACTION_SENT_VALS;
            $this->repostAllForTestUser(array($this,'callbackFinish'));
        } catch (EFWUT $e) {
            $this->assertEqual('55.66.77.88', $this->lastData['ipadd_out']);
        }
    }
    //--------------------------------------------------------------------------


    public function testDeliveryOrder() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC15']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
            $this->assertEqual(1, sizeof($repostQueue));
            $this->assertEqual($campaign['repost_hours'], $repostQueue[0]['interval_hours']);
        }
    }
    //--------------------------------------------------------------------------


    public function testOneMinuteRepostThrottle() {
        $this->logIncoming   = SingletonRegistry::getModelLogIncoming();
        $this->logIncoming->deleteUsersRepostsFromQueue(TEST_USER_ID);

        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(0, $count);

        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC15']);
        $this->assertEqual(1, $campaign['is_oob']);
        $this->assertEqual(0, $campaign['throttle_type']);
        $this->assertEqual(1, $campaign['throttle_value']);

        SingletonRegistry::getModelLogIncomingRepost()->deleteShRepostForCampaign($campaign['id']);
        $now = $this->makeSureWeHaveMoreThan5SecondsTillMinuteEnds();

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test1@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            //
        }
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(1, $count);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            //
        }
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(2, $count);

        $this->repostAllForTestUser();
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(1, $count);

        $this->repostAllForTestUser();
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);

        $this->assertEqual(1, $count, 'Second repost_queue row should not be processed becasue campaign throttle is one per minute');
        $ts = DB::$db->getTopLeft("SELECT NOW()");

        $this->assertEqual(substr($now, -5, 2), substr($ts, -5, 2), "Both reposts should have happened in same minute, re-run the tests");
    }
    //--------------------------------------------------------------------------


    private function makeSureWeHaveMoreThan5SecondsTillMinuteEnds() {
        $now = DB::$db->getTopLeft("SELECT NOW()");
        if (substr($now, -2) > 55) {
            // Make sure we have at least 5 seconds time so both reposts happen inside same minute
            sleep(61 - substr($now, -2));
            $now = DB::$db->getTopLeft("SELECT NOW()");
            if (substr($now, -2) > 55) {
                die($now);
            }
        }
        return $now;
    }
    //--------------------------------------------------------------------------


    public function testOneHourRepostThrottle() {
        $this->logIncoming   = SingletonRegistry::getModelLogIncoming();
        $this->logIncoming->deleteUsersRepostsFromQueue(TEST_USER_ID);

        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(0, $count);

        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC28']);
        $this->assertEqual(1, $campaign['is_oob']);
        $this->assertEqual(1, $campaign['throttle_type']);
        $this->assertEqual(2, $campaign['throttle_value']);

        SingletonRegistry::getModelLogIncomingRepost()->deleteShRepostForCampaign($campaign['id']);

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        }

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'), $this->cbDelivered);
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual(3, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        }

        $this->repostAllForTestUser();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual(1, $count, 'Third repost_queue row should not be processed becasue campaign throttle is two per hour');
    }
    //--------------------------------------------------------------------------


    public function testApiOobCapOneHour() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC31']);
        $this->assertEqual(1, $campaign['is_oob']);
        $this->assertEqual(1, $campaign['is_oob_capped']);
        $this->assertEqual(1, $campaign['oob_cap_type']);
        $this->assertEqual(2, $campaign['oob_cap_value']);

        $logIncoming = SingletonRegistry::getModelLogIncoming();

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(1, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(2, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('FAIL', $e->getMessage());
            $this->assertEqual(2, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('FAIL', $e->getMessage());
            $this->assertEqual(2, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Test API-mode cap. This limits how many submissions are accepted per period, and
     * will reject all that are over the quota. Specs are to only count success and not count
     * deliveries where all partners are skipped. Since API campaigns will only know this after
     * first repost I test counts separately before and after reposts.
     */
    public function testApiOobCapOneHourNoDeliveryPartners() {
        $campaign = $this->modelCampaign->get(SetupTestData::$cids['UTC32']);

        $this->assertEqual(1, $campaign['is_oob']);
        $this->assertEqual(1, $campaign['is_oob_capped']);
        $this->assertEqual(1, $campaign['oob_cap_type']);
        $this->assertEqual(2, $campaign['oob_cap_value']);

        $logIncoming = SingletonRegistry::getModelLogIncoming();

        try {
            $engine = $this->newEngineSubmission(array('email' => 'test@yahoo.com', 'first_name' => 'Name'));
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(1, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }
        $this->repostAllForTestUser();
        $this->assertEqual(0, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(1, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }
        $this->repostAllForTestUser();
        $this->assertEqual(0, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(1, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }

        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
            $this->assertEqual(2, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }

        // This one will fail because there are already 2 accepted but snub_count is not updated until after
        // repost, ie after $this->repostAllForTestUser();
        try {
            $engine->processIncomingFormSubmission($campaign);
        } catch (EDoneException $e) {
            $this->assertEqual('FAIL', $e->getMessage());
            $this->assertEqual(2, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
        }
        $this->repostAllForTestUser();
        $this->assertEqual(0, $logIncoming->campaignIncomingCount($campaign['id'], Model_Partner::DELIVERY_CTYPE_HOUR));
    }
    //--------------------------------------------------------------------------
}
