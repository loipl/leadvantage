<?php

class Model_NotificationKeyed extends CrudModel {

    protected $t_notifications_keyed;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_notifications_keyed);
        $this->timestampForInsert = array('created_at');
    }
    //--------------------------------------------------------------------------


    public function add($userId, $key, $text) {
        $data = array('user_id' => (int)$userId, 'key' => $key, 'content' => $text);
        return $this->insert($data, true);
    }
    //--------------------------------------------------------------------------
}
