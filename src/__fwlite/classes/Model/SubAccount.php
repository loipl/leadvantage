<?php

class Model_SubAccount extends CrudModelCaching {
    
    const OBJECT_TYPE_CAMPAIGN = '1';
    const OBJECT_TYPE_PARTNER  = '2';
       
    /**
     * @var DB
     */
    protected $db;

    protected $t_sub_accounts;
    
    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_sub_accounts);
    }
    //--------------------------------------------------------------------------
    
    
    public function isUserWithSubUsers($userId) {
        $sql = "SELECT count(*) FROM `$this->tableName` WHERE `user_id` = ?";
        return ($this->db->getTopLeft($sql, array((int)$userId)) !== '0');
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfUserIsSubAccount($userId) {
        $sql = "SELECT * FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";
        return $this->db->getTopArray($sql,array($userId));
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokeReportingAccess($subAccountId) {
        $sql = "SELECT `revoke_reporting_access` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokeCampaignControl($subAccountId) {
        $sql = "SELECT `revoke_campaign_control` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokePartnerControl($subAccountId) {
        $sql = "SELECT `revoke_partner_control` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokeSuccessLeads($subAccountId) {
        $sql = "SELECT `revoke_success_leads` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokeFailedLeads($subAccountId) {
        $sql = "SELECT `revoke_failed_leads` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkIfRevokeSkippedLeads($subAccountId) {
        $sql = "SELECT `revoke_skipped_leads` FROM `$this->tableName` WHERE `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getTopLeft($sql,array($subAccountId));
        
        return intval($result);
    }
    //--------------------------------------------------------------------------
    
    
    public function checkSubAccountExist($userId, $subAccountId) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE `user_id` = ? AND `sub_account_id` = ? LIMIT 1";

        $result = $this->db->getArray($sql,array($userId, $subAccountId));

        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
    
    
    /**
     * @Cached
     */
    public function listAllSubAccounts($userId = null) {
        $this->setTableName('sub_accounts');
        
        $sql = "SELECT * FROM `$this->tableName` ";
        if (!empty($userId)) {
            $sql .= "WHERE user_id = ". $userId . " ";
        }
        $sql .= "ORDER BY `user_id` ASC, `id` DESC";
        return $this->db->getArray($sql);
    }
    //--------------------------------------------------------------------------
    
    public function countCampaignAndPartnerOfSubAccount($subAccountId) {
        $sql = "SELECT count(*) "
                . "FROM `sub_accounts_rights` "
                . "WHERE sub_account_id = ? "
                . "AND object_type = ?";
        $campaignCount = $this->db->getTopLeft($sql, array($subAccountId, self::OBJECT_TYPE_CAMPAIGN));
        
        $sql .= " AND object_name NOT LIKE '%(post)'";
        $partnerCount  = $this->db->getTopLeft($sql, array($subAccountId, self::OBJECT_TYPE_PARTNER));
        return array ($campaignCount, $partnerCount);
    }
    //--------------------------------------------------------------------------
    
    
    public function updateSubAccountPermission($accountId, $revokeReporting, $revokeCampaign, $revokePartner, $revokeSuccessLeads, $revokeFailedLeads, $revokeSkippedLeads) {
        $sql = "UPDATE `$this->tableName` 
                SET `revoke_reporting_access` = ?, `revoke_campaign_control` = ?, `revoke_partner_control` = ?, 
                    `revoke_success_leads` = ?, `revoke_failed_leads` = ?, `revoke_skipped_leads` = ?
                WHERE `sub_account_id` = ?
                LIMIT 1
                ";

        $this->db->query($sql, array($revokeReporting, $revokeCampaign, $revokePartner, $revokeSuccessLeads, $revokeFailedLeads, $revokeSkippedLeads, $accountId));
    }
    //--------------------------------------------------------------------------
    
    
    public function insertCampaigns($id, $name, $campaigns, $campaignIds) {
        $this->setTableName('sub_accounts_rights');
        
        foreach ($campaigns as $campaign) {
            if (in_array($campaign['id'],$campaignIds)) {
                $object = array (
                    'sub_account_id' => $id,
                    'sub_account_username' => $name,
                    'object_id' => $campaign['id'],
                    'object_name' => $campaign['name'],
                    'object_type' => self::OBJECT_TYPE_CAMPAIGN
                );
                $this->insert($object);
            }
        }
        
        $this->setTableName('sub_accounts');
    }
    //--------------------------------------------------------------------------
    
    
    public function insertRecord($data) {
        $this->setTableName('sub_accounts_rights');
        
        $this->insert($data);
        
        $this->setTableName('sub_accounts');
    }
    //--------------------------------------------------------------------------
    
    
    public function deleteRecordFromSubAccountRight($objectId, $objectType) {
        $this->setTableName('sub_accounts_rights');
        $this->deleteWhere(array('object_id' => $objectId, 'object_type' => $objectType));
        $this->setTableName('sub_accounts');
    }
    //--------------------------------------------------------------------------
    
    
    public function insertPartners($id, $name, $partners, $partnerIds) {
        $this->setTableName('sub_accounts_rights');
        
        foreach ($partners as $partner) {
            if (in_array($partner['id'],$partnerIds)) {
                $object = array (
                    'sub_account_id' => $id,
                    'sub_account_username' => $name,
                    'object_id' => $partner['id'],
                    'object_name' => $partner['name'],
                    'object_type' => self::OBJECT_TYPE_PARTNER
                );
                $this->insert($object);
            }
        }
        
        $this->setTableName('sub_accounts');
    }
    //--------------------------------------------------------------------------
    
    
    public function deleteAllCampaignAndPartner($accountId) {
        $this->setTableName('sub_accounts_rights');
        $this->deleteWhere(array('sub_account_id' => $accountId));
        $this->setTableName('sub_accounts');
    }
    //--------------------------------------------------------------------------
    
    
    public function listAllCampaignIds ($accountId) {
        $this->setTableName('sub_accounts_rights');
        $objects = $this->listAllWhere(array('sub_account_id' => $accountId, 'object_type' => self::OBJECT_TYPE_CAMPAIGN));
        
        $result = array();
        foreach ($objects as $object) {
            $result[] = $object['object_id'];
        }
        
        $this->setTableName('sub_accounts');
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    public function listAllPartnerIds ($accountId) {
        $this->setTableName('sub_accounts_rights');
        $objects = $this->listAllWhere(array('sub_account_id' => $accountId, 'object_type' => self::OBJECT_TYPE_PARTNER));
        
        $result = array();
        foreach ($objects as $object) {
            $result[] = $object['object_id'];
        }
        
        $this->setTableName('sub_accounts');
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    public function listAllPartnerNameWithIds($accountId) {
        $this->setTableName('sub_accounts_rights');
        $objects = $this->listAllWhere(array('sub_account_id' => $accountId, 'object_type' => self::OBJECT_TYPE_PARTNER));
        
        $result = array();
        foreach ($objects as $object) {
            $result[$object['object_id']] = $object['object_name'];
        }
        
        $this->setTableName('sub_accounts');
        
        return $result;
    }
    //--------------------------------------------------------------------------
    
    
    
}
