<?php

/**
 * @desc This class and other Engine_Something classes process incoming submissions and reposts.
 * Most of the business logic is in Engine and Model classes. This one is used externally and
 * creates instances of other three as needed.
 *
 *  <ul>
 *  <li>Engine_Data is dumb data holder</li>
 *  <li>Engine_IncomingData will accept the data from submission, check the data using campaign rules and
 *  transform into a form rest of the Engine system will use. Mostly works with campaign data</li>
 *  <li>Engine_Delivery will deliver the data to partners, checking the partner rules and modifying the data as necessary. Mostly works with partner data</li>
 *  </ul>
 */
abstract class Engine {
    const REGEX_TOKENS_FOR_REPLACEMENTS = '/(\\[[\\w]{1,}\\]|\\[:[\\w]{1,}:\\]|\$[\\d]{1,}|\\[[a-zA-Z0-9\$\/\.\*@()+_-]+\\])/';

    const MODIFY_REPLACEMENT = 'dlg-modifier-replacements';
    const MODIFY_DICTIONARY  = 'dlg-modifier-dictionary';


    // These constants are used to "report" actions performed by Engine in case there's callback set
    const ACTION_INCOMING            = 'incoming';
    const ACTION_GATHERED            = 'gathered';
    const ACTION_TOO_MANY            = 'too many';
    const ACTION_DELIVERED           = 'delivered';
    const ACTION_DELIVERY_FAILED     = 'delivery_failed';
    const ACTION_DELIVERY_LOGGED     = 'delivery_logged';
    const ACTION_SENT_VALS           = 'sent_values';
    const ACTION_PARTNER_RESPONSE    = 'partner_response';
    const ACTION_FILTERED            = 'filtered';
    const ACTION_URL_PROC            = 'url_processed';
    const ACTION_DELIVER_REP_LIST    = 'deliver_rep_list';
    const ACTION_INCOMING_DUPLICATE  = 'incoming_duplicate';
    const ACTION_TEMPLATE_PROCESSED  = 'template_processed';
    const ACTION_CHILD_CAMPAIGN      = 'child_campaign';

    /**
     * @var Engine_Data
     */
    protected $data;

    /**
     * @var Engine_Job
     */
    protected $job;

    /**
     * @desc This is for unit testing, a way for Engine class to report an event
     */
    protected $callback = false;

    /**
     * @desc is set to true if we need to postpone the repost_queue entry because
     * partner delivery caps are already full
     */
    protected $postponeCatchupEntry = false;

    /**
     * @var Engine_Delivery
     */
    protected $deliveryEngine;

    /**
     * @var Engine_Flags
     */
    public $flags;


    public function __construct() {
        $this->flags = new Engine_Flags();

        $this->job   = new Engine_Job();
        $this->data  = new Engine_Data();

        $this->deliveryEngine = new Engine_Delivery($this, $this->data, $this->job);
    }
    //--------------------------------------------------------------------------


    protected function isPartnerWithinCaps(Partner $partner) {
        // Model_PartnerCap
        $mpc = SingletonRegistry::getModelPartnerCap();
        $caps = $mpc->listAllForPartner($partner->getId());
        
        if (empty($caps)) {
            return true;
        }
        
        foreach ($caps as $cap) {
            $exceedCap = false;
            $cap['capTypes'] = $mpc->getIntervals();
            
            switch ($cap['cap_type']) {
                case $mpc::CAP_TYPE_DELIVERY:
                    $alreadyDelivered = $this->data->logDelivery->getDeliveriesToPartner($cap, $partner->getPartnerTimezone());
                    if ($alreadyDelivered !== false && intval($alreadyDelivered) >= intval($cap['cap_value'])) {
                        $exceedCap = true;
                    }
                    break;
                case $mpc::CAP_TYPE_BUDGET:
                    $alreadyBudget = $this->data->logDelivery->getBudgetToPartner($cap, $partner->getPartnerTimezone());
                    if ($alreadyBudget !== false && floatval($alreadyBudget) >= floatval($cap['cap_value'])) {
                        $exceedCap = true;
                    }
                    break;
                default:
                    break;
            }
            
            if ($exceedCap) {
                // return infomation of exceeded Cap
                return array(
                    'cap_type' => $cap['cap_type'],
                    'interval' => $cap['interval']
                );
            }
        }
        
        return true;
    }
    //--------------------------------------------------------------------------
    
    protected function getPartnerSuccessUrl($url) {
        if (!$this->job->currentPartner->isParseResponse()) {
            return $this->processUrlWithCampaignFieldsAndPartnerTokens($url);
        }
        $responseType = $this->job->currentPartner->getResponseType();
        
        if ($responseType == Model_Partner::RESPONSE_TYPE_JSON && Model_Partner::isValidJsonPathExpression($url)) {
            $partnerResponse = $this->deliveryEngine->getValueFromPartnerJsonResponseByPath($url);
            if (!empty($partnerResponse)) {
                return $partnerResponse;
            }
        } else if ($responseType == Model_Partner::RESPONSE_TYPE_XML && Model_Partner::isValidXPathExpression($url)) {
            $partnerResponse = $this->deliveryEngine->getValueFromPartnerXmlResponseByPath($url);
            if (!empty($partnerResponse)) {
                return $partnerResponse;
            }
        }
               
        return $this->processUrlWithCampaignFieldsAndPartnerTokens($url);
    }

    //--------------------------------------------------------------------------
    public function processUrlWithCampaignFields($url) {
        if ((trim($url) == '') || !$this->foundRecognizedTokens($url)) {
            return trim($url);
        }
        
        $urlParts     = $this->splitUrlIntoTokensForReplacement($url);
        $shouldEncode = (stripos($url, 'http://') === 0) || (stripos($url, 'https://') === 0);
        $responseType = $this->job->currentPartner->getResponseType();

        foreach ($urlParts as & $part) {

            if ($this->isFieldTypeToken($part)) {
                $part = $this->replaceFieldTypeToken($part);

            } elseif ($this->isSystemFieldToken($part)) {
                $part = $this->replaceSystemFieldToken($part);

            } elseif ($this->job->currentPartner->isParseResponse() && $this->isResponseReferenceToken($part)) {
                $part = $this->replaceResponseFieldReference($part, $shouldEncode);
            }

            // get [$.url] in json response, [/url] in xml response
            if (
                    $responseType == Model_Partner::RESPONSE_TYPE_JSON &&
                    preg_match('/^\\[[a-zA-Z0-9\$\.\*@()+_-]+\\]$/', $part) 
            ) {
                $partnerResponse = $this->deliveryEngine->getValueFromPartnerJsonResponseByPath(substr($part,1,-1));
                if (!empty($partnerResponse)) {
                    $part = $partnerResponse;
                }
            } else if (
                    $responseType == Model_Partner::RESPONSE_TYPE_XML &&
                    preg_match('/^\\[[a-zA-Z0-9\/\.\*@()+_-]+\\]$/', $part)
            ) {
                
                $partnerResponse = $this->deliveryEngine->getValueFromPartnerXmlResponseByPath(substr($part,1,-1));
                if (!empty($partnerResponse)) {
                    $part = $partnerResponse;
                }
            }
        }

        $this->report(Engine::ACTION_URL_PROC, $urlParts);
        return trim(trim(implode('', $urlParts)));
    }
    //--------------------------------------------------------------------------

    
    public function processUrlWithCampaignFieldsAndPartnerTokens($url) {
        $url = $this->processUrlWithCampaignFields($url);
        if (!empty($this->job->currentPartner)) {
            $fields = SingletonRegistry::getModelPartnerField()->listAllForPartner($this->job->currentPartner->getId());
            foreach ($fields as $field) {
                if (substr($field['value'], 0, 1) !== '[' || substr($field['value'], -1) !== ']') {
                    $url = str_replace("{" . trim($field['name'] . "}"), trim($field['value']), $url);
                }
            }
        }
        return $url;
    }
    //--------------------------------------------------------------------------
    

    private function foundRecognizedTokens($url) {
        if (preg_match(self::REGEX_TOKENS_FOR_REPLACEMENTS, $url)) {
            return true;
        } else {
            if ($this->callback) {
                $urlParts = array($url);
                $this->report(Engine::ACTION_URL_PROC, $urlParts);
            }
            return false;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Pulls out parts that look like this:
     * <ul>
     *   <li>[Some_Token]</li>
     *   <li>[:system_field:]</li>
     *   <li>$1 - any non-zero number after a dollar sign</li>
     * </ul>
     * interspersed with non-matching text.
     *
     * <p>Example:<br>
     * <b>http://www.[Other_1].com/signup?id=[:lead_id:]</b><br>
     * will become:<br>
     * <pre>
     * array (
     *   'http://www.',
     *   '[Other_1]',
     *   '.com/signup?id=',
     *   '[:lead_id:]'
     * )</pre>
     *
     * @return array:
     */
    private function splitUrlIntoTokensForReplacement($url) {
        return preg_split(self::REGEX_TOKENS_FOR_REPLACEMENTS, trim($url), null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }
    //--------------------------------------------------------------------------


    private function isFieldTypeToken($part) {
        return preg_match('/^\\[[\\w]{1,}\\]$/i', $part) == 1;
    }
    //--------------------------------------------------------------------------


    private function replaceFieldTypeToken($part) {
        $fieldName = strtolower(substr($part, 1, -1));
        $ftypeId   = array_search($fieldName, $this->data->fieldTypesLCase);
        if (!$ftypeId) {
            return $part;
        }
        
        if ($ftypeId === Model_CampaignField::FIELD_TYPE_LEAD_ID && empty($this->data->inputData[$ftypeId])) {
            $this->data->inputData[$ftypeId] = $this->data->incomingId;
        }
        
        return isset($this->data->inputData[$ftypeId]) ? urlencode($this->data->inputData[$ftypeId]) : '';
    }
    //--------------------------------------------------------------------------


    private function isSystemFieldToken($part) {
        return preg_match('/^\\[:[\\w]{1,}:\\]$/i', $part) == 1;
    }
    //--------------------------------------------------------------------------


    private function replaceSystemFieldToken($part) {

        // Remove angle brackets
        $field = strtolower(substr($part, 1, -1));

        if (($field == ':aff_login_docket:') && ($this->data->lastPartnerDeliveredTo['posting_type'] == Model_Partner::POST_TYPE_AFF)) {
            $part = urlencode($this->job->currentPartner->FF_Encrypt_Ticket($this->data->lastDataDeliveredToPartner['uname'], '', '', $this->data->lastDataDeliveredToPartner['password']));
        } else {
            $found = false;
            $value = $this->deliveryEngine->valueOfSystemField($field, $found);
            if ($found) {
                $part = urlencode($value);
            }
        }
        return $part;
    }
    //--------------------------------------------------------------------------


    private function isResponseReferenceToken($part) {
        return preg_match('/^\$[\\d]{1,}$/i', $part) == 1;
    }
    //--------------------------------------------------------------------------


    private function replaceResponseFieldReference($part, $shouldEncode) {
        // References are 1-based, like $1 for first field. Substract 1 to get zero-based index
        $responseIndex = substr($part, 1) - 1;

        if ($this->job->currentPartner->getResponseType() == Model_Partner::RESPONSE_TYPE_TEXT 
            && isset($this->job->partnerResponseParsed[$responseIndex])
        ) {
            $part = trim($this->job->partnerResponseParsed[$responseIndex]);
            return $shouldEncode ? urlencode($part) : $part;
        }
        return '';
    }
    //--------------------------------------------------------------------------


    protected function getOwnerUserCaps($userId) {
        if ($this->flags->test_OwnerUserCaps) {
            return $this->flags->test_OwnerUserCaps;
        } else {
            return $this->data->modelUser->listUserCaps($userId);
        }
    }
    //--------------------------------------------------------------------------


    protected function startingNewJob($jobKind) {
        $this->job->setKind($jobKind);
        $this->job->reset();
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine
     */
    public function setListenerCallback($callback) {
        $this->callback = $callback;
        return $this;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine
     */
    public function setTestIncomingData(array $data) {
        $this->flags->testIncomingData = $data;
        return $this;
    }
    //--------------------------------------------------------------------------


    public function getDeliveryIDs() {
        return $this->data->deliveryIDs;
    }
    //--------------------------------------------------------------------------

    public function getIncomingId() {
        return $this->data->incomingId;
    }
    //--------------------------------------------------------------------------


    public function valueOfSystemField($fieldName, & $found) {
        return $this->deliveryEngine->valueOfSystemField($fieldName, $found);
    }
    //--------------------------------------------------------------------------


    public function redirectToCampaignFailureUrlProcessed() {
        throw new ERedirectException($this->processUrlWithCampaignFields($this->job->campaign->getFailureUrl()));
    }
    //--------------------------------------------------------------------------


    public function report($action, array & $data = array()) {
        if ($this->callback) {
            return call_user_func($this->callback, $this, $action, $data);
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    abstract protected function removeSubmissionFromQueueIfPartnerListExhausted();
    //--------------------------------------------------------------------------


    protected function assignCampaign(array & $campaign) {
        $this->job->campaign->row = & $campaign;
        $this->job->campaign->sanityCheckForParasiteShadowCampaign();
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Data
     */
    public function getData() {
        return $this->data;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Engine_Job
     */
    public function getJob() {
        return $this->job;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc For unit tests
     */
    public function activateDebugMode() {
        $this->flags->checkForDuplicates = false;
        $this->flags->fakeUrlResponses   = array(
            'http://w1.localhost/return-url.php' => "OK\nhttp://wiki.localhost/\nx+y",
            'http://w1.localhost/?username'      => 'username taken',
            'http://w1.localhost/?succ'          => 'success',
            'http://w1.localhost/?fail'          => 'fail',
            'http://w1.localhost/?0'             => '0',
            'http://w1.localhost/'               => 'success|0.08',

            'http://aaa.com'                     => 'success',
            'http://bbb.com'                     => 'success',
            'http://xxx.com'                     => 'success',
            'http://yyy.com'                     => 'success',
            'http://zzz.com'                     => 'success',
        );
        $this->flags->neverSkim = true;

        $this->flags->useProfilesForMissingData = false;
    }
    //--------------------------------------------------------------------------
    
    
    public function getRoundRobinDeliverySettingAndUpdateIndex($deliverySetting, $roundRobinLastIndex)
    {
        // set array to standard, because it might be unset in previous step
        $deliverySetting = array_values($deliverySetting);
        
        $newDeliverySetting = array();
        
        if (is_null($roundRobinLastIndex) || (intval($roundRobinLastIndex) >= count($deliverySetting) - 1)) {
            $roundRobinLastIndex = -1;
        }
        
        $nextIndex = intval($roundRobinLastIndex) + 1;
        
        for ($i = $nextIndex; $i < count($deliverySetting); $i ++) {
            $newDeliverySetting[] = $deliverySetting[$i];
        }
        
        for ($i = 0; $i < $nextIndex; $i ++) {
            $newDeliverySetting[] = $deliverySetting[$i];
        }
        
        // update current round-robin index to campaign
        SingletonRegistry::getModelCampaign()->updateCampaignRoundRobinLastIndex($this->job->campaign->getId(), $nextIndex);

        return $newDeliverySetting;
    }
    //--------------------------------------------------------------------------
}
