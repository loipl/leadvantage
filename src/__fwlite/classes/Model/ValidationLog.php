<?php

class Model_ValidationLog extends CrudModel {

    protected $t_validation_log;
    protected $t_validation_log_daily;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_validation_log);
    }
    //--------------------------------------------------------------------------


    public function log($campaignId, $sourceId, $isValid) {
        $field = $isValid ? 'success_count' : 'failure_count';
        $sql   = "INSERT INTO `$this->tableName` (`month_nr`, `campaign_id`, `source_id`, `$field`) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE `$field` = `$field` + 1";
        $this->db->query($sql, array((int)date('Ym'), (int)$campaignId, (int)$sourceId));

        $sql   = "INSERT INTO `$this->t_validation_log_daily` (`day_nr`, `campaign_id`, `source_id`, `$field`) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE `$field` = `$field` + 1";
        $this->db->query($sql, array((int)date('Ymd'), (int)$campaignId, (int)$sourceId));
    }
    //--------------------------------------------------------------------------

}
