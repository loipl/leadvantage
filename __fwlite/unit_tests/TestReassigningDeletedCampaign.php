<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

/**
 * @desc If user is deleted from wordpress and he has campaigns which are sources for
 * shadow parasite campaigns, those source campaigns need to be reassigned to leadwrench
 * user so we preserve the data, and name of the campaign modified if need be.
 */
class TestReassigningDeletedCampaign extends MyUT {

    /**
     * @desc ID of existing wordpress user we will use for testing. 16 should be lwdemo
     */
    const USER_FOR_TESTING = 16;

    /**
     * @desc We will temporarily change ID in wp_users to make it look like user was deleted.
     * This will be the temp id.
     */
    const TEMPORARY_USER_ID = 15;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    private $campaignId;

    private $campaign;

    private $shadowId = 0;


    public function setUp() {

        // In case previous unit test was stopped in half, we will have an invalid user id in wp_users table
        DB::$wpDb->query("UPDATE `wp_users` SET `ID` = ? WHERE `ID` = ?", array(self::USER_FOR_TESTING, self::TEMPORARY_USER_ID));

        // Delete all campaigns for test user (lwdemo)
        $this->modelCampaign->deleteWhere(array('user_id' => self::USER_FOR_TESTING));
    }
    //--------------------------------------------------------------------------


    public function tearDown() {
        DB::$wpDb->query("UPDATE `wp_users` SET `ID` = ? WHERE `ID` = ?", array(self::USER_FOR_TESTING, self::TEMPORARY_USER_ID));
        $this->modelCampaign->deleteWhere(array('user_id' => self::USER_FOR_TESTING));

        $this->modelCampaign->delete($this->campaignId);

        DB::$db->query("ALTER TABLE `campaigns` AUTO_INCREMENT =1");
        DB::$db->query("ALTER TABLE `system_messages` AUTO_INCREMENT =1");
    }
    //--------------------------------------------------------------------------


    public function __construct() {
        parent::__construct();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();
    }
    //--------------------------------------------------------------------------


    public function test() {
        $this->createTestCampaign('should disappear');
        $idOfNonShadowedCampaign = $this->campaignId;

        $this->createTestCampaign('test');
        $this->createShadowOfTestCampaign();
        $this->changeToTmpUserId();

        $this->reassign();

        // This one should be gone since it had no shadow campaigns
        $row = $this->modelCampaign->get($idOfNonShadowedCampaign);
        $this->assertNull($row['user_id']);

        $this->checkCorrectReplacement();
        $this->checkMessage();

        $purge = new Helper_Purge(1600);
        $purge->purgeDeleted();

        // This one should be gone since it had no shadow campaigns
        $row = $this->modelCampaign->get($idOfNonShadowedCampaign);
        $this->assertTrue(empty($row));
    }
    //--------------------------------------------------------------------------


    private function createTestCampaign($name, $userId = self::USER_FOR_TESTING) {
        $exampleCid = SetupTestData::$cids['UTC00'];
        $campaign = $this->modelCampaign->get($exampleCid, MYSQL_ASSOC);

        $campaign['user_id']       = $userId;
        $campaign['name']          = $name;
        $campaign['is_oob']        = '0';
        $campaign['should_repost'] = '0';

        unset($campaign['id']);
        unset($campaign['created_at']);

        $this->campaignId = $this->modelCampaign->insert($campaign);
        $this->campaign   = $this->modelCampaign->get($this->campaignId, MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    private function createShadowOfTestCampaign() {
        $this->shadowId = $this->modelCampaign->createShadowCampaignOf($this->campaign, $this->campaign['name'], Config::$userIdForReassigningDeletedCampaigns);
        $shadow         = $this->modelCampaign->get($this->shadowId, MYSQL_ASSOC);

        $shadow['skim_percentage'] = 0;
        $this->modelCampaign->update($this->shadowId, $shadow);
    }
    //--------------------------------------------------------------------------


    private function changeToTmpUserId() {
        DB::$wpDb->query("UPDATE `wp_users` SET `ID` = ? WHERE `ID` = ?", array(self::TEMPORARY_USER_ID, self::USER_FOR_TESTING));
    }
    //--------------------------------------------------------------------------


    private function reassign() {
        $purge = new Helper_Purge(1600);
        $purge->reassignGhostCampaigns();
    }
    //--------------------------------------------------------------------------


    private function checkCorrectReplacement() {
        $c = $this->modelCampaign->get($this->campaignId, MYSQL_ASSOC);

        $this->assertEqual($c['user_id'], Config::$userIdForReassigningDeletedCampaigns);
        $this->assertTrue(is_numeric(substr($c['name'], 5)));
    }
    //--------------------------------------------------------------------------


    private function checkMessage() {
        /* @var $msm Model_SystemMessage */
        $msm = SingletonRegistry::getSingleInstance('Model_SystemMessage');

        $lastMessageId = $msm->lastMessageId();
        $row = $msm->get($lastMessageId, MYSQL_ASSOC);

        $text = $row['text'];

        $this->assertTrue(strpos($text, "href=\"/campaigns/edit.html?id=$this->campaignId\"") !== false);
        $this->assertTrue(strpos($text, "was reassigned to you because it has shadow parasite campaigns attached") !== false);

        $msm->delete($lastMessageId);
    }
    //--------------------------------------------------------------------------

}
