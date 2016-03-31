<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestShadowCampaigns extends C11UnitTests {

    public function __construct() {
        parent::__construct();
        $this->logIncoming = SingletonRegistry::getModelLogIncoming();
    }
    //--------------------------------------------------------------------------


    public function testRepostIntervalOnOOBCampaign() {
        //
    }
    //--------------------------------------------------------------------------


    public function testShadowPartnerListNoFiltering() {
        $campaignId = SetupTestData::$cids['UTC11 - Shadow Copy 3'];
        $this->initializeShadowCopy3($campaignId, 0);

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-http://xxx.com-n', '2-http://yyy.com-n'), array(1, 2), 2, 1);

        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual($campaignId, $repostQueue[0]['campaign_id']);
        $this->assertTrue(timeDiff($repostQueue[0]['repost_at']) < 10);

        try {
            $this->waitAction = Engine::ACTION_DELIVER_REP_LIST;
            $this->setupDelivery(array('5-http://xxx.com-n', '6-http://aaa.com-n'), $campaignId);
            $this->repostAllForTestUser($this->cbFinish, false);
        } catch (EFWUT $e) {
            $this->assertEqual(Engine::ACTION_DELIVER_REP_LIST, $e->getMessage());

            $this->assertEqual(2, sizeof($this->lastData));
            $this->assertEqual('http://xxx.com', $this->lastData[SetupTestData::$pids['C11-P05']]['delivery_addr']);
            $this->assertEqual('http://aaa.com', $this->lastData[SetupTestData::$pids['C11-P06']]['delivery_addr']);
        }
    }
    //--------------------------------------------------------------------------


    private function initializeShadowCopy3($campaignId, $delayHours) {
        $shadow = $this->modelCampaign->get($campaignId);
        if ($shadow) {
            $shadow['should_repost'] = '1';
            $shadow['repost_hours'] = 0;
            $shadow['repost_max'] = 2;
            $shadow['leads_per'] = 2;
            $shadow['is_active'] = '1';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $shadow['shadow_delay_hours'] = $delayHours;
            $this->modelCampaign->update($shadow['id'], $shadow);
        }
    }
    //--------------------------------------------------------------------------


    public function testShadowPartnerListRemovedUsedPartner() {
        $campaignId = SetupTestData::$cids['UTC11 - Shadow Copy 3'];
        $this->initializeShadowCopy3($campaignId, 0);

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-http://xxx.com-n', '2-http://yyy.com-n'), array(1, 2), 2, 1);
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual($campaignId, $repostQueue[0]['campaign_id']);
        $this->assertTrue(timeDiff($repostQueue[0]['repost_at']) < 10);

        try {
            $this->waitAction = Engine::ACTION_DELIVER_REP_LIST;
            $this->setupDelivery(array('5-http://xxx.com-n', '6-http://aaa.com-n'), $campaignId);
            $this->repostAllForTestUser(array($this, 'callbackFinish'), true);
        } catch (EFWUT $e) {
            $this->assertEqual(1, sizeof($this->lastData));
            $this->assertEqual('http://aaa.com', $this->lastData[SetupTestData::$pids['C11-P06']]['delivery_addr']);
        }
    }
    //--------------------------------------------------------------------------


    public function testShadowRepostDelay() {
        $campaignId = SetupTestData::$cids['UTC11 - Shadow Copy'];
        $this->initializeShadowCopy3($campaignId, 10);

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(SetupTestData::$cids['UTC11 - Shadow Copy'], $repostQueue[0]['campaign_id']);
        $this->assertTrue(timeDiff($repostQueue[0]['repost_at'], 10 * 3600) < 10);
    }
    //--------------------------------------------------------------------------


    public function testShadowAndSourceIsRepostField() {
        $this->initializeShadowCopy3(SetupTestData::$cids['UTC11 - Shadow Copy 2'], 0);

        // This is only needed so source campaign will be second in list of repost queue for user
        $this->repostHours = 1;

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2, 1);
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(2, sizeof($repostQueue));
        $shadowRepost = $repostQueue[0];
        $sourceRepost = $repostQueue[1];
        $this->assertEqual(SetupTestData::$cids['UTC11 - Shadow Copy 2'], $shadowRepost['campaign_id']);
        $this->assertEqual(SetupTestData::$cids['UTC11'],                 $sourceRepost['campaign_id']);

        $logDelivery = SingletonRegistry::getModelLogDelivery();
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($this->engine->getIncomingId(), SetupTestData::$cids['UTC11']);
        $this->assertEqual(2, sizeof($deliveries));
        foreach ($deliveries as $del) {
            $this->assertEqual('0', $del['is_repost'], 'Original submission should have is_repost=0');
        }

        $this->assertEqual(2, $this->repostAllForTestUser());
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($this->engine->getIncomingId(), SetupTestData::$cids['UTC11 - Shadow Copy 2']);
        $this->assertEqual(2, sizeof($deliveries));
        foreach ($deliveries as $del) {
            $this->assertEqual('0', $del['is_repost'], 'First repost for OOB campaign should have is_repost=0');
        }

        $this->assertEqual(1, $this->repostAllForTestUser());
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($this->engine->getIncomingId(), SetupTestData::$cids['UTC11 - Shadow Copy 2']);
        $this->assertEqual(3, sizeof($deliveries));
        $this->assertEqual('1', $deliveries[2]['is_repost'], '%s - Second repost for OOB campaign should have is_repost=1');
    }
    //--------------------------------------------------------------------------


    public function testShadowAndRepostCounters() {
        $shadow = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy']);
        if ($shadow) {
            $shadow['should_repost'] = '1';
            $shadow['repost_hours'] = 0;
            $shadow['repost_max'] = 1;
            $shadow['is_active'] = '1';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $this->modelCampaign->update($shadow['id'], $shadow);
        }

        $this->repostHours = 1;
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2, 1);
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(2, sizeof($repostQueue));
        $shadowRepost = $repostQueue[0];
        $sourceRepost = $repostQueue[1];
        $this->assertEqual(SetupTestData::$cids['UTC11 - Shadow Copy'], $shadowRepost['campaign_id']);
        $this->assertEqual(SetupTestData::$cids['UTC11'], $sourceRepost['campaign_id']);
        $this->assertTrue(timeDiff($shadowRepost['repost_at']) < 10);
        $this->assertTrue(timeDiff($sourceRepost['repost_at'], 3600) < 10);

        $this->assertEqual(1, $shadowRepost['remaining_count']);
        $this->assertEqual(1, $sourceRepost['remaining_count']);

        // Only one was processed because source campaign is one hour into the future
        $this->assertEqual(1, $this->repostAllForTestUser());

        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);

        // OOB campaign should be gone because it only had one partner and it's already delivered to
        $this->assertEqual(1, sizeof($repostQueue));
        $sourceRepost = $repostQueue[0];
        $this->assertEqual(SetupTestData::$cids['UTC11'], $sourceRepost['campaign_id']);

        // Make sure times are still correct
        $this->assertTrue(timeDiff($sourceRepost['repost_at'], 3600) < 10);

        // And make sure it only reduced remaining_count to the processed one
        $this->assertEqual(1, $sourceRepost['remaining_count']);
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Check if reposting everything will process both shadow and source campaigns
     */
    public function testShadowCampaignRepostWorksForShadowAndSourceCampaign() {
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $shadow = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy']);
        if ($shadow) {
            $shadow['should_repost'] = '0';
            $shadow['repost_hours'] = 0;
            $shadow['repost_max'] = 0;
            $shadow['is_active'] = '1';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $this->modelCampaign->update($shadow['id'], $shadow);
        }

        $this->repostHours = 1;

        // with shadow it should be 2
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n', '3-y-n'), array(1, 2), 2, 0);
        $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(1, $this->repostAllForTestUser());

        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        // Source campaign should still have one repost
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(SetupTestData::$cids['UTC11'], $repostQueue[0]['campaign_id']);
    }
    //--------------------------------------------------------------------------


    public function testShadowCampaignValuesWhenAddingToRepostQueue() {
        $shadow = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy 2']);
        if ($shadow) {
            $shadow['is_active'] = '1';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $shadow['should_repost'] = '1';
            $shadow['repost_hours'] = 3;
            $shadow['repost_max'] = 2;
            $this->modelCampaign->update($shadow['id'], $shadow);
        }

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(SetupTestData::$cids['UTC11 - Shadow Copy 2'], $repostQueue[0]['campaign_id']);
        $this->assertEqual(2, $repostQueue[0]['remaining_count']);
        $this->assertEqual(3, $repostQueue[0]['interval_hours']);

        $time = strtotime($repostQueue[0]['repost_at']);
        $now  = strtotime(DB::$db->getTopLeft("SELECT NOW()"));
        $this->assertTrue(abs($time - $now) < 10);

        $this->repostAllForTestUser();

        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $time = strtotime($repostQueue[0]['repost_at']);
        $this->assertTrue(abs($time - ($now + 3 * 3600)) < 10);

        $this->logIncoming->deleteUsersRepostsFromQueue(TEST_USER_ID);
    }
    //--------------------------------------------------------------------------


    public function testShadowCampaignHandling() {

        // Checks without shadow campaign
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $shadow = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy']);
        if ($shadow) {
            $shadow['should_repost'] = '0';
            $shadow['is_active'] = '1';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $this->modelCampaign->update($shadow['id'], $shadow);
        }


        // Same checks but with shadow campaign, make sure there is one more entry in repost queue
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);

        // Check that shadow campaign is added to repost queue
        $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        // Make sure that shadow campaign is added even if all partners from source campaign
        // accepted the data
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(SetupTestData::$cids['UTC11 - Shadow Copy'], $repostQueue[0]['campaign_id']);
        $this->assertEqual(0, $repostQueue[0]['interval_hours']);
        $this->assertEqual(0, $repostQueue[0]['remaining_count']);

        $cnt = $this->repostAllForTestUser();
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(0, sizeof($repostQueue));
    }
    //--------------------------------------------------------------------------


    public function testInactiveShadowCampaignsAreIgnored() {
        // Checks without shadow campaign
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $shadow = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy']);
        if ($shadow) {
            $shadow['is_active'] = '0';
            $shadow['should_repost'] = '0';
            $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
            $this->modelCampaign->update($shadow['id'], $shadow);
        }

        // Checks with inactive shadow campaign
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-n-n'), array(1), 2, 1);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------
}