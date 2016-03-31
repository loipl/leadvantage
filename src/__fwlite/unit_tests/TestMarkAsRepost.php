<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestMarkAsRepost extends C11UnitTests {

    public function testFirstNotMarkedAsRepost() {
        $this->campaignId = SetupTestData::$cids['UTC22'];
        $this->campaign   = $this->modelCampaign->get($this->campaignId);

        try {
            $this->checkDeliveryOrderAndTurnRepostOn($this->fullList, array(1, 2), 2, 2, true, true);
        } catch (EDoneException $e) {
            $this->assertEqual('SUCCESS', $e->getMessage());
        }

        $this->assertEqual(1, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $repostQueue = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual(1, sizeof($repostQueue));
        $this->assertEqual(2, $repostQueue[0]['remaining_count']);

        $incomingId = $this->engine->getIncomingId();
        $logDelivery = SingletonRegistry::getModelLogDelivery();
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($incomingId, $this->campaignId);
        $this->assertEqual(0, sizeof($deliveries));

        $this->repostAllForTestUser();
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($incomingId, $this->campaignId);
        $deliveryIDs = array();
        foreach ($deliveries as $del) {
            $this->assertEqual('0', $del['is_repost']);
            $deliveryIDs[] = (int)$del['id'];
        }

        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($incomingId, $this->campaignId);
        foreach ($deliveries as $del) {
            $this->assertEqual('0', $del['is_repost']);
        }

        $this->repostAllForTestUser();
        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($incomingId, $this->campaignId);
        foreach ($deliveries as $del) {
            if (in_array($del['id'], $deliveryIDs)) {
                continue;
            }
            $this->assertEqual('1', $del['is_repost']);
        }

        $deliveries = $logDelivery->listDeliveriesForIncomingIdAndCampaignId($incomingId, $this->campaignId);
        foreach ($deliveries as $del) {
            if (in_array($del['id'], $deliveryIDs)) {
                continue;
            }
            $this->assertEqual('1', $del['is_repost']);
        }

    }
    //--------------------------------------------------------------------------
}
