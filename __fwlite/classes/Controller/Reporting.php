<?php

class Controller_Reporting extends Controller {

    protected $from;
    protected $to;
    protected $partnerId;
    protected $campaignId;
    protected $partnerSuccessStatus;
    protected $campaignSuccessStatus;
    protected $rejectReason = '';
    protected $selectedUserId = 0;
    protected $incomingId     = 0;

    protected $columns = array();

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming = null;

    /**
     * @var Model_LogDelivery
     */
    protected $logDelivery = null;


    public function __construct() {
        parent::__construct();
        
        $auth = Auth::getInstance();
        $userId = $auth->getUserId();
        $subAccountModel = SingletonRegistry::getModelSubAccount();
        $subAccountInfo = $subAccountModel->checkIfUserIsSubAccount($userId);
        $isRevokeReportingAccess = $subAccountModel->checkIfRevokeReportingAccess($userId);
        $isRevokeSuccessLeads = $subAccountModel->checkIfRevokeSuccessLeads($userId);
        $isRevokeFailedLeads = $subAccountModel->checkIfRevokeFailedLeads($userId);
        $isRevokeSkippedLeads = $subAccountModel->checkIfRevokeSkippedLeads($userId);
        
        if (!empty($subAccountInfo) && $isRevokeReportingAccess) {
            throw new EAccessDenied('Access denied');
        }
        
        if (!empty($subAccountInfo) && $isRevokeSuccessLeads) {
            $this->out['isRevokeSuccessLeads'] = true;
        }
        
        if (!empty($subAccountInfo) && $isRevokeFailedLeads) {
            $this->out['isRevokeFailedLeads'] = true;
        }
        
        if (!empty($subAccountInfo) && $isRevokeSkippedLeads) {
            $this->out['isRevokeSkippedLeads'] = true;
        }
        
        $this->columns = array();
        $this->columns[1]   = 'User';
        $this->columns[2]   = 'Full Name';
        $this->columns[3]   = 'Email';
        $this->columns[4]   = 'Full Address';
        $this->columns[5]   = 'Phone';
        $this->columns[6]   = 'Campaign (field type)';
        $this->columns[7]   = 'Campaign';
        $this->columns[8]   = 'Partner';
        $this->columns[9]   = 'Revenue';
        $this->columns[10]  = 'Send&nbsp;Time';
        $this->columns[11]  = 'IP';
        $this->columns[12]  = 'Referrer';
        
        
    }
    //--------------------------------------------------------------------------


    public function indexAction() {
        App::getFrontController()->extraCss[] = 'reports.css';
        $this->pageTitle = 'Reporting';

        $auth = Auth::getInstance();
        if (isset($_GET['muid']) && $auth->isAdmin() && ($_GET['muid'] != $auth->getUserId())) {
            $ur = SingletonRegistry::getModelUser()->get((int)$_GET['muid']);
            if ($ur) {
                $arr = $_GET;
                unset($arr['muid']);
                $arr['uid'] = $ur['id'];
                $arr['tz']  = $ur['time_zone'];
                App::getFrontController()->redirectToCP($this, array('action' => 'adminReport'), $arr);
            }
        }
        $helper = $this->getHelperReport();
        $helper->setArrays($this->out, $_GET);
        $helper->prepareCommonReportData(false);
    }
    //--------------------------------------------------------------------------


    public function adminReportAction() {
        if (!Auth::getInstance()->isAdmin()) {
            throw new EAccessDenied();
        }
        App::getFrontController()->extraCss[] = 'reports.css';
        $this->pageTitle = 'Admin Reporting';

        $this->setViewFile($this->getViewFileFor($this, array('action' => 'index')));

        $helper = $this->getHelperReport();
        $helper->setArrays($this->out, $_GET);
        $helper->prepareCommonReportData(true);
    }
    //--------------------------------------------------------------------------


    public function csvReportAction() {
        $helper = $this->getHelperReport();
        $helper->setArrays($this->out, $_GET);
        $helper->csvReport();

        App::getFrontController()->setUsePageTemplate(false);
        set_time_limit(36000);
        $helper->outputCsvReport();
    }
    //--------------------------------------------------------------------------


    public function viewFormValuesAction() {
        $incId = isset($_GET['inc_id']) ? max(0, (int)$_GET['inc_id']) : 0;
        App::getFrontController()->extraCss[] = 'reports.css';
        App::getFrontController()->extraCss[] = 'reports2.css';

        $li = SingletonRegistry::getModelLogIncoming();
        $inc = $li->get($incId);
        if (!$inc) {
            throw new EExplainableError("Invalid inc_id value");
        }
        if (!Auth::getInstance()->isAdmin()) {
            $ownerId = $li->getOwnerOf($incId);
            if ($ownerId != Auth::getInstance()->getUserId() && (!in_array($inc['campaign_id'], SingletonRegistry::getModelSubAccount()->listAllCampaignIds(Auth::getInstance()->getUserId())))) {
                throw new EAccessDenied();
            }
        }

        $values = $li->listSubmissionValues($incId);
        $values[Model_CampaignField::FIELD_TYPE_EMAIL] = $inc['email'];
        $this->out['values'] = $values;

        $mcf = SingletonRegistry::getModelCampaignField();
        $fieldTypes = $mcf->listFieldTypesAssoc();
        $this->out['fieldTypes'] = $fieldTypes;

        $mc = SingletonRegistry::getModelCampaign();
        $campaign = $mc->get($inc['campaign_id']);
        $campaignFields = array(Model_CampaignField::FIELD_TYPE_EMAIL => $campaign['email_field_name']);
        foreach ($mcf->listAllForCampaign($inc['campaign_id']) as $row) {
            $campaignFields[$row['field_type']] = $row['name'];
        }
        $this->out['campaignFields'] = $campaignFields;
        $this->out['ipAddress']      = $inc['remote_ip'];
        $this->out['hashCode']       = $mc->getCampaignHashCode($campaign['user_id'], $campaign['id']);
        $this->out['campaignId']     = $campaign['id'];
        $this->out['incId']          = $incId;
    }
    //--------------------------------------------------------------------------


    public function viewPartnerResponseAction() {
        $delId = isset($_GET['delivery_id']) ? max(0, (int)$_GET['delivery_id']) : 0;
        App::getFrontController()->extraCss[] = 'reports.css';
        App::getFrontController()->extraCss[] = 'reports2.css';

        $mld = SingletonRegistry::getModelLogDelivery();
        $delivery = $mld->get($delId);
        if (!$delivery) {
            throw new EExplainableError("Invalid delivery_id value");
        }
        if (!Auth::getInstance()->isAdmin()) {
            $li = SingletonRegistry::getModelLogIncoming();
            $ownerId = $li->getOwnerOf($delivery['incoming_id']);
            if ($ownerId != Auth::getInstance()->getUserId() && (!in_array($delivery['partner_id'], SingletonRegistry::getModelSubAccount()->listAllPartnerIds(Auth::getInstance()->getUserId())))) {
                throw new EAccessDenied();
            }
        }

        $this->out['response'] = $delivery['partner_response'];
        $this->out['incId']    = $delivery['incoming_id'];
    }
    //--------------------------------------------------------------------------


    public function viewSentValuesAction() {
        $delId = isset($_GET['delivery_id']) ? max(0, (int)$_GET['delivery_id']) : 0;
        App::getFrontController()->extraCss[] = 'reports.css';
        App::getFrontController()->extraCss[] = 'reports2.css';

        $mld = SingletonRegistry::getModelLogDelivery();
        $delivery = $mld->get($delId);
        if (!$delivery) {
            throw new EExplainableError("Invalid delivery_id value");
        }

        if (!Auth::getInstance()->isAdmin()) {
            $li = SingletonRegistry::getModelLogIncoming();
            $ownerId = $li->getOwnerOf($delivery['incoming_id']);
            if ($ownerId != Auth::getInstance()->getUserId() && (!in_array($delivery['partner_id'], SingletonRegistry::getModelSubAccount()->listAllPartnerIds(Auth::getInstance()->getUserId())))) {
                throw new EAccessDenied();
            }
        }
        parse_str($delivery['data'], $arr);
        $this->out['values'] = $arr;
        $this->out['incId']  = $delivery['incoming_id'];
        
        $this->out['templateSent'] = $mld->getTemplateTextSent($delId);
        $deliveryPartner = SingletonRegistry::getModelPartner()->get($delivery['partner_id']);
        $this->out['hideTemplateSent'] = ((int)$deliveryPartner['posting_type'] === Model_Partner::POST_TYPE_POST || 
                                            (int)$deliveryPartner['posting_type'] === Model_Partner::POST_TYPE_GET) ? true : false;
    }
    //--------------------------------------------------------------------------


    public function viewSystemResponseAction() {
        $incId = isset($_GET['inc_id']) ? max(0, (int)$_GET['inc_id']) : 0;
        App::getFrontController()->extraCss[] = 'reports.css';
        App::getFrontController()->extraCss[] = 'reports2.css';

        $li = SingletonRegistry::getModelLogIncoming();
        $inc = $li->get($incId);
        if (!$inc) {
            throw new EExplainableError("Invalid inc_id value");
        }
        if (!Auth::getInstance()->isAdmin()) {
            $ownerId = $li->getOwnerOf($incId);
            if ($ownerId != Auth::getInstance()->getUserId() && (!in_array($inc['campaign_id'], SingletonRegistry::getModelSubAccount()->listAllCampaignIds(Auth::getInstance()->getUserId())))) {
                throw new EAccessDenied();
            }
        }

        $this->out['response'] = $inc['system_response'];
        $this->out['incId']    = $incId;
    }
    //--------------------------------------------------------------------------


    public function setTimeZoneAction() {
        $arr = DateTimeZone::listIdentifiers();
        $tz = isset($_POST['tz']) ? $_POST['tz'] : Config::$timeZone;
        if (!in_array($tz, $arr)) {
            $tz = Config::$timeZone;
        }
        $mu = SingletonRegistry::getModelUser();
        $mu->updateTimeZone(Auth::getInstance()->getUserId(), $tz);
        die('OK');
    }
    //--------------------------------------------------------------------------


    public function saveReportColumnsSelectionAction() {
        $columns = array();
        if (isset($_POST['ids']) && is_array($_POST['ids'])) {
            foreach ($_POST['ids'] as $cid) {
                if (isset($this->columns[(int)$cid])) {
                    $columns[(int)$cid] = $this->columns[(int)$cid];
                }
            }
        }
        if ($columns) {
            Session::getSession()->reportColumns = $columns;
            Session::getSession()->markAsCustomColumns = true;
        }
        die('OK');
    }
    //--------------------------------------------------------------------------


    public function preRun() {
        parent::preRun();
        App::getFrontController()->activeSection = FrontController::SECTION_REPORTING;
    }
    //--------------------------------------------------------------------------


    public function prevNext($urlBaseName, $isAdminReport, $isIid, $iid, $did, $hideRepost, array $originalGetArgs, array $iids, array $dids = array()) {
        if ($isIid) {
            return $this->getIidPrevNext($urlBaseName, $isAdminReport, $iid, $did, $hideRepost, $originalGetArgs, $iids, $dids);
        } else {
            return $this->getDidPrevNext($urlBaseName, $isAdminReport, $did, $hideRepost, $originalGetArgs, $dids);
        }
    }
    //--------------------------------------------------------------------------


    public function getIidPrevNext($urlBaseName, $isAdminReport, $iid, $did, $hideRepost, array $originalGetArgs, array $iids, array $dids = array()) {
        /* $iid = Incoming ID (we accepted the lead
           $did = Delivery ID (where did we send this lead to) */
        $prev = array('o' => $originalGetArgs, 'dids' => implode('-', $dids), 'iids' => implode('-', $iids));
        if ($hideRepost) {
            $prev['hide_repost'] = 1;
        }
        $next = $prev;
        $showPrev = $showNext = false;
        
        $pos = array_search($did, $dids);
        // in case delivery_id = 0, use incoming_id to dertermine current position
        if (intval($did) === 0) {
            $pos = array_search($iid, $iids);
        }
        
        if ($pos === false) {
            return '';
        }
        if ($pos < (sizeof($dids) - 1)) {
            $p = $pos + 1;
            while ($p < sizeof($dids)) {
                if ($iids[$p] != $iid) {
                    $showPrev = true;
                    $prev['delivery_id'] = $dids[$p];
                    $prev['inc_id']      = $iids[$p];
                    break;
                }
                $p++;
            }
        }
        if ($pos > 0) {
            $p = $pos - 1;
            while ($p >= 0) {
                if ($iids[$p] != $iid) {
                    $showNext = true;
                    $next['delivery_id'] = $dids[$p];
                    $next['inc_id']      = $iids[$p];
                    break;
                }
                $p--;
            }
        }
        $return = array();
        if ($showPrev) {
            $url = "$urlBaseName?" . http_build_query($prev);
            $return[] = "<a href=\"$url\">[Previous]</a>";
        } else {
            $url = $this->getPrevIidUrl($isAdminReport, $iid, $did, $hideRepost, $originalGetArgs, $iids, $dids);
            if ($url) {
                $return[] = "<a href=\"$urlBaseName?$url\">[Previous]</a>";
            }
        }
        if ($showNext) {
            $url = "$urlBaseName?" . http_build_query($next);
            $return[] = "<a href=\"$url\">[Next]</a>";
        } else {
            $url = $this->getNextIidUrl($isAdminReport, $iid, $did, $hideRepost, $originalGetArgs, $iids, $dids);
            if ($url) {
                $return[] = "<a href=\"$urlBaseName?$url\">[Next]</a>";
            }
        }
        return implode(' ', $return);
    }
    //--------------------------------------------------------------------------


    private function getPrevIidUrl($isAdminReport, $iid, $did, $hideRepost, array $originalGetArgs, array $iids, array $dids) {
        $args = $originalGetArgs;
        for ($page = empty($args['page']) ? 2 : (int)$args['page'] + 1; ; $page++) {
            $args['page'] = $page;
            $hr = $this->getHelperReport();
            $out = array();
            $hr->setArrays($out, $args);
            $hr->prepareCommonReportData($isAdminReport && Auth::getInstance()->isAdmin());
            if (empty($out['dids'])) {
                return '';
            }
            $iids2 = $out['iids'];
            for($i = 0; $i < sizeof($iids2); $i++) {
                if ($iids2[$i] && ($iids2[$i] != $iid)) {
                    $getArgs = array();
                    $getArgs['dids'] = implode('-', $out['dids']);
                    $getArgs['iids'] = implode('-', $out['iids']);
                    $getArgs['o']    = $args;
                    if ($hideRepost) {
                        $getArgs['hide_repost'] = 1;
                    }
                    $getArgs['delivery_id'] = $out['dids'][$i];
                    $getArgs['inc_id'] = $iids2[$i];
                    return http_build_query($getArgs);
                }
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    private function getNextIidUrl($isAdminReport, $iid, $did, $hideRepost, array $originalGetArgs, array $iids, array $dids) {
        $args = $originalGetArgs;
        if (empty($args['page']) || $args['page'] == 1) {
            return '';
        }
        for ($page = (int)$args['page'] - 1; $page > 0; $page--) {
            $args['page'] = $page;
            $hr = $this->getHelperReport();
            $out = array();
            $hr->setArrays($out, $args);
            $hr->prepareCommonReportData($isAdminReport && Auth::getInstance()->isAdmin());
            if (empty($out['dids'])) {
                return '';
            }
            $iids2 = $out['iids'];
            for($i = sizeof($iids2) - 1; $i >= 0; $i--) {
                if ($iids2[$i] && ($iids2[$i] != $iid)) {
                    $getArgs = array();
                    $getArgs['dids'] = implode('-', $out['dids']);
                    $getArgs['iids'] = implode('-', $out['iids']);
                    $getArgs['o']    = $args;
                    if ($hideRepost) {
                        $getArgs['hide_repost'] = 1;
                    }
                    $getArgs['delivery_id'] = $out['dids'][$i];
                    $getArgs['inc_id'] = $iids2[$i];
                    return http_build_query($getArgs);
                }
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public function getDidPrevNext($urlBaseName, $isAdminReport, $did, $hideRepost, array $originalGetArgs, array $dids = array()) {
        $prev = array('o' => $originalGetArgs, 'dids' => implode('-', $dids));
        if ($hideRepost) {
            $prev['hide_repost'] = 1;
        }
        $next = $prev;
        $showPrev = $showNext = false;
        $pos = array_search($did, $dids);
        if ($pos === false) {
            return '';
        }

        $mld = SingletonRegistry::getModelLogDelivery();
        if ($pos < (sizeof($dids) - 1)) {
            $p = $pos + 1;
            while ($p < sizeof($dids)) {
                $delId = max(0, (int)$dids[$p]);
                if ($mld->get($delId)) {
                    $showPrev = true;
                    $prev['delivery_id'] = $dids[$p];
                    break;
                }
                $p++;
            }
        }
        if ($pos > 0) {
            $p = $pos - 1;
            while ($p >= 0) {
                $delId = max(0, (int)$dids[$p]);
                if ($mld->get($delId)) {
                    $showNext = true;
                    $next['delivery_id'] = $dids[$p];
                    break;
                }
                $p--;
            }
        }

        $return = array();
        if ($showPrev) {
            $url = "$urlBaseName?" . http_build_query($prev);
            $return[] = "<a href=\"$url\">[Previous]</a>";
        } else {
            $url = $this->getPrevDidUrl($isAdminReport, $did, $hideRepost, $originalGetArgs, $dids);
            if ($url) {
                $return[] = "<a href=\"$urlBaseName?$url\">[Previous]</a>";
            }
        }
        if ($showNext) {
            $url = "$urlBaseName?" . http_build_query($next);
            $return[] = "<a href=\"$url\">[Next]</a>";
        } else {
            $url = $this->getNextDidUrl($isAdminReport, $did, $hideRepost, $originalGetArgs, $dids);
            if ($url) {
                $return[] = "<a href=\"$urlBaseName?$url\">[Next]</a>";
            }
        }
        return implode(' ', $return);
    }
    //--------------------------------------------------------------------------


    private function getPrevDidUrl($isAdminReport, $did, $hideRepost, array $originalGetArgs, array $iids, array $dids = array()) {
        $args = $originalGetArgs;
        for ($page = empty($args['page']) ? 2 : (int)$args['page'] + 1; ; $page++) {
            $args['page'] = $page;
            $hr = $this->getHelperReport();
            $out = array();
            $hr->setArrays($out, $args);
            $hr->prepareCommonReportData($isAdminReport && Auth::getInstance()->isAdmin());
            if (empty($out['dids'])) {
                return '';
            }
            $dids2 = $out['dids'];
            for($i = 0; $i < sizeof($dids2); $i++) {
                if ($dids2[$i] && ($dids2[$i] != $did)) {
                    $getArgs = array();
                    $getArgs['dids'] = implode('-', $out['dids']);
                    $getArgs['o']    = $args;
                    if ($hideRepost) {
                        $getArgs['hide_repost'] = 1;
                    }
                    $getArgs['delivery_id'] = $dids2[$i];
                    return http_build_query($getArgs);
                }
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    private function getNextDidUrl($isAdminReport, $did, $hideRepost, array $originalGetArgs, array $dids = array()) {
        $args = $originalGetArgs;
        if (empty($args['page']) || $args['page'] == 1) {
            return '';
        }
        for ($page = (int)$args['page'] - 1; $page > 0; $page--) {
            $args['page'] = $page;
            $hr = $this->getHelperReport();
            $out = array();
            $hr->setArrays($out, $args);
            $hr->prepareCommonReportData($isAdminReport && Auth::getInstance()->isAdmin());
            if (empty($out['dids'])) {
                return '';
            }
            $dids2 = $out['dids'];
            for($i = sizeof($dids2) - 1; $i >= 0; $i--) {
                if ($dids2[$i] && ($dids2[$i] != $did)) {
                    $getArgs = array();
                    $getArgs['dids'] = implode('-', $out['dids']);
                    $getArgs['o']    = $args;
                    if ($hideRepost) {
                        $getArgs['hide_repost'] = 1;
                    }
                    $getArgs['delivery_id'] = $dids2[$i];
                    return http_build_query($getArgs);
                }
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    /**
     * @return Helper_Report
     */
    private function getHelperReport() {
        return new Helper_Report();
    }
    //--------------------------------------------------------------------------
}
