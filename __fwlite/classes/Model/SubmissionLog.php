<?php

class Model_SubmissionLog extends CrudModel {
    protected $t_submission_log;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_submission_log);

        $this->timestampForInsert = array('request_time');
        $this->zeroOneFields      = array('is_post');
    }
    //--------------------------------------------------------------------------
}
