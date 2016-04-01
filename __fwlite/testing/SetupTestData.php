<?php

require_once CFG_FWLITE_HOME . '/simpletest/autorun.php';
require_once CFG_FWLITE_HOME . '/simpletest/browser.php';

class SetupTestData {

    /**
     * @desc ID of our test user
     */
    public static $userId = TEST_USER_ID;

    /**
     * @desc campaign names => campaign IDs
     */
    public static $cids = array();

    /**
     * @desc campaign names => campaign IDs
     */
    public static $pids = array();

    private static $op2np = array();

    public static $expectedRedirect = 'http://w1.localhost/';

    public static $runValidationTests = false;

    public static $sqlLog = '';

    public static $shouldLog = false;


    public static function preFlight() {
        if (Config::$timeZone) {
            date_default_timezone_set(Config::$timeZone);
        }

        Config::$lockPrefix .= 'unit-tests.';

        // Wordpress will apparently addslashes() to get, post etc, so we need to undo that
        $input = array(& $_GET, & $_POST, & $_REQUEST);
        App::unquoteArray($input);

        $nextDeliveryId = DB::$db->getTopLeftInt("SELECT Auto_increment
                FROM information_schema.tables
                WHERE table_name='log_delivery'
                AND table_schema = DATABASE();");
        $maxDID = DB::$db->getTopLeftInt("SELECT MAX(`delivery_id`) FROM `log_delivery_campaigns`");
        if ($nextDeliveryId && ($maxDID >= $nextDeliveryId)) {
            if (getenv('BOBAN_DEV_MACHINE')) {
                DB::$db->query("DELETE FROM `log_delivery_campaigns` WHERE `delivery_id` >= $nextDeliveryId");
            }
        }

        // When manually interrupting unit tests, partner row can stay with created_at into future, which breaks the results.
        // So here we make sure all the create dates are in the past.
        DB::$db->query("UPDATE `partners` SET `created_at` = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE `created_at` >= NOW()");

        // In case "impossible" partner filter for C11-P01 was left on
        DB::$db->query("DELETE FROM `partner_filters` WHERE `partner_id` = " . self::$pids['C11-P01']);

        unset($input);
        App::$wordpressIncluded = true;

        SingletonRegistry::getModelUser()->recreateUserCapCache();
        $arr = SingletonRegistry::getModelPartner()->get(SetupTestData::$pids['C11-P11'], MYSQL_ASSOC);
        $arr['delivery_ctype'] = 1;
        $arr['delivery_cap']   = 0;
        SingletonRegistry::getModelPartner()->update(SetupTestData::$pids['C11-P11'], $arr);

        // Make sure partitions are up to date for unit tests
        SingletonRegistry::getModelLogDelivery()->dailyApiReportSmallPartitionMaintenance(Config::$keepDelivery);
        SetupTestData::deleteEverythingForTestUser();

        if (PHP_SAPI !== 'cli') {
            DB::$db->addListener('SetupTestData::sqlErrorListener', false);
        }

        if (Config::$codeCoverageOutputFile) {
            register_shutdown_function(function () {
                SetupTestData::saveProfilerData(xdebug_get_code_coverage(), Config::$codeCoverageOutputFile);
            });
        }
        register_shutdown_function(function () {
                if (SetupTestData::$sqlLog) {
            echo '
        <div style="background-color: #fff; padding: 10px; font-family: monospace;"><h3>SQL Log:<br /></h3>';
            echo SetupTestData::$sqlLog;
            list($count, $time) = DB::getGlobalStats();
            echo "<br>Total of $count queries in {$time}s</div>";
        }
        });
    }
    //--------------------------------------------------------------------------


    public static function sqlErrorListener($sql, array $params, $sqlToRun, DB $db, $sqlErr, $sqlErrNo, $queryTime) {
        if (!self::$shouldLog) {
            return;
        }
        $bgColor = $sqlErr ? '#faa;' : '#eee';
        self::$sqlLog .= "<pre style=\"padding: 2px 9px; margin: 0; background-color: $bgColor\"><b>/* " . number_format($queryTime, 4) . "s */</b> ";
        self::$sqlLog .= escapeHtml($sqlToRun) . ";";
        if ($sqlErr) {
            self::$sqlLog .= "\n<span style=\"font-weight: bold; color: red;\">/* $sqlErrNo: " . escapeHtml(wordwrap($sqlErr, 100)) . " */</span>";
        }

        // ob_start();
        // debug_print_backtrace();
        // self::$sqlLog .= "\n<span style=\"color: #888; font-size: 75%;\">" . escapeHtml(ob_get_clean());
        self::$sqlLog .= "</span>";

        self::$sqlLog .= "</pre>\n\n";
    }
    //--------------------------------------------------------------------------


    public static function resetCampaign($name) {
        static $arr = false;
        if ($arr === false) {
            $arr = include dirname(__FILE__) . '/testCampaignsExport.inc';
        }
        $campaign = $arr[$name]['campaign'];
        $cid = SetupTestData::$cids[$name];
        SingletonRegistry::getModelCampaign()->update($cid, $campaign);
        SingletonRegistry::getModelCampaignField()->deleteWhere(array('campaign_id' => (int)$cid));
    }
    //--------------------------------------------------------------------------


    public static function addCampaignField($campaignName, $type, $name, $mandatory = false) {
        $data = array(
            'campaign_id'  => (int)SetupTestData::$cids[$campaignName],
            'name'         => $name,
            'field_type'   => $type,
            'description'  => $name,
            'is_mandatory' => $mandatory ? '1' : '0',
            'validate'     => ''
        );
        SingletonRegistry::getModelCampaignField()->insert($data);
    }
    //--------------------------------------------------------------------------


    public static function setCampaignDelivery($campaignName, array $partnerNames) {
        $cid = SetupTestData::$cids[$campaignName];
        $data = array();
        foreach ($partnerNames as $partnerName) {
            $data[] = array('partner_id' => SetupTestData::$pids[$partnerName], 'always_send' => '0');
        }
        SingletonRegistry::getModelCampaign()->insertDeliverySettings($cid, $data);
    }
    //--------------------------------------------------------------------------


    public static function exportCampaign($id) {
        $sql = "SELECT * FROM `campaigns` WHERE `id` = ?";
        $data = DB::$db->getTopArray($sql, array((int)$id), MYSQL_ASSOC);
        $output = array('campaign' => $data);

        $output['fields']   = DB::$db->getArrayIndexed("SELECT * FROM `campaign_fields` WHERE `campaign_id` = ?", array((int)$id), 'id');
        $output['delivery'] = DB::$db->getArrayIndexed("SELECT * FROM `campaign_delivery` WHERE `campaign_id` = ? ORDER BY order_nr ", array((int)$id), 'partner_id');

        return $output;
    }
    //--------------------------------------------------------------------------


    public static function exportAllCampaigns() {
        $sql = "SELECT `name`, `id` FROM `campaigns` WHERE `user_id` = ?";
        $names = DB::$db->getArrayAssoc($sql, array((int)self::$userId));
        $output = array();
        foreach ($names as $name => $id) {
            $output[$name] = self::exportCampaign($id);
        }
        return $output;
    }
    //--------------------------------------------------------------------------


    public static function exportPartner($id) {
        $sql = "SELECT * FROM `partners` WHERE `id` = ?";
        $data = DB::$db->getTopArray($sql, array((int)$id), MYSQL_ASSOC);
        $output = array('partner' => $data);

        $output['fields']   = DB::$db->getArrayIndexed("SELECT * FROM `partner_fields`    WHERE `partner_id` = ?", array((int)$id), 'id');
        $output['filters']  = DB::$db->getArrayIndexed("SELECT * FROM `partner_filters`   WHERE `partner_id` = ?", array((int)$id), 'id');
        $output['settings'] = DB::$db->getArrayIndexed("SELECT * FROM `partner_settings`  WHERE `partner_id` = ?", array((int)$id), 'id');

        return $output;
    }
    //--------------------------------------------------------------------------


    public static function exportAllPartners() {
        $sql = "SELECT `name`, `id` FROM `partners` WHERE `user_id` = ?";
        $names = DB::$db->getArrayAssoc($sql, array((int)self::$userId));
        $output = array();
        foreach ($names as $name => $id) {
            $output[$name] = self::exportPartner($id);
        }
        return $output;
    }
    //--------------------------------------------------------------------------


    public static function readData() {
        $sql = "SELECT `name`, `id` FROM `campaigns` WHERE `user_id` = ?";
        self::$cids = DB::$db->getArrayAssoc($sql, array((int)self::$userId));

        $sql = "SELECT `name`, `id` FROM `partners` WHERE `user_id` = ?";
        self::$pids = DB::$db->getArrayAssoc($sql, array((int)self::$userId));
    }
    //--------------------------------------------------------------------------


    public static function recreateTestCampaigns($data) {
        DB::$db->query("DELETE FROM `campaigns` WHERE `user_id` = ?", array((int)self::$userId));
        DB::$db->query("ALTER TABLE `campaigns` AUTO_INCREMENT  = 1");

        $mc = SingletonRegistry::getModelCampaign();
        $mcf = SingletonRegistry::getModelCampaignField();

        $old2new = array();
        $shadows = array();
        foreach ($data as $cname => $cdata) {
            $campaign = $cdata['campaign'];
            $campaign['user_id'] = self::$userId;
            if ($campaign['shadow_of']) {
                $shadows[$campaign['id']] = $campaign['shadow_of'];
            }
            $campaign['shadow_of'] = null;
            $oldId = $campaign['id'];
            unset($campaign['id']);
            $cid = $mc->insert($campaign);
            if (!$cid) {
                die('Cannot create campaign ' . $cname);
            }
            $old2new[$oldId] = $cid;
            foreach ($cdata['fields'] as $fdata) {
                Lib::removeIntKeys($fdata);
                unset($fdata['id']);
                $fdata['campaign_id'] = $cid;
                $mcf->insert($fdata);
            }
            $deliveryData = array();
            foreach ($cdata['delivery'] as $ddata) {
                Lib::removeIntKeys($ddata);
                $ddata['partner_id']  = self::$op2np[(int)$ddata['partner_id']];
                $deliveryData[] = $ddata;
            }
            $mc->insertDeliverySettings($cid, $deliveryData);
        }
        foreach ($shadows as $oldId => $oldSHId) {
            $id   = $old2new[$oldId];
            $shId = $old2new[$oldSHId];
            $data = $mc->get($id);
            Lib::removeIntKeys($data);
            $new  = $data;
            $new['shadow_of'] = (int)$shId;
            $mc->updateDiff($id, $new, $data);
        }
    }
    //--------------------------------------------------------------------------


    public static function recreateTestPartners($data) {
        DB::$db->query("DELETE FROM `partners` WHERE `user_id` = ?", array((int)self::$userId));
        DB::$db->query("ALTER TABLE `partners` AUTO_INCREMENT  = 1");

        $mp = SingletonRegistry::getModelPartner();
        $mpf = new Model_PartnerField;
        $mpr = new Model_PartnerFilter;
        $mps = new Model_PartnerSettings;

        foreach ($data as $pname => $pdata) {
            $partner = $pdata['partner'];
            $oldId = (int)$partner['id'];
            unset($partner['id']);
            $partner['user_id'] = self::$userId;
            $pid = $mp->insert($partner);
            if (!$pid) {
                die("Cannot create partner $pname");
            }
            self::$pids[$pname] = $pid;
            self::$op2np[$oldId] = $pid;

            foreach ($pdata['fields'] as $fdata) {
                Lib::removeIntKeys($fdata);
                unset($fdata['id']);
                $fdata['partner_id'] = $pid;
                if (!$mpf->insert($fdata)) {
                    die(mysql_error());
                }
            }

            foreach ($pdata['filters'] as $fdata) {
                Lib::removeIntKeys($fdata);
                unset($fdata['id']);
                $fdata['partner_id'] = $pid;
                if (!$mpr->insert($fdata)) {
                    die(mysql_error());
                }
            }

            foreach ($pdata['settings'] as $fdata) {
                Lib::removeIntKeys($fdata);
                unset($fdata['id']);
                $fdata['partner_id'] = $pid;
                if (!$mps->insert($fdata)) {
                    die(mysql_error());
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    public static function deleteEverythingForTestUser() {
        // self::deleteTrafficGeneratedDataAndCatchupJobs();
        // return;

        DB::$db->query("DELETE FROM log_incoming");
        DB::$db->query("DELETE FROM log_delivery");
        DB::$db->query("DELETE FROM repost_queue");
        DB::$db->query("DELETE FROM repost_queue_mem");
        DB::$db->query("DELETE FROM catchup_jobs");
        // DB::$db->query("DELETE FROM validation_log");
        DB::$db->query("DELETE FROM sh_delivery");
        DB::$db->query("DELETE FROM sh_delivery");
        DB::$db->query("DELETE FROM sh_incoming");
        DB::$db->query("DELETE FROM sh_repost");
        DB::$db->query("DELETE FROM summary_acceptance");
    }
    //--------------------------------------------------------------------------


    public static function deleteTrafficGeneratedDataAndCatchupJobs() {
        DB::$db->query("DELETE FROM api_report_small");
        $queries = "
            SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE `api_report_small`;
            TRUNCATE `catchup_jobs`;
            TRUNCATE `external_lookup_cache`;
            TRUNCATE `geoip_ip_cache`;
            TRUNCATE `log_api_errors`;
            TRUNCATE `log_delivery`;
            TRUNCATE `log_delivery_campaigns`;
            TRUNCATE `log_incoming`;
            TRUNCATE `log_incoming_reposts`;
            TRUNCATE `log_incoming_tracking`;
            TRUNCATE `log_incoming_values`;
            TRUNCATE `profiles`;
            TRUNCATE `profiles_data`;
            TRUNCATE `profiles_inferred`;
            TRUNCATE `repost_log`;
            TRUNCATE `repost_queue`;
            TRUNCATE `repost_queue_mem`;
            TRUNCATE `repost_threads`;
            TRUNCATE `sh_catchup`;
            TRUNCATE `sh_delivery`;
            TRUNCATE `sh_incoming`;
            TRUNCATE `sh_repost`;
            TRUNCATE `submission_log`;
            TRUNCATE `summary_acceptance`;
            TRUNCATE `validation_cache`;
            TRUNCATE `validation_log`;
            TRUNCATE `visitors`;
            SET FOREIGN_KEY_CHECKS=1;
        ";

        foreach (explode("\n", trim($queries)) as $sql) {
            DB::$db->query(trim($sql));
        }
    }
    //--------------------------------------------------------------------------


    public static function saveProfilerData($profilerData, $saveToHtmlFile) {
        ksort($profilerData);
        $arr = xdebug_get_code_coverage();
        $html = "";
        $header = "";
        $fileCounter = 1;
        foreach ($profilerData as $fileName => $lineCounts) {
            if (strpos($fileName, CFG_ROOT_DIR . 'wp') === 0) {
                continue;
            }
            if (strpos($fileName, CFG_FWLITE_HOME . 'simpletest') === 0) {
                continue;
            }
            if (strpos($fileName, CFG_FWLITE_HOME . 'unit_tests') === 0) {
                continue;
            }
            if (strpos($fileName, CFG_COMMON_HOME) === 0) {
                continue;
            }
            if ((dirname($fileName)) == dirname(__FILE__)) {
                continue;
            }
            if (!is_readable($fileName)) {
                echo "\n\tsaveProfilerData($fileName);\n";
            }
            $lines = file($fileName);
            $shortFileName = str_replace('\\', '/', substr($fileName, strlen(CFG_ROOT_DIR)));
            if ($shortFileName == '__fwlite/classes/Browser.php') {
                continue;
            }
            $html .= "\n\n\t<b><a name=\"$fileCounter\">$shortFileName</a>:</b>\n\n";
            foreach ($lines as $lineNr => $line) {
                $count = isset($lineCounts[$lineNr + 1]) ? (int)$lineCounts[$lineNr + 1] : 0;
                $style = $count ? ' style="background-color: green;"' : '';
                $html .= sprintf("<span$style>%7d : %s</span>", $lineNr + 1, escapeHtml($line));
            }
            $covered = sizeof($lineCounts);
            $totalLines = sizeof($lines);
            $percentage = (int)($covered * 100 / $totalLines);
            $header .= "<a href=\"#$fileCounter\">$shortFileName ($covered of $totalLines, $percentage%)</a>\n";
            $fileCounter++;
        }
        $fp = fopen($saveToHtmlFile, 'wb');
        if ($fp) {
            fprintf($fp, "<html><head><title>Code Coverage</title><style type=\"text/css\">pre {font-size: 10px;}</style></head>\n\n<body><pre>");
            fprintf($fp, "%s<br><br>%s</pre></body></html>", $header, $html);
            fclose($fp);
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Repost
     */
    public static function debugEngineRepost() {
        $engine = new Engine_Repost();
        $engine->activateDebugMode();
        return $engine;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Submission
     */
    public static function debugEngineSubmission($testData = false) {
        $engine = new Engine_Submission();
        $engine->activateDebugMode();
        if (is_array($testData)) {
            $engine->setTestIncomingData($testData);
        }
        return $engine;
    }
    //--------------------------------------------------------------------------
}


class MyUT extends UnitTestCase {

    protected $deliveredToPartnerIDs = array();

    protected $deliveryFailedToPartnerIDs = array();

    protected $waitAction = false;

    protected $lastData = array();

    protected $gathered = array();

    protected $actionsReported = array();

    protected $cbFinish     = array();
    protected $cbGatherData = array();
    protected $cbDelivered  = array();
    protected $cbActionList = array();


    public function __construct() {
        parent::__construct();
        $this->cbFinish      = array($this, 'callbackFinish');
        $this->cbGatherData  = array($this, 'callbackGatherData');
        $this->cbDelivered   = array($this, 'callbackDelivered');
        $this->cbActionList  = array($this, 'callbackActionList');
    }
    //--------------------------------------------------------------------------


    public function setUp() {
        $this->deliveredToPartnerIDs = array();
        $this->deliveryFailedToPartnerIDs = array();
        $this->waitAction = false;
        $this->lastData = array();
        $this->gathered = array();
        $this->actionsReported = array();
    }
    //--------------------------------------------------------------------------


    public function callbackFinish(Engine $engine, $action, array & $data) {
        $this->actionsReported[] = $action;
        if (($this->waitAction === false) || ($this->waitAction == $action)) {
            $this->lastData = $data;
            throw new EFWUT($action);
        }
    }
    //--------------------------------------------------------------------------


    public function callbackGatherData(Engine $engine, $action, array & $data) {
        $this->actionsReported[] = $action;
        if ($this->waitAction !== false) {
            if ($this->waitAction == $action) {
                $this->gathered[] = $data;
            }
        } else {
            $this->gathered[] = $data;
        }
    }
    //--------------------------------------------------------------------------


    public function callbackDelivered(Engine $engine, $action, array & $data) {
        $this->actionsReported[] = $action;
        if ($action == Engine::ACTION_DELIVERED) {
            $this->deliveredToPartnerIDs[] = (int)$data['id'];
        } elseif ($action == Engine::ACTION_DELIVERY_FAILED) {
            $this->deliveryFailedToPartnerIDs[] = (int)$data['id'];
        }
    }
    //--------------------------------------------------------------------------


    public function callbackActionList(Engine $engine, $action, array & $data) {
        $this->actionsReported[] = $action;
    }
    //--------------------------------------------------------------------------


    protected function repostAllForTestUser($callback = false, $removePartnersFromSourceCampaign = false) {
        $rh = new Helper_Repost();
        if ($callback) {
            $rh->setCallback($callback);
        }
        $rh->removePartnersFromSourceCampaign = $removePartnersFromSourceCampaign;
        return $rh->doRepostDebug();
    }
    //--------------------------------------------------------------------------


    protected function repostAll() {
        $rh = new Helper_Repost();
        $rh->onlyUserId = TEST_USER_ID;
        return $rh->doRepost(time(), 1200);
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Submission
     */
    protected function newEngineSubmission($testData = false, $callback = false, $waitAction = false) {
        $engine = SetupTestData::debugEngineSubmission($testData);
        $engine->setListenerCallback($callback);
        $this->waitAction = $waitAction;

        return $engine;
    }


    /**
     * @return Engine
     */
    protected function setupEngineCallbackAndTestData(Engine $engine, array $callback, $testData = false, $waitAction = false) {
        $engine->setListenerCallback($callback);
        if (is_array($testData)) {
            $engine->setTestIncomingData($testData);
        }
        if ($waitAction !== false) {
            $this->waitAction = $waitAction;
        }
        return $engine;
    }
    //--------------------------------------------------------------------------
}


class C11UnitTests
extends MyUT
{

    /**
     * @var Engine_Submission
     */
    protected $engine;

    /**
     * @var Model_Campaign
     */
    protected $modelCampaign;

    /**
     * @var Model_Partner
     */
    protected $modelPartner;

    /**
     * @var Model_LogIncoming
     */
    protected $logIncoming;

    protected $campaign;

    protected $campaignId;

    protected $partnerRetries = 0;

    protected $repostHours = 0;

    protected $testData = array();

    protected $fullList = array(
        '1-y-n',
        '2-y-n',
        '3-y-n',
        '4-y-n',
        '5-y-n',
        '6-y-n',
        '7-y-n',
        '8-y-n',
        '9-y-n',
        '10-y-n',
        '11-y-n',
        '12-y-n',
        '13-y-n',
        '14-y-n',
        '15-y-n',
        '16-y-n',
    );

    protected $ids;


    public function setUp() {
        Validator_Registry::clearGlobalCounters();
        $this->engine = SetupTestData::debugEngineSubmission();

        $this->deliveredToPartnerIDs      = array();
        $this->deliveryFailedToPartnerIDs = array();

        $this->modelCampaign = SingletonRegistry::getModelCampaign();
        $this->modelPartner  = SingletonRegistry::getModelPartner();
        $this->logIncoming   = SingletonRegistry::getModelLogIncoming();

        $this->campaignId    = SetupTestData::$cids['UTC11'];
        $this->campaign      = $this->modelCampaign->get($this->campaignId);

        for($i = 1; $i <= 5; $i++) {
            $shadow = $sh2 = $this->modelCampaign->get(SetupTestData::$cids['UTC11 - Shadow Copy' . (($i == 1) ? '' : " $i")]);
            $shadow['shadow_of'] = null;
            $shadow['shadow_delay_hours'] = 0;
            $shadow['skim_percentage']    = 0;
            if (!is_null($sh2['shadow_of']) || ($sh2['shadow_delay_hours'] != 0) || ($sh2['skim_percentage'] != 0)) {
                $this->modelCampaign->update($shadow['id'], $shadow);
            }
        }

        $this->partnerRetries = 0;
        $this->testData = array('email' => 'c11@campaign.com');
        $ids = array();
        foreach (SetupTestData::$pids as $n => $id) {
            if (strpos($n, 'C11') === 0) {
                $ids[intval(substr($n, -2))] = $id;
            }
        }
        $this->ids = $ids;
        $this->repostHours = 0;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Check Submission Delivery - Setup partner delivery settings for C11 to
     * given delivery data, then check if delivery went in expected order
     *
     * @param $deliveryData see setupDelivery()
     * @param array $expectDeliveredToPartnerIDs numbers of partners, ie C11-P[number] which were delivered to
     */
    protected function checkDeliveryOrderNoRepost(array $deliveryData, array $expectDeliveredToPartnerIDs, $leadsPer = 2, $expectCampaignSuccess = true) {
        $this->engine = SetupTestData::debugEngineSubmission();

        $this->deliveredToPartnerIDs       = array();
        $this->deliveryFailedToPartnerIDs  = array();

        $this->campaign['should_repost']   = 0;
        $this->campaign['repost_hours']    = 0;
        $this->campaign['is_oob']          = 0;
        $this->campaign['leads_per']       = $leadsPer;
        $this->campaign['partner_retries'] = $this->partnerRetries;

        $this->engine->setTestIncomingData($this->testData);
        $this->engine->setListenerCallback(array($this, 'callbackDelivered'));
        $this->setupDelivery($deliveryData);

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {
            $this->assertEqual($e->getMessage(), $expectCampaignSuccess ? $this->campaign['success_url'] : $this->campaign['failure_url']);
            $this->assertEqual(sizeof($expectDeliveredToPartnerIDs), sizeof($this->deliveredToPartnerIDs));

            $expected = array();
            foreach ($expectDeliveredToPartnerIDs as $nr) {
                $expected[] = SetupTestData::$pids['C11-P' . sprintf('%02d', $nr)];
            }
            $this->assertEqual($expected, $this->deliveredToPartnerIDs);
            return $e->getMessage();
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @param array $expectDeliveredToPartnerIDs numbers of partners, ie C11-P[number]
     */
    protected function checkDeliveryOrderAndTurnRepostOn(array $deliveryData, array $expectDeliveredToPartnerIDs, $leadsPer = 2, $repostMax = 2, $expectCampaignSuccess = true, $isOOB = false) {
        $this->engine = SetupTestData::debugEngineSubmission();
        $this->deliveredToPartnerIDs = array();
        $this->deliveryFailedToPartnerIDs = array();

        $this->campaign['should_repost']   = 1;
        $this->campaign['repost_max']      = $repostMax;
        $this->campaign['repost_hours']    = $this->repostHours;
        $this->campaign['is_oob']          = $isOOB ? '1' : '0';
        $this->campaign['leads_per']       = $leadsPer;
        $this->campaign['partner_retries'] = $this->partnerRetries;

        $this->engine->setTestIncomingData($this->testData);
        $this->engine->setListenerCallback(array($this, 'callbackDelivered'));
        $this->setupDelivery($deliveryData);

        $this->logIncoming->deleteUsersRepostsFromQueue(TEST_USER_ID);
        $this->assertEqual(0, $this->logIncoming->countOfRepostQueuePerUser(TEST_USER_ID));

        try {
            $this->engine->processIncomingFormSubmission($this->campaign);
        } catch (ERedirectException $e) {

            $this->assertEqual($e->getMessage(), $expectCampaignSuccess ? $this->campaign['success_url'] : $this->campaign['failure_url']);
            $this->assertEqual(sizeof($expectDeliveredToPartnerIDs), sizeof($this->deliveredToPartnerIDs));
            $expected = array();
            foreach ($expectDeliveredToPartnerIDs as $nr) {
                $expected[] = SetupTestData::$pids['C11-P' . sprintf('%02d', $nr)];
            }
            $this->assertEqual($expected, $this->deliveredToPartnerIDs);
        }
    }
    //--------------------------------------------------------------------------


    protected function repostLastSubmission() {
        $incRow = $this->logIncoming->get($this->engine->getIncomingId());
        $engine = SetupTestData::debugEngineRepost();
        $this->deliveredToPartnerIDs = array();

        $engine->setListenerCallback(array($this, 'callbackDelivered'));
        $engine->processRepost($incRow, $this->campaign);
    }
    //--------------------------------------------------------------------------


    /**
     *
     * @param array $deliveryData array of strings like partnerNr-success-always, 11-y-n
     */
    protected function setupDelivery(array $deliveryData, $campaignId = false) {
        if ($campaignId === false) {
            $campaignId = $this->campaignId;
        }
        $data = array();
        $urls = array(
            'y' => 'http://w1.localhost/',
            'n' => 'http://w1.localhost/?fail',
            'u' => 'http://w1.localhost/?username',
            'f' => 'http://asdfasdf.localhost/'
        );
        foreach ($deliveryData as $s) {
            $arr = explode('-', $s);
            if (sizeof($arr) != 3) {
                throw new EServerError("Invalid test data '$s'");
            }
            $pname = 'C11-P' . sprintf('%02d', (int)$arr[0]);
            $pid = SetupTestData::$pids[$pname];
            if (!$pid) {
                throw new EServerError("Invalid test data '$s'");
            }
            $partner = $this->modelPartner->get($pid);
            $url = (strlen($arr[1]) == 1) ? $urls[$arr[1]] : $arr[1];
            $p2 = $partner;
            if ($arr[1] == 'u') {
                $p2['should_retry'] = '1';
                $p2['username_failed_keyword'] = 'username taken';
            }
            $p2['delivery_addr'] = $url;
            $this->modelPartner->updateDiff($pid, $p2, $partner);
            $data[] = array('partner_id' => (int)$pid, 'always_send' => ($arr[2] == 'y') ? 1 : 0);
        }
        $this->modelCampaign->insertDeliverySettings($campaignId, $data);
    }
    //--------------------------------------------------------------------------
}

SetupTestData::readData();

function timeDiff($timeStamp, $seconds = 0) {
    $now  = strtotime(DB::$db->getTopLeft("SELECT NOW()")) + $seconds;
    $time = strtotime($timeStamp);
    $diff = abs($time - $now);
    return $diff;
}

class EFWUT extends Exception {
    //
}

