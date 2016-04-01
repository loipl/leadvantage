<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestCatchupJobs extends C11UnitTests {
    private $addRows          = 21;
    private $hourlyLimit      = 5;
    private $partnerHourlyCap = 8;
    private $partnerId;
    private $catchupId;

    /**
     * @var Model_PartnerFilter
     */
    private $modelPartnerFilter;


    public function __construct() {
        parent::__construct();
        $this->modelPartnerFilter = SingletonRegistry::getModelPartnerFilter();
    }
    //--------------------------------------------------------------------------


    public function testCatchupJobAllSkip() {
        SetupTestData::deleteEverythingForTestUser();
        $this->setImpossibleFilterOnPartner('C11-P01');

        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->setupDelivery(array('2-y-n'), SetupTestData::$cids['UTC11']);
        $this->createTestData(2);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->partnerId = SetupTestData::$pids['C11-P01'];
        $rlType          = array_search(Model_Partner::DELIVERY_CTYPE_HOUR, Model_Partner::$deliveryTypes);

        $entriesAdded = 0;
        $this->catchupId = $this->logIncoming->createCatchUpJob($entriesAdded, SetupTestData::$cids['UTC11'], TEST_USER_ID, $this->partnerId, 1, 0, 0, false, $rlType, $this->hourlyLimit, TEST_USER_ID);


        $this->assertEqual(2, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
        $this->repostAllForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->deleteFiltersForPartnerName('C11-P01');
    }
    //--------------------------------------------------------------------------


    private function setImpossibleFilterOnPartner($partnerName) {
        $data = array (
            'partner_id'    => SetupTestData::$pids[$partnerName],
            'filter_action' => 1,
            'field_type_id' => Model_CampaignField::FIELD_TYPE_EMAIL,
            'system_field'  => '',
            'match_type'    => - Model_PartnerFilter::FILTER_MATCH_EQUALS,
            'match_value'   => '@@',
            'argument'      => '',
            'is_active'     => '1'
        );

        $this->modelPartnerFilter->insert($data);
    }
    //--------------------------------------------------------------------------


    private function deleteFiltersForPartnerName($partnerName) {
        $this->modelPartnerFilter->deleteWhere(array('partner_id' => SetupTestData::$pids[$partnerName]));
    }
    //--------------------------------------------------------------------------


    public function testCatchUpJobAddingAndDeleting() {
        SetupTestData::deleteEverythingForTestUser();
        $this->checkDeliveryOrderAndTurnRepostOn(array('1-y-n', '2-y-n'), array(1, 2), 2, 1);

        $count = $this->logIncoming->countOfCatchUpJobsForUser(TEST_USER_ID);
        $this->assertEqual(0, $this->logIncoming->countOfCatchUpJobsForUser(TEST_USER_ID));
        $x = 0;
        $this->logIncoming->createCatchUpJob($x, SetupTestData::$cids['UTC11'], 2, SetupTestData::$pids['C11-P11'], 1, 0, 1, false, 0, 0, TEST_USER_ID);
        $this->assertEqual($count + 1, $this->logIncoming->countOfCatchUpJobsForUser(TEST_USER_ID));

        $this->repostAllForTestUser();
        $this->assertEqual($count, $this->logIncoming->countOfCatchUpJobsForUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    public function testRepostingJobWithRateLimits() {
        $this->createTestDataAndCatchupJob();

        $this->repostAllForTestUser();
        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $this->assertEqual($this->addRows - $count, $this->hourlyLimit);

        $this->setupPartnerWithHourlyCapAndRepost();

        $count = $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID);
        $deliveredToPartner = SingletonRegistry::getModelLogDelivery()->countDeliveriesForPartner($this->partnerId);

        $this->assertEqual($this->addRows - $this->partnerHourlyCap, $count);
        $this->assertEqual($this->addRows - $deliveredToPartner, $count);
        $this->assertEqual($deliveredToPartner, $this->partnerHourlyCap);

        $arr = $this->logIncoming->listRepostQueuesForUserId(TEST_USER_ID);
        $this->assertEqual($this->addRows - $deliveredToPartner, sizeof($arr));

        if ($this->addRows == 21 && $this->partnerHourlyCap == 8) {
            // Of the remaining 13 repost_queue entries, 2 should be postponed an hour to the future,
            // 11 left as is
            $postponed = $notPostponed = 0;
            foreach ($arr as $row) {
                $t = strtotime($row['repost_at']);
                if ($t - 3000 > time()) {
                    $postponed++;
                } else {
                    $notPostponed++;
                }
            }
            $this->assertEqual(2, $postponed);
            $this->assertEqual(11, $notPostponed);
        }
    }
    //--------------------------------------------------------------------------


    private function createTestDataAndCatchupJob() {
        SetupTestData::deleteEverythingForTestUser();
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->setupDelivery(array('1-y-n', '2-y-n'), SetupTestData::$cids['UTC11']);
        $this->createTestData($this->addRows);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        $this->partnerId = SetupTestData::$pids['C11-P11'];
        $rlType          = array_search(Model_Partner::DELIVERY_CTYPE_HOUR, Model_Partner::$deliveryTypes);

        $entriesAdded = 0;
        $this->catchupId = $this->logIncoming->createCatchUpJob($entriesAdded, SetupTestData::$cids['UTC11'], TEST_USER_ID, $this->partnerId, 1, 0, 0, false, $rlType, $this->hourlyLimit, TEST_USER_ID);
        $this->assertEqual($this->addRows, $entriesAdded);
        $this->assertEqual($this->addRows, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));
    }
    //--------------------------------------------------------------------------


    private function setupPartnerWithHourlyCapAndRepost() {
        $p = $this->modelPartner->get($this->partnerId, MYSQL_ASSOC);
        $oldCT = $p['delivery_ctype'];
        $oldCV = $p['delivery_cap'];

        $p['delivery_ctype'] = 1;
        $p['delivery_cap']   = $this->partnerHourlyCap;
        $this->modelPartner->update($p['id'], $p);

        // All sh_catchup entries for this job_id are deleted, so reposting code
        // will think we sent nothing. But entries for partner are still left in log_delivery
        $this->logIncoming->deleteCatchupLogForJobId($this->catchupId);

        $this->repostAllForTestUser();

        $p['delivery_ctype'] = $oldCT;
        $p['delivery_cap']   = $oldCV;
        $this->modelPartner->update($p['id'], $p);
    }
    //--------------------------------------------------------------------------


    private function createTestData($count = 100) {
        $engine = $this->newEngineSubmission();
        for ($i = 0; $i < $count; $i++) {
            try {
                $engine->setTestIncomingData(array('email' => "$i.testData@unittest.com", 'un' => "user{$i}name", 'ph' => ''));
                $engine->processIncomingFormSubmission($this->campaign);
            } catch (Exception $e) {
                //
            }
        }
    }
    //--------------------------------------------------------------------------
}
