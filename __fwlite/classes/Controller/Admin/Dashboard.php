<?php

class Controller_Admin_Dashboard extends Controller {
    const DEFAULT_TOP_PERIOD = 90;

    const QUOTA_PERCENTAGE   = 80;

    const DEFAULT_INACTIVE_PERIOD = 7;

    const TOP_USERS_COUNT = 5;

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming;

    public function indexAction() {
        $this->out['noShadowCount'] = $this->listCampaignsWithNoShadows(true);
        $this->out['stats']         = $this->getStats();
        $this->out['usernames']     = SingletonRegistry::getModelUser()->listUsernamesAssoc();
    }
    //--------------------------------------------------------------------------


    public function noShadowReportAction() {
        App::getFrontController()->extraCss[] = 'dashboard.css';
        $campaigns = array();
        foreach ($this->listCampaignsWithNoShadows() as $oneCampaign) {
            if (!$oneCampaign['is_active']) {
                continue;
            }
            $userId = $oneCampaign['user_id'];
            if (!isset($campaigns[$userId])) {
                $campaigns[$userId] = array();
            }
            $campaigns[$userId][] = $oneCampaign;
        }
        $data = array();
        foreach ($campaigns as $userId => $userCampaigns) {
            $nsr = $this->prepareNoShadowReport($userId, $userCampaigns);
            if ($nsr) {
                $data[$userId] = $nsr;
            }
        }
        $this->out['data']         = $data;
        $this->out['allCampaigns'] = $campaigns;
        $this->out['usernames']    = SingletonRegistry::getModelUser()->listUsernamesAssoc();
    }
    //--------------------------------------------------------------------------


    private function prepareNoShadowReport($userId, array $campaigns) {
        $result = array();

        $logIncoming = SingletonRegistry::getModelLogIncoming();

        // $campaigns = SingletonRegistry::getModelCampaign()->listAllWhere(array('is_active' => '1', 'user_id' => $userId));
        $result['campaigns'] = $campaigns;
        $cids = array();
        $cass = array();
        foreach ($campaigns as $campaign) {
            $cids[] = $campaign['id'];
            $cass[$campaign['id']] = $campaign;
        }
        $cStats = $logIncoming->dashboardStats($userId, $cids, Session::getSession()->timeZone, $cass);
        $result['cStats'] = $cStats;

        $pnAss = SingletonRegistry::getModelPartner()->listPartnerNamesAssoc($userId);
        $result['partners'] = $pnAss;
        $scIds = array();
        foreach ($cids as $index => $cid) {
            $scIds[$index] = !empty($cass[$cid]['shadow_of']) ? (int)$cass[$cid]['shadow_of'] : $cid;
        }
        $graphStats = $logIncoming->listHourlyReportLast24Hours($cids, Session::getSession()->timeZone, $scIds);
        $result['graphStats'] = $graphStats;
        return $result;
    }
    //--------------------------------------------------------------------------


    protected function listCampaignsWithNoShadows($justCount = false) {
        $this->modelCampaign or $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $mu = SingletonRegistry::getModelUser();

        $userIDs = $mu->listUsersWithRole('subscriber');
        foreach ($mu->listUsersWithRole('s2member_level0') as $uid) {
            if (!in_array($uid, $userIDs)) {
                $userIDs[] = $uid;
            }
        }
        foreach ($mu->listUsersWithRole('s2member_level1') as $uid) {
            if (!in_array($uid, $userIDs)) {
                $userIDs[] = $uid;
            }
        }
        foreach ($mu->listUsersWithRole('s2member_level2') as $uid) {
            if (!in_array($uid, $userIDs)) {
                $userIDs[] = $uid;
            }
        }
        foreach ($mu->listUsersWithRole('s2member_level5') as $uid) {
            if (!in_array($uid, $userIDs)) {
                $userIDs[] = $uid;
            }
        }
        $index = array_search(TEST_USER_ID, $userIDs);
        if ($index !== false) {
            unset($userIDs[$index]);
        }
        $ids = $this->modelCampaign->listCampaignsWithNoShadows($userIDs);
        if ($justCount) {
            return sizeof($ids);
        }
        return $this->modelCampaign->listGroup($ids);
    }
    //--------------------------------------------------------------------------


    public function refreshStatsAction() {
        apc_delete(__FILE__ . '/stats');
        App::getFrontController()->redirectToCP($this);
    }
    //--------------------------------------------------------------------------


    protected function getStats() {
        $this->logIncoming or $this->logIncoming = SingletonRegistry::getModelLogIncoming();
        $top5IncSucc = $this->logIncoming->listTopUsersByIncoming(self::TOP_USERS_COUNT, self::DEFAULT_TOP_PERIOD, true);
        $top5IncFail = $this->logIncoming->listTopUsersByIncoming(self::TOP_USERS_COUNT, self::DEFAULT_TOP_PERIOD, false);
        $top5Del     = $this->logIncoming->listTopUsersByDeliveries(self::TOP_USERS_COUNT, self::DEFAULT_TOP_PERIOD, true);

        $mu = SingletonRegistry::getModelUser();
        $inactive = $this->logIncoming->listUsersWitnNoSubmissionsFor(self::DEFAULT_INACTIVE_PERIOD);

        $helper = new Helper_MailWarning();
        $quotas  = $helper->listUsersWithQuotasOver(self::QUOTA_PERCENTAGE);

        $stats = array('inc_succ' => $top5IncSucc, 'inc_fail' => $top5IncFail, 'del' => $top5Del, 'inactive' => $inactive, 'quotas' => $quotas, 'time' => time());

        return $stats;
    }
    //--------------------------------------------------------------------------
}
