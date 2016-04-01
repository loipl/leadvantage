<?php

class Helper_RepostSubset {

    /**
     * @var Model_LogIncomingRepost
     */
    protected $modelRepost;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming;

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;


    private $capTypes = array();

    private $capTypesMinute = array();


    /**
     * @desc Assoc array campaign_id => $rowCampaign
     */
    private $campaigns = array();

    /**
     * @desc Assoc array catchup_id => $rowCatchupJob
     */
    private $catchupJobs = array();


    /**
     * @desc array(campaign_id => array(catchup_id => count))
     */
    private $campaignCatchupCounts = array();


    private $userCounts = array();

    private $now;

    private $limit;


    public function __construct($now) {
        $this->now = $now;

        $this->modelRepost    = SingletonRegistry::getModelLogIncomingRepost();
        $this->logIncoming    = SingletonRegistry::getModelLogIncoming();
        $this->modelCampaign  = SingletonRegistry::getModelCampaign();

        $this->capTypes       = SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssoc();
        $this->capTypesMinute = SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssocWithMinute();
    }
    //--------------------------------------------------------------------------


    public function updateSubsetTable($limit = 50000) {
        $this->campaignCatchupCounts = $this->modelRepost->listCampaignAndCatchupCounts($this->now);
        if (!$this->campaignCatchupCounts) {
            return;
        }
        $this->limit = $limit;

        $this->initializeCampaignsAndCatchupJobs();
        $this->reduceCountsForThrottled();
        $this->updateUserCounts();
        if (array_sum($this->userCounts) > $this->limit) {
            $this->distributeCounts();
        }
        $this->putNewDataToSubsetTable();
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Get data for campaigns and catchup jobs that are present in array
     * $this->campaignCatchupCounts.
     */
    private function initializeCampaignsAndCatchupJobs() {
        $catchupIds  = array();
        $campaignIds = array();

        foreach ($this->campaignCatchupCounts as $oneRow) {
            if ($oneRow['catchup_id']) {
                $catchupIds[] = (int)$oneRow['catchup_id'];
            } else {
                $campaignIds[] = (int)$oneRow['campaign_id'];
            }
        }

        if ($campaignIds) {
            $this->campaigns = $this->modelCampaign->listGroup($campaignIds, 'id', true);
        } else {
            $this->campaigns = array();
        }

        $this->catchupJobs = array();
        if ($catchupIds) {
            $catchupJobs = $this->logIncoming->listCatchupJobRateLimits();
            foreach ($catchupIds as $id) {
                if (isset($catchupJobs[$id])) {
                    $this->catchupJobs[$id] = $catchupJobs[$id];
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Calculates per-user total from $this->campaignCatchupCounts and puts
     * into $this->userCounts
     */
    private function updateUserCounts() {
        $this->userCounts  = array();
        foreach ($this->campaignCatchupCounts as $oneRow) {
            if (!isset($this->userCounts[$oneRow['user_id']])) {
                $this->userCounts[$oneRow['user_id']] = 0;
            }
            $this->userCounts[$oneRow['user_id']] += (int)$oneRow['count'];
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc This function will distribute counts evenly, in two steps. First we have
     * $limit of how many rows total we need to find, for all users and all campaigns
     * combined. First we set per-user count so that the sum fits within the limit.
     *
     * Next, if user has more than one campaign or catchup job, we do the same within
     * each user's quota, so that each campaign or job will be processed in round-robin
     * fashion.
     *
     * Catchup jobs are always tied to a campaign and a partner, and they have their
     * own rate limit settings. This code will ignore throttle settings for parent campaign
     * when figuring out limits for catchup job.
     */
    private function distributeCounts() {
        $this->userCounts = self::distributePerQuota($this->userCounts, $this->limit);

        $userCountsTotals = array();
        $indexes = array();
        foreach ($this->campaignCatchupCounts as $index => $oneRow) {
            $key = $oneRow['catchup_id'] ? "$oneRow[campaign_id]_$oneRow[catchup_id]" : "$oneRow[campaign_id]_0";
            $userCountsTotals[(int)$oneRow['user_id']][$key] = $oneRow['count'];
            $indexes[$key] = $index;
        }

        foreach ($this->userCounts as $userId => $userLimit) {
            if (!empty($userCountsTotals[$userId])) {
                $distributed = self::distributePerQuota($userCountsTotals[$userId], $userLimit);
                foreach ($distributed as $key => $count) {
                    $index = $indexes[$key];
                    $this->campaignCatchupCounts[$index]['count'] = $count;
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc If there are throttled campaigns or rate-limited catchup jobs in result set,
     * reduce the ['count'] value for them. If no more room left, remove from result set.
     */
    private function reduceCountsForThrottled() {
        foreach ($this->campaignCatchupCounts as & $oneRow) {
            if($oneRow['catchup_id']) {
                $this->reduceCountForCatchupJob($oneRow);
            } else {
                $this->reduceCountForCampaign($oneRow);
            }
        }

        foreach (array_keys($this->campaignCatchupCounts) as $index) {
            if (!$this->campaignCatchupCounts[$index]['count']) {
                unset($this->campaignCatchupCounts[$index]);
            }
        }
    }
    //--------------------------------------------------------------------------

    /**
     * @desc If campaign is throttled make sure that $oneRow['count'] is within
     * that limit
     */
    private function reduceCountForCampaign(array & $oneRow) {
        $campaignId = (int)$oneRow['campaign_id'];
        if (!isset($this->campaigns[$campaignId])) {
            $oneRow['count'] = 0;
            return;
        }
        $campaign = $this->campaigns[$campaignId];
        $max      = (int)$campaign['throttle_value'];

        if (!$max) {
            return;
        }
        if (!isset($this->capTypesMinute[$campaign['throttle_type']])) {
            return;
        }

        $usedUp = $this->modelRepost->campaignRepostCount($campaignId, $this->capTypesMinute[$campaign['throttle_type']], $this->now);

        if ($max > $usedUp) {
            $oneRow['count'] = min($oneRow['count'], $max - $usedUp);
        } else {
            $oneRow['count'] = 0;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc If catchup job is rate-limited make sure that $oneRow['count']
     * is within that limit
     */
    private function reduceCountForCatchupJob(array & $oneRow) {
        $jobId = (int)$oneRow['catchup_id'];
        if (!isset($this->catchupJobs[$jobId])) {
            $oneRow['count'] = 0;
            return;
        }
        $job = $this->catchupJobs[$jobId];
        $max = (int)$job['rl_value'];

        if (!$max) {
            return;
        }
        if (!isset($this->capTypesMinute[$job['rl_type']])) {
            return;
        }

        $usedUp = $this->modelRepost->catchupRepostCount($jobId, $this->capTypesMinute[$job['rl_type']], $this->now);

        if ($max > $usedUp) {
            $oneRow['count'] = min($oneRow['count'], $max - $usedUp);
        } else {
            $oneRow['count'] = 0;
        }
    }
    //--------------------------------------------------------------------------


    private function putNewDataToSubsetTable() {
        $this->modelRepost->createTempTableForRepostSubset();
        $this->modelRepost->addDataToTempRepostSubsetTable($this->campaignCatchupCounts, $this->now);
        $this->modelRepost->pushTempRepostToSubset();
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Distributes data from $uid2cnt according to $limit. For example,
     * if we give it array (1 => 300, 2 => 30, 4 => 50) and $limit 100, result
     * will be array(1 => 35, 2 => 30, 4 => 35) - so that total sum is 100 and
     * everyone gets maximum available.
     */
    public static function distributePerQuota(array $uid2cnt, $limit) {
        asort($uid2cnt);
        $counts  = array(); $usedUp = array();
        $quota   = (int)($limit / sizeof($uid2cnt));
        $surplus = $limit;
        foreach ($uid2cnt as $uid => $cnt) {
            if ($cnt < $quota) {
                $counts[$uid] = $cnt;
                $surplus -= $cnt;
                $usedUp[$uid] = 1;
            } else {
                $counts[$uid] = $quota;
                if ($quota == $cnt) {
                    $usedUp[$uid] = 1;
                }
                $surplus -= $quota;
            }
        }
        $remaining = sizeof($uid2cnt) - sizeof($usedUp);
        while ($surplus && $remaining) {
            foreach ($uid2cnt as $uid => $cnt) {
                if (isset($usedUp[$uid])) {
                    continue;
                }

                // Sanity check so we don't get a div by zero
                if ($remaining == 0) {
                    break;
                }

                $add = max(1, (int)($surplus / $remaining));
                $add = min($surplus, $add);
                if ($add <= 0) {
                    break;
                }
                if (($counts[$uid] + $add) <= $cnt) {
                    $counts[$uid] += $add;
                    $usedUp[$uid] = 1;

                    $surplus -= $add;
                    if (!$surplus) {
                        break;
                    }
                    $remaining--;
                } else {
                    $surplus -= ($cnt - $counts[$uid]);
                    $counts[$uid] = $cnt;
                    $usedUp[$uid] = 1;
                    if (!$surplus) {
                        break;
                    }
                    $remaining--;
                }
            }
        }
        return $counts;
    }
    //--------------------------------------------------------------------------
}
