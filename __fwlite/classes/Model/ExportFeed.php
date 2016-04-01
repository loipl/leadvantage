<?php

class Model_ExportFeed extends CrudModel {

    protected $t_export_feeds;
    protected $t_log_incoming;
    protected $t_log_incoming_values;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_export_feeds);

        $this->timestampForInsert = array('create_time');
        $this->zeroOneFields      = array('is_active');
    }
    //--------------------------------------------------------------------------


    public function listIncomingRowsFor_MysqlRes($campaignId, $days, $delay) {
        $dateTo   = $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$delay));
        $dateFrom = $this->db->getTopLeft("SELECT DATE_SUB(CURDATE(), INTERVAL ? DAY)", array((int)$delay + (int)$days - 1));

        $dateTo   = substr($dateTo, 0, 10)   . ' 23:59:59';
        $dateFrom = substr($dateFrom, 0, 10) . ' 00:00:00';

        $sql = "SELECT `id`, `request_time`, `campaign_id`, `email`, `remote_ip`, `http_referer` FROM `$this->t_log_incoming` WHERE
        `campaign_id`  =  ? AND
        `is_success`   = '1' AND
        `request_time` >= '$dateFrom' AND
        `request_time` <= '$dateTo'
        ORDER BY `id` ";

        return $this->db->query($sql, array((int)$campaignId));
    }
    //--------------------------------------------------------------------------
}
