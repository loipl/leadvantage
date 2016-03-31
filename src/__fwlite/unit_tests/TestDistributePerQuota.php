<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestDistributePerQuota extends UnitTestCase {


    public function testDistribute0() {
        $arr = array(1 => 300, 2 => 30, 4 => 50);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 100);
        $this->assertEqual(3, sizeof($counts));
        $this->assertEqual(35, $counts[1]);
        $this->assertEqual(30, $counts[2]);
        $this->assertEqual(35, $counts[4]);
    }
    //--------------------------------------------------------------------------


    public function testDistribute1() {
        $arr = array(1 => 100, 2 => 500, 3 => 800);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 1000);
        $this->assertEqual(3, sizeof($counts));
        $this->assertEqual(100, $counts[1]);
        $this->assertEqual(450, $counts[2]);
        $this->assertEqual(450, $counts[3]);
    }
    //--------------------------------------------------------------------------


    public function testDistribute2() {
        $arr = array(1 => 100, 2 => 200, 3 => 800);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 1000);
        $this->assertEqual(3, sizeof($counts));
        $this->assertEqual(100, $counts[1]);
        $this->assertEqual(200, $counts[2]);
        $this->assertEqual(700, $counts[3]);
    }
    //--------------------------------------------------------------------------


    public function testDistribute3() {
        $arr = array(1 => 100, 2 => 200, 3 => 800, 4 => 700);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 1000);
        $this->assertEqual(4, sizeof($counts));
        $this->assertEqual(100, $counts[1]);
        $this->assertEqual(200, $counts[2]);
        $this->assertEqual(350, $counts[3]);
        $this->assertEqual(350, $counts[4]);
    }
    //--------------------------------------------------------------------------


    public function testDistribute4() {
        $arr = array(1 => 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 400);
        $this->assertEqual(28, sizeof($counts));
        $this->assertEqual(400, array_sum($counts));
    }
    //--------------------------------------------------------------------------

    public function testDistribute5() {
        $arr = array (4 => 2056, 8 => 1613, 18 => 64912);
        $counts = Helper_RepostSubset::distributePerQuota($arr, 1000);
        $this->assertEqual(3, sizeof($counts));
        $this->assertEqual(334, $counts[8]);
        $this->assertEqual(333, $counts[4]);
        $this->assertEqual(333, $counts[18]);
    }
    //--------------------------------------------------------------------------


    public function testHelperRepostSubsetDistribute1() {
        $userRows = array (
            2 => array (
                '268_0' => array (
                    array (
                        'incoming_id' => '6913',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                )
            )
        );
        $result = Model_LogIncomingRepost::getValuesForInsertDistributedPerUserAndCampaign($userRows);
        $this->assertEqual(sizeof($result), 1);
        $this->assertEqual($result[0], "(6913, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
    }
    //--------------------------------------------------------------------------


    public function testHelperRepostSubsetDistribute2() {
        $userRows = array (
            2 => array (
                '268_0' => array (
                    array (
                        'incoming_id' => '6913',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    ),
                    array (
                        'incoming_id' => '1111',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                )
            )
        );
        $result = Model_LogIncomingRepost::getValuesForInsertDistributedPerUserAndCampaign($userRows);
        $this->assertEqual(sizeof($result), 2);
        $this->assertEqual($result[0], "(6913, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[1], "(1111, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
    }
    //--------------------------------------------------------------------------


    public function testHelperRepostSubsetDistribute3() {
        $userRows = array (
            2 => array (
                '268_0' => array (
                    array (
                        'incoming_id' => '6913',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    ),
                    array (
                        'incoming_id' => '1111',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                ),
                '269_0' => array (
                    array (
                        'incoming_id' => '2222',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                )
            )
        );
        $result = Model_LogIncomingRepost::getValuesForInsertDistributedPerUserAndCampaign($userRows);
        $this->assertEqual(sizeof($result), 3);
        $this->assertEqual($result[0], "(6913, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[1], "(2222, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[2], "(1111, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
    }
    //--------------------------------------------------------------------------


    public function testHelperRepostSubsetDistribute4() {
        $userRows = array (
            2 => array (
                '268_0' => array (
                    array (
                        'incoming_id' => '11',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    ),
                    array (
                        'incoming_id' => '12',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                ),
                '269_0' => array (
                    array (
                        'incoming_id' => '21',
                        'campaign_id' => '268',
                        'catchup_id' => NULL,
                        'user_id' => '2',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                )
            ),
            3 => array (
                '123_0' => array (
                    array (
                        'incoming_id' => '31',
                        'campaign_id' => '123',
                        'catchup_id' => NULL,
                        'user_id' => '3',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    ),
                    array (
                        'incoming_id' => '32',
                        'campaign_id' => '123',
                        'catchup_id' => NULL,
                        'user_id' => '3',
                        'repost_at' => '2013-02-07 15:44:35',
                        'row_key' => '268_0',
                    )
                )
            )
        );
        $result = Model_LogIncomingRepost::getValuesForInsertDistributedPerUserAndCampaign($userRows);
        $this->assertEqual(sizeof($result), 5);
        $this->assertEqual($result[0], "(11, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[1], "(31, 123, NULL, 3, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[2], "(21, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[3], "(32, 123, NULL, 3, '2013-02-07 15:44:35', '268_0')");
        $this->assertEqual($result[4], "(12, 268, NULL, 2, '2013-02-07 15:44:35', '268_0')");
    }
    //--------------------------------------------------------------------------

}
