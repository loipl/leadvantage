<?php

class Model_SystemMessage extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_system_messages;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_system_messages);

        $this->timestampForInsert[] = 'msg_time';
        $this->zeroOneFields[] = 'should_escape';
    }
    //--------------------------------------------------------------------------


    public function lastMessageId() {
        return $this->db->getTopLeftInt("SELECT MAX(`id`) FROM `$this->tableName`");
    }
    //--------------------------------------------------------------------------


    public function listMessagesAfterId($lastMessageId, $userId) {
        return $this->db->getArray("SELECT * FROM `$this->tableName` WHERE (`id` > ? AND `user_id` IS NULL) OR `user_id` = ? ORDER BY `id` DESC", array((int)$lastMessageId, (int)$userId), MYSQL_ASSOC);
    }
    //--------------------------------------------------------------------------
}
