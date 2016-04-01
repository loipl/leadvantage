<?php

class Model_LogIncomingDuplication extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_log_incoming_duplication;

    protected $t_log_incoming_values_duplication;
    
    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_incoming_duplication);
        $this->timestampForInsert = array('request_time');
    }
    //--------------------------------------------------------------------------
    
    
    public function listRecent($campaignId, $email, $isSuccess, $timeoutSeconds) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE `campaign_id` = ? AND `is_success` = ? AND `email` = ? AND
        `request_time` > DATE_SUB(NOW(), INTERVAL ? SECOND) ORDER BY `request_time`";

        return $this->db->getArray1v($sql, array((int)$campaignId, $isSuccess ? '1' : '0', $email, (int)$timeoutSeconds));
    }
    //--------------------------------------------------------------------------
    
    
    public function updateDuplicationIncomingId($id, $incomingId) {
        return $this->db->query("UPDATE `$this->tableName` SET `incoming_id` = ? WHERE `id` = ?", array((int)$incomingId, (int)$id));
    }
    //--------------------------------------------------------------------------
    
    
    public function insertSubmissionValues($logId, array $valuesAssoc) {
        if (!$valuesAssoc) {
            return;
        }
        $logId = (int)$logId;
        $sql = "INSERT INTO `$this->t_log_incoming_values_duplication` (`id`, `name`, `value`) VALUES\n\t";
        $sql .= implode(",\n\t", array_fill(0, sizeof($valuesAssoc), "($logId, ?, ?)"));

        $params = array();
        foreach ($valuesAssoc as $fieldName => $value) {
            $params[] = $fieldName;
            $params[] = $value;
        }
        return $this->db->query($sql, $params);
    }
    //--------------------------------------------------------------------------
    
    
    public function getRealIncomingId($id) {
        $data = $this->get($id);
        return $data['incoming_id'];
    }
    //--------------------------------------------------------------------------
    
    
    public function listSubmissionValuesGroup(array $ids) {
        if (!$ids) {
            return array();
        }
        return $this->db->getArrayAssoc("SELECT `id`, `name`, `value` FROM `$this->t_log_incoming_values_duplication` WHERE `id` IN (" . self::implodeInts($ids) . ')');
    }
    //--------------------------------------------------------------------------
    
    
    public function purgeOldDuplicationRecords() {
        return $this->db->query("DELETE FROM `$this->tableName` WHERE `request_time` < DATE_SUB(NOW(), INTERVAL 65 MINUTE)");
    }
    //--------------------------------------------------------------------------
}
