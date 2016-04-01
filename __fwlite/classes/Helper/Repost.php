<?php

class Helper_Repost {

    /**
     * @var Model_LogIncomingRepost
     */
    protected $modelRepost;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming;

    /**
     * @var Model_LogDelivery
     */
    protected $logDelivery;

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;

    protected $campaignsAssoc = array();

    protected $repostCount = 0;

    protected $partnersReposted = 0;

    protected $callback = false;

    protected $deadCatchUpJobs = array();

    protected $now;

    protected $capTypes = array();
    protected $capTypesMinute = array();

    public $removePartnersFromSourceCampaign = false;

    public $fakePostToPartner = null;

    private $lockString = '';

    private $myIp = '';

    private $toRepost = array();

    private $ids = array();

    private $counter = 0;

    private $repostQueueRow = array();

    private $isDebug = false;

    public $onlyUserId = false;

    public static $mysqlServerLinkGone = false;


    public function __construct() {
        $this->modelRepost   = SingletonRegistry::getModelLogIncomingRepost();
        $this->logIncoming   = SingletonRegistry::getModelLogIncoming();
        $this->logDelivery   = SingletonRegistry::getModelLogDelivery();
        $this->modelCampaign = SingletonRegistry::getModelCampaign();

        $this->myIp = gethostbyname(gethostname());

        if (Config::$repostThreadLogging) {
            Config::$repostThreadId = $this->modelRepost->startNewRepostThread($this->myIp);
        }
    }
    //--------------------------------------------------------------------------


    public function doRepost($startTime, $batchSize) {
        if (self::$mysqlServerLinkGone) {
            return;
        }

        if ($this->modelRepost->repostKillSwitchOn($this->myIp)) {
            die();
        }

        $this->partnersReposted = 0;
        $this->repostCount      = 0;
        $this->deadCatchUpJobs  = array();
        $this->campaignsAssoc   = array();
        $this->lockString       = '';

        $this->executeRepost($startTime, $batchSize);
    }
    //--------------------------------------------------------------------------


    private function executeRepost($startTime, $batchSize) {
        $this->now = DB::$db->getTopLeft("SELECT NOW()");

        $this->toRepost = $this->getRepostData($batchSize, $this->onlyUserId);
        if ($this->toRepost === false) {
            sleep(1);
            return;
        }

        if (empty($this->toRepost)) {
            sleep(5);
            return;
        }

        try {
            $result = $this->processRepostArray($startTime);
            $this->done();
        } catch (Exception $e) {
            $this->done();
            throw $e;
        }

        if ($this->repostCount == 0) {
            sleep(5);
            return;
        }
    }
    //--------------------------------------------------------------------------


    private function done() {
        if ($this->toRepost) {
            $this->returnRemainingFromToRepostToSubset();

            $ids = array();
            foreach ($this->toRepost as $row) {
                $pos = array_search($row['orig_id'], $this->ids);
                if ($pos !== false) {
                    unset($this->ids[$pos]);
                }
            }
        }

        if ($this->ids) {
            $this->modelRepost->deleteRowsFromRepostQueueMem($this->ids);
        }
    }
    //--------------------------------------------------------------------------


    private function returnRemainingFromToRepostToSubset() {
        if (!$this->toRepost) {
            return;
        }
        $ids = array();
        foreach ($this->toRepost as $row) {
            $ids[] = $row['orig_id'];
        }
        $this->modelRepost->lockRepostEntriesFromSubsetTable($ids, false);
    }
    //--------------------------------------------------------------------------


    private function processRepostArray($startTime) {
        $this->counter = 0;

        foreach ($this->toRepost as $index => $row) {  
            
            // prevent batch upload from posting mass data in a short time.
            if (!empty($row['upload_file'])) {
                sleep(1);
            }
            
            $this->repostQueueRow = $row;
            $this->processOneRepost();

            $this->modelRepost->deleteOneRowFromRepostQueueSubsetById($row['orig_id']);
            unset($this->toRepost[$index]);

            if ($this->shouldStop($startTime)) {
                return false;
            }
        }

        return true;
    }
    //--------------------------------------------------------------------------


    private function shouldStop($startTime) {
        if ($this->counter++ > 10) {
            if ($this->modelRepost->repostKillSwitchOn($this->myIp)) {
                return true;
            }
            $this->counter = 0;
        }

        if (self::$mysqlServerLinkGone) {
            return true;
        }
        if ((time() - $startTime) >= Engine_Settings::$settings->cronTimeout) {
            return true;
        }

        return false;
    }
    //--------------------------------------------------------------------------


    protected function processOneRepost() {
        $campaignId = (int)$this->repostQueueRow['campaign_id'];
        $this->lockString = $this->repostQueueRow['id'] . '-' . $campaignId . '-' . (int)$this->repostQueueRow['catchup_id'] . '.lock';
        try {
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/repost-job-' . $this->lockString, 0);
        } catch (EServerError $e) {
            return;
        }

        try {
            $this->executeOneRepost($campaignId);
            $lock->release();
        } catch (Exception $e) {
            $lock->release();
            throw $e;
        }
    }
    //--------------------------------------------------------------------------


    private function executeOneRepost($campaignId) {
        if ($this->repostQueueRowDeletedOrPostponed($campaignId)) {
            return;
        }

        $incRow = $this->logIncoming->get((int)$this->repostQueueRow['id']);
        if (!$incRow) {
            return;
        }

        $jobId = (int)$this->repostQueueRow['catchup_id'];
        $catchingUpPartnerId = false;
        $isRateLimited = false;
        if ($jobId && !$this->catchupJobActiveAndUnderRateLimit($jobId, $catchingUpPartnerId, $isRateLimited)) {
            return;
        }

        $campaign = array();
        if (!$this->initCampaignAndCheckThrottle($campaignId, $campaign)) {
            return;
        }

        $engine = $this->createEngine();

        $engine->removePartnersFromSourceCampaign = $this->removePartnersFromSourceCampaign;

        $engine->catchingUpPartnerId     = $catchingUpPartnerId;
        $engine->fakePostToPartner       = $this->fakePostToPartner;
        $engine->catchupJobId            = (int)$jobId;
        $engine->catchupJobIsRateLimited = $isRateLimited;

        $batchUploadLeadFlag = !empty($this->repostQueueRow['upload_file']) ? true : false;
        $accepted = $engine->processRepost($incRow, $campaign, $batchUploadLeadFlag);
        $this->partnersReposted += $accepted;

        if ($engine->catchupEntryProcessedButNotPostponed()) {
            $this->logIncoming->procesedOneCatchupJobEntry($jobId, $accepted ? true : false);
        }
        $this->repostCount++;
    }
    //--------------------------------------------------------------------------


    private function repostQueueRowDeletedOrPostponed($campaignId) {
        $rqRow = $this->logIncoming->getRepostQueueEntry((int)$this->repostQueueRow['id'], $campaignId, $this->repostQueueRow['catchup_id']);
        if (!$rqRow) {
            // repost_queue entry was already processed and deleted
            return true;
        }
        if ($rqRow['repost_at'] > $this->now) {
            // repost_queue entry was already processed and pushed to future
            return true;
        }

        return false;
    }
    //--------------------------------------------------------------------------


    private function createEngine() {
        $engine = new Engine_Repost;
        if ($this->isDebug) {
            $engine->activateDebugMode();
        }

        if ($this->callback) {
            $engine->setListenerCallback($this->callback);
        }

        return $engine;
    }
    //--------------------------------------------------------------------------


    private function initCampaignAndCheckThrottle($campaignId, array & $campaign) {
        if (isset($this->campaignsAssoc[$campaignId])) {
            $campaign = $this->campaignsAssoc[$campaignId];
        } else {
            $campaign = $this->modelCampaign->get($campaignId);
            if (!$campaign) {
                return false;
            }
            $this->campaignsAssoc[$campaignId] = $campaign;
        }

        return $this->isCampaignUnderThrottleLimit($campaign);
    }
    //--------------------------------------------------------------------------


    private function isCampaignUnderThrottleLimit(array $campaign) {
        if ((intval($campaign['delivery_type']) === Model_Campaign::DELIVERY_TYPE_QUEUED_KEY || $campaign['shadow_of']) && $campaign['throttle_value']) {
            if (($campaign['throttle_value'] == 1) && ($campaign['throttle_type'] == 0)) {
                $throttleType = -1;
            } else {
                $throttleType = Model_Partner::$deliveryTypesWithMinute[$campaign['throttle_type']];
            }

            $count = $this->modelRepost->campaignRepostCount($campaign['id'], $throttleType);
            if ($count >= $campaign['throttle_value']) {
                // Double check, we should get here only rarely. Code that creates
                // repost queue subset checks rate limits before adding to queue.
                // Checks are in class Helper_RepostSubset
                return false;
            }
        }
        return true;
    }
    //--------------------------------------------------------------------------


    private function catchupJobActiveAndUnderRateLimit($jobId, & $catchupPID, & $isRateLimited) {
        if (isset($this->deadCatchUpJobs[$jobId])) {
            return false;
        }

        $job = $this->logIncoming->getCatchUpJob($jobId);
        if (!$job) {
            $this->deadCatchUpJobs[$jobId] = 1;
            return false;
        }

        $catchupPID = (int)$job['partner_id'];

        if ($this->isJobOverRateLimit($job, $isRateLimited)) {
            $this->deadCatchUpJobs[$jobId] = 1;
            return false;

        }

        return true;
    }
    //--------------------------------------------------------------------------


    private function isJobOverRateLimit($job, & $isRateLimited) {
        if ($job['rl_type'] && $job['rl_value'] && isset(Model_Partner::$deliveryTypes[$job['rl_type']])) {
            $isRateLimited = true;
            $count = $this->modelRepost->catchupRepostCount($job['id'], Model_Partner::$deliveryTypes[$job['rl_type']], $this->now);
            if ($count >= $job['rl_value']) {
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    public function doRepostDebug($batchSize = 1000) {
        if (self::$mysqlServerLinkGone) {
            return;
        }
        $this->isDebug = true;

        $startTime = time();
        $this->partnersReposted = 0;
        $this->deadCatchUpJobs = array();
        $this->now = DB::$db->getTopLeft("SELECT NOW()");

        $toRepost = $this->getRepostData($batchSize, TEST_USER_ID);

        if (empty($toRepost)) {
            return 0;
        }
        $this->toRepost = $toRepost;
        try {
            $result = $this->processRepostArray($startTime);
            $this->done();
        } catch (Exception $e) {
            $this->done();
            throw $e;
        }
        if ($this->repostCount == 0) {
            return 0;
        }
        return $this->partnersReposted;
    }
    //--------------------------------------------------------------------------


    public function setCallback($callback) {
        $this->callback = $callback;
    }
    //--------------------------------------------------------------------------


    /**
     * Meant for testing this function from UI
     */
    public function testGetRepostData($batchSize) {

        // This is a hack so delayIfNotRunningTests() won't wait for 120 seconds
        if (!defined('RUN_ALL_TESTS')) {
            define('RUN_ALL_TESTS', 1);
        }

        $this->now = DB::$db->getTopLeft("SELECT NOW()");

        // Another hack so we don't wait for real mysql lock
        Config::$lockPrefix .= 'testing.';

        $toRepost = $this->getRepostData($batchSize);
        $this->returnRemainingFromToRepostToSubset();

        return $toRepost;
    }
    //--------------------------------------------------------------------------


    private function getRepostData($batchSize, $userId = false) {
        $this->refreshRepostQueueSubset();

        try {
            // Make sure only one thread is reading repost list at a time
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/fetch-repost-data', 0);
        } catch (EServerError $e) {
            return false;
        }

        $toRepost = $this->modelRepost->listRepostEntriesFromSubsetTable($batchSize, $this->now);
        if ($toRepost) {
            $this->ids = array();
            foreach ($toRepost as $row) {
                $this->ids[] = $row['orig_id'];
            }
            $this->modelRepost->lockRepostEntriesFromSubsetTable($this->ids);
        }
        $lock->release();
        return $toRepost;
    }
    //--------------------------------------------------------------------------


    private function refreshRepostQueueSubset() {
        $subsetSize = Engine_Settings::$settings->repostQueueMemSize;
        try {
            // Make sure only one thread is reading repost list at a time
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/refreshSubset', 0);
        } catch (EServerError $e) {
            return;
        }

        $count = $this->modelRepost->sizeOfRepostQueueSubset();
        if ($count > $subsetSize * 0.9) {
            return;
        }

        $startedAt = time();
        $this->modelRepost->deleteLockedEntriesLockedBefore(time() - 900);
        $count -= DB::$db->affectedRows();

        $hrs = new Helper_RepostSubset($this->now);
        $hrs->updateSubsetTable($subsetSize - $count);

        $this->delayIfNotRunningTests($startedAt, 120);
    }
    //--------------------------------------------------------------------------


    private function delayIfNotRunningTests($startedAt, $minSeconds) {
        if (!defined('RUN_ALL_TESTS') && (time() < ($startedAt + $minSeconds))) {
            $delay = 10 - (time() - $startedAt);
            if ($delay > 0) {
                sleep($delay);
            }
        }
    }
    //--------------------------------------------------------------------------


    public static function sqlErrorListener($sql, array $params, $sqlToRun, DB $db, $sqlErr, $sqlErrNo, $queryTime) {
        $CR_SERVER_GONE_ERROR = 2006;
        $CR_SERVER_LOST       = 2013;
        if (($sqlErrNo == $CR_SERVER_GONE_ERROR) || ($sqlErrNo == $CR_SERVER_LOST)) {
            self::$mysqlServerLinkGone  =true;
        }
    }
    //--------------------------------------------------------------------------
}
