<?php

class Helper_Postback {
    private $url;
    protected $userId = 0;
    protected $leadId = 0;
    protected $action;

    protected $allLeadIDs = array();

    protected $get = array();

    const ACTION_STOP_DELIVERY = 'stop_delivery';
    const ACTION_CONVERSION    = 'conversion';

    /**
     * @var Model_LogIncoming
     */
    private $logIncoming;

    /**
     * @var Model_Conversion
     */
    private $modelConversion;

    private static $recognizedActions = array(
        self::ACTION_CONVERSION,
        self::ACTION_STOP_DELIVERY,
    );


    public function __construct() {
        $this->logIncoming     = SingletonRegistry::getModelLogIncoming();
        $this->modelConversion = SingletonRegistry::getModelConversion();
    }
    //--------------------------------------------------------------------------


    public function run($url) {
        $this->url = $url;
        $this->findUserId();
        $this->findLeadIdAndCheckUserId();
        $this->findAction();
    }
    //--------------------------------------------------------------------------


    protected function findUserId() {
        $path = parse_url($this->url, PHP_URL_PATH);
        $parts = explode('/', substr($path, 1));
        if (sizeof($parts) <= 2) {
            throw new EError404('Access Forbidden', 1);
        }
        $hash = $parts[1];
        $this->userId = Auth::postbackHashToUserId($hash);
        if ($this->userId == 0) {
            throw new EError404('Access Forbidden', 1);
        }
    }
    //--------------------------------------------------------------------------


    protected function findLeadIdAndCheckUserId() {

        $query = parse_url($this->url, PHP_URL_QUERY);
        $getArray = array();
        $this->allLeadIDs = array();

        parse_str($query, $getArray);
        $this->leadId = isset($getArray['lead_id']) ? max(0, (int)$getArray['lead_id']) : 0;

        if ($this->leadId) {
            $leadData = $this->logIncoming->get($this->leadId, MYSQL_ASSOC);
            if (!isset($leadData['user_id'])) {
                throw new EError404('Access Forbidden', 2);
            }
            if ($leadData['user_id'] != $this->userId) {
                throw new EError404('Access Forbidden', 2);
            }
            $this->allLeadIDs = array($this->leadId);
        } elseif (!empty($getArray['email'])) {
            if (empty($getArray['campaign_id'])) {
                $this->listAllLeadIDs($this->userId, $getArray['email']);
            } else {
                $this->listAllLeadIDs($this->userId, $getArray['email'], $getArray['campaign_id']);
            }
            if (sizeof($this->allLeadIDs)) {
                $this->leadId = $this->allLeadIDs[0];
            } else {
                throw new EAccessDenied('Access Denied', 2);
            }
        } else {
            throw new EAccessDenied('Access Denied', 2);
        }

        $this->get = $getArray;
    }
    //--------------------------------------------------------------------------


    protected function listAllLeadIDs($userId, $email, $campaignId = null) {
        if ($campaignId) {
            $arr = $this->logIncoming->listByEmailAndUserId($email, $userId, $campaignId);
        } else {
            $arr = $this->logIncoming->listByEmailAndUserId($email, $userId);
        }
    	foreach ($arr as $row) {
    	    $this->allLeadIDs[] = (int)$row['id'];
    	}
    }
    //--------------------------------------------------------------------------


    protected function findAction() {
        if (!isset($this->get['action'])) {
            throw new EError404('Access Forbidden', 3);
        }
        $actions = is_array($this->get['action']) ? $this->get['action'] : array($this->get['action']);

        foreach ($actions as $action) {
            if (in_array($action, self::$recognizedActions)) {
                $this->executeAction($action);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function executeAction($action) {
        switch ($action) {
            case self::ACTION_STOP_DELIVERY:
                return $this->deleteLead();

            case self::ACTION_CONVERSION:
                return $this->recordConversion();

            default:
                throw new EError404('Access Forbidden', 4);
        }
    }
    //--------------------------------------------------------------------------


    private function deleteLead() {
        foreach ($this->allLeadIDs as $id) {
            $this->logIncoming->deleteIncomingIdFromRepostQueue($id);
        }
    }
    //--------------------------------------------------------------------------


    private function recordConversion() {
        $partnerId = isset($this->get['partner_id']) ? max(0, (int)$this->get['partner_id']) : 0;
        if (!$partnerId) {
            throw new EError404('Access Forbidden', 5);
        }
        $partner = SingletonRegistry::getModelPartner()->get($partnerId);
        if (!$partner) {
            throw new EError404('Access Forbidden', 5);
        }
        if ($partner['user_id'] != $this->userId) {
            throw new EError404('Access Forbidden', 6);
        }
        // if (empty($this->get['type'])) {
        //     throw new EError404('Access Forbidden', 7);
        // }
        $data = array(
            'incoming_id' => $this->leadId,
            'partner_id'  => $partnerId,
            'type'        => isset($this->get['type']) ? $this->get['type'] : '',
            'value'       => isset($this->get['value']) ? (float)$this->get['value'] : 0
        );
        $this->modelConversion->insert($data);
    }
    //--------------------------------------------------------------------------

}
