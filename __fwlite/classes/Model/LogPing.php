<?php

class Model_LogPing extends CrudModelCaching {
    
    const TYPE_AUCTION          = '1';
    const TYPE_OTHER            = '2';
    
    protected $db;

    protected $t_log_ping;
    
    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_log_ping);

        $this->timestampForInsert = array('created_at');
    }
    //--------------------------------------------------------------------------
    
    
    public function getPingDataByLeadID($leadID) {
        $sql = "SELECT * FROM `$this->tableName` WHERE `incoming_id` = ? AND `is_accepted` = '1'";

        $result = $this->db->getArray($sql, array((int)$leadID));
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    public function checkLeadIdExist($leadID) {
        $sql = "SELECT `incoming_id` FROM `$this->tableName` WHERE `incoming_id` = ? LIMIT 1";

        $result = $this->db->getArray($sql,array((int)$leadID));

        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function getBidPriceByIncomingIdAndPartner($incomingId, $partnerId) {
        $sql = "SELECT `price` FROM `$this->tableName` WHERE `incoming_id` = ? AND `partner_id` = ? LIMIT 1";
        $params = array($incomingId, $partnerId);

        return $this->db->getTopLeft($sql, $params);
    }
    //--------------------------------------------------------------------------
}

