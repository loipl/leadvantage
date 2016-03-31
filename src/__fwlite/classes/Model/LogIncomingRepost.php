<?php

class Model_LogIncomingRepost extends CrudModel {

    protected $t_campaigns;
    protected $t_catchup_jobs;
    protected $t_sh_catchup;
    protected $t_sh_repost;
    protected $t_log_incoming_reposts;
    protected $t_log_incoming;

    protected $t_repost_kill_switch;
    protected $t_repost_log;
    protected $t_repost_queue;
    protected $t_repost_queue_mem;
    protected $t_repost_queue_temp;
    protected $t_repost_threads;

    const MINUTE_SCALE = 10;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_incoming_reposts);

        $this->timestampForInsert[] = 'repost_time';
    }
    //--------------------------------------------------------------------------


    public function log(array $data) {
        $campaignId = isset($data['campaign_id']) ? (int)$data['campaign_id'] : 0;
        $jobId      = isset($data['job_id'])      ? (int)$data['job_id'] : 0;
        unset($data['campaign_id']);
        unset($data['job_id']);

        if ($campaignId && !$jobId) {
            $sql = "INSERT INTO `$this->t_sh_repost` (`campaign_id`, `repost_hour`, `repost_count`) VALUES (?, FROM_UNIXTIME(UNIX_TIMESTAMP() - UNIX_TIMESTAMP() MOD 60), 1) ON DUPLICATE KEY UPDATE
            `repost_count` = `repost_count` + 1";
            $this->db->query($sql, array((int)$campaignId));
        }

        $params = array();
        $condition = '';
        if ($campaignId) {
            $condition .= "`campaign_id` = ? AND ";
            $params[] = $campaignId;
        }
        if ($jobId) {
            $condition .= "`catchup_id` = ? AND ";
            $params[] = $jobId;
        } else {
            $condition .= "`catchup_id` IS NULL AND ";
        }

        $params[] = (int)$data['incoming_id'];
        $this->db->query("DELETE FROM `$this->t_repost_queue` WHERE $condition`incoming_id` = ? AND `remaining_count` = 0 ", $params);

        $this->db->query("UPDATE `$this->t_repost_queue` SET
                        `remaining_count` = `remaining_count` - 1,
                        `repost_at`       = DATE_ADD(NOW(), INTERVAL `interval_hours` HOUR)
                        WHERE $condition`incoming_id` = ? AND `remaining_count` > 0", $params);
    }
    //--------------------------------------------------------------------------


    public function campaignRepostCount($campaignId, $intervalType, $now = false) {
        $scale = 0;
        $upToTimestamp = ($now === false) ? 'NOW()' : "'$now'";

        switch ($intervalType) {
            case -1:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 MINUTE)";
                break;
            case Model_Partner::DELIVERY_CTYPE_MINUTE:
                $scale = self::MINUTE_SCALE;
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL $scale MINUTE)";
                break;
            case Model_Partner::DELIVERY_CTYPE_HOUR:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 HOUR)";
                break;
            case Model_Partner::DELIVERY_CTYPE_DAY:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 DAY)";
                break;
            case Model_Partner::DELIVERY_CTYPE_WEEK:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 WEEK)";
                break;
            case Model_Partner::DELIVERY_CTYPE_MONTH:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 MONTH)";
                break;
            case Model_Partner::DELIVERY_CTYPE_EVER:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 YEAR)";
                break;
            default:
                return 0;
        }
        $sql = "SELECT SUM(`repost_count`) FROM `$this->t_sh_repost` WHERE `campaign_id` = ? $condition";
        $count = $this->db->getTopLeftInt($sql, array((int)$campaignId));
        if ($scale) {
            $count = (int)($count / $scale);
        }
        return $count;
    }
    //--------------------------------------------------------------------------


    public function catchupRepostCount($catchupId, $intervalType, $now = false) {
        $scale = 0;
        $upToTimestamp = ($now === false) ? 'NOW()' : "'$now'";
        switch ($intervalType) {
            case -1:
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL 1 MINUTE)";
                break;
            case Model_Partner::DELIVERY_CTYPE_MINUTE:
                $scale = self::MINUTE_SCALE;
                $condition = "AND `repost_hour` >= DATE_SUB($upToTimestamp, INTERVAL $scale MINUTE)";
                break;
            case Model_Partner::DELIVERY_CTYPE_HOUR:
            case Model_Partner::DELIVERY_CTYPE_DAY:
            case Model_Partner::DELIVERY_CTYPE_WEEK:
            case Model_Partner::DELIVERY_CTYPE_MONTH:
            case Model_Partner::DELIVERY_CTYPE_EVER:
            case Model_Partner::DELIVERY_CTYPE_HOUR_CALENDAR:
            case Model_Partner::DELIVERY_CTYPE_DAY_CALENDAR:
            case Model_Partner::DELIVERY_CTYPE_WEEK_CALENDAR:
            case Model_Partner::DELIVERY_CTYPE_MONTH_CALENDAR:
                $condition = " AND `repost_hour` >= " . Model_Partner::getTimeFrameStart($intervalType, $upToTimestamp);
                break;
            default:
                return 0;
        }
        $sql = "SELECT SUM(`catchup_count`) FROM `$this->t_sh_catchup` WHERE `catchup_id` = ? $condition";
        $count = $this->db->getTopLeftInt($sql, array((int)$catchupId));
        if ($scale) {
            $count = (int)($count / $scale);
        }
        return $count;
    }
    //--------------------------------------------------------------------------


    public function deleteShRepostForCampaign($campaignId) {
        return $this->db->query("DELETE FROM `$this->t_sh_repost` WHERE `campaign_id` = ?", array((int)$campaignId));
    }
    //--------------------------------------------------------------------------


    public function postponeRepostQueueEntry($incomingId, $campaignId, $byHours, $catchupId) {
        if ($catchupId) {
            $sql = "UPDATE `$this->t_repost_queue` SET `repost_at` = DATE_ADD(NOW(), INTERVAL ? HOUR) WHERE `campaign_id` = ? AND `catchup_id` = ? AND `incoming_id` = ? ORDER BY `repost_at`";
            $this->db->query($sql, array((int)$byHours, (int)$campaignId, (int)$catchupId, (int)$incomingId));
        } else {
            $sql = "UPDATE `$this->t_repost_queue` SET `repost_at` = DATE_ADD(NOW(), INTERVAL ? HOUR) WHERE `campaign_id` = ? AND `incoming_id` = ? AND `catchup_id` IS NULL ORDER BY `repost_at`";
            $this->db->query($sql, array((int)$byHours, (int)$campaignId, (int)$incomingId));
        }
    }
    //--------------------------------------------------------------------------


    public function listRepostEntriesFromSubsetTable($limit, $now) {
        $sql = "SELECT `incoming_id` AS `id`, `campaign_id`, `catchup_id`, `repost_at`, `upload_file`, `id` AS `orig_id` FROM `$this->t_repost_queue_mem` AS `s`
        WHERE `s`.`repost_at` <= ? AND `is_taken` = '0' ORDER BY `s`.`id` LIMIT ?";
        $params = array($now, (int)$limit);
        return $this->db->getArray($sql, $params, MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function lockRepostEntriesFromSubsetTable(array $ids, $lock = true) {
        if (!$ids) {
            return;
        }
        $s = $lock ? 1 : 0;
        $now = time();
        $sql = "UPDATE `$this->t_repost_queue_mem` SET `is_taken` = '$s', `reserve_timestamp` = $now WHERE `id` IN (" . implode(', ', $ids) . ')';
        return $this->db->query($sql);
    }
    //--------------------------------------------------------------------------


    public function deleteLockedEntriesLockedBefore($unixTimestamp) {
        $sql = "DELETE FROM `$this->t_repost_queue_mem` WHERE `is_taken` = '1' AND `reserve_timestamp` <= $unixTimestamp";
        $this->db->query($sql);
    }
    //--------------------------------------------------------------------------


    public function sizeOfRepostQueueSubset() {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_repost_queue_mem` WHERE `is_taken` = '0'");
    }
    //--------------------------------------------------------------------------


    public function deleteOneRowFromRepostQueueSubsetById($id) {
        $this->db->query("DELETE FROM `$this->t_repost_queue_mem` WHERE `id` = " . (int)$id);
    }
    //--------------------------------------------------------------------------


    public function deleteOneRowFromRepostQueueSubset($incomingId, $campaignId, $jobId) {
        $sql = "DELETE FROM `$this->t_repost_queue_mem` WHERE `incoming_id` = ? AND `campaign_id` = ? AND `catchup_id`";
        $params = array((int)$incomingId, (int)$campaignId);
        if ($jobId) {
            $sql .= " = ?";
            $params[] = (int)$jobId;
        } else {
            $sql .= " IS NULL";
        }
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function deleteRowsFromRepostQueueMem(array $ids) {
        if ($ids) {
            $sql = "DELETE FROM `$this->t_repost_queue_mem` WHERE `id` IN (".  self::implodeInts($ids) . ')';
            $this->db->query($sql);
        }
    }
    //--------------------------------------------------------------------------


    public function startNewRepostThread($ip) {
        $sql = "INSERT INTO `$this->t_repost_threads` (`machine_ip`, `start_time`, `activity_log`) VALUES(?, NOW(), CONCAT(NOW(), ' - Start'))";
        $this->db->query($sql, array($ip));
        return $this->db->lastInsertId();
    }
    //--------------------------------------------------------------------------


    public function updateRepostThreadActivityLog($threadId, $activityLog) {
        $this->db->query("UPDATE `$this->t_repost_threads` SET `activity_log` = CONCAT(`activity_log`, '\n', NOW(), ' - ', ?) WHERE `id` = ?", array($activityLog, (int)$threadId));
    }
    //--------------------------------------------------------------------------


    public function setEndTimeForRepostThread($threadId) {
        $this->db->query("UPDATE `$this->t_repost_threads` SET `end_time` = NOW() WHERE `id` = ?", array((int)$threadId));
    }
    //--------------------------------------------------------------------------


    public function logToRepostLog(array $data) {
        $sql = "INSERT INTO `$this->t_repost_log` (`thread_id`, `delivery_id`, `incoming_id`, `campaign_id`, `partner_id`, `user_id`, `attempt_type`, `result`, `del_time`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $params = array(
            (int)$data['thread_id'],
            (int)$data['delivery_id'],
            (int)$data['incoming_id'],
            (int)$data['campaign_id'],
            (int)$data['partner_id'],
            (int)$data['user_id'],
            (int)$data['attempt_type'],
            (int)$data['result']
        );
        $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------


    public function listRepostQueueBacklog($now) {
        $sql = "SELECT `campaign_id`, `catchup_id`, COUNT(*) AS `size`, TIMEDIFF(?, MIN(`repost_at`)) AS `delay`
            FROM `$this->t_repost_queue` WHERE `repost_at` <= ? GROUP BY `campaign_id`, `catchup_id` HAVING `delay` > '00:00:00' ORDER BY `user_id`, `campaign_id`, `catchup_id` ";
        return $this->db->getArray($sql, array($now, $now), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function getAverageOutboundRepostCount($lastMinutes = 20) {
        $sql = "SELECT SUM(`repost_count`) / $lastMinutes FROM `$this->t_sh_repost` WHERE `repost_hour` >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        return $this->db->getTopLeftInt($sql, array((int)$lastMinutes));
    }
    //--------------------------------------------------------------------------


    public function repostKillSwitchOn($ipAddress) {
        return $this->db->getTopLeftInt("SELECT COUNT(*) FROM `$this->t_repost_kill_switch` WHERE `ip_address` = ?", array($ipAddress));
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns array(campaign_id , catchup_id, count, user_id). Either all
     * or up to some repost_at timestamp. Param $subset controls if reading from
     * repost_queue_subset or "real" repost_queue. Only works for active campaigns
     * that are not in the process of being deleted (when user_id would be NULL)
     */
    public function listCampaignAndCatchupCounts($upToTimestamp = false) {
        $where = ($upToTimestamp === false) ? '' : " WHERE `repost_at` <= '$upToTimestamp'";

        $subQuery = "SELECT `campaign_id`, `catchup_id`, COUNT(*) AS `count` FROM `$this->t_repost_queue`$where GROUP BY `campaign_id`, `catchup_id` ORDER BY 1, 2";
        $sql = "SELECT `counts`.*, `c`.`user_id` FROM ($subQuery) AS `counts`, `$this->t_campaigns` AS `c` WHERE
        `c`.`id` = `counts`.`campaign_id` AND
        `c`.`is_active` = '1' AND
        `c`.`user_id` IS NOT NULL";
        return $this->db->getArray($sql, array(), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------


    public function createTempTableForRepostSubset() {
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS `$this->t_repost_queue_temp`");
        $sql = "
CREATE TEMPORARY TABLE `$this->t_repost_queue_temp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `incoming_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `catchup_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `repost_at` datetime NOT NULL,
  `upload_file` varchar(100) DEFAULT NULL,
  `row_key` char(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incoming_id` (`incoming_id`,`campaign_id`,`catchup_id`),
  KEY `row_key` (`row_key`,`id`)
);";
        $this->db->query($sql);
    }
    //--------------------------------------------------------------------------


    public function addDataToTempRepostSubsetTable(array $data, $upToTimestamp) {
        foreach ($data as $row) {
            $params = array((int)$row['campaign_id'], $row['catchup_id'] ? (int)$row['catchup_id'] : null, (int)$row['campaign_id']);
            $sql = "
          SELECT
            `incoming_id`,
            ? AS `campaign_id`,
            ? AS `catchup_id`,
            `user_id`,
            `repost_at`,
            `upload_file`,
            CONCAT(`campaign_id`, '_', IFNULL(`catchup_id`, 0)) AS `row_key`
         FROM `$this->t_repost_queue`
         WHERE
            `campaign_id` = ? AND
            `catchup_id`";

            if ($row['catchup_id']) {
                $sql .= " = ?";
                $params[] = (int)$row['catchup_id'];
            } else {
                $sql .=" IS NULL";
            }

            $sql .= " AND
            `repost_at` <= ?
         ORDER BY
            `repost_at`
         LIMIT ?";
            $params[] = $upToTimestamp;
            $params[] = (int)$row['count'];

            $sql = "INSERT INTO `$this->t_repost_queue_temp` (`incoming_id`, `campaign_id`, `catchup_id`, `user_id`, `repost_at`, `upload_file`, `row_key`) $sql";
            $this->db->query($sql, $params);
        }
    }
    //--------------------------------------------------------------------------


    public function pushTempRepostToSubset() {
        $sql  = "SELECT `incoming_id`, `campaign_id`, `catchup_id`, `user_id`, `repost_at`, `upload_file`, `row_key`
        FROM `$this->t_repost_queue_temp` ORDER BY `user_id`, `row_key`, `id`";

        $data = $this->db->getArray($sql, array(), MYSQL_ASSOC);
        if (!$data) {
            return;
        }

        $userRows = array();
        foreach ($data as $row) {
            $userRows[$row['user_id']][$row['row_key']][]= $row;
        }

        $result = self::getValuesForInsertDistributedPerUserAndCampaign($userRows);
        if (!$result) {
            // Sanity check - if there was anything in $data above there should be in $result as well
            return;
        }

        $this->db->query("TRUNCATE TABLE `$this->t_repost_queue_temp`");
        $sql = "INSERT INTO `$this->t_repost_queue_temp` (`incoming_id`, `campaign_id`, `catchup_id`, `user_id`, `repost_at`, `upload_file`, `row_key`) VALUES ";
        $this->db->query($sql . implode(",\n", $result));

        $result = $this->db->query("LOCK TABLES `$this->t_repost_queue_mem` WRITE, `$this->t_repost_queue_temp` WRITE");
        if (!$result) {
            return;
        }
        $sql = "
        INSERT INTO `$this->t_repost_queue_mem` (`incoming_id`, `campaign_id`, `catchup_id`, `repost_at`, `upload_file`, `user_id`)
        SELECT `incoming_id`, `campaign_id`, `catchup_id`, `repost_at`, `upload_file`, `user_id` FROM `$this->t_repost_queue_temp` ORDER BY `id`
        ON DUPLICATE KEY UPDATE `repost_at` = `$this->t_repost_queue_mem`.`repost_at`";
        $this->db->query($sql);
        $this->db->query("UNLOCK TABLES");
    }
    //--------------------------------------------------------------------------


    public static function getValuesForInsertDistributedPerUserAndCampaign(array $userRows) {
        if (!$userRows) {
            return array();
        }

        foreach ($userRows as $userId => $keysSubArrays) {

            $result = array();
            for(;;) {
                $found = false;
                foreach ($keysSubArrays as $key => & $rows) {
                    if (!$rows) {
                        unset($keysSubArrays[$key]);
                    } else {
                        $oneRow    = array_shift($rows);
                        $catchupId = $oneRow['catchup_id'] ? $oneRow['catchup_id'] : 'NULL';
                        $result[]  = "($oneRow[incoming_id], $oneRow[campaign_id], $catchupId, $oneRow[user_id], '$oneRow[repost_at]', '$oneRow[upload_file]', '$oneRow[row_key]')";
                        $found = true;
                    }
                }
                if (!$found) {
                    break;
                }
            }
            if ($result) {
                $userRows[$userId] = $result;
            } else {
                unset($userRows[$userId]);
            }
        }

        $result = array();
        for(;;) {
            $found = false;
            foreach ($userRows as $userId => $rows) {
                if ($rows) {
                    $found = true;
                    $result[] = array_shift($userRows[$userId]);
                } else {
                    unset($userRows[$userId]);
                }
            }
            if (!$found) {
                break;
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------
}
