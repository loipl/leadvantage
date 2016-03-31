<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestDeliveryWithPartnerCaps extends C11UnitTests {
    private $shadowOwnerUserId;

    public function test1() {
        $campaignId = SetupTestData::$cids['UTC11 - Shadow Copy 4'];
        $this->initialize($campaignId);

        $list = array('1-f-n');
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->testData['email'] = 'another@email.com';
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->testData['email'] = 'another2@email.com';
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(3, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $count = $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(2, $count);

        $pid18 = SetupTestData::$pids['C11-P18'];

        $dummy = 0;
        SingletonRegistry::getModelLogIncoming()->createCatchUpJob($dummy, $campaignId, $this->shadowOwnerUserId, $pid18, 10, 0, 1000, SetupTestData::$cids['UTC11'], 0, 0, TEST_USER_ID);

        $count = $this->repostAllForTestUser();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(3, $count);

        $count = $this->repostAllForTestUser();
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testCatchupJobWithRateLimit() {
        $campaignId = SetupTestData::$cids['UTC11 - Shadow Copy 4'];
        $this->initialize($campaignId);

        $list = array('1-f-n');
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->testData['email'] = 'another@email.com';
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->testData['email'] = 'another2@email.com';
        $this->checkDeliveryOrderNoRepost($list, array(), 1, false);
        $this->assertEqual(3, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $count = $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(2, $count);

        $pid18 = SetupTestData::$pids['C11-P18'];
        $dummy = 0;
        SingletonRegistry::getModelLogIncoming()->createCatchUpJob($dummy, $campaignId, $this->shadowOwnerUserId, $pid18, 10, 0, 1000, SetupTestData::$cids['UTC11'], 1, 2, TEST_USER_ID);

        $count = $this->repostAllForTestUser();

        // One should have been postponed
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->assertEqual(2, $count);

        $count = $this->repostAllForTestUser();

        // The one postponed row will be postponed for an hour so it won't be processed now
        $this->assertEqual(0, $count);

        // ... and we still have one row
        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    private function initialize($campaignId) {
        SetupTestData::deleteEverythingForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $shadow = $this->modelCampaign->get($campaignId);
        if (!$shadow) {
            die('Missing campaign ' . __FILE__ . ':' . __LINE__);
        }

        $shadow['should_repost'] = '0';
        $shadow['repost_hours'] = 0;
        $shadow['repost_max'] = 0;
        $shadow['leads_per'] = 1;
        $shadow['is_active'] = '1';
        $shadow['skim_percentage'] = 0;
        $shadow['shadow_of'] = SetupTestData::$cids['UTC11'];
        $shadow['shadow_delay_hours'] = 0;

        $this->modelCampaign->update($shadow['id'], $shadow);
        $this->shadowOwnerUserId = $shadow['user_id'];

        $pid17 = SetupTestData::$pids['C11-P17'];
        $index = array_search(Model_Partner::DELIVERY_CTYPE_HOUR, Model_Partner::$deliveryTypes);

        $ds = $this->modelCampaign->listDeliverySettings($campaignId);
        $this->assertEqual(1, sizeof($ds));
        $this->assertEqual($pid17, $ds[0]['partner_id']);

        $partner = SingletonRegistry::getModelPartner()->get($pid17);
        $this->assertEqual($index, $partner['delivery_ctype']);
        $this->assertEqual(2, $partner['delivery_cap']);

    }
}
