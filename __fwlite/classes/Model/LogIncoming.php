<?php

class Model_LogIncoming extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_api_report_small;
    protected $t_api_report_inc;

    protected $t_campaigns;
    protected $t_catchup_jobs;
    protected $t_log_delivery;
    protected $t_log_incoming;
    protected $t_log_incoming_np_errors;
    protected $t_log_incoming_tracking;
    protected $t_log_incoming_values;
    protected $t_partners;
    protected $t_repost_queue;
    protected $t_users;

    protected $t_sh_catchup;
    protected $t_sh_delivery;
    protected $t_sh_incoming;
    protected $t_summary_acceptance;
    protected $t_sh_response_times;

    protected $cidCache = array();

    protected $now = false;
    
    public $messages = array();


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_incoming);

        $this->timestampForInsert = array('request_time');
        $this->zeroOneFields      = array('is_success');
    }
    //--------------------------------------------------------------------------


    public function insertSubmissionValues($logId, array $valuesAssoc) {
        if (!$valuesAssoc) {
            return;
        }
        $logId = (int)$logId;
        $sql = "INSERT INTO `$this->t_log_incoming_values` (`incoming_id`, `name`, `value`) VALUES\n\t";
        $sql .= implode(",\n\t", array_fill(0, sizeof($valuesAssoc), "($logId, ?, ?)"));

        $params = array();
        foreach ($valuesAssoc as $fieldName => $value) {
            $params[] = $fieldName;
            $params[] = $value;
        }
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function insertTrackingData($id) {
        $sess = isset($_SESSION) ? $_SESSION : array();
        $req_data =
        "\$_SERVER  = " . var_export($_SERVER, true) . ";\n\n" .
        "\$_GET     = " . var_export($_GET, true) . ";\n\n" .
        "\$_POST    = " . var_export($_POST, true) . ";\n\n" .
        "\$_COOKIE  = " . var_export($_COOKIE, true) . ";\n\n" .
        "\$_SESSION = " . var_export($sess, true) . ";\n\n" .
        "\$_ENV     = " . var_export($_ENV, true) . ";";
        $params = array((int)$id, $req_data);

        $this->db->query("INSERT INTO `$this->t_log_incoming_tracking` (`incoming_id`, `req_data`) VALUES (?, ?)", $params);
    }
    //--------------------------------------------------------------------------


    public function insertFailedSubmissionIntoReportingTable($data, $userId, $isError = true) {
        $report = array(
            (int)$data['id'],
            0,
            $data['request_time'],
            substr($data['request_time'], 0, -5) . '00:00',
            $data['request_time'],
            substr($data['request_time'], 0, -5) . '00:00',
            (int)$userId,
            (int)$data['campaign_id'],
            $isError ? 0 : 1,
            0,
            0
        );

        $this->db->query("INSERT INTO `$this->t_api_report_inc` (`id`, `delivery_id`, `incoming_id`) VALUES (NULL, 0, ?)", array((int)$data['id']));

        $aid = $this->db->lastInsertId();
        $did = ((Lib::TO_DAYS($data['request_time']) - Model_LogDelivery::DAY_ID_CUTOFF) * 24 + (int)substr($data['request_time'], 11, 2)) . sprintf('%013u', $aid);
        $sql = "INSERT INTO `$this->t_api_report_small` (
            `delivery_id`,
            `user_id`,
            `campaign_id`,
            `partner_id`,
            `incoming_status`,
            `delivery_status`,
            `is_repost`
        )
        VALUES ($did, ?, ?, 0, ?, 0, 0)";
        $params = array((int)$userId, (int)$data['campaign_id'], $isError ? 0 : 1);
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function insertFailedSubmissionNoPartners($incomingId) {
        $sql = "INSERT IGNORE INTO `$this->t_log_incoming_np_errors` (`id`) VALUES (?)";
        $this->db->query($sql, array((int)$incomingId));
    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    public function listFailedNoPartners(array $incomingIDs) {
        if (!$incomingIDs) {
            return false;
        }
        $sql = "SELECT `id` FROM `$this->t_log_incoming_np_errors` WHERE `id` IN (" . self::implodeInts($incomingIDs) . ') ORDER BY 1';
        $values = $this->db->getArray1v($sql);
        return $values;
    }
    //--------------------------------------------------------------------------


    public function increaseSnubCountInIncomingSummaryTable($campaignId, $time) {
        $sql = "INSERT INTO `$this->t_sh_incoming` (`campaign_id`, `inc_hour`, `snub_count`) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE `snub_count` = `snub_count` + 1";
        $this->db->query($sql, array((int)$campaignId, substr($time, 0, -6) . ':00:00'));
    }
    //--------------------------------------------------------------------------


    public function setSystemResponse($logId, $systemResponse, $redirectTo = null) {
        $sql = "UPDATE `$this->tableName` SET `system_response` = ?, `redirected_to` = ? WHERE `id` = ?";
        $this->db->query($sql, array($systemResponse, $redirectTo, (int)$logId));
    }
    //--------------------------------------------------------------------------


    public function setIsSuccessStatus($logId, $isSuccess, $rejectReason) {
        $sql = "UPDATE `$this->tableName` SET `is_success` = ?, `reject_reason` = ? WHERE `id` = ?";
        $this->db->query($sql, array($isSuccess, $rejectReason, (int)$logId));
    }
    //--------------------------------------------------------------------------
    
    
    public function listSubmissionValues($incomingId) {
        return $this->db->getArrayAssoc("SELECT `name`, `value` FROM `$this->t_log_incoming_values` WHERE `incoming_id` = ?", array((int)$incomingId));
    }
    //--------------------------------------------------------------------------


    public function listSubmissionValuesGroup(array $incomingIds) {
        if (!$incomingIds) {
            return array();
        }
        return $this->db->getArrayAssoc("SELECT `incoming_id`, `name`, `value` FROM `$this->t_log_incoming_values` WHERE `incoming_id` IN (" . self::implodeInts($incomingIds) . ')');
    }
    //--------------------------------------------------------------------------


    public function listHourlyReportLast24Hours($campaignIds = false, $tz = false, $sourceCampaignIds = false) {
        if (!$tz) {
            $tz = Config::$timeZone;
        }
        
        $baseTz = Config::$timeZone;
        $now = $this->db->getTopLeft("SELECT NOW()");

        $start  = $this->db->getTopLeft("SELECT DATE_SUB('$now', INTERVAL 1 DAY)");
        $start  = substr_replace($start, '00:00', -5);
        $result = $this->initResult($campaignIds, $now, $baseTz, $tz);
        
        if (!empty($campaignIds)) {
            $this->readIncomingStatsForHourlyReportLast24Hours($result, $sourceCampaignIds, $tz, $baseTz, $start);
            $this->readDeliveryStatsForHourlyReportLast24Hours($result, $campaignIds, $tz, $baseTz, $start);
        }

        return $result;
    }
    //--------------------------------------------------------------------------


    private function initResult($campaignIds, $now, $baseTz, $tz) {
        $nowTZ = $this->db->getTopLeft("SELECT CONVERT_TZ('$now', '$baseTz', '$tz')");

        $hour       = substr($nowTZ, 11, 2);
        $hourNumber = $hour;
        if ($hourNumber[0] == '0') {
            $hourNumber = $hourNumber[1];
        }

        $hours = array();
        for($i = 1; $i <= 24; $i++) {
            $hour = sprintf(' %02d', ($hourNumber + $i) % 24);
            $hours[$hour] = 0;
        }

        $result = array();
        foreach ($campaignIds as $cid) {
            $result[$cid] = array(
                'inc-0' => $hours,
                'inc-1' => $hours,
                'out-1' => $hours
            );
        }

        return $result;
    }
    //--------------------------------------------------------------------------


    private function readIncomingStatsForHourlyReportLast24Hours(array & $result, $sourceCampaignIds, $tz, $baseTz, $start) {
        if (!empty($sourceCampaignIds)) {
            $scids = ' `campaign_id` IN ( ' . join(',', $sourceCampaignIds) . ' ) AND ';
        } else {
            $scids = '';
        }

        if ($tz == $baseTz) {
            $incHourField = '`inc_hour` AS `ut`';
        } else {
            $incHourField = "CONVERT_TZ(`inc_hour`, '$baseTz', '$tz') AS `ut`";
        }

        $data = $this->db->getArray("
            SELECT `campaign_id`, $incHourField, `accept_count` AS `acc`, `reject_count` AS `rej` FROM $this->t_sh_incoming
            WHERE $scids `inc_hour` > '$start'
            ORDER BY 1");
        
        foreach ($data as $arr) {
            $scid = $arr['campaign_id'];
            
            if (isset($result[$scid])) {
                if (isset($result[$scid]["inc-0"][substr($arr['ut'], 10, 3)])) {
                    $result[$scid]["inc-0"][substr($arr['ut'], 10, 3)] += (int)$arr['rej'];
                } else {
                    $result[$scid]["inc-0"][substr($arr['ut'], 10, 3)] = (int)$arr['rej'];
                }
                if (isset($result[$scid]["inc-1"][substr($arr['ut'], 10, 3)])) {
                    $result[$scid]["inc-1"][substr($arr['ut'], 10, 3)] += (int)$arr['acc'];
                } else {
                    $result[$scid]["inc-1"][substr($arr['ut'], 10, 3)] = (int)$arr['acc'];
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    private function readDeliveryStatsForHourlyReportLast24Hours(array & $result, $campaignIds, $tz, $baseTz, $start) {
        if ($tz == $baseTz) {
            $delHourField = '`del_hour`';
        } else {
            $delHourField = "CONVERT_TZ(`del_hour`, '$baseTz', '$tz')";
        }

        if ($campaignIds) {
            $cids = ' `campaign_id` IN ( ' . join(',', $campaignIds) . ' ) AND ';
        } else {
            $cids = '';
        }

        $data = $this->db->getArray("
            SELECT `campaign_id`, $delHourField AS `ut`, `accept_count` AS `cnt` FROM `$this->t_sh_delivery`
            WHERE $cids `del_hour` > '$start'
            ORDER BY 1");

        foreach ($data as $arr) {
            $cid = $arr['campaign_id'];
            
            if (isset($result[$cid])) {
                if (isset($result[$cid]["out-1"][substr($arr['ut'], 10, 3)])) {
                    $result[$cid]["out-1"][substr($arr['ut'], 10, 3)] += (int)$arr['cnt'];
                } else {
                    $result[$cid]["out-1"][substr($arr['ut'], 10, 3)] = (int)$arr['cnt'];
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    public function dashboardStats($userId, array $campaignIDs, $tz, array $campaignsAssoc) {
        return $this->getDashboardStatsFromSummaryTables($userId, $campaignIDs, $tz, $campaignsAssoc);
    }
    //--------------------------------------------------------------------------


    public function listMissingRepostRecords() {
        $sql = "SELECT a.id, a.campaign_id, a.user_id FROM `log_incoming` AS a, `campaigns` AS b
                WHERE a.is_success='1'
                AND a.campaign_id = b.id
                AND b.delivery_type = '2'
                AND a.id NOT IN (SELECT incoming_id FROM `log_delivery`)
                AND a.id NOT IN (SELECT incoming_id FROM `repost_queue`)
                AND a.request_time > DATE_SUB(NOW(), INTERVAL 3 HOUR)
                AND a.request_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------

    public function deleteOldDataFromSummaryAcceptance() {
        $start = substr($this->db->getTopLeft("SELECT DATE_SUB(NOW(), INTERVAL 1 DAY)"), 0, 13) . ':00:00';

        do {
            $this->db->query("DELETE FROM `$this->t_summary_acceptance` WHERE `time_stamp` < '$start' LIMIT 5000");
            usleep(100000);
        } while ($this->db->affectedRows() > 0);
    }
    //--------------------------------------------------------------------------


    public function logSubmissionToSummaryTable($campaignId, $hour, $accepted) {
        $field = $accepted ? 'accept_count' : 'reject_count';

        $sql   = "INSERT INTO `$this->t_sh_incoming` (`campaign_id`, `inc_hour`, `$field`) VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE `$field` = `$field` + 1";
        $this->db->query($sql, array((int)$campaignId, substr($hour, 0, 13) . ':00:00'));
    }
    //--------------------------------------------------------------------------


    public function campaignIncomingCount($campaignId, $intervalType, $now = false) {
        if (is_numeric($intervalType)) {
            $intervalType = isset(Model_Partner::$deliveryTypes[$intervalType]) ? Model_Partner::$deliveryTypes[$intervalType] : 0;
        }
        $upToTimestamp = ($now === false) ? 'NOW()' : "'$now'";
        
        $countTimeStart = Model_Partner::getTimeFrameStart($intervalType, $upToTimestamp);

        if (!empty($countTimeStart)) {
            $condition = " AND `inc_hour` >= " . $countTimeStart;
        } else {
            $condition = "";
        }

        $sql = "SELECT SUM(`accept_count`) - SUM(`snub_count`) FROM `$this->t_sh_incoming` WHERE `campaign_id` = ? $condition";
        $count = $this->db->getTopLeftInt($sql, array((int)$campaignId));
        return $count;
    }
    //--------------------------------------------------------------------------


    public function listRecent($campaignId, $email, $isSuccess, $timeoutSeconds) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE `campaign_id` = ? AND `is_success` = ? AND `email` = ? AND
        `request_time` > DATE_SUB(NOW(), INTERVAL ? SECOND) ORDER BY `request_time`";

        return $this->db->getArray1v($sql, array((int)$campaignId, $isSuccess ? '1' : '0', $email, (int)$timeoutSeconds));
    }
    //--------------------------------------------------------------------------


    public function getTotalSuccessfulCount($userId) {
        $result = $this->getSuccessfulCount($userId);
        $subaccountModel = SingletonRegistry::getSingleInstance('Model_SubAccount');
        $allSubAccounts = $subaccountModel->listAllSubAccounts($userId);
        
        if (!empty($allSubAccounts)) {
            foreach ($allSubAccounts as $row) {
                $result += $this->getSuccessfulCount($row['sub_account_id']);
            }
        }

        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    public function getSuccessfulCount($userId) {
        $sql = "SELECT COUNT(*) FROM `$this->tableName` AS `li`, `$this->t_campaigns` AS `c` WHERE
        `li`.`campaign_id`  = `c`.`id` AND
        `li`.`is_success`   = '1'      AND
        `li`.`request_time` >= ?       AND
        `c`.`user_id`       = ?        AND
        `c`.`is_master`     = '0'      AND
        NOT EXISTS (
            SELECT * FROM `$this->t_log_incoming_values` `liv` 
                WHERE `liv`.`incoming_id`   = `li`.`id` 
                    AND `liv`.`name`        = ?
                    AND `liv`.`value`      != ''
        );";

        return $this->db->getTopLeftInt($sql, array(date('Y-m-01'), (int)$userId, strval(Model_CampaignField::FIELD_TYPE_LEAD_ID)));
    }
    //--------------------------------------------------------------------------


    public function getOwnerOf($incomingId) {
        $sql = "SELECT `c`.`user_id` FROM `$this->t_campaigns` AS `c`, `$this->tableName` AS `li` WHERE
        `li`.`id` = ? AND `c`.`id` = `li`.`campaign_id`";
        return $this->db->getTopLeftInt($sql, array((int)$incomingId));
    }
    //--------------------------------------------------------------------------


    public function insertIntoRepostQueue($incomingId, $delay, $repostCount, $campaignId, $userId, $firstRepostImmediately = false, $firstRepostDelay = 0, $initialFileName = null) {
        if ($firstRepostImmediately) {
            $repostAt = $this->db->getTopLeft("SELECT DATE_ADD(`request_time`, INTERVAL ? HOUR)
                FROM `$this->t_log_incoming` WHERE `id` = ?", array((int)$firstRepostDelay, (int)$incomingId));
        } else {
            $repostAt = $this->db->getTopLeft("SELECT DATE_ADD(`request_time`, INTERVAL ? HOUR)
                FROM `$this->t_log_incoming` WHERE `id` = ?", array((int)$delay, (int)$incomingId));
        }

        if (!isset($repostAt)) {
            if ($firstRepostImmediately) {
                $repostAt = date('Y-m-d H:i:s', strtotime("+" . $firstRepostDelay . " hours"));
            } else {
                $repostAt = date('Y-m-d H:i:s', strtotime("+" . $delay . " hours"));
            }
        }

        $sql = "INSERT INTO `$this->t_repost_queue` (`incoming_id`, `user_id`, `campaign_id`, `remaining_count`, `interval_hours`, `repost_at`, `upload_file`)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->query($sql, array((int)$incomingId, (int)$userId, (int)$campaignId, (int)$repostCount, (int)$delay, $repostAt, $initialFileName));
    }
    //--------------------------------------------------------------------------


    public function countOfRepostQueuePerUser($userId) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_repost_queue` WHERE `user_id` = ?", array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function listRepostQueuesForIncomingId($incomingId) {
        return $this->db->getArray("SELECT * FROM `$this->t_repost_queue` WHERE `incoming_id` = ? ORDER BY `repost_at`", array((int)$incomingId));
    }
    //--------------------------------------------------------------------------


    public function listRepostQueuesForUserId($userId) {
        return $this->db->getArray("SELECT * FROM `$this->t_repost_queue` WHERE `user_id` = ? ORDER BY `repost_at`", array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function countRepostQueues($incomingId, $campaignId = 0) {
        $sql    = "SELECT COUNT(*) FROM `$this->t_repost_queue` WHERE ";
        $params = array();

        if ($campaignId) {
            $sql .= "`campaign_id` = ? AND ";
            $params[] = (int)$campaignId;
        }

        $sql .= "`incoming_id` = ?";
        $params[] = (int)$incomingId;

        return $this->db->getTopLeftInt($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function getRepostQueueEntry($incomingId, $campaignId, $catchupId = false) {
        $sql = "SELECT * FROM `$this->t_repost_queue` WHERE ";
        if ($campaignId) {
            $sql .= "`campaign_id` = " . (int)$campaignId;
        }

        if ($catchupId) {
            $sql .= " AND `catchup_id` = " . (int)$catchupId;
        } else {
            $sql .= " AND `catchup_id` IS NULL";
        }
        $sql .= " AND `incoming_id` = ?";
        return $this->db->getTopArray($sql, array((int)$incomingId), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function deleteUsersRepostsFromQueue($userId) {
        return $this->db->query("DELETE FROM `$this->t_repost_queue` WHERE `user_id` = ?", array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function deleteSubmissionFromRepostQueue($incomingId, $campaignId = false) {
        $sql    = "DELETE FROM `$this->t_repost_queue` WHERE ";
        $params = array();

        if ($campaignId) {
            $sql .= "`campaign_id` = ? AND ";
            $params[] = (int)$campaignId;
        }

        $sql .= "`catchup_id` IS NULL AND `incoming_id` = ?";
        $params[] = (int)$incomingId;
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function deleteIncomingIdFromRepostQueue($incomingId) {
        $this->db->query("DELETE FROM `$this->t_repost_queue` WHERE `incoming_id` = ?", array((int)$incomingId));
    }
    //--------------------------------------------------------------------------


    public function createCatchUpJob(& $affectedRows, $campaignId, $userId, $partnerId, $days, $hours, $limit, $shadowOfId = false, $rlType = 0, $rlValue = 0, $ownerId = 1) {
        $sql = "INSERT INTO `$this->t_catchup_jobs` (`campaign_id`, `partner_id`, `remaining`, `rl_type`, `rl_value`, `user_id`) VALUES (?, ?, ?, ?, ?, ?)";
        $params =  array((int)$campaignId, (int)$partnerId, $limit ? (int)$limit : 4000000000, (int)$rlType, (int)$rlValue, (int)$ownerId);

        if (!$this->db->query($sql, $params)) {
            throw new EExplainableError("Unable to process your request");
        }
        $jobId  = $this->db->lastInsertId();

        if ($shadowOfId === false) {
            $shadowOfId = $campaignId;
        }
        $cutOff = $this->db->getTopLeft("SELECT DATE_SUB(DATE_SUB(NOW(), INTERVAL ? DAY), INTERVAL ? HOUR)", array((int)$days, (int)$hours));
        $params = array((int)$userId, (int)$campaignId, (int)$jobId, (int)$shadowOfId, $cutOff);

        $sql = "
        INSERT INTO `$this->t_repost_queue`
        (`incoming_id`, `user_id`, `campaign_id`, `remaining_count`, `interval_hours`, `catchup_id`, `repost_at`)
        SELECT `id`, ?, ?, 0, 0, ?, `request_time` FROM `$this->tableName` WHERE `campaign_id` = ? AND `is_success` = '1' AND `request_time` > ? ORDER BY `id`";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $this->db->query($sql, $params);
        $affectedRows = $this->db->affectedRows();

        if ($affectedRows) {
            $sql = "UPDATE `$this->t_catchup_jobs` SET `start_count` = ? WHERE `id` = ?";
            $this->db->query($sql, array((int)$affectedRows, (int)$jobId));
        }

        return $jobId;
    }
    //--------------------------------------------------------------------------


    public function procesedOneCatchupJobEntry($jobId, $accepted) {
        $foundJobId = $this->db->getTopLeftInt("SELECT `catchup_id` FROM `$this->t_repost_queue` WHERE `catchup_id` = ? LIMIT 1", array((int)$jobId));

        if (!$foundJobId) {
            // No entries in repost_queue left? Then delete the job
            $this->db->query("DELETE FROM `$this->t_catchup_jobs` WHERE `id` = ?", array((int)$jobId));
        } else {
            if ($accepted) {
                $sql = "INSERT INTO `$this->t_sh_catchup` (`catchup_id`, `repost_hour`, `catchup_count`) VALUES (?, FROM_UNIXTIME(UNIX_TIMESTAMP() - UNIX_TIMESTAMP() MOD 60), 1) ON DUPLICATE KEY UPDATE
                `catchup_count` = `catchup_count` + 1";
                $this->db->query($sql, array((int)$jobId));
            }
            $this->db->query("UPDATE `$this->t_catchup_jobs` SET `remaining` = `remaining` - 1 WHERE `id` = ?", array((int)$jobId));
            $this->db->query("DELETE FROM `$this->t_catchup_jobs` WHERE `remaining` = 0 AND `id` = ?", array((int)$jobId));
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Only needed for unit tests
     */
    public function deleteCatchupLogForJobId($catchupId) {
        $this->db->query("DELETE FROM `$this->t_sh_catchup` WHERE `catchup_id` = ?", array((int)$catchupId));
    }
    //--------------------------------------------------------------------------


    public function deleteCatchupJob($jobId) {
        return $this->db->query("DELETE FROM `$this->t_catchup_jobs` WHERE `id` = ?", array((int)$jobId));
    }
    //--------------------------------------------------------------------------
    
    public function cancelUploadedFile($userId, $filename, $campaignId) {
        return $this->db->query("DELETE FROM `$this->t_repost_queue` WHERE `user_id` = ? AND `upload_file` = ? AND campaign_id = ?", array($userId, $filename, $campaignId));
    }
    //--------------------------------------------------------------------------


    public function catchUpJobExists($jobId) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_catchup_jobs` WHERE `id` = ?", array((int)$jobId)) != 0;
    }
    //--------------------------------------------------------------------------


    public function countOfCatchUpJobs() {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_catchup_jobs`");
    }
    //--------------------------------------------------------------------------


    public function countOfCatchUpJobsForUser($userId) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_catchup_jobs` WHERE `user_id` = ?", array((int)$userId));
    }
    //--------------------------------------------------------------------------


    public function getCatchUpJob($id) {
        return $this->db->getTopArray("SELECT * FROM `$this->t_catchup_jobs` WHERE `id` = ?", array((int)$id));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Lists catchup jobs related to campaigns that belong to $userId
     */
    public function listCatchupJobsForUser($userId) {
        $sql = "SELECT `cj`.* FROM `$this->t_catchup_jobs` AS `cj`, `$this->t_campaigns` AS `c` WHERE
        `c`.`user_id` = ? AND `cj`.`campaign_id` = `c`.`id` ORDER BY `cj`.`campaign_id`, `cj`.`partner_id`";
        $arr = $this->db->getArray($sql, array((int)$userId));

        return $arr;
    }
    //--------------------------------------------------------------------------


    public function listCatchupJobs() {
        $arr = $this->db->getArrayIndexed("SELECT `cj`.*, `c`.`name` AS `campaign_name`, `p`.`name` AS `partner_name`, 0 AS `in_queue`
            FROM `$this->t_catchup_jobs` AS `cj`, `$this->t_campaigns` AS `c`, `$this->t_partners` AS `p`
            WHERE `cj`.`campaign_id` = `c`.`id` AND `cj`.`partner_id` = `p`.`id`
            ORDER BY `campaign_name`", array(), 'id', MYSQL_ASSOC);
        if (!$arr) {
            return array();
        }

        $counts = $this->db->getArrayAssoc("SELECT `catchup_id`, COUNT(*) FROM `$this->t_repost_queue` GROUP BY `catchup_id` ORDER BY 1");
        foreach ($counts as $jobId => $remainingCount) {

            // $jobId can be NULL
            if ($jobId) {
                $arr[$jobId]['in_queue'] = $remainingCount;
            }
        }

        foreach (array_keys($arr) as $jobId) {
            if ($arr[$jobId]['in_queue'] == 0) {
                $this->deleteCatchupJob($jobId);
                unset($arr[$jobId]);
            }
        }

        return $arr;
    }
    //--------------------------------------------------------------------------
    
    
    public function getUploadedFiles() {
        $sql = "SELECT 
                    `$this->t_repost_queue`.user_id,
                    `$this->t_repost_queue`.upload_file,
                    `$this->t_repost_queue`.campaign_id,
                    `$this->t_campaigns`.name as campaign_name,
                    count(*) as count
                FROM
                    `$this->t_repost_queue`
                INNER JOIN
                    `$this->t_campaigns`
                ON 
                    `$this->t_repost_queue`.`campaign_id` = `$this->t_campaigns`.id
                WHERE 
                    upload_file is not null
                GROUP BY
                    user_id, upload_file
                ";
        return $this->db->getArray($sql);        
    }
    //--------------------------------------------------------------------------


    public function deleteDeadCatchupJobs() {
        $jobIds = $this->db->getArray1v("SELECT `id` FROM `$this->t_catchup_jobs` ORDER BY 1");
        if (!$jobIds) {
            return;
        }

        $counts = $this->db->getArrayAssoc("SELECT `catchup_id`, COUNT(*) AS `cnt` FROM `$this->t_repost_queue` GROUP BY `catchup_id`
                        HAVING `catchup_id` IN (" . implode(', ', $jobIds) . ')');
        foreach ($jobIds as $jobId) {
            if (empty($counts[$jobId])) {
                $this->deleteCatchupJob($jobId);
            }
        }
    }
    //--------------------------------------------------------------------------


    public function listCatchupJobRateLimits() {
        $sql = "SELECT `id`, `rl_type`, `rl_value` FROM `$this->t_catchup_jobs` ORDER BY 1";
        return $this->db->getArrayIndexed($sql, array(), 'id', MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function listTopUsersByIncoming($userCount = 5, $periodDays = 0, $onlySuccess = false) {
        $field = $onlySuccess ? 'accept_count' : 'reject_count';
        if ($periodDays) {
            $s = ' WHERE `inc_hour` >= DATE_SUB(NOW(), INTERVAL ' . (int)$periodDays . ' DAY)';
        } else {
            $s = '';
        }

        $subQuery = "SELECT `campaign_id`, SUM(`$field`) AS `cnt` FROM `$this->t_sh_incoming` $s GROUP BY `campaign_id`";

        $sql = "SELECT `user_id`, SUM(`cnt`) AS `cnt` FROM ($subQuery) as `t1`, `$this->t_campaigns` AS `c`
        WHERE `c`.`id` = `t1`.`campaign_id`
        GROUP BY `user_id`
        ORDER BY 2 DESC
        LIMIT " . (int)$userCount;

        return $this->db->getArray1v($sql);
    }
    //--------------------------------------------------------------------------


    public function listTopUsersByDeliveries($userCount = 5, $periodDays = 0, $onlyAccepted = false) {
        $field = $onlyAccepted ? 'accept_count' : 'reject_count';

        $s = $onlyAccepted ? ' WHERE `del_hour` >= DATE_SUB(NOW(), INTERVAL ' . (int)$periodDays . ' DAY)' : '';

        $subQuery = "SELECT `campaign_id`, SUM(`$field`) AS `cnt` FROM `$this->t_sh_delivery` $s GROUP BY `campaign_id`";

        $sql = "SELECT `user_id`, SUM(`cnt`) AS `cnt` FROM ($subQuery) as `t1`, `$this->t_campaigns` AS `c`
        WHERE `c`.`id` = `t1`.`campaign_id`
        GROUP BY `user_id`
        ORDER BY 2 DESC
        LIMIT " . (int)$userCount;

        return $this->db->getArray1v($sql);
    }
    //--------------------------------------------------------------------------


    public function listUsersWitnNoSubmissionsFor($days) {
        $sql = "SELECT `user_id`, MAX(`request_time`) AS `last_activity` FROM `$this->tableName` GROUP BY `user_id`
        HAVING `last_activity` < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->getArray1v($sql, array((int)$days));
    }
    //--------------------------------------------------------------------------


    public function deleteDataOlderThan($days = 365, $chunk = 100) {
        $cutOff =  $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$days));
        $sql    = "SELECT `id`, `request_time` FROM `$this->tableName` ORDER BY `id` LIMIT ?, 1";
        $row    = $this->db->getTopArray($sql, array((int)$chunk));

        if (isset($row['request_time']) && ($row['request_time'] < $cutOff)) {
            $this->db->query("DELETE FROM `$this->tableName` ORDER BY `id` LIMIT ?", array((int)$chunk));
        } else {
            $row = $this->db->getTopArray("SELECT `id`, `request_time` FROM `$this->tableName` ORDER BY `id` LIMIT 1");
            if (!(isset($row['request_time']) && ($row['request_time'] >= $cutOff))) {
                $this->db->query("DELETE FROM `$this->tableName` WHERE `request_time` < ?", array($cutOff));
            }
            return true;
        }

        return false;
    }
    //--------------------------------------------------------------------------


    public function getMinIncomingDate() {
        $sql = "SELECT MIN(`request_time`) FROM `$this->tableName`";
        return substr($this->db->getTopLeft($sql), 0, 10);
    }
    //--------------------------------------------------------------------------


    public function getCountInLastHours($campaignId, $hours) {
        $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE `campaign_id` = ? AND `request_time` > DATE_SUB(NOW(), INTERVAL ? HOUR)";
        return $this->db->getTopLeftInt($sql, array((int)$campaignId, (int)$hours));
    }
    //--------------------------------------------------------------------------


    public function getMinId() {
        return $this->db->getTopLeftInt("SELECT MIN(`id`) FROM `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function getMinIdAfterTs($timeStamp) {
        $sql = "SELECT `user_id`, `id`, MIN(`request_time`) FROM `$this->tableName` WHERE `request_time` >= ? GROUP BY `user_id` ORDER BY 3 LIMIT 1";
        $arr = $this->db->getTopArray($sql, array($timeStamp), MYSQL_NUM);
        if ($arr) {
            return $arr[1];
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Gets report data using api_report_small table
     */
    public function listSRReportFor($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $fieldSearch, $incomingId, $hideRepost, $userId = 0, $page = 0, $perPage = 0, $countFieldSearch = null) {
        if ($incomingId) {
            $subQuery = $this->sqlForListSRReportForIncomingId($tz, $incomingId, $hideRepost, $page, $perPage, $userId);
        } else {
            $subQuery = $this->sqlForListSRReport($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage);
            if ($subQuery === false) {
                return array();
            }
        }
        $fieldSearchJoinTable = '';
        $fieldSearchWhere = '';
        $fieldSearchGroupBy = '';
        if (!empty($fieldSearch)){
            $fieldSearchJoinTable = ' INNER JOIN `log_incoming_values` as `liv` ON `liv`.`incoming_id` = `i`.`id`';
            $fieldSearchWhere = ' AND ( (`i`.`email` LIKE "%' . $fieldSearch . '%" ) OR (`liv`.`value` LIKE "%' . $fieldSearch . '%"))';
            $fieldSearchGroupBy = ' GROUP BY `i`.`id`';
        }
        
        $sql = "SELECT `t`.*,
        `d`.`revenue`, `d`.`data`, `d`.`is_accepted`, `d`.`reject_reason`, `d`.`is_repost`,
        `d`.`partner_response`, `d`.`id` AS `log_del_id`, `d`.`message`,
        `i`.`email`, `i`.`remote_ip`, `i`.`http_referer`, `i`.`is_success`, `i`.`reject_reason` as incoming_reject_reason,
        `i`.`full_name`, `i`.`full_address`, `i`.`phone`, `i`.`campaign`  
        FROM ($subQuery) AS `t`
        LEFT JOIN
        `log_delivery` AS `d`
        ON
        `d`.`id` = `t`.`delivery_id`,
        `log_incoming` AS `i`
        $fieldSearchJoinTable
        WHERE
        `t`.`incoming_id` = `i`.`id`
        $fieldSearchWhere $fieldSearchGroupBy";
        if (isset($countFieldSearch)) {
            $countQuery = "SELECT COUNT(*) FROM ($sql) as temp";
            return $this->db->getTopLeftInt($countQuery);
        }
        $arr = $this->db->getArray($sql);
        
        foreach ($arr as & $row) {
            $row['inc_hour'] = substr($row['incoming_time'], 0, 13) . ':00:00';
            $row['del_hour'] = substr($row['delivery_time'], 0, 13) . ':00:00';
        }
        unset($row);
        return $arr;
    }
    //--------------------------------------------------------------------------

    /**
     * @desc Gets report data using api_report_small table
     */
    public function listSRReportForOldData($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $fieldSearch, $incomingId, $hideRepost, $userId = 0, $page = 0, $perPage = 0, $countFieldSearch = null) {
        
        $where = $params = array();
        if ($incomingId) {
            $where[]  = 'i.id = ?';
            $params[] = $incomingId;
        } else {
            $this->getWhereForSRReportOldData($where, $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage);
        }
        
        if ($tz == Config::$timeZone) {
            $tzConvertStart = $tzConvertEnd = '';
        } else {
            $tzConvertStart = 'CONVERT_TZ(';
            $tzConvertEnd   = ", '" . Config::$timeZone . "', '$tz')";
        }

        $fieldSearchJoinTable = '';
        $fieldSearchWhere = '';
        $fieldSearchGroupBy = '';
        if (!empty($fieldSearch)){
            $fieldSearchJoinTable = ' INNER JOIN `log_incoming_values` as `liv` ON `liv`.`incoming_id` = `i`.`id` ';
            $fieldSearchWhere = ' AND ( (`i`.`email` LIKE "%' . $fieldSearch . '%" ) OR (`liv`.`value` LIKE "%' . $fieldSearch . '%"))';
            $fieldSearchGroupBy = ' GROUP BY `i`.`id`';
        }
        
        $sql = "SELECT 
            `c`.`user_id`,
            `i`.`campaign_id`,
            `d`.`partner_id`,
            `i`.`is_success` as incoming_status,
            `d`.is_accepted as `delivery_status`,
            `i`.`id` as `incoming_id`,
            `d`.`id` as `delivery_id`,
            {$tzConvertStart}`i`.`request_time`{$tzConvertEnd} AS `incoming_time`,
            {$tzConvertStart}IFNULL(`d`.`created_at`, `i`.`request_time`){$tzConvertEnd} AS `delivery_time`,
            `d`.`revenue`, `d`.`data`, `d`.`is_accepted`, `d`.`reject_reason`, `d`.`is_repost`,
            `d`.`partner_response`, `d`.`id` AS `log_del_id`, `d`.`message`,
            `i`.`email`, `i`.`remote_ip`, `i`.`http_referer`, `i`.`is_success`, `i`.`reject_reason` as incoming_reject_reason,
            `i`.`full_name`, `i`.`full_address`, `i`.`phone`, `i`.`campaign`
        FROM `log_incoming` AS `i`
        $fieldSearchJoinTable
        INNER JOIN campaigns c ON i.campaign_id = c.id
        LEFT JOIN `log_delivery` AS `d` ON `d`.`incoming_id` = `i`.`id` 
        WHERE
            " . implode(" AND ", $where) . $fieldSearchWhere . $fieldSearchGroupBy;
            
        if (!$incomingId) {
            $sql .= " HAVING delivery_time BETWEEN ? AND ?";
            $params[] = $from;
            $params[] = $to;
        }
        
        $sql .= " ORDER BY i.id DESC";
        if ($page) {
            $sql .= " LIMIT " . (($page - 1) * $perPage) . ", $perPage";
        }

        if (isset($countFieldSearch)) {
            $countQuery = "SELECT COUNT(incoming_id) FROM ($sql) as temp";
            return $this->db->getTopLeftInt($countQuery, $params);
        }
        
        $arr = $this->db->getArray($sql, $params);
        foreach ($arr as & $row) {
            $row['inc_hour'] = substr($row['incoming_time'], 0, 13) . ':00:00';
            $row['del_hour'] = substr($row['delivery_time'], 0, 13) . ':00:00';
        }
        unset($row);
        return $arr;
    }
    //--------------------------------------------------------------------------
    /**
     * @desc Gets report data using api_report_small table
     */
    public function countSRReportForOldData($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $incomingId, $hideRepost, $userId = 0) {
        
        $where = $params = array();
        if ($incomingId) {
            $where[]  = 'i.id = ?';
            $params[] = $incomingId;
        } else {
            $this->getWhereForSRReportOldData($where, $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId);
        }
        
        if ($tz == Config::$timeZone) {
            $tzConvertStart = $tzConvertEnd = '';
        } else {
            $tzConvertStart = 'CONVERT_TZ(';
            $tzConvertEnd   = ", '" . Config::$timeZone . "', '$tz')";
        }

        $sql = 
        "SELECT 
            count(*) as count
        FROM `log_incoming` AS `i`
        INNER JOIN campaigns c ON i.campaign_id = c.id
        LEFT JOIN `log_delivery` AS `d` ON `d`.`incoming_id` = `i`.`id`";
            
        if (!$incomingId) {
            $where[] = "{$tzConvertStart}IFNULL(`d`.`created_at`, `i`.`request_time`){$tzConvertEnd} BETWEEN ? AND ?";
            $params[] = $from;
            $params[] = $to;
        }
        
        $sql .= " WHERE " . implode(" AND ", $where);
        
        return $this->db->getTopLeft($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    
     private function getWhereForSRReportOldData(array & $where, array & $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page = null, $perPage = null) {
        if (($partnerSuccess == 1) && ($campaignSuccess == 0)) {
            $campaignSuccess = 2;
        }

        if ($partnerId || $campaignId) {
            $userId = 0;
        }

        if ($userId) {
            $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds(Auth::getInstance()->getUserId());
            $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds(Auth::getInstance()->getUserId());

            $condition = '(';
            $condition .= '`c`.`user_id` = ?';
            if (!empty($subAccountCampaignIds)) {
                $condition .= ' OR `c`.`campaign_id` IN ('. implode(",", $subAccountCampaignIds) .')';
            }
            if (!empty($subAccountPartnerIds)) {
                $condition .= ' OR `d`.`partner_id` IN ('. implode(",", $subAccountPartnerIds) .')';
            }
            $condition .= ')';
            $where[] = $condition;
            
            $params[] = $userId;
        }
 

        if ($campaignId) {
            $where[]  = '`c`.`id` = ?';
            $params[] = $campaignId;
        }
        if ($partnerId) {
            $where[]  = '`d`.`partner_id` = ?';
            $params[] = $partnerId;
        }
        if ($campaignSuccess) {
            $where[]  = '`i`.`is_success` = ?';
            $params[] = strval($campaignSuccess - 1);
        }
        if ($partnerSuccess) {
            if ($partnerSuccess == '1') {
                $where[]  = '`d`.`reject_reason` != ?';
                $params[] = 'skipped';
                $where[] = '`d`.`is_accepted` = ?';
                $params[] = '0';
            } elseif ($partnerSuccess == '3') {
                $where[]  = '`d`.`reject_reason` = ?';
                $params[] = 'skipped';
                $where[] = '`d`.`is_accepted` = ?';
                $params[] = '0';
            } else {
                $where[]  = '`d`.`is_accepted` = ?';
                $params[] = '1';
            }   
        }

        if ($hideRepost) {
            $where[]  = '`d`.`is_repost` = ?';
            $params[] = 0;
        }
     }


    /**
     * @desc Gets report data using api_report_small table
     */
    public function listSRReportQueryFor($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $fieldSearch, $incomingId, $hideRepost, $userId = 0, $page = 0, $perPage = 0) {
        if ($incomingId) {
            $sql = $this->sqlForListSRReportForIncomingId($tz, $incomingId, $hideRepost, $page, $perPage, $userId);
        } else {
            $sql = $this->sqlForListSRReport($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage);
            if ($sql === false) {
                return array();
            }
        }

        $fieldSearchJoinTable = '';
        $fieldSearchWhere = '';
        $fieldSearchGroupBy = '';
        if (!empty($fieldSearch)){
            $fieldSearchJoinTable = ' INNER JOIN `log_incoming_values` as `liv` ON `liv`.`incoming_id` = `i`.`id`';
            $fieldSearchWhere = ' AND ( (`i`.`email` LIKE "%' . $fieldSearch . '%" ) OR (`liv`.`value` LIKE "%' . $fieldSearch . '%"))';
            $fieldSearchGroupBy = ' GROUP BY `i`.`id`';
        }
        
        $sql = "SELECT `t`.*,
        `d`.`revenue`, `d`.`data`, `d`.`is_accepted`, `d`.`reject_reason`, `d`.`is_repost`,
        `d`.`partner_response`, `d`.`id` AS `log_del_id`, `d`.`message`,
        `i`.`email`, `i`.`remote_ip`, `i`.`http_referer`, `i`.`is_success`,
        `i`.`full_name`, `i`.`full_address`, `i`.`phone`, `i`.`campaign`
        FROM ($sql) AS `t`
        LEFT JOIN
        `log_delivery` AS `d`
        ON
        `d`.`id` = `t`.`delivery_id`,
        `log_incoming` AS `i`
        $fieldSearchJoinTable
        WHERE
        `t`.`incoming_id` = `i`.`id`
        $fieldSearchWhere $fieldSearchGroupBy";
        
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------

    /**
     * @desc Gets report data using api_report_small table
     */
    public function listSRReportQueryForOldData($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $fieldSearch, $incomingId, $hideRepost, $userId = 0, $page = 0, $perPage = 0) {
        $where = $params = array();
        if ($incomingId) {
            $where[]  = 'i.id = ?';
            $params[] = $incomingId;
        } else {
            $this->getWhereForSRReportOldData($where, $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage);
        }
        
        if ($tz == Config::$timeZone) {
            $tzConvertStart = $tzConvertEnd = '';
        } else {
            $tzConvertStart = 'CONVERT_TZ(';
            $tzConvertEnd   = ", '" . Config::$timeZone . "', '$tz')";
        }

        $fieldSearchJoinTable = '';
        $fieldSearchWhere = '';
        $fieldSearchGroupBy = '';
        if (!empty($fieldSearch)){
            $fieldSearchJoinTable = ' INNER JOIN `log_incoming_values` as `liv` ON `liv`.`incoming_id` = `i`.`id` ';
            $fieldSearchWhere = ' AND ( (`i`.`email` LIKE "%' . $fieldSearch . '%" ) OR (`liv`.`value` LIKE "%' . $fieldSearch . '%"))';
            $fieldSearchGroupBy = ' GROUP BY `i`.`id`';
        }
        
        $sql = "SELECT 
            `c`.`user_id`,
            `i`.`campaign_id`,
            `d`.`partner_id`,
            `i`.`is_success` as incoming_status,
            `d`.is_accepted as `delivery_status`,
            `d`.`is_repost`,
            `i`.`id` as `incoming_id`,
            `d`.`id` as `delivery_id`,
            {$tzConvertStart}`i`.`request_time`{$tzConvertEnd} AS `incoming_time`,
            {$tzConvertStart}IFNULL(`d`.`created_at`, `i`.`request_time`){$tzConvertEnd} AS `delivery_time`,
            `d`.`revenue`, `d`.`data`, `d`.`is_accepted`, `d`.`reject_reason`, `d`.`is_repost`,
            `d`.`partner_response`, `d`.`id` AS `log_del_id`, `d`.`message`,
            `i`.`email`, `i`.`remote_ip`, `i`.`http_referer`, `i`.`is_success`,
            `i`.`full_name`, `i`.`full_address`, `i`.`phone`, `i`.`campaign`
        FROM `log_incoming` AS `i`
        $fieldSearchJoinTable
        INNER JOIN campaigns c ON i.campaign_id = c.id
        LEFT JOIN `log_delivery` AS `d` ON `d`.`incoming_id` = `i`.`id` 
        WHERE
            " . implode(" AND ", $where). $fieldSearchWhere . $fieldSearchGroupBy;
            
        if (!$incomingId) {
            $sql .= " HAVING delivery_time BETWEEN ? AND ?";
            $params[] = $from;
            $params[] = $to;
        }
        
        $sql .= " ORDER BY d.id DESC";
        
        $arr = $this->db->query($sql, $params);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function countSRReportFor($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $incomingId, $hideRepost, $userId = 0) {
        if ($incomingId) {
            $params = array((int)$incomingId);
            if (!$userId) {
                $userId = Auth::getInstance()->getUserId();
            }
            $sql = "SELECT COUNT(*) FROM `$this->t_log_delivery` AS `ld`, `$this->t_partners` AS `p` WHERE `p`.`id` = `ld`.`partner_id` AND `incoming_id` = ? AND `user_id` = ?";
            $params[] = (int)$userId;
            if ($hideRepost) {
                $sql .= " AND `is_repost` = '0'";
            }
            return $this->db->getTopLeftInt($sql, $params);
        }

        if ($campaignSuccess == 1) {
            // if we need rejected campaigns then ignore partner id and delivery status
            // since no deliveries are made on rejected campaigns
            $partnerId = 0;
            $partnerSuccess = 0;
        } elseif ($partnerSuccess && ($campaignSuccess == 0)) {
            // if specific delivery status is requested and campaign status is set to "All"
            // then we set campaign status to accepted since only accepted submissions
            // will have deliveries that can have delivery state
            $campaignSuccess = 2;
        };

        if ($partnerId || $campaignId) {
            // if either of these is given we don't need user_id since
            // partners and campaigns belong to specific users
            $userId = 0;
        }

        if ($campaignSuccess == 1) {
            // Rejected
            $count = $this->getCountFromShIncoming($tz, $from, $to, $campaignId, 1, $userId);
        } else {
            $count = $this->getCountFromShDelivery($tz, $from, $to, $campaignId, $partnerId, $partnerSuccess, $hideRepost, $userId);
            if (!$partnerId && ($campaignSuccess == 0)) {
                // 0 means all - the sh_delivery query will only get deliveries from successful submissions,
                // so we need to add count from failed ones
                $count += $this->getCountFromShIncoming($tz, $from, $to, $campaignId, 1, $userId);
            }
        }

        return $count;
    }
    //--------------------------------------------------------------------------


    public function listByEmailAndUserId($email, $userId, $campaignId = null) {
        if ($campaignId) {
            $sql = "SELECT * FROM `$this->t_log_incoming` WHERE `email` = ? AND `user_id` = ? AND `campaign_id` = ?";
            return $this->db->getArray($sql, array($email, (int)$userId, (int)$campaignId), MYSQL_ASSOC);
        } else {
            $sql = "SELECT * FROM `$this->t_log_incoming` WHERE `email` = ? AND `user_id` = ?";
            return $this->db->getArray($sql, array($email, (int)$userId), MYSQL_ASSOC);
        }
    }
    //--------------------------------------------------------------------------


    private function getCountFromShDelivery($tz, $from, $to, $campaignId, $partnerId, $partnerSuccess, $hideRepost, $userId) {
        $where  = array();
        $params = array();

        if ($userId && !$campaignId && !$partnerId) {
            // sh_delivery doesn't have user_id so we check for all of user's campaigns
            if (isset($this->cidCache[$userId])) {
                $cids = $this->cidCache[$userId];
            } else {
                $cids = $this->db->getArray1v("SELECT `id` FROM `$this->t_campaigns` WHERE `user_id` = ? ORDER BY 1", array((int)$userId));
                $this->cidCache[$userId] = $cids;
            }
            if ($cids) {
                $where[] = "`campaign_id` IN (" . implode(', ', $cids) . ')';
            } else {
                return 0;
            }
        }

        if ($tz == Config::$timeZone) {
            $this->now or $this->now = $this->db->getTopLeft("SELECT NOW()");
            $s = '?';
        } else {
            $this->now or $this->now = $this->db->getTopLeft("SELECT CONVERT_TZ(NOW(), '$tz', '" . Config::$timeZone . "')");
            $s = "CONVERT_TZ(?, '$tz', '" . Config::$timeZone . "')";
        }
        $where[]  = "`del_hour` >= $s";
        $params[] = $from;

        if ($this->now > $to) {
            $where[]  = "`del_hour` <= $s";
            $params[] = $to;
        }

        if ($campaignId) {
            $where[]  = 'campaign_id = ?';
            $params[] = (int)$campaignId;
        }
        if ($partnerId) {
            $where[]  = 'partner_id = ?';
            $params[] = (int)$partnerId;
        }
        // Values are from dropdown on reporting page
        // 0 - All
        // 1 - Rejected
        // 2 - Accepted
        // 3 - Skipped
        //
        // in api_report table they were being written decreased by 1 - 0 - reject, 1 - accept, 2 - skip
        switch ($partnerSuccess) {
            case 1:
                // Rejected by partner
                $expr = "SUM(`reject_count`)";
                if ($hideRepost) {
                    $expr .= " - SUM(`repost_reject_count`)";
                }
                break;
            case 2:
                // Accepted by partner
                $expr = "SUM(`accept_count`)";
                if ($hideRepost) {
                    $expr .= " - SUM(`repost_accept_count`)";
                }
                break;
            case 3:
                // Skipped
                $expr = "SUM(`skip_count`)";
                if ($hideRepost) {
                    $expr .= " - SUM(`repost_skip_count`)";
                }
                break;
            default:
                // All, also value 0
                $expr = "SUM(`accept_count`) + SUM(`reject_count`) + SUM(`skip_count`)";
                if ($hideRepost) {
                    $expr .= " - SUM(`repost_count`)";
                }
                break;
        }
        $sql = "SELECT $expr AS `cnt` FROM `$this->t_sh_delivery` WHERE " . implode(" AND\n", $where);
        return $this->db->getTopLeftInt($sql, $params);
    }
    //--------------------------------------------------------------------------


    private function getCountFromShIncoming($tz, $from, $to, $campaignId, $campaignSuccess, $userId) {
        $where  = array();
        $params = array();

        if ($userId && !$campaignId) {
            // sh_incoming doesn't have user_id so we check for all of user's campaigns
            if (isset($this->cidCache[$userId])) {
                $cids = $this->cidCache[$userId];
            } else {
                $cids = $this->db->getArray1v("SELECT `id` FROM `$this->t_campaigns` WHERE `user_id` = ? ORDER BY 1", array((int)$userId));
                $this->cidCache[$userId] = $cids;
            }
            if ($cids) {
                $where[] = "`campaign_id` IN (" . implode(', ', $cids) . ')';
            } else {
                return 0;
            }
        }

        if ($tz == Config::$timeZone) {
            $this->now or $this->now = $this->db->getTopLeft("SELECT NOW()");
            $s = '?';
        } else {
            $this->now or $this->now = $this->db->getTopLeft("SELECT CONVERT_TZ(NOW(), '$tz', '" . Config::$timeZone . "')");
            $s = "CONVERT_TZ(?, '$tz', '" . Config::$timeZone . "')";
        }
        $where[]  = "`inc_hour` >= $s";
        $params[] = $from;

        if ($this->now > $to) {
            $where[]  = "`inc_hour` <= $s";
            $params[] = $to;
        }
        if ($campaignId) {
            $where[]  = 'campaign_id = ?';
            $params[] = (int)$campaignId;
        }
        $sql = "SELECT SUM(`" . (($campaignSuccess == 1) ? 'reject_count' : 'accept_count') . "`) FROM `$this->t_sh_incoming` WHERE " . implode(" AND\n", $where);
        return $this->db->getTopLeftInt($sql, $params);
    }
    //--------------------------------------------------------------------------


    private function sqlForListSRReportForIncomingId($tz, $incomingId, $hideRepost, $page, $perPage, $userId) {
        if ($tz == Config::$timeZone) {
            $fields = "
            `li`.`request_time` AS `incoming_time`,
            IFNULL(`ld`.`created_at`, `li`.`request_time`) AS `delivery_time`,";
        } else {
            $baseTZ = Config::$timeZone;
            $fields = "
            CONVERT_TZ(`li`.`request_time`, '$baseTZ', '$tz') AS `incoming_time`,
            CONVERT_TZ(IFNULL(`ld`.`created_at`, `li`.`request_time`), '$baseTZ', '$tz') AS `delivery_time`,";
        }
        if (!$userId) {
            $userId = Auth::getInstance()->getUserId();
        } else {
            $userId = (int)$userId;
        }

        $sql = "
        SELECT
            $incomingId as `incoming_id`,
            IFNULL(`ld`.`id`, 0) AS `delivery_id`,$fields
            `li`.`user_id`,
            `li`.`campaign_id`,
            IF(`li`.`is_success` = '0', 0, 1) AS `incoming_status`,
            IFNULL(`ld`.`partner_id`, 0) AS `partner_id`,
            IF(`ld`.`id` IS NULL, 0, IF(`ld`.`is_accepted` = '1', 1, IF(`ld`.`reject_reason` = 'skipped', 2, 0))) AS `delivery_status`
        FROM
            `$this->t_log_incoming` AS `li`
        LEFT JOIN
            `$this->t_log_delivery` AS `ld` ON `li`.`id` = `ld`.`incoming_id`
        WHERE
            `ld`.`incoming_id` = `li`.`id` AND
            `ld`.`incoming_id` = " . (int)$incomingId;
        if ($hideRepost) {
            $sql .= " AND
            IFNULL(`ld`.`is_repost`, '0') = '0'";
        }
        $sql .= "
        ORDER BY `ld`.`id`";
        if ($page) {
            $sql .= "\n            LIMIT " . (($page - 1) * $perPage) . ", $perPage";
        }
        return $sql;
    }
    //--------------------------------------------------------------------------


    private function sqlForListSRReport($tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage) {
        $where  = array();
        $params = array();
        $this->getWhereForSRReport($where, $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage);

        if ($tz == Config::$timeZone) {
            $tzConvertStart = $tzConvertEnd = '';
        } else {
            $tzConvertStart = 'CONVERT_TZ(';
            $tzConvertEnd   = ", '" . Config::$timeZone . "', '$tz')";
        }

        $sql = "SELECT
            `x`.`user_id`,
            `x`.`campaign_id`,
            `x`.`partner_id`,
            `x`.`incoming_status`,
            `x`.`delivery_status`,
            `a`.`incoming_id`,
            `a`.`delivery_id`,
            {$tzConvertStart}`i`.`request_time`{$tzConvertEnd} AS `incoming_time`,
            {$tzConvertStart}IFNULL(`d`.`created_at`, `i`.`request_time`){$tzConvertEnd} AS `delivery_time`
        FROM
            `$this->t_log_incoming` AS `i`,
            (
        SELECT * FROM `$this->t_api_report_small` AS `s` WHERE
            "  . implode(" AND \n            ", $where) . "
        ORDER BY `s`.`delivery_id` DESC";

        if ($page) {
            $sql .= "\n        LIMIT " . (($page - 1) * $perPage) . ", $perPage";
        }
        $sql .= "
            ) AS `x`,
            `$this->t_api_report_inc` AS `a`
        LEFT JOIN
            `$this->t_log_delivery` as `d` ON `d`.`id` = `a`.`delivery_id`
        WHERE
            CAST(SUBSTRING(`x`.`delivery_id`, -13) AS UNSIGNED) = `a`.`id` AND
            `a`.`incoming_id` = `i`.`id`";
        return $this->db->processParams($sql, $params);
    }
    //--------------------------------------------------------------------------


    private function getWhereForSRReport(array & $where, array & $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $hideRepost, $userId, $page, $perPage) {
        if (($partnerSuccess == 1) && ($campaignSuccess == 0)) {
            $campaignSuccess = 2;
        }

        if ($partnerId || $campaignId) {
            $userId = 0;
        }

        if ($userId) {
            $subAccountCampaignIds = SingletonRegistry::getModelSubAccount()->listAllCampaignIds(Auth::getInstance()->getUserId());
            $subAccountPartnerIds = SingletonRegistry::getModelSubAccount()->listAllPartnerIds(Auth::getInstance()->getUserId());
            $condition = '(';
            $condition .= '`s`.`user_id` = ?';
            if (!empty($subAccountCampaignIds)) {
                $condition .= ' OR `s`.`campaign_id` IN ('. implode(",", $subAccountCampaignIds) .')';
            }
            if (!empty($subAccountPartnerIds)) {
                $condition .= ' OR `s`.`partner_id` IN ('. implode(",", $subAccountPartnerIds) .')';
            }
            $condition .= ')';
            $where[] = $condition;
            
            $params[] = $userId;
        }

        if ($tz == Config::$timeZone) {
            $this->now or $this->now = $this->db->getTopLeft("SELECT NOW()");
            $baseTZ = false;
        } else {
            $baseTZ = Config::$timeZone;
            $this->now or $this->now = $this->db->getTopLeft("SELECT CONVERT_TZ(NOW(), '$tz', '$baseTZ')");
        }

        if ($this->now > $to) {
            if (substr($to, -6) == ':59:59') {
                $to = $this->db->getTopLeft("SELECT DATE_ADD(?, INTERVAL 1 SECOND)", array($to));
            }
            $arr = $this->deliveryTimeToId($tz, $from, $to);
            if ($arr[0]) {
                $where[]  = "`s`.`delivery_id` >= ? /* $from $tz */";
                $params[] = $arr[0];
            }
            if ($arr[1]) {
                $where[]  = "`s`.`delivery_id` < ? /* $to $tz */";
                $params[] = $arr[1];
            }
        } else {
            $arr = $this->deliveryTimeToId($tz, $from);
            if ($arr[0]) {
                $where[]  = "`s`.`delivery_id` >= ? /* $from $tz */";
                $params[] = $arr[0];
            }
        }

        if ($campaignId) {
            $where[]  = '`s`.`campaign_id` = ?';
            $params[] = $campaignId;
        }
        if ($partnerId) {
            $where[]  = '`s`.`partner_id` = ?';
            $params[] = $partnerId;
        }
        if ($campaignSuccess) {
            $where[]  = '`s`.`incoming_status` = ?';
            $params[] = ($campaignSuccess - 1);
        }
        if ($partnerSuccess) {
            $where[]  = '`s`.`delivery_status` = ?';
            $params[] = ($partnerSuccess - 1);
        }

        if ($hideRepost) {
            $where[]  = '`s`.`is_repost` = ?';
            $params[] = 0;
        }
        
        $subAccountModel = SingletonRegistry::getModelSubAccount();
        $subAccountInfo = $subAccountModel->checkIfUserIsSubAccount($userId);
        $isRevokeSuccessLeads = $subAccountModel->checkIfRevokeSuccessLeads($userId);
        $isRevokeFailedLeads = $subAccountModel->checkIfRevokeFailedLeads($userId);
        $isRevokeSkippedLeads = $subAccountModel->checkIfRevokeSkippedLeads($userId);
        
        if (!empty($subAccountInfo) && $isRevokeSuccessLeads) {
            $where[]  = '`s`.`delivery_status` != ?';
            $params[] = '1';
        }
        
        if (!empty($subAccountInfo) && $isRevokeFailedLeads) {
            $where[]  = '`s`.`delivery_status` != ?';
            $params[] = '0';
        }
        
        if (!empty($subAccountInfo) && $isRevokeSkippedLeads) {
            $where[]  = '`s`.`delivery_status` != ?';
            $params[] = '2';
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    private function deliveryTimeToId($timezone, $time1, $time2 = false) {
        $arr = func_get_args();
        array_shift($arr);
        if ($timezone != Config::$timeZone) {
            $data = array();
            foreach ($arr as $k => $t) {
                $data[] = "SELECT $k, CONVERT_TZ(?, '$timezone', '" . Config::$timeZone . "')";
            }
            $arr = $this->db->getArrayAssoc(implode("\nUNION ALL\n", $data), array_values($arr));
        }
        $result = array();
        foreach ($arr as $s) {
            $result[] = ((Lib::TO_DAYS($s) - Model_LogDelivery::DAY_ID_CUTOFF) * 24 + (int)substr($s, 11, 2)) . '0000000000000';
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    private function initReportWhereParams(array & $where, array & $params, $tz, $from, $to, $campaignId, $partnerId, $campaignSuccess, $partnerSuccess, $incomingId, $hideRepost, $userId) {
        $where  = array();
        $params = array();
        if (($partnerSuccess == 1) && ($campaignSuccess == 0)) {
            $campaignSuccess = 2;
        }

        if ($partnerId || $campaignId) {
            $userId = 0;
        }

        $field = 'delivery_time';
        if ($userId) {
            $where[]  = 'user_id = ?';
            $params[] = $userId;
        }

        if ($incomingId) {
            $where[]  = 'incoming_id = ?';
            $params[] = (int)$incomingId;
            if (!$userId && Auth::getInstance()->getUserId()) {
                $where[] = '`user_id` = ?';
                $params[] = Auth::getInstance()->getUserId();
            }
        } else {
            if ($tz == Config::$timeZone) {
                $this->now or $this->now = $this->db->getTopLeft("SELECT NOW()");
                $where[]  = "$field >= ?";
                if ($this->now > $to) {
                    $where[]  = "$field <= ?";
                }
            } else {
                $baseTZ = Config::$timeZone;
                $this->now or $this->now = $this->db->getTopLeft("SELECT CONVERT_TZ(NOW(), '$tz', '$baseTZ')");
                $where[]  = "$field >= CONVERT_TZ(?, '$tz', '$baseTZ')";
                if ($this->now > $to) {
                    $where[]  = "$field <= CONVERT_TZ(?, '$tz', '$baseTZ')";
                }
            }
            $params[] = $from;
            if ($this->now > $to) {
                $params[] = $to;
            }
            if ($campaignId) {
                $where[]  = 'campaign_id = ?';
                $params[] = $campaignId;
            }
            if ($partnerId) {
                $where[]  = 'partner_id = ?';
                $params[] = $partnerId;
            }
            if ($campaignSuccess) {
                $where[]  = 'incoming_status = ?';
                $params[] = ($campaignSuccess - 1);
            }
            if ($partnerSuccess) {
                $where[]  = 'delivery_status = ?';
                $params[] = ($partnerSuccess - 1);
            }
        }
        if ($hideRepost) {
            $where[]  = '`is_repost` = ?';
            $params[] = 0;
        }
    }
    //--------------------------------------------------------------------------


    private function getDashboardStatsFromSummaryTables($userId, array $campaignIDs, $tz, array $campaignsAssoc) {
        $baseTz = Config::$timeZone;
        $start  = substr($this->db->getTopLeft("SELECT DATE_SUB(NOW(), INTERVAL 1 DAY)"), 0, 13) . ':00:00';
        if ($tz == $baseTz) {
            $midnight = $this->db->getTopLeft("SELECT CURDATE()");
            $m2d      = $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL 2 DAY)");
            $m7d      = $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
            $m30d     = $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL 29 DAY)");
            $ym       = substr($start, 0, 10);
        } else {
            $s = "CAST(CAST(CONVERT_TZ(NOW(), '$baseTz', '$tz') AS DATE) AS DATETIME) ";
            $midnight = $this->db->getTopLeft("SELECT CONVERT_TZ($s, '$tz', '$baseTz')");
            $ym       = $this->db->getTopLeft("SELECT DATE_SUB(CONVERT_TZ($s, '$tz', '$baseTz'), INTERVAL 1 DAY)");
            $m2d      = $this->db->getTopLeft("SELECT DATE_SUB(CONVERT_TZ($s, '$tz', '$baseTz'), INTERVAL 2 DAY)");
            $m7d      = $this->db->getTopLeft("SELECT DATE_SUB(CONVERT_TZ($s, '$tz', '$baseTz'), INTERVAL 6 DAY)");
            $m30d     = $this->db->getTopLeft("SELECT DATE_SUB(CONVERT_TZ($s, '$tz', '$baseTz'), INTERVAL 29 DAY)");
        }

        $out = array();
        
        $campaignCsIDs = array();
        foreach ($campaignIDs as $index => $cid) {
            $csId = empty($campaignsAssoc[$cid]['shadow_of']) ? $cid : (int)$campaignsAssoc[$cid]['shadow_of'];
            $campaignCsIDs[$index] = $csId;
        }
        
        $campaignIDsSql = join(',', $campaignIDs);
        $campaignCsIDsSql = join(',', $campaignCsIDs);
        
        $acceptedData       = $this->getAcceptedData($campaignCsIDsSql, $start);
        $rejectedData       = $this->getRejectedData($campaignCsIDsSql, $start);
        $deliveredData      = $this->getDeliveredData($campaignIDsSql, $start);
        $todayAcceptedData  = $this->getAcceptedData($campaignCsIDsSql, $midnight);
        $todayRejectedData  = $this->getRejectedData($campaignCsIDsSql, $midnight);
        $ymAcceptedData     = $this->getAcceptedData($campaignCsIDsSql, $ym, $midnight);
        $ymRejectedData     = $this->getRejectedData($campaignCsIDsSql, $ym, $midnight);
        $m7dAcceptedData    = $this->getAcceptedData($campaignCsIDsSql, $m7d);
        $m7dRejetedData     = $this->getRejectedData($campaignCsIDsSql, $m7d);
        $m30dAcceptedData   = $this->getAcceptedData($campaignCsIDsSql, $m30d);
        $m30dRejetedData    = $this->getRejectedData($campaignCsIDsSql, $m30d);
        
        $pdDatas            = $this->getPdDatas($campaignIDsSql);
        $partnerIDs         = $this->getPartnerIDs($pdDatas);
        $partnerResponseTimeData = $this->getPartnerResponseTimeData($partnerIDs, $m2d);
        
        foreach ($campaignIDs as $index => $cid) {
            $csId = $campaignCsIDs[$index];
            $data = array();
            
            $data['accepted'] = !empty($acceptedData[$csId]) ?  $acceptedData[$csId] : 0;
            $data['rejected'] = !empty($rejectedData[$csId]) ? $rejectedData[$csId] : 0;
            $data['delivered'] = !empty($deliveredData[$cid]) ? $deliveredData[$cid] : 0;
            
            $data['t']['a'] = !empty($todayAcceptedData[$csId]) ? $todayAcceptedData[$csId] : 0;
            $data['t']['r'] = !empty($todayRejectedData[$csId]) ? $todayRejectedData[$csId] : 0;

            $data['y']['a'] = !empty($ymAcceptedData[$csId]) ? $ymAcceptedData[$csId] : 0;
            $data['y']['r'] = !empty($ymRejectedData[$csId]) ? $ymRejectedData[$csId] : 0;
            
            $data['7']['a'] = !empty($m7dAcceptedData[$csId]) ? $m7dAcceptedData[$csId] : 0;
            $data['7']['r'] = !empty($m7dRejetedData[$csId]) ? $m7dRejetedData[$csId] : 0;

            $data['30']['a'] = !empty($m30dAcceptedData[$csId]) ? $m30dAcceptedData[$csId] : 0;
            $data['30']['r'] = !empty($m30dRejetedData[$csId]) ? $m30dRejetedData[$csId] : 0;

            $pd = array();
            
            // get succ/fail data
            foreach ($pdDatas as $row) {
                if ($row['campaign_id'] === $cid) {
                    $pid = (int)$row['partner_id'];
                    if (!isset($pd[$pid][0])) {
                        $pd[$pid][0] = 0;
                    }
                    if (!isset($pd[$pid][1])) {
                        $pd[$pid][1] = 0;
                    }
                    $pd[$pid][(int)$row['is_accepted']] = (int)$row['cnt'];
                }
            }
            
            // get timeout_count, time_combined data
            if(!empty($pd) && !empty($partnerResponseTimeData)) {
                $keys = array_keys($pd);
                
                foreach($keys AS $pid) {
                    foreach ($partnerResponseTimeData as $row) {
                        if ((int) $pid === (int) $row['partner_id']) {
                            $pd[$pid][2] = (int)$row['timeout_count'];
                            $pd[$pid][3] = (float)$row['time_combined'] / (int)$row['response_count'];
                        }
                    }
                }
            }
            
            $data['pd'] = $pd;

            $out[$cid] = $data;
        }
        return $out;
    }
    //--------------------------------------------------------------------------
    
    
    private function getAcceptedData($campaignCsIDsSql, $time1, $time2 = null) {
        if (empty($campaignCsIDsSql)) {
            return array();
        }
        
        $timeCondition = "`inc_hour` >= '$time1'";
        
        if (!empty($time2)) {
            $timeCondition = "`inc_hour` >= '$time1' AND `inc_hour` < '$time2'";
        }
        $sql = "SELECT `campaign_id`, SUM(`accept_count`) as accepted_count FROM `$this->t_sh_incoming` WHERE `campaign_id` IN ( ".$campaignCsIDsSql." ) AND $timeCondition GROUP BY `campaign_id`";
        $queryResult = $this->db->getArray($sql);

        $result = array();
        
        if (!empty($queryResult)) {
            foreach ($queryResult as $row) {
                $result[$row['campaign_id']] = $row['accepted_count'];
            }
        }
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    private function getRejectedData($campaignCsIDsSql, $time1, $time2 = null) {
        if (empty($campaignCsIDsSql)) {
            return array();
        }
        
        $timeCondition = "`inc_hour` >= '$time1'";
        
        if (!empty($time2)) {
            $timeCondition = "`inc_hour` >= '$time1' AND `inc_hour` < '$time2'";
        }
        
        $sql = "SELECT `campaign_id`, SUM(`reject_count`) as rejected_count FROM `$this->t_sh_incoming` WHERE `campaign_id` IN ( ".$campaignCsIDsSql." ) AND $timeCondition GROUP BY `campaign_id`";
        $queryResult = $this->db->getArray($sql);

        $result = array();
        
        if (!empty($queryResult)) {
            foreach ($queryResult as $row) {
                $result[$row['campaign_id']] = $row['rejected_count'];
            }
        }
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    private function getDeliveredData ($campaignIDsSql, $start) {
        if (empty($campaignIDsSql)) {
            return array();
        }
        
        $sql = "SELECT `campaign_id`, SUM(`accept_count`) as accepted_count FROM `$this->t_sh_delivery` WHERE `campaign_id` IN ( ".$campaignIDsSql." ) AND `del_hour` >= '$start' GROUP BY `campaign_id`";
        $queryResult = $this->db->getArray($sql);

        $result = array();
        
        if (!empty($queryResult)) {
            foreach ($queryResult as $row) {
                $result[$row['campaign_id']] = $row['accepted_count'];
            }
        }
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    private function getPdDatas($campaignIDsSql) {
        if (empty($campaignIDsSql)) {
            return array();
        }
        
        $sql = "SELECT 
                    `campaign_id`, 
                    `partner_id`, 
                    `is_accepted`, 
                    COUNT(DISTINCT `incoming_id`) AS `cnt` 
                FROM `$this->t_summary_acceptance`
                WHERE 
                    `campaign_id` IN (".$campaignIDsSql.")
                    AND `time_stamp` >= NOW() - INTERVAL 2 DAY
                GROUP BY campaign_id, partner_id, is_accepted";
        
        $queryResult = $this->db->getArray($sql);
        
        usort($queryResult, array('Model_LogIncoming','sortByCnt'));

        return $queryResult;
    }
    //--------------------------------------------------------------------------
    
    
    private function sortByCnt($a1, $a2) {
        return $a1['cnt'] < $a2['cnt'];
    }
    //--------------------------------------------------------------------------
    
    
    private function getPartnerIDs($pdDatas) {
        $result = array();
        
        if (!empty($pdDatas)) {
            foreach ($pdDatas as $row) {
                $result[] = $row['partner_id'];
            }
        }
        
        return array_unique($result);
    }
    //--------------------------------------------------------------------------
    
    
    private function getPartnerResponseTimeData($partnerIDs, $m2d) {
        if (empty($partnerIDs)) {
            return false;
        }
        
        $sql = "SELECT `partner_id`, `response_count`, `time_combined`, `timeout_count` FROM `$this->t_sh_response_times` WHERE `partner_id` IN ( ". join(',', $partnerIDs) ." ) AND `day` = '$m2d' GROUP BY `partner_id`";

        $queryResult = $this->db->getArray($sql);
        
        return $queryResult;
    }
    //--------------------------------------------------------------------------
    
    
    public function addToQueueFromCsv($rowNumber, $campaign, $header, $row, $fieldTypesFull, $initialFileName) {
        $csvData        = $this->combineHeaderAndData($header, $row);
        $emailFieldName = $campaign['email_field_name'];
        
        $fieldModel     = SingletonRegistry::getModelCampaignField();
        $campaignFields = $fieldModel->listAllForCampaign($campaign['id']);  
        
        if (!$this->validateCsvLeadData($rowNumber, $csvData, $campaign, $campaignFields, $fieldTypesFull)) {
            return false;
        }
        
        // add to log_incoming
        $submissionData = array(
            'campaign_id'  => $campaign['id'],
            'email'        => isset($csvData[$emailFieldName]) ? $csvData[$emailFieldName] : "",
            'user_agent'   => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'http_referer' => isset($_SERVER['HTTP_REFERER'])    ? $_SERVER['HTTP_REFERER']    : '',
            'remote_ip'    => isset($_SERVER['REMOTE_ADDR'])     ? $_SERVER['REMOTE_ADDR']     : '',
            'is_success'   => '1',
            'user_id'      => $campaign['user_id']
        );
        $incomingId     = $this->insert($submissionData);
        // end add to log_incoming
        
        if ($incomingId) {
                     
            // add to log_incoming_value     
            $fieldTypes     = array();
            foreach ($campaignFields as $field) {
                $fieldTypes[$field['name']] = $field['field_type'];
            }
            
            $logData = $this->combineHeaderAndData($fieldTypes, $csvData);
            $this->insertSubmissionValues($incomingId, $logData);
            // end add to log_incoming_value
            
            // add to summary table
            $timestamp  = date('Y-m-d H:i:s');
            $this->logSubmissionToSummaryTable($campaign['id'], $timestamp, true);
            
            // add to queue
            $this->insertIntoRepostQueue($incomingId, '0', '1', $campaign['id'], $campaign['user_id'], false, 0, $initialFileName);

        }
        return $incomingId;
    }
    // -------------------------------------------------------------------------
    
    
    public function combineHeaderAndData($headers, $data) {
        $result = array();
        foreach ($headers as $index => $header) {
            $result[$header] = isset($data[$index]) ? $data[$index] : "";
        }
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    public function validateCsvLeadData($rowNumber, $csvData, $campaign, $campaignFields, $fieldTypesFull) {

        if ($campaign['email_field_mandatory'] && empty($csvData[$campaign['email_field_name']])) {
            $this->messages[] = "Row " . $rowNumber . ": Email field is mandatory";
            return false;
        }
        
        if (!empty($csvData[$campaign['email_field_name']]) && !filter_var($csvData[$campaign['email_field_name']], FILTER_VALIDATE_EMAIL)) {
            $this->messages[] = "Row " . $rowNumber . ": " . $csvData[$campaign['email_field_name']] . " is not a valid email";
            return false;
        }
        
        foreach ($campaignFields as $field) {
            if ($field['is_mandatory'] == '1' && empty($csvData[$field['name']])) {
                $this->messages[] = "Row " . $rowNumber . ": " . $field['name'] . " field is mandatory";
                return false;
            }
            
            $fieldType = $fieldTypesFull[$field['field_type']];
            switch ($fieldType['validation_type']) {
                case Model_FieldType::VALIDATION_EMAIL:
                    if(!empty($csvData[$field['name']]) && !filter_var($csvData[$field['name']], FILTER_VALIDATE_EMAIL)) {
                        $this->messages[] = "Row " . $rowNumber . ": " . $csvData[$field['name']] . " is not a valid email";
                        return false;
                    }
                    break;
                case Model_FieldType::VALIDATION_URL:
                    if(!empty($csvData[$field['name']]) && !filter_var($csvData[$field['name']], FILTER_VALIDATE_URL)) {
                        $this->messages[] = "Row " . $rowNumber . ": " . $csvData[$field['name']] . " is not a valid URL";
                        return false;
                    }
                    break;

                case Model_FieldType::VALIDATION_DATE:
                    if(!empty($csvData[$field['name']]) && strtotime($csvData[$field['name']]) === false) {
                        $this->messages[] = "Row " . $rowNumber . ": " . $csvData[$field['name']] . " is not a valid date";
                        return false;
                    }
                    break;

                case Model_FieldType::VALIDATION_REGEX:
                    if(
                            !empty($csvData[$field['name']]) && 
                            !empty($fieldType['validation_data']) && 
                            !preg_match($fieldType['validation_data'], $csvData[$field['name']])
                    ) {
                        $this->messages[] = "Row " . $rowNumber . ": " . $csvData[$field['name']] . " is not a valid " . $field['name'];
                        return false;
                    }
                    break;
            }
        }

        $inputData = array();
        foreach ($campaignFields as $field) {
            if (isset($csvData[$field['name']])) {
                $inputData[$field['field_type']] = $csvData[$field['name']];
            }
        }
        if (isset($csvData[$campaign['email_field_name']])) {
            $inputData[Model_CampaignField::FIELD_TYPE_EMAIL] = $csvData[$campaign['email_field_name']];
        } else {
            $inputData[Model_CampaignField::FIELD_TYPE_EMAIL] = '';
        }
        
        $engine = new Engine_Submission ();
        $checkRuleResult = $engine->setInputDataAndCheckCampaignFiltersForCSV($campaign, $inputData);
        if ($checkRuleResult['status'] === false) {
            $this->messages[] = "Row " . $rowNumber . ': ' . $checkRuleResult['message'];
            return false;
        }
        
        return true;
    }
    //--------------------------------------------------------------------------
    
    
    public function getMinIncomingId(){
        $sql = "SELECT `id` FROM `$this->t_log_incoming` ORDER BY `id` ASC LIMIT 1";
        $id = $this->db->getTopLeft($sql);
        if ($id) {
            return (int)$id;
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function getMaxIncomingId(){
        $sql = "SELECT `id` FROM `$this->t_log_incoming` ORDER BY `id` DESC LIMIT 1";
        $id = $this->db->getTopLeft($sql);
        if ($id) {
            return (int)$id;
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function backfillStandardLeadFields($fromIndex, $toIndex) {
        $relatedFieldTypes = array(
            Model_CampaignField::FIELD_TYPE_FULL_NAME,
            Model_CampaignField::FIELD_TYPE_PHONE,
            Model_CampaignField::FIELD_TYPE_CAMPAIGN,
            Model_CampaignField::FIELD_TYPE_ADDRESS_1,
            Model_CampaignField::FIELD_TYPE_STATE_CODE,
            Model_CampaignField::FIELD_TYPE_CITY,
            Model_CampaignField::FIELD_TYPE_FIRST_NAME,
            Model_CampaignField::FIELD_TYPE_MIDDLE_NAME,
            Model_CampaignField::FIELD_TYPE_LAST_NAME
        );
        
        $sql = "UPDATE log_incoming li
                INNER JOIN
                (
                    SELECT 
                        log_incoming_values.incoming_id,
                        GROUP_CONCAT(CASE WHEN (log_incoming_values.`name` = " .Model_CampaignField::FIELD_TYPE_FIRST_NAME. " 
                            OR log_incoming_values.`name` = " . Model_CampaignField::FIELD_TYPE_MIDDLE_NAME . " 
                            OR log_incoming_values.`name` = ". Model_CampaignField::FIELD_TYPE_LAST_NAME . ") 
                            THEN log_incoming_values.`value`
                        ELSE NULL END ORDER BY ABS(log_incoming_values.`name` - 9.4) DESC SEPARATOR ' ') as temp_full_name,
                        GROUP_CONCAT(CASE WHEN log_incoming_values.`name` = " . Model_CampaignField::FIELD_TYPE_PHONE . " THEN log_incoming_values.`value`
                        ELSE NULL END SEPARATOR '') as temp_phone,
                        GROUP_CONCAT(CASE WHEN log_incoming_values.`name` = " . Model_CampaignField::FIELD_TYPE_CAMPAIGN . " THEN log_incoming_values.`value`
                        ELSE NULL END SEPARATOR '') as temp_campaign,
                        GROUP_CONCAT(CASE WHEN (log_incoming_values.`name` = " .Model_CampaignField::FIELD_TYPE_ADDRESS_1. " 
                            OR log_incoming_values.`name` = " . Model_CampaignField::FIELD_TYPE_STATE_CODE . " 
                            OR log_incoming_values.`name` = ". Model_CampaignField::FIELD_TYPE_CITY . ") 
                            THEN log_incoming_values.`value`
                        ELSE NULL END ORDER BY ABS(log_incoming_values.`name` - " . Model_CampaignField::FIELD_TYPE_STATE_CODE . ") DESC SEPARATOR ', ') as temp_full_address
                    FROM log_incoming
                    INNER JOIN log_incoming_values ON log_incoming.id = log_incoming_values.incoming_id
                    AND log_incoming_values.`name` IN (" . join(',', $relatedFieldTypes) . ") AND log_incoming_values.`value` != ''
                    WHERE
                        log_incoming.id BETWEEN $fromIndex AND $toIndex
                    GROUP BY log_incoming_values.incoming_id
                ) as temp ON li.id = temp.incoming_id
                SET 
                    li.full_name = temp.temp_full_name,
                    li.phone = temp.temp_phone,
                    li.campaign = temp.temp_campaign,
                    li.full_address = temp.temp_full_address
                WHERE li.id  BETWEEN $fromIndex AND $toIndex";
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------
}
