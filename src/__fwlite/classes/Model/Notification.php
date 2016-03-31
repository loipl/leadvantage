<?php

class Model_Notification extends CrudModel {
    protected $t_notifications;
    protected $t_notifications_keyed;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_notifications);
        $this->timestampForInsert = array('created_at');
    }
    //--------------------------------------------------------------------------


    public function add($userId, $text, $shouldEscape = true) {
        $data = array('user_id' => (int)$userId, 'content' => $text, 'should_escape' => $shouldEscape ? '1' : '0');
        return $this->insert($data);
    }
    //--------------------------------------------------------------------------


    public function listAndDelete($userId) {
        $sql = "SELECT `id`, `created_at`, `content`, `should_escape` FROM `$this->tableName` WHERE `user_id` = ? ORDER BY 2 DESC";
        $output = array();
        $arr = $this->db->getArray($sql, array((int)$userId));
        if ($arr) {
            $ids = array();
            foreach ($arr as $row) {
                $ids[] = (int)$row['id'];
                $output[] = array($row['created_at'], $row['content'], $row['should_escape']);
            }
            $this->db->query("DELETE FROM `$this->tableName` WHERE `id` IN (" . self::implodeInts($ids) . ')');
        }

        $sql = "SELECT `id`, `created_at`, `content` FROM `$this->t_notifications_keyed` WHERE `user_id` = ? ORDER BY 2 DESC";
        $arr = $this->db->getArray($sql, array((int)$userId));
        if ($arr) {
            $ids = array();
            foreach ($arr as $row) {
                $ids[] = (int)$row['id'];
                $output[] = array($row['created_at'], $row['content']);
            }
            $this->db->query("DELETE FROM `$this->t_notifications_keyed` WHERE `id` IN (" . self::implodeInts($ids) . ')');
        }
        return $output;
    }
    //--------------------------------------------------------------------------
}
