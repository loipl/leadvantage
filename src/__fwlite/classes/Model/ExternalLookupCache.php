<?php

class Model_ExternalLookupCache extends CrudModel {

    protected $t_external_lookup_cache;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_external_lookup_cache);

        $this->timestampForInsert = array('lookup_time');
    }
    //--------------------------------------------------------------------------


    public function cachedValueFor($engine, $key, $maxAgeInSeconds) {
        $sql = "SELECT `result`,`lookup_time` FROM `$this->tableName` WHERE `engine` = ? AND `key` = ?";

        return $this->db->getTopLeft($sql, array($engine, $key));
    }
    //--------------------------------------------------------------------------


    public function flushCacheEntries($daysAge = 30) {
        $this->db->query("DELETE FROM `$this->tableName` WHERE `lookup_time` < DATE_SUB(NOW(), INTERVAL ? DAY)", array((int)$daysAge));
    }
    //--------------------------------------------------------------------------


    public function flushRapleafCacheEntries($daysAge = 1) {
        $this->db->query("DELETE FROM `$this->tableName` WHERE `lookup_time` < DATE_SUB(NOW(), INTERVAL ? DAY) AND `engine` = ?", array((int)$daysAge,'Rapleaf'));
    }
    //--------------------------------------------------------------------------


    public function deleteEntry($engine, $email) {
        $sql = "DELETE FROM `$this->tableName` WHERE `key` LIKE ? ESCAPE '|' AND `engine` = ?";
        $key = urlencode($email);
        $key = str_replace('|', '||', $key);
        $key = 'email=' . str_replace('%', '|%', $key) . '%';
        $params = array($key, $engine);
        $this->db->query($sql, $params);
        return $this->db->affectedRows();
    }
    //--------------------------------------------------------------------------
}
