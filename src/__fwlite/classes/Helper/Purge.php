<?php

class Helper_Purge {
    private $startTime = false;
    private $timeLimit = false;
    private $deletedUserIDs = array();

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    /**
     * @var Model_LogDelivery
     */
    private $logDelivery;


    public function __construct($timeLimit) {
        $this->timeLimit = $timeLimit;

        $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $this->logDelivery   = SingletonRegistry::getModelLogDelivery();
    }
    //--------------------------------------------------------------------------


    public function run() {
        Helper_SQLLog::run();
        if ($this->isTimeOut()) {
            return;
        }

        $this->cleanUpDeletedUsers();
        if ($this->isTimeOut()) {
            return;
        }

        $this->purgeDeleted();
    }
    //--------------------------------------------------------------------------


    public function purgeDeleted() {
        $this->logDelivery->purgeApiReportDataForMissingPC($this->startTime, $this->timeLimit);

        $this->logDelivery->purgeDeletedCampaigns($this->startTime, $this->timeLimit);
        $this->logDelivery->purgeDeletedPartners($this->startTime,  $this->timeLimit);
    }
    //--------------------------------------------------------------------------


    public function reassignGhostCampaigns() {
        $this->startTime = time();
        $this->cleanUpDeletedUsers();
    }
    //--------------------------------------------------------------------------


    private function isTimeOut() {
        $elapsed = time() - $this->startTime;
        return $elapsed >= $this->timeLimit;
    }
    //--------------------------------------------------------------------------


    private function cleanUpDeletedUsers() {
        $this->readDeletedUserIDs();
        foreach ($this->deletedUserIDs as $userId) {
            if ($this->isTimeOut()) {
                return;
            }

            $this->processDeletedUser($userId);
        }
    }
    //--------------------------------------------------------------------------


    private function readDeletedUserIDs() {
        $wpUsernamesAssoc = SingletonRegistry::getModelUser()->listUsernamesAssoc(true);
        $ourUserIDs       = SingletonRegistry::getModelUser()->listUserIDs();

        $this->deletedUserIDs = array();
        foreach ($ourUserIDs as $id) {
            if (!isset($wpUsernamesAssoc[$id])) {
                $this->deletedUserIDs[] = $id;
            }
        }
    }
    //--------------------------------------------------------------------------


    private function processDeletedUser($userId) {
        $this->markUsersPartnersAsDeleted($userId);
        $this->processDeletedUsersCampaigns($userId);
    }
    //--------------------------------------------------------------------------


    private function markUsersPartnersAsDeleted($userId) {
        $modelPartner = SingletonRegistry::getModelPartner();
        $partnerIDs   = $modelPartner->listPartnerIDsForUser($userId);

        foreach ($partnerIDs as $id) {
            $modelPartner->markAsDeleted($id);
        }
    }
    //--------------------------------------------------------------------------


    private function processDeletedUsersCampaigns($userId) {
        $this->markCampainsWithNoParasiteShadowsAsDeleted($userId);
        $campaigns = $this->modelCampaign->listAllWhere(array('user_id' => (int)$userId));
        foreach ($campaigns as $campaign) {
            $this->hijackCampaign($campaign, $userId);
        }
    }
    //--------------------------------------------------------------------------


    private function markCampainsWithNoParasiteShadowsAsDeleted($userId) {
        $campaigns = $this->modelCampaign->listAllWhere(array('user_id' => (int)$userId));
        foreach ($campaigns as $campaign) {
            $shadows = $this->modelCampaign->listShadowsOfCampaign($campaign['id'], true, false);
            if (!$shadows) {
                $this->modelCampaign->markAsDeleted($campaign['id']);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function hijackCampaign(array $campaign, $userId) {
        $targetUserId = Config::$userIdForReassigningDeletedCampaigns;
        $this->modelCampaign->deactivateCampaign($campaign['id']);

        $done = $this->logDelivery->reassignCampaignRelatedTables($campaign['id'], $campaign['user_id'], $targetUserId, $this->startTime + $this->timeLimit);
        if ($done) {
            $campaign = $this->modelCampaign->reassignCampaignAndRename($campaign, $targetUserId);
            $this->sendMessageAboutReassignedCampaign($campaign);
        }
    }
    //--------------------------------------------------------------------------


    private function sendMessageAboutReassignedCampaign(array $campaign) {
        /* @var $msm Model_SystemMessage */
        $msm = SingletonRegistry::getSingleInstance('Model_SystemMessage');

        $text = "Campaign <a href=\"/campaigns/edit.html?id=$campaign[id]\">" . escapeHtml($campaign['name']) . "</a> was reassigned to you because it has shadow parasite campaigns attached";
        $msmRow = array(
            'user_id' => Config::$userIdForReassigningDeletedCampaigns,
            'text'    => $text,
            'should_escape' => false
        );

        $msm->insert($msmRow);
    }
    //--------------------------------------------------------------------------
}
