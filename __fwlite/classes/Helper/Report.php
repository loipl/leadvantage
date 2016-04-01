<?php

class Helper_Report {
    protected $out = array();
    protected $get;

    protected $from;
    protected $to;
    protected $campaignId;
    protected $partnerId;
    protected $selectedUserId;
    protected $partnerSuccessStatus;
    protected $campaignSuccessStatus;
    protected $hideRepost;
    protected $fieldSearch;

    protected $incomingId;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming = null;

    /**
     * @var Model_LogDelivery
     */
    protected $logDelivery = null;


    public function setArrays(array & $out, array $get) {
        $this->out = & $out;
        $this->get = $get;
    }
    //--------------------------------------------------------------------------


    public function prepareCommonReportData($isAdminReport) {
        $this->out['isAdminReport']   = $isAdminReport;
        $this->out['reportForUserId'] = $isAdminReport ? 0 : Auth::getInstance()->getUserId();

        $mc = SingletonRegistry::getModelCampaign();
        $campaigns = $mc->listCampaigsAssocPerUser();
        $userId = Auth::getInstance()->getUserId();
        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount($userId);
        $isSubAccount = !empty($subAccountInfo) ? true : false;
        
        $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds($userId);
        $subAccountCampaigns = $mc->listCampaignsWithIDs($subAccountCampaignIds);

        if (!empty($campaigns[$userId]) && !empty($subAccountCampaigns)) {
            $campaigns[$userId] = array_merge($campaigns[$userId], $subAccountCampaigns);
        } elseif (!empty($subAccountCampaigns)) {
            $campaigns[$userId] = $subAccountCampaigns;
        }
        
        $all = $mc->listAll('name');
        foreach ($all as $row) {
            $checkAccess = false;
            if ($isSubAccount) {
                foreach ($campaigns[$userId] as $c) {
                    if ($c['id'] == $row['id']) {
                        $checkAccess = true;
                        break;
                    }
                }
            }
            
            $allc[$row['id']] = ($isSubAccount && !$checkAccess) ? $row['id'] : $row['name'];
            $allC[$row['id']] = $row;
        }
        
        $this->out['campaigns'] = $campaigns;
        $this->out['allCampaigns'] = $allc;
        $this->out['allC'] = $allC;

        $mp = SingletonRegistry::getModelPartner();
        $subAccountPartnerNames = SingletonRegistry::getModelSubAccount()->listAllPartnerNameWithIds(Auth::getInstance()->getUserId());
        $partners = $mp->listAllPartnerNamesAssoc();

        if (!empty($partners[$userId]) && !empty($subAccountPartnerNames)) {
            $partners[$userId] += $subAccountPartnerNames;
        } elseif (!empty($subAccountPartnerNames)) {
            $partners[$userId] = $subAccountPartnerNames;
        }
        
        $this->out['partners']              = $partners;
        $this->out['allPartners']           = $mp->listAllPartnerNamesAssoc(true);
        $partnersWithWrappers               = $mp->listPartnerNamesWithWrappersAssoc(true);
        if ($isSubAccount && !empty($partners[$userId])) {
            $partnersWithWrappers = $this->showPartnerIdInsteadOfPartnerName($partnersWithWrappers, $partners, $userId);
        }
        
        $this->out['partnersWithWrappers']  = $partnersWithWrappers;
        $this->out['usernames'] = SingletonRegistry::getModelUser()->listUsernamesAssoc();

        $this->logIncoming = SingletonRegistry::getModelLogIncoming();
        $this->logDelivery = SingletonRegistry::getModelLogDelivery();

        $this->readReportGetParams($isAdminReport);
        $this->gatherReportData();
    }
    //--------------------------------------------------------------------------


    public function readReportGetParams($isAdminReport = false) {
        $this->from = $this->dmy2SQLDate('from', strtotime('-0 day')) . ' 00:00:00';
        $this->to   = $this->dmy2SQLDate('to')   . ' 23:59:59';
        $auth       = Auth::getInstance();

        $this->campaignId            = isset($this->get['cid']) ? max(0, (int)$this->get['cid']) : 0;
        $this->partnerId             = isset($this->get['pid']) ? max(0, (int)$this->get['pid']) : 0;
        $this->incomingId            = isset($this->get['iid']) ? max(0, (int)$this->get['iid']) : 0;
        $this->fieldSearch           = isset($this->get['search']) ? trim($this->get['search']) : '';

        if ($isAdminReport) {
            $this->selectedUserId    = isset($this->get['uid']) ? max(0, (int)$this->get['uid']) : ($auth->isAdmin() ? 0 : $auth->getUserId());
        } else {
            if ((sizeof($this->get) == 1) && isset($this->get['iid']) && $auth->isAdmin()) {
                $this->selectedUserId = 0;
            } elseif(isset($this->get['iid']) && isset($this->get['uid']) && ($auth->isAdmin() || $auth->getUserId() == $this->get['uid'])) {
                $this->selectedUserId = $this->get['uid'];
            } else {
                $this->selectedUserId = $auth->getUserId();
            }
        }

        $this->partnerSuccessStatus  = isset($this->get['sst']) ? max(0, (int)$this->get['sst']) : 0;
        $this->campaignSuccessStatus = isset($this->get['cst']) ? max(0, (int)$this->get['cst']) : 0;
        $this->hideRepost            = !empty($this->get['hide_repost']);
    }
    //--------------------------------------------------------------------------


    public function csvReport() {
        $userId = Auth::getInstance()->getUserId();
        $mc = SingletonRegistry::getModelCampaign();
        
        $campaigns = $mc->listCampaigsAssocPerUser();
        $subAccountInfo = SingletonRegistry::getModelSubAccount()->checkIfUserIsSubAccount($userId);
        $isSubAccount = !empty($subAccountInfo) ? true : false;
        $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds($userId);
        $subAccountCampaigns = $mc->listCampaignsWithIDs($subAccountCampaignIds);

        if (!empty($campaigns[$userId]) && !empty($subAccountCampaigns)) {
            $campaigns[$userId] = array_merge($campaigns[$userId], $subAccountCampaigns);
        } elseif (!empty($subAccountCampaigns)) {
            $campaigns[$userId] = $subAccountCampaigns;
        }
        
        
        $allCampaigns = $mc->listAll('name');
        if ($isSubAccount) {
            $allCampaigns = $this->showCampaignIdInsteadOfCampaignName($allCampaigns, $campaigns, $userId);
        }
        $this->out['campaigns'] = $allCampaigns;
        
        $mp = SingletonRegistry::getModelPartner();
        $subAccountPartnerNames = SingletonRegistry::getModelSubAccount()->listAllPartnerNameWithIds(Auth::getInstance()->getUserId());
        $partners = $mp->listAllPartnerNamesAssoc();

        if (!empty($partners[$userId]) && !empty($subAccountPartnerNames)) {
            $partners[$userId] += $subAccountPartnerNames;
        } elseif (!empty($subAccountPartnerNames)) {
            $partners[$userId] = $subAccountPartnerNames;
        }
        $allPartners = $mp->listAllPartnerNamesAssoc(true);
        if ($isSubAccount && !empty($partners[$userId])) {
            $allPartners = $this->showPartnerIdInsteadOfPartnerName($allPartners, $partners, $userId);
        }
        $this->out['partners'] = $allPartners;
        $this->logIncoming = SingletonRegistry::getModelLogIncoming();
        $this->logDelivery = SingletonRegistry::getModelLogDelivery();

        $this->readReportGetParams(!empty($this->get['is_admin_report']) && Auth::getInstance()->isAdmin());
        $this->gatherReportData(true);
        if (!empty($this->get['is_admin_report'])) {
            $this->out['usernames'] = SingletonRegistry::getModelUser()->listUsernamesAssoc();
        }
    }
    //--------------------------------------------------------------------------


    private function showCampaignIdInsteadOfCampaignName($allCampaigns, $campaigns, $userId) {
        foreach ($allCampaigns as $index => $row) {
            $checkAccess = false;
            foreach ($campaigns[$userId] as $c) {
                if ($c['id'] == $row['id']) {
                    $checkAccess = true;
                    break;
                }
            }

            if (!$checkAccess) {
                $allCampaigns[$index]['name'] = $row['id'];
            }
        }
        
        return $allCampaigns;
    }
    //--------------------------------------------------------------------------
    
    
    private function showPartnerIdInsteadOfPartnerName($allPartners, $partners, $userId) {
        foreach ($allPartners as $id => $name) {
            $checkAccess = false;
            foreach ($partners[$userId] as $pid => $pname) {
                if ($id == $pid) {
                    $checkAccess = true;
                    break;
                }
            }

            if (!$checkAccess) {
                $allPartners[$id] = $id;
            }
        }
        
        return $allPartners;
    }
    //--------------------------------------------------------------------------
    
    
    public function outputCsvReport($addHeaders = true) {
        if ($addHeaders) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=report.csv');
        }
        extract($this->out);
        $isAdminReport = !empty($this->get['is_admin_report']);
        if ($isAdminReport) {
            echo 'User,';
        }

        ?>Email,Full Name,Full Address,Phone,Campaign(field type),Campaign,Data Accepted,Partner,Partner Accepted,Repost,Reject Reason,Revenue,Request Time,IP,Referrer,Partner Response<?php

        $campaigns = $this->out['campaigns'];
        $campaignNames = array();
        foreach ($campaigns as $row) {
            $campaignNames[(int)$row['id']] = $row['name'];
        }
        $report    = $this->out['report'];
        $usernames = SingletonRegistry::getModelUser()->listUsernamesAssoc();
        $partners  = $this->out['partners'];

        while (($row = mysql_fetch_assoc($report)) !== false) {
            echo "\n";
            if ($isAdminReport) {
                echo '"' . str_replace('"', '""', $usernames[$row['user_id']]) . '",';
            }
            echo "\"" . str_replace('"', '""', $row['email']) . '"';
            echo ',"' . str_replace('"', '""', $row['full_name']) . '"';
            echo ',"' . str_replace('"', '""', $row['full_address']) . '"';
            echo ',"' . str_replace('"', '""', $row['phone']) . '"';
            echo ',"' . str_replace('"', '""', $row['campaign']) . '"';
            echo ',"' . str_replace('"', '""', (isset($campaignNames[$row['campaign_id']]) ? $campaignNames[$row['campaign_id']] : '') ) . '"';
            echo ',"' . ($row['is_success'] ? 'YES' : 'NO') . '"';
            echo ',"' . str_replace('"', '""', isset($partners[$row['partner_id']]) ? $partners[$row['partner_id']] : '') . '"';
            echo ',"' . ($row['is_accepted'] ? 'YES' : 'NO') . '"';
            echo ',"' . ($row['is_repost'] ? 'YES' : 'NO') . '"';

            $rj = $row['reject_reason'];
            if ($rj == 'curl') {
                $rj = 'timeout';
            } else if ($rj == 'skipped') {
                $skipReason = (!empty($row['message'])) ? $row['message'] : 'Rules';
                $rj .= ' (' . $skipReason . ')';
            }
            echo ',"' . str_replace('"', '""', $rj) . '"';
            echo ',"' . ($row['revenue'] ? $row['revenue'] : 0) . '"';
            echo ',"' . str_replace('"', '""', $row['incoming_time']) . '"';
            echo ',"' . str_replace('"', '""', $row['remote_ip']) . '"';
            echo ',"' . str_replace('"', '""', $row['http_referer']) . '"';

            $response = substr($row['partner_response'], 0, 64);
            $response = addcslashes($response, "\\\r\n");
            echo ',"' . str_replace('"', '""',  $response) . '"';
        }
    }
    //--------------------------------------------------------------------------


    protected function dmy2SQLDate($get, $ts = false, $delimiter = '/') {
        $default = date('Y-m-d', $ts ? $ts : time());
        $var = isset($this->get[$get]) ? $this->get[$get] : '';
        if (!$var || !is_string($var)) {
            return $default;
        }
        $arr   = explode($delimiter, $var);
        if (sizeof($arr) != 3) {
            return $default;
        }
        if (checkdate($arr[0], $arr[1], $arr[2])) {
            return sprintf('%04d-%02d-%02d', $arr[2], $arr[0], $arr[1]);
        } else {
            return $default;
        }
    }
    //--------------------------------------------------------------------------


    protected function gatherReportData($all = false) {
        $page    = isset($this->get['page']) ? max(1, (int)$this->get['page']) : 1;
        $perPage = 25;
        $this->out['perPage'] = $perPage;

        $tz = Session::getSession()->timeZone;
        if (isset($_GET['tz'])) {
            $zones = Controller_Dashboard::listTimeZones();
            foreach ($zones as $arr) {
                if ($arr[0] == $_GET['tz']) {
                    $tz = $arr[0];
                    break;
                }
            }
        }
        $this->out['npErrors'] = array();

        $cids      = SingletonRegistry::getModelCampaign()->listAllCampaignIDs();
        $oobRanges = SingletonRegistry::getModelCampaign()->listOOBRanges(array_unique($cids));
        $this->out['oobRanges'] = $oobRanges;
        $this->out['oobStates'] = SingletonRegistry::getModelCampaign()->listOOBStatesAssoc(array_unique($cids));

        if ($all) {
            if ($this->from > date('Y-m-d H:i:s', time() - Config::$keepApiReport * 86400)) {
                $report  = $this->logIncoming->listSRReportQueryFor($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, $all ? 0 : $page, $all ? 0 : $perPage);
            } else {
                $report  = $this->logIncoming->listSRReportQueryForOldData($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, $all ? 0 : $page, $all ? 0 : $perPage);
            }
            
            $this->checkNpIncomingErrors($report);
            $this->out['report']  = $report;
            return;
        }
        
        if ($this->from > date('Y-m-d H:i:s', time() - Config::$keepApiReport * 86400) && !$this->incomingId) {
            $report  = $this->logIncoming->listSRReportFor($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, $all ? 0 : $page, $all ? 0 : $perPage);
            if (!empty($this->fieldSearch)){
                $this->out['count']   = $this->logIncoming->listSRReportFor($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, 0, 0, true);
            } else {
                $this->out['count']   = $this->logIncoming->countSRReportFor($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                            $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->incomingId, $this->hideRepost, $this->selectedUserId);
            }

        } else {
            $report  = $this->logIncoming->listSRReportForOldData($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, $all ? 0 : $page, $all ? 0 : $perPage);
            if (!empty($this->fieldSearch)){
                $this->out['count']   = $this->logIncoming->listSRReportForOldData($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->fieldSearch, $this->incomingId, $this->hideRepost,
                        $this->selectedUserId, 0, 0, true);
            } else {
                $this->out['count']   = $this->logIncoming->countSRReportForOldData($tz, $this->from, $this->to, $this->campaignId, $this->partnerId,
                        $this->campaignSuccessStatus, $this->partnerSuccessStatus, $this->incomingId, $this->hideRepost, $this->selectedUserId);
            }

        }
        
        $this->out['report']  = $report;
        $this->checkNpIncomingErrors($report);
        
        $iids  = array();
        $dids1 = array();
        $dids2 = array();
        $p = 0;
        $allCampaigns = isset($this->out['allC']) ? $this->out['allC'] : array();
        foreach ($report as $row) {
            if (!isset($this->out['showRevenueReport']) && $row['revenue'] > '0') {
                $this->out['showRevenueReport'] = true;
            }
            
            $campaignId = $row['campaign_id'];
            
            // if campaign is deleted, dont add delivery_id and incomming_id to next/prev cycle
            if (isset($allCampaigns[$campaignId]) && is_null($allCampaigns[$campaignId]['user_id'])) {
                continue;
            }
            
            $iids[] = $row['incoming_id'];
            $dids1[] = ($row['reject_reason'] == 'skipped') ? "0,$p" : $row['delivery_id'];
            if ($row['delivery_id'] && ($row['reject_reason'] != 'skipped')) {
                $dids2[] = $row['delivery_id'];
            }
            $p++;
        }
        $this->out['iids'] = $iids;
        $this->out['dids'] = $dids1;

        $this->checkForTimeouts($dids2);
    }
    //--------------------------------------------------------------------------


    private function checkNpIncomingErrors($report) {
        $iids = array();
        if (is_array($report)) {
            foreach ($report as $row) {
                $iids[$row['incoming_id']] = 1;
            }
        } else {
            while(($row = mysql_fetch_assoc($report)) != false) {
                $iids[$row['incoming_id']] = 1;
            }
            
            // only execute mysql_data_seek in case num_rows > 0 (resolved ticket 580)
            if (mysql_num_rows($report) > 0) {
                mysql_data_seek($report, 0);
            }
        }
        $iids = array_keys($iids);
        $npErrors = $this->logIncoming->listFailedNoPartners($iids);
        $this->out['npErrors'] = $npErrors;
    }
    //--------------------------------------------------------------------------


    private function checkForTimeouts(array $dids) {
        $timeouts = $this->logDelivery->checkForTimeouts($dids);
        $this->out['timeouts'] = $timeouts;
    }
    //--------------------------------------------------------------------------
}
