<?php

class Model_LogDelivery extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    /**
     * SELECT TO_DAYS('2011-01-01')
     */
    const DAY_ID_CUTOFF = 734503;

    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 0;
    const STATUS_SKIPPED  = 2;

    protected $t_log_delivery;
    protected $t_log_delivery_campaigns;
    protected $t_log_delivery_templates_sent;
    protected $t_log_delivery_timeouts;
    protected $t_api_report_inc;
    protected $t_api_report_small;
    protected $t_log_incoming;

    protected $t_sh_delivery;
    protected $t_sh_incoming;
    protected $t_sh_repost;
    protected $t_sh_response_times;
    protected $t_summary_acceptance;

    protected $t_campaigns;
    protected $t_partners;
    protected $t_users;

    protected $t_repost_log;
    protected $t_repost_queue;
    protected $t_repost_queue_mem;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_delivery);

        $this->timestampForInsert = array('created_at');
        $this->zeroOneFields      = array('is_accepted');
    }
    //--------------------------------------------------------------------------


    public function getDeliveriesToPartner($cap, $timezone = null, $now = false) {
        $where = $this->getWhereCapCondition($cap, $now, $timezone);
        if ($where === false) {
            return false;
        }
        
        // add random modifier to avoid caching
        $where .= ' AND ' . Engine_Utilities::getQueryRandomModifier();

        $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE `partner_id` = ? AND `is_accepted` = '1' " . $where;
        return $this->db->getTopLeftInt($sql, array((int)$cap['partner_id']));
    }
    //--------------------------------------------------------------------------


    public function getBudgetToPartner($cap, $timezone = null, $now = false) {
        $where = $this->getWhereCapCondition($cap, $now, $timezone);
        if ($where === false) {
            return false;
        }
        
        // add random modifier to avoid caching
        $where .= ' AND ' . Engine_Utilities::getQueryRandomModifier();

        $sql = "SELECT SUM(revenue) FROM `$this->tableName` WHERE `partner_id` = ? AND `is_accepted` = '1' " . $where;
        return $this->db->getTopLeftInt($sql, array((int)$cap['partner_id']));
    }
    //--------------------------------------------------------------------------
    
    
    public function getWhereCapCondition($cap, $now, $timezone) {
        $interval = $cap['interval'];
        $upToTimestamp = ($now === false) ? 'NOW()' : "'$now'";
        $capTypeName = isset($cap['capTypes'][$interval]) ? $cap['capTypes'][$interval] : '';
        
        $countTimeStart = Model_Partner::getTimeFrameStart($capTypeName, $upToTimestamp, $timezone, $cap);

        if (!empty($countTimeStart)) {
            $where = " AND `created_at` >= " . $countTimeStart;
        } elseif ($interval === Model_PartnerCap::INTERVAL_DAY_OF_WEEK || $interval === Model_PartnerCap::INTERVAL_TIME_OF_DAY) {
            $where = false;
        } else {
            $where = "";
        }
        
        return $where;
    }
    //--------------------------------------------------------------------------
    
    
    public function countOfDelieryAttempts($incomingId, $partnerId) {
        $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE `incoming_id` = ? AND `partner_id` = ?";
        return $this->db->getTopLeftInt($sql, array((int)$incomingId, (int)$partnerId));
    }
    //--------------------------------------------------------------------------


    public function countOfDeliveryAttemptsGroup($incomingId, $campaignId, array $partnerIds) {
        if (!$partnerIds) {
            return array();
        }

        $sql = "
        SELECT
            `partner_id`, COUNT(*)
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ? AND
            `partner_id` IN (" . self::implodeInts($partnerIds) . ") GROUP BY `partner_id` ORDER BY 1";

        $arr = $this->db->getArrayAssoc($sql, array((int)$incomingId, (int)$campaignId));
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function listPartnersDeliveredTo($incomingId, $campaignId, $onlyAccepted = false) {
        $sql = "
        SELECT
            DISTINCT `partner_id`
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ?";
        if ($onlyAccepted) {
            $sql .= " AND
            `ld`.`is_accepted` = '1'";
        }
        $sql .= "
        ORDER BY 1";
        $arr = $this->db->getArray1v($sql, array((int)$incomingId, (int)$campaignId));

        return $arr;
    }
    //--------------------------------------------------------------------------


    public function listPartnersWhoAcceptedOrSkipped($incomingId, $campaignId) {
        $sql = "
        SELECT
            DISTINCT `partner_id`
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ? AND
            (
                (`ld`.`is_accepted` = '1')
                OR
                (`ld`.`is_accepted` = '0' AND `ld`.`reject_reason` = 'skipped')
            )
        ORDER BY 1";
        $ids = array();
        foreach ($this->db->getArray1v($sql, array((int)$incomingId, (int)$campaignId)) as $id) {
            $ids[]= (int)$id;
        }
        return $ids;
    }
    //--------------------------------------------------------------------------


    public function insertWithApiReportData(array $data, $campaignId, $userId, $incStatus, $incomingTimestamp, $logToDeliveryCampaigns = false) {
        if (!isset($data['partner_response']) || is_null($data['partner_response'])) {
            $data['partner_response'] = '';
        }

        if ($data['is_accepted']) {
            $deliveryStatus = self::STATUS_ACCEPTED;
        } elseif ($data['reject_reason'] == 'skipped') {
            $deliveryStatus = self::STATUS_SKIPPED;
            $count = $this->countOfSkippedDeliveries($data['incoming_id'], $data['partner_id'], $campaignId);
            if ($count) {
                return;
            }
        } else {
            $deliveryStatus = self::STATUS_REJECTED;
        }

        $id = $this->insert($data);
        if ($id) {
            if ($logToDeliveryCampaigns) {
                $this->db->query("INSERT INTO `$this->t_log_delivery_campaigns` (`delivery_id`, `campaign_id`) VALUES ($id, ?)", array((int)$campaignId));
            }
            $this->logDeliveryToReportingTables($id, $data, $deliveryStatus, $campaignId, $userId, $incStatus);
        }
        return $id;
    }
    //--------------------------------------------------------------------------


    public function markAsTimeout($deliveryId) {
        $this->db->query("INSERT INTO `$this->t_log_delivery_timeouts` VALUES (?)", array((int)$deliveryId));
    }
    //--------------------------------------------------------------------------


    public function checkForTimeouts(array $deliveryIDs) {
        if (!$deliveryIDs) {
            return array();
        }
        $sql = "SELECT `id`, 1 FROM `$this->t_log_delivery_timeouts` WHERE `id` IN (" . self::implodeInts($deliveryIDs) . ') ORDER BY 1';
        return $this->db->getArrayAssoc($sql);
    }
    //--------------------------------------------------------------------------


    private function logDeliveryToReportingTables($id, array $data, $deliveryStatus, $campaignId, $userId, $incStatus) {
        $now        = $this->db->getTopLeft("SELECT `created_at` FROM `$this->t_log_delivery` WHERE `id` = ?", array($id));
        $hourNumber = ((Lib::TO_DAYS($now) - self::DAY_ID_CUTOFF) * 24 + (int)substr($now, 11, 2));

        $this->db->query("INSERT INTO `$this->t_api_report_inc` (`delivery_id`, `incoming_id`) VALUES (?, ?)", array($id, (int)$data['incoming_id']));
        $deliveryIdForApiReportSmall = $hourNumber . sprintf('%013u', $this->db->lastInsertId());

        $sql = "INSERT INTO `$this->t_api_report_small` (
        `delivery_id`,
        `user_id`,
        `campaign_id`,
        `partner_id`,
        `incoming_status`,
        `delivery_status`,
        `is_repost`
        )
        VALUES ($deliveryIdForApiReportSmall, ?, ?, ?, ?, ?, ?)";
        $params = array((int)$userId, (int)$campaignId, (int)$data['partner_id'], $incStatus ? 1 : 0, $deliveryStatus, (int)$data['is_repost']);
        $this->db->query($sql, $params);

        $row = array(
            'campaign_id'     => $campaignId,
            'partner_id'      => $data['partner_id'],
            'delivery_status' => $deliveryStatus,
            'reject_reason'   => $data['reject_reason'],
            'is_repost'       => (int)$data['is_repost']
        );

        $hour = substr($now, 0, -5) . '00:00';
        $this->saveOneRowInShDelivery($row, $hour);

        $this->logToSummaryAceptance($data, $campaignId, $id);
    }
    //--------------------------------------------------------------------------


    private function saveOneRowInShDelivery(array $row, $hour) {
        $field = '';
        if ($row['delivery_status'] == self::STATUS_ACCEPTED) {
            $field = 'accept_count';
        } elseif ($row['delivery_status'] == self::STATUS_SKIPPED) {
            $field = 'skip_count';
        } else {
            if ($row['reject_reason'] == 'curl') {
                $field = 'curl_count';
            } elseif ($row['reject_reason'] == 'username') {
                $field = 'username_count';
            } else {
                $field = 'reject_count';
            }
        }

        $fieldsNames = array(
            self::STATUS_REJECTED => 'repost_reject_count',
            self::STATUS_ACCEPTED => 'repost_accept_count',
            self::STATUS_SKIPPED  => 'repost_skip_count',
        );

        if ($row['is_repost']) {
            $extraFields = ", `repost_count`, `{$fieldsNames[$row['delivery_status']]}`";
            $extraValues = ', 1, 1';
            $extraUpdate = ", `repost_count` = `repost_count` + 1, `{$fieldsNames[$row['delivery_status']]}` = `{$fieldsNames[$row['delivery_status']]}` + 1";
        } else {
            $extraFields = $extraValues = $extraUpdate = '';
        }

        $params = array((int)$row['campaign_id'], (int)$row['partner_id'], $hour);
        if ($field == 'username_count') {
            $sql = "INSERT INTO `$this->t_sh_delivery` (`campaign_id`, `partner_id`, `del_hour`, `reject_count`, `$field`$extraFields)
            VALUES (?, ?, ?, 1, 1$extraValues) ON DUPLICATE KEY UPDATE `$field` = `$field` + 1, `reject_count` = `reject_count` + 1$extraUpdate";
        } elseif ($field == 'curl_count') {
            $sql = "INSERT INTO `$this->t_sh_delivery` (`campaign_id`, `partner_id`, `del_hour`, `reject_count`, `$field`$extraFields)
            VALUES (?, ?, ?, 1, 1$extraValues) ON DUPLICATE KEY UPDATE `$field` = `$field` + 1, `reject_count` = `reject_count` + 1$extraUpdate";
        } else {
            $sql = "INSERT INTO `$this->t_sh_delivery` (`campaign_id`, `partner_id`, `del_hour`, `$field`$extraFields)
            VALUES (?, ?, ?, 1$extraValues) ON DUPLICATE KEY UPDATE `$field` = `$field` + 1$extraUpdate";
        }
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    private function logToSummaryAceptance(array $data, $campaignId, $id) {
        if ($data['reject_reason'] != 'skipped') {
            if ($data['is_accepted']) {
                $count = 0;
            } else {
                // $data['is_accepted'] was empty, so we're writing summary for a failed delivery.
                // Check if there was another failed one earlier and do not write this one to summary table if there was.
                $sql    = "SELECT COUNT(*) FROM `$this->tableName` WHERE `incoming_id` = ? AND `partner_id` = ? AND `is_accepted` = '0' AND `id` <> ?";
                $params = array((int)$data['incoming_id'], (int)$data['partner_id'], (int)$id);
                $count  = $this->db->getTopLeftInt($sql, $params);
            }
            if ($count == 0) {
                $this->saveOneRowInSummaryAcceptance($campaignId, (int)$data['partner_id'], (int)$data['incoming_id'], $id, $data['is_accepted']);
            }
        }
    }
    //--------------------------------------------------------------------------


    public function countOfSkippedDeliveries($incomingId, $partnerId, $campaignId) {
        $sql = "
        SELECT
            COUNT(*)
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            `ld`.`partner_id` = ?          AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ? AND
            `ld`.`reject_reason` = 'skipped'";

        return $this->db->getTopLeftInt($sql, array((int)$incomingId, (int)$partnerId, (int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function listDeliveriesForIncomingIdAndCampaignId($incomingId, $campaignId) {
        $sql = "
        SELECT
            `ld`.*
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ?
        ORDER BY `ld`.`id`";

        return $this->db->getArray($sql, array((int)$incomingId, (int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function countDeliveriesForPartner($partnerId) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->tableName` WHERE `partner_id` = ?", array((int)$partnerId));
    }
    //--------------------------------------------------------------------------


    public function countDeliveriesForIncomingIdAndCampaignId($incomingId, $campaignId) {
        $sql = "
        SELECT
            COUNT(*)
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ?";

        return $this->db->getTopLeftInt($sql, array((int)$incomingId, (int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function listUsedUpPartnerIds($incomingId, $campaignId) {
        $sql = "
        SELECT
            `partner_id`, `reject_reason`
        FROM
            `$this->t_log_incoming` AS `li`,
            `$this->t_log_delivery` AS `ld`
        LEFT JOIN
            `$this->t_log_delivery_campaigns` AS `lc` ON `lc`.`delivery_id` = `ld`.`id`
        WHERE
            `ld`.`incoming_id` = ?         AND
            `li`.`id` = `ld`.`incoming_id` AND
            IFNULL(`lc`.`campaign_id`, `li`.`campaign_id`) = ?
        ORDER BY `partner_id`";
        $arr = $this->db->getArray($sql, array((int)$incomingId, (int)$campaignId));

        $ids = array();
        foreach ($arr as $row) {
            if ($row['reject_reason'] != 'skipped') {
                $ids[] = $row['partner_id'];
            }
        }
        return array_unique($ids);
    }
    //--------------------------------------------------------------------------


    public function deleteDataOlderThan($days = 90, $chunk = 100) {
        $cutOff =  $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$days));
        $sql    = "SELECT `id`, `created_at` FROM `$this->tableName` ORDER BY `id` LIMIT ?, 1";
        $row    = $this->db->getTopArray($sql, array((int)$chunk));
        $result = false;

        if (isset($row['created_at']) && ($row['created_at'] < $cutOff)) {
            $this->db->query("DELETE FROM `$this->tableName` ORDER BY `id` LIMIT ?", array((int)$chunk));
        } else {
            $row = $this->db->getTopArray("SELECT `id`, `created_at` FROM `$this->tableName` ORDER BY `id` LIMIT 1");
            if (!(isset($row['created_at']) && ($row['created_at'] >= $cutOff))) {
                $pids = $this->db->getArray1v("SELECT `id` FROM `$this->t_partners` ORDER BY 1");
                foreach ($pids as $pid) {
                    $this->db->query("DELETE FROM `$this->tableName` WHERE `partner_id` = $pid AND `is_accepted` = '0' AND `created_at` < ?", array($cutOff));
                    $this->db->query("DELETE FROM `$this->tableName` WHERE `partner_id` = $pid AND `is_accepted` = '1' AND `created_at` < ?", array($cutOff));
                }
            }
            $result = true;
        }

        return $result;
    }
    //--------------------------------------------------------------------------


    public function deleteSummaryDataOlderThan($days = 90) {
        $this->db->query("DELETE FROM `$this->t_sh_delivery` WHERE `del_hour`    < DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$days));
        $this->db->query("DELETE FROM `$this->t_sh_incoming` WHERE `inc_hour`    < DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$days));
        $this->db->query("DELETE FROM `$this->t_sh_repost`   WHERE `repost_hour` < DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$days));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Maintains partitions in api_report_small table.
     * Drop unused partitions, truncate ones older than $days days and create new ones.
     * This function should be called once a day.
     *
     * @param int $days Days of api_report_small history we are keeping
     */
    public function dailyApiReportSmallPartitionMaintenance($days = 90) {
        $today = $this->db->getTopLeftInt("SELECT TO_DAYS(CURDATE())") - self::DAY_ID_CUTOFF;
        $arr   = $this->db->getTopArray("SHOW CREATE TABLE `$this->t_api_report_small`", array(), MYSQL_NUM);

        preg_match_all('/PARTITION p([\\d]{1,}) VALUES LESS THAN \\([\\d]{14,}\\)/', $arr[1], $matches);
        $existing = $matches[1];
        $minPartition = isset($existing[0]) ? intval($existing[0]) : 0;

        foreach ($existing as $i) {
            if ($i == 0) {
                continue;
            }
            if ($i < ($today - $days - 2)) {
                $this->db->query("ALTER TABLE `$this->t_api_report_small` DROP PARTITION p$i");
            } elseif ($i < ($today - $days - 1)) {
                $this->db->query("ALTER TABLE `$this->t_api_report_small` TRUNCATE PARTITION p$i");
            }
        }

        for ($i = $today - $days - 2; $i < $today + 10; $i++) {
            if (!in_array($i, $existing) && $i > $minPartition) {
                $day = $i;
                $cutOffDid = ($day * 24 - 3) . '0000000000000';
                $this->db->query("ALTER TABLE `$this->t_api_report_small` ADD PARTITION (PARTITION p$day VALUES LESS THAN ($cutOffDid))");
            }
        }

        $did = $this->db->getTopLeft("SELECT MIN(`delivery_id`) FROM `$this->t_api_report_small`");
        $apiReportIncId = (int)substr($did, -13);
        for(;;) {
            $this->db->query("DELETE FROM `$this->t_api_report_inc` WHERE `id` < $apiReportIncId LIMIT 50000");
            if ($this->db->affectedRows() == 0) {
                break;
            }
            usleep(50000);
        }
    }
    //--------------------------------------------------------------------------


    public function getMinId() {
        return $this->db->getTopLeftInt("SELECT MIN(`id`) FROM `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function purgeApiReportDataForMissingPC($start, $timeLimit) {
        try {
            // PHP has no try-finally, so I am using good old C++ style RAII locking.
            // Destructor will release the lock whether there were exceptions or not
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/purge-job.lock', 0);
        } catch (EServerError $e) {
            return;
        }

        $partnerIDs  = $this->db->getArray1v("SELECT `id` FROM `$this->t_partners`  WHERE `user_id` IS NOT NULL ORDER BY `id`");
        $campaignIDs = $this->db->getArray1v("SELECT `id` FROM `$this->t_campaigns` WHERE `user_id` IS NOT NULL ORDER BY `id`");
        $userIDs     = $this->db->getArray1v("SELECT `id` FROM `$this->t_users`     WHERE `id` <> 2 ORDER BY `id`");

        $this->purgeFromApiReportSmall($start, $timeLimit, $partnerIDs, $campaignIDs, $userIDs);
        $lock->release();
    }
    //--------------------------------------------------------------------------


    private function purgeFromApiReportSmall($start, $timeLimit, array $partnerIDs, array $campaignIDs, array $userIDs) {
        $toPurgePartnerIds  = $this->getIdsFiltered($this->db->getTopLeftInt("SELECT MAX(`partner_id`)   FROM `$this->t_api_report_small`"), $partnerIDs);
        $toPurgeCampaignIds = $this->getIdsFiltered($this->db->getTopLeftInt("SELECT MAX(`campaign_id`)  FROM `$this->t_api_report_small`"), $campaignIDs);
        $toPurgeUserIds     = $this->getIdsFiltered($this->db->getTopLeftInt("SELECT MAX(`user_id`)      FROM `$this->t_api_report_small`"), $userIDs);

        do {
            if (!$toPurgePartnerIds && !$toPurgeCampaignIds && !$toPurgeUserIds) {
                break;
            }

            $this->deleteFromApiReportSmall($toPurgeUserIds,     'user_id',     $start, $timeLimit);
            $this->deleteFromApiReportSmall($toPurgePartnerIds,  'partner_id',  $start, $timeLimit);
            $this->deleteFromApiReportSmall($toPurgeCampaignIds, 'campaign_id', $start, $timeLimit);

        } while ((time() - $start) < ($timeLimit - 20));
    }
    //--------------------------------------------------------------------------


    private function getIdsFiltered($maxId, array $excluedIDs) {
        $ids = array();
        for($i = 1; $i <= $maxId; $i++) {
            if (!in_array($i, $excluedIDs)) {
                $ids[$i] = 1;
            }
        }
        return $ids;
    }
    //--------------------------------------------------------------------------


    private function deleteFromApiReportSmall(array & $ids, $matchField, $start, $timeLimit) {
        foreach ($ids as $id => $x) {
            if (!$id) {
                continue;
            }
            $this->db->query("DELETE FROM `$this->t_api_report_small` WHERE `$matchField` = ? ORDER BY `delivery_id` DESC LIMIT 5000", array((int)$id));
            if ($this->db->affectedRows() == 0) {
                unset($ids[$id]);
            } else {
                usleep(100000);
            }
            if ((time() - $start) > ($timeLimit - 20)) {
                break;
            }
        }
    }
    //--------------------------------------------------------------------------


    public function reassignCampaignRelatedTables($campaignId, $oldUserId, $newUserId, $cutoffTime) {
        $campaignId = (int)$campaignId;
        $oldUserId  = (int)$oldUserId;
        $newUserId  = (int)$newUserId;

        $queriesToRun = array(
            $this->db->processParams("UPDATE `$this->t_api_report_small` SET `user_id` = ? WHERE `user_id` = ? AND `campaign_id` = ?",
                array($newUserId, $oldUserId, $campaignId)
            ),
            $this->db->processParams("UPDATE `$this->t_log_incoming` SET `user_id` = ? WHERE `campaign_id` = ?",
                array($newUserId, $campaignId)
            ),
            $this->db->processParams("UPDATE `$this->t_repost_queue` SET `user_id` = ? WHERE `catchup_id` IS NULL AND `campaign_id` = ?",
                array($newUserId, $campaignId)
            ),
            $this->db->processParams("UPDATE `$this->t_repost_queue_mem` SET `user_id` = ? WHERE `catchup_id` IS NULL AND `campaign_id` = ?",
                array($newUserId, $campaignId)
            )
        );

        for(;;) {
            if (empty($queriesToRun) || (time() >= $cutoffTime)) {
                break;
            }

            foreach ($queriesToRun as $index => $query) {
                $this->db->query("$query LIMIT 10000");
                if (time() >= $cutoffTime) {
                    break 2;
                }

                if (!$this->db->affectedRows()) {
                    unset($queriesToRun[$index]);
                }
            }
        }

        return empty($queriesToRun);
    }
    //--------------------------------------------------------------------------


    public function purgeDeletedCampaigns($start, $timeLimit) {
        $deletedCampaignIDs = $this->db->getArray1v("SELECT `id` FROM `$this->t_campaigns` WHERE `user_id` IS NULL ORDER BY 1");
        if (!$deletedCampaignIDs) {
            return;
        }

        try {
            // PHP has no try-finally, so I am using good old C++ style RAII locking.
            // Destructor will release the lock whether there were exceptions or not
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/purge-deleted-campaigns-job.lock', 0);
        } catch (EServerError $e) {
            return;
        }

        do {
            if (!$deletedCampaignIDs) {
                $lock->release();
                return;
            }

            foreach ($deletedCampaignIDs as $index => $campaignId) {
                $sql = "DELETE FROM `$this->t_log_incoming` WHERE `campaign_id` = ? LIMIT 1000";
                $this->db->query($sql, array((int)$campaignId));

                // If nothing was deleted
                if ($this->db->affectedRows() == 0) {
                    unset($deletedCampaignIDs[$index]);
                    $this->db->query("DELETE FROM `$this->t_campaigns` WHERE `id` = ?", array((int)$campaignId));
                }

                if ((time() - $start) > ($timeLimit - 20)) {
                    break;
                }
            }
        } while ((time() - $start) < ($timeLimit - 20));

        $lock->release();
    }
    //--------------------------------------------------------------------------


    public function purgeDeletedPartners($start, $timeLimit) {
        $deletedPartnerIDs = $this->db->getArray1v("SELECT `id` FROM `$this->t_partners` WHERE `user_id` IS NULL ORDER BY 1");
        if (!$deletedPartnerIDs) {
            return;
        }
        try {
            // PHP has no try-finally, so I am using good old C++ style RAII locking.
            // Destructor will release the lock whether there were exceptions or not
            $lock = new MySQLLocker(Config::$lockPrefix . __CLASS__  . '/purge-deleted-partners-job.lock', 0);
        } catch (EServerError $e) {
            return;
        }

        do {
            if (!$deletedPartnerIDs) {
                $lock->release();
                return;
            }

            foreach ($deletedPartnerIDs as $index => $partnerId) {
                $sql = "DELETE FROM `$this->t_log_delivery` WHERE `partner_id` = ? LIMIT 1000";
                $this->db->query($sql, array((int)$partnerId));

                // If nothing was deleted
                if (!$this->db->affectedRows()) {
                    unset($deletedPartnerIDs[$index]);
                    $this->db->query("DELETE FROM `$this->t_partners` WHERE `id` = ?", array((int)$partnerId));
                }

                if ((time() - $start) > ($timeLimit - 20)) {
                    break;
                }
            }

        } while ((time() - $start) < ($timeLimit - 20));
        $lock->release();
    }
    //--------------------------------------------------------------------------


    public function logDeliveryTemplateSent($deliveryId, $textSent) {
        $sql    = "INSERT INTO `$this->t_log_delivery_templates_sent` (`id`, `sent`) VALUES (?, ?)";
        $params = array((int)$deliveryId, $textSent);

        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function getTemplateTextSent($deliveryId) {
        return $this->db->getTopLeft("SELECT `sent` FROM `$this->t_log_delivery_templates_sent` WHERE `id` = ?", array((int)$deliveryId));
    }
    //--------------------------------------------------------------------------


    public function updatePartnerResponseTime($partnerId, $duration) {
        $sql = "INSERT INTO `$this->t_sh_response_times` (`day`, `partner_id`, `response_count`, `time_combined`) VALUES (CURDATE(), ?, 1, ?)
        ON DUPLICATE KEY UPDATE `response_count` = `response_count` + 1, `time_combined` = `time_combined` + ?";
        $responseTime = round($duration, 4);
        $params = array((int)$partnerId, $responseTime, $responseTime);

        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function updatePartnerTimeoutCount($partnerId) {
        $sql = "INSERT INTO `$this->t_sh_response_times` (`day`, `partner_id`, `timeout_count`) VALUES (CURDATE(), ?, 1)
        ON DUPLICATE KEY UPDATE `timeout_count` = `timeout_count` + 1";
        $params = array((int)$partnerId);

        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listPartnerResponseTimesAndTimeouts() {
        $sql = "
        SELECT `partner_id`, SUM(`response_count`) AS `sum_rc`, SUM(`time_combined`) AS `sum_rt`, SUM(`timeout_count`) AS `sum_ti`,
        IF (SUM(`response_count`) = 0, 0, SUM(`time_combined`) / SUM(`response_count`)) AS `avg`
        FROM `$this->t_sh_response_times` AS `rt`, `$this->t_partners` AS `p`
        WHERE `p`.`id` = `rt`.`partner_id`
        GROUP BY `partner_id`
        ORDER BY `avg` DESC";
        return $this->db->getArrayIndexed($sql, array(), 'partner_id', MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    private function saveOneRowInSummaryAcceptance($campaignId, $partnerId, $incomingId, $deliveryId, $isAccepted) {
        $sql = "INSERT INTO `$this->t_summary_acceptance` (`campaign_id`, `partner_id`, `incoming_id`, `last_delivery_id`, `is_accepted`, `time_stamp`) VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE `last_delivery_id` = ?, `is_accepted` = ?, `time_stamp` = NOW()";
        $params = array((int)$campaignId, (int)$partnerId, (int)$incomingId, (int)$deliveryId, $isAccepted ? '1' : '0', (int)$deliveryId, $isAccepted ? '1' : '0');
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------
}
