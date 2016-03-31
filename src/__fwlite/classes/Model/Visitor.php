<?php

class Model_Visitor extends Model {
    protected $t_visitors;
    protected $tableName;

    /**
     * @var DB
     */
    protected $db;


    public function __construct(DB $db = null) {
        $this->db = ($db instanceof DB) ? $db : DB::$db;
        $this->setTablePrefix($this->db->getTablePrefix());
        $this->tableName = $this->t_visitors;
    }
    //--------------------------------------------------------------------------


    public function nextVisitorId() {
        if ($this->db->query("INSERT INTO `$this->tableName` (`id`) VALUES (NULL)")) {
            $id = $this->db->lastInsertId();
            $this->db->query("DELETE FROM `$this->tableName` WHERE `id` = ?", array($id));
            return $id;
        } else {
            return 0;
        }
    }
    //--------------------------------------------------------------------------
}