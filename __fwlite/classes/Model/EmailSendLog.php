<?php

class Model_EmailSendLog extends CrudModel {

    protected $t_email_send_log;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_email_send_log);

        $this->timestampForInsert = array('send_time');
    }
    //--------------------------------------------------------------------------


    public function userHasMailTypeThisMonth($userId, $mailTypeId) {
        $sql = "SELECT COUNT(*) FROM `$this->tableName` WHERE `user_id` = ? AND `type` = ?";
        return $this->db->getTopLeftInt($sql, array((int)$userId, (int)$mailTypeId));
    }
    //--------------------------------------------------------------------------
}
