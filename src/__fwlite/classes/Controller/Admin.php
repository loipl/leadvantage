<?php

class Controller_Admin extends Controller {
    private $capTypesMinute = array();

    /**
     * @var Model_LogIncomingRepost
     */
    protected $modelRepost;

    private $now;


    public function indexAction() {
        $this->out['usernames'] = SingletonRegistry::getModelUser()->listUsernamesAssoc(false);
    }
    //--------------------------------------------------------------------------


    public function importZipAction() {
        $f = new Form_Data();
        $f->add('text',   'file_name', 'Path to a CSV file you previously uploaded');
        $f->add('file',   'file', 'CSV File');
        $f->add('button', '',     'Import', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $f, true);
        $pf->title = 'Import ZIP Data';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $f->importFromArray($_POST);
            $fileName = isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : '';
            if (!$fileName) {
                $fileName = $f->file_name;
            }
            $fp = fopen($fileName, 'rb');
            if (!$fp) {
                $f->addErrorAndThrowEDone('file', 'Error reading the file');
            }
            $arr = fgetcsv($fp);

            /* @var $mzc Model_UsZipCode */
            $mzc = SingletonRegistry::getModelUsZipCode();
            $mzc->truncate();

            for(;;) {
                $arr = fgetcsv($fp);
                if (!$arr) {
                    break;
                }
                $row = array(
                'zip_code'   => $arr[0],
                'city_name'  => $arr[1],
                'state_abbr' => $arr[2],
                'area_code'  => $arr[4],
                'county'     => $arr[3],
                'time_zone'  => $arr[10],
                'latitude'   => $arr[8],
                'longitude'  => $arr[9],
                );
                $mzc->insert($row, true);
            }
            fclose($fp);
        }
    }
    //--------------------------------------------------------------------------


    public function importGeoIpAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $model = SingletonRegistry::getModelGeoipLocation();
            if (isset($_POST['fn_locations']) && is_string($_POST['fn_locations']) && is_readable($_POST['fn_locations'])) {
                $model->readLocations($_POST['fn_locations']);
            }

            if (isset($_POST['fn_blocks']) && is_string($_POST['fn_blocks']) && is_readable($_POST['fn_blocks'])) {
                $model->readBlocks($_POST['fn_blocks']);
            }
        } else {
            $f = new Form_Data();
            $f->add('text',   'fn_locations', 'Path to "locations" CSV file you previously uploaded');
            $f->add('text',   'fn_blocks',    'Path to "blocks" CSV file you previously uploaded');
            $f->add('button', '',     'Import', array('type' => 'submit'));
            $pf = new PageFragment_FormAuto($this, $f, true);
            $pf->title = 'Import GeoIP Data';
        }
    }
    //--------------------------------------------------------------------------


    public function importTestDataAction() {
        $form = new Form_Data();
        $form->add('button', '', 'Import', array('type' => 'submit'));

        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Import Test Data';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once CFG_FWLITE_HOME . '/testUtils.php';
            importTestData();
        }
    }
    //--------------------------------------------------------------------------


    public function flushOpcodeCacheAction() {
        apc_clear_cache();
        App::getFrontController()->redirectToCP($this);
    }
    //--------------------------------------------------------------------------


    public function flushApcUserCacheAction() {
        apc_clear_cache('user');
        App::getFrontController()->redirectToCP($this);
    }
    //--------------------------------------------------------------------------


    public function impersonateAction() {
        $form = new Form_Data();
        $form->add('text',   'username', 'Login as', array('* required' => true, '* trim' => true));
        $form->add('button', '',         'Login', array('type' => 'submit'));
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Log in as';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            try {
                Auth::getInstance()->logInAs($form->username);
                App::getFrontController()->redirectToCP('Controller_Dashboard');
            } catch (ERequestError $e) {
                $form->addErrorAndThrowEDone('username', $e->getMessage());
            }
        }
    }
    //--------------------------------------------------------------------------


    public function magicCookiesAction() {
        $form = new Form_Data();
        $pf = new PageFragment_FormAuto($this, $form, true);
        $pf->title = 'Magic Cookies Management';

        $sqlOptions = array(
                '0' => 'No output',
                '1' => 'Debug Output',
                'L' => 'Debug Output With Call Stack'
        );

        $form->add('select',   'sql_debug',        'On-screen SQL output', array('* items' => $sqlOptions));
        $form->add('checkbox', 'debug_redirect',   'Debug redirect');
        $form->add('button',   '',                 'Save', array('type' => 'submit'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $form->importPostRequestAndThrowEDoneOnError();
            setcookie(App::SQL_LOG_COOKIE,        $form->sql_debug,              time() + 365 * 10 * 86400, '/');
            setcookie(App::DEBUG_REDIRECT_COOKIE, $form->debug_redirect ? 1 : 0, time() + 365 * 10 * 86400, '/');

            App::getFrontController()->redirectToCP($this, array('action' => 'magicCookies'));
        } else {
            $form->sql_debug        = isset($_COOKIE[App::SQL_LOG_COOKIE]) ? $_COOKIE[App::SQL_LOG_COOKIE] : '0';
            $form->debug_redirect   = empty($_COOKIE[App::DEBUG_REDIRECT_COOKIE]) ? '0' : '1';
        }
    }
    //--------------------------------------------------------------------------


    public function repostBacklogAction() {
        $this->pageTitle   = 'Repost Backlog';
        $this->modelRepost = SingletonRegistry::getModelLogIncomingRepost();

        $this->now   = DB::$db->getTopLeft("SELECT NOW()");
        $lastMinutes = 20;
        $repostCount = $this->modelRepost->getAverageOutboundRepostCount($lastMinutes);

        $this->out['repostCount'] = $repostCount;
        $this->out['lastMinutes'] = $lastMinutes;

        $this->out['usernames']   = SingletonRegistry::getModelUser()->listUsernamesAssoc();
        $this->out['backlogInfo'] = $this->getBacklogData();
    }
    //--------------------------------------------------------------------------


    public function getBacklogData() {
        $backlogInfo = $this->modelRepost->listRepostQueueBacklog($this->now);
        if (!$backlogInfo) {
            return array();
        }

        $cids = $jids = $cids2 = array();
        foreach ($backlogInfo as & $row) {
            if ($row['catchup_id']) {
                $jids[$row['catchup_id']]  = 1;
            } else {
                $cids[$row['campaign_id']] = 1;
            }
            $cids2[$row['campaign_id']] = 1;
            $row['limited'] = false;
            $row['full_size'] = '';
            $row['full_delay'] = '';
        }

        $this->capTypesMinute = SingletonRegistry::getModelPartner()->listDeliveryCapTypesAssocWithMinute();
        $mc        = SingletonRegistry::getModelCampaign();
        $campaigns = $mc->listGroup(array_keys($cids),  'id');

        if ($cids) {
            foreach ($campaigns as $c) {
                if (intval($c['delivery_type']) === Model_Campaign::DELIVERY_TYPE_QUEUED_KEY && $c['throttle_value']) {
                    $this->setDelaysForCampaign($backlogInfo, $c['id'], $c['throttle_value'], $c['throttle_type']);
                }
            }
        }

        if ($jids) {
            /* @var $logIncoming Model_LogIncoming */
            $logIncoming = SingletonRegistry::getModelLogIncoming();

            $catchupJobs = $logIncoming->listCatchupJobRateLimits();
            foreach (array_keys($jids) as $id) {
                if (isset($catchupJobs[$id])) {
                    $job = $catchupJobs[$id];
                    if ($job['rl_value']) {
                        $this->setDelaysForCatchupJob($backlogInfo, $job['id'], $job['rl_value'], $job['rl_type']);
                    }
                }
            }
        }

        $campaigns2 = $mc->listGroup(array_keys($cids2), 'id');
        foreach ($backlogInfo as & $row) {
            $row['user_id']       = isset($campaigns2[$row['campaign_id']]['user_id']) ? (int)$campaigns2[$row['campaign_id']]['user_id'] : 0;
            $row['campaign_name'] = isset($campaigns2[$row['campaign_id']]['name']) ? $campaigns2[$row['campaign_id']]['name'] : '';
            $row['is_active']     = $campaigns2[$row['campaign_id']]['is_active'];
        }
        return $backlogInfo;
    }
    //--------------------------------------------------------------------------


    private function setDelaysForCampaign(array & $backlogInfo, $campaignId, $limitValue, $limitType) {
        $usedUp = $this->modelRepost->campaignRepostCount($campaignId, $this->capTypesMinute[$limitType], $this->now);
        foreach ($backlogInfo as & $row) {
            if (($row['campaign_id'] == $campaignId) && !$row['catchup_id']) {
                $row['limited'] = true;
                $actualBacklog = min(max(0, $limitValue - $usedUp), $row['size']);
                $row['full_size'] = $row['size'];
                $row['size']      = $actualBacklog;
                $delay = $this->getDelay($actualBacklog, $limitValue, $limitType);
                if ($delay !== false) {
                    $row['full_delay'] = $row['delay'];
                    $row['delay']      = $delay;
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    private function setDelaysForCatchupJob(array & $backlogInfo, $jobId, $limitValue, $limitType) {
        $usedUp = $this->modelRepost->catchupRepostCount($jobId, $this->capTypesMinute[$limitType], $this->now);
        foreach ($backlogInfo as & $row) {
            if ($row['catchup_id'] == $jobId) {
                $row['limited'] = true;
                $actualBacklog = min(max(0, $limitValue - $usedUp), $row['size']);
                $row['full_size'] = $row['size'];
                $row['size']      = $actualBacklog;
                $delay = $this->getDelay($actualBacklog, $limitValue, $limitType);
                if ($delay !== false) {
                    $row['full_delay'] = $row['delay'];
                    $row['delay']      = $delay;
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    private function getDelay($actualBacklog, $limitValue, $limitType) {
        $minutesTable = array(
            Model_Partner::DELIVERY_CTYPE_MINUTE => 1,
            Model_Partner::DELIVERY_CTYPE_HOUR   => 60,
            Model_Partner::DELIVERY_CTYPE_DAY    => 24 * 60,
            Model_Partner::DELIVERY_CTYPE_WEEK   => 7 * 24 * 60,
            Model_Partner::DELIVERY_CTYPE_MONTH  => 30 * 24 * 60
        );
        if (!isset($this->capTypesMinute[$limitType])) {
            return false;
        }
        $minutesTotal = $minutesTable[$this->capTypesMinute[$limitType]];
        $perMinute    = $limitValue / $minutesTotal;
        $delayMinutes = $actualBacklog / $perMinute;
        $totalSeconds = round($delayMinutes * 60);

        $seconds      = $totalSeconds % 60;
        $delayMinutes = floor($totalSeconds / 60);
        $minutes = $delayMinutes % 60;
        $hours = (int)($delayMinutes / 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    //--------------------------------------------------------------------------
}
