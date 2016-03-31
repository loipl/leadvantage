<?php

/**
 * @desc Refactored out of Engine class to deal with delivering data to list of partners
 */
class Engine_Delivery {

    /**
     * @var Engine
     */
    private $engine = null;

    /**
     * @var Engine_Data
     */
    private $data;

    /**
     * @var Engine_Job
     */
    private $job;

    /**
     * @var Engine_PartnerFieldHelper
     */
    private $fieldHelper;

    /**
     * @var Engine_DeliveryCurlHelper
     */
    private $curlHelper;


    /**
     * @desc In case curl returned an error, it will be here
     */
    private $lastCurlError = '';

    /**
     * @desc In case curl returned an error, it will be here
     */
    private $lastCurlErrorNo = 0;

    /**
     * @desc will be set to true if last delivery to partner failed because username was taken or invalid
     */
    private $usernameProblem = false;

    /**
     * @desc In case last delivery to a partner failed because of username problem and
     * partner response has suggestions for username, they will be in this array
     */
    private $usernameSuggestions = array();

    /**
     * @desc Flag that means what delivery are we now doing. Can be initial delivery, retry
     * on network error or retry on username problem. Only useful for logging
     */
    private $deliveryAttemptType = 0;

    /**
     * @desc Used to keep track if we tried delivering to this partner more times
     * than it is allowed in campaign settings
     */
    private $partnerRetryCount = 0;

    /**
     * @desc Rows from partner_fields table for current partner
     */
    private $partnerFields = array();

    /**
     * @desc Whether current partner accepted the delivery
     */
    private $partnerAccepted = false;

    /**
     * @desc Assoc array of values that we are sending to the partner
     */
    private $valuesForDelivery = array();

    /**
     * @desc Text of partner response
     */
    private $partnerResponse = '';

    /**
     * @desc Text of partner response
     */
    private $partnerHeader = '';

    /**
     * @desc Flag that says whether partner filters accept current delivery. It will be turned off
     * if there is a match for rule that forbids delivery, but not for rule that just changes
     * partner's success url.
     */
    private $partnerFiltersOK = false;
    
    /**
     * Storing template values of Ping partner, for showing in Reporting purpose
     * @var array
     */
    private $pingPartnerTemplates = array();


    public function __construct(Engine $engine, Engine_Data $data, Engine_Job $job) {
        $this->engine = $engine;
        $this->data   = $data;
        $this->job    = $job;

        $this->fieldHelper = new Engine_PartnerFieldHelper($engine, $data, $job);
        $this->curlHelper  = new Engine_DeliveryCurlHelper($this, $engine);
    }
    //--------------------------------------------------------------------------


    public function setLastCurlError($error, $errorNo) {
        $this->lastCurlError   = $error;
        $this->lastCurlErrorNo = $errorNo;
    }
    //--------------------------------------------------------------------------

    
    public function pingToPartnersAndReturnResult() {
        $this->pingToAllDeliveryPartners();
        
        $this->analyzePingResponseAndReturnResult();
    }
    //--------------------------------------------------------------------------
    
    
    public function analyzePingResponseAndReturnResult() {
        $pingResult = false;
        $bidPrice = 0;
        $leadId = $this->data->incomingId;

        $campaignId = $this->job->campaign->getID();
        foreach ($this->job->deliverySettings as $setting) {
            $partnerId = $setting['partner_id'];
            
            if (! isset($setting['skipped']) && !empty($this->job->pingPartnerResponses[$partnerId])) {
                $response = $this->job->pingPartnerResponses[$partnerId];
                $responseType = $this->job->partnersAssoc[$partnerId]['response_type'];
                $delimiter = $this->job->partnersAssoc[$partnerId]['delimiter'];
                $pricePath    = $this->job->partnersAssoc[$partnerId]['price_per_lead'];
                $transactionPath = $this->job->partnersAssoc[$partnerId]['auction_transaction_id'];

                if ($responseType == Model_Partner::RESPONSE_TYPE_XML) {
                    $parsedResponse = Helper_XML::removeXmlNamespace($response);
                } else {
                    $parsedResponse = $response;
                }
                $price = $this->getPingPriceFromPartnerResponse($parsedResponse, $responseType, $delimiter, $pricePath); 
                $transactionId = $this->getPingTransactionFromPartnerResponse($parsedResponse, $responseType, $delimiter, $transactionPath);

                $this->job->partnersAssoc[$partnerId]['response'] = $response;
                $isAccepted = $this->hasPartnerAccepted($this->job->partnersAssoc[$partnerId]);
                
                // log data including Ping data of Auction or Round-Robin or Prioritized
                $type = ($this->job->campaign->isAuction()) ? Model_LogPing::TYPE_AUCTION : Model_LogPing::TYPE_OTHER;
                $this->logPingData($this->data->incomingId, $campaignId, $partnerId, $isAccepted, $price, $transactionId, $response, $type);
            } else {
                $isAccepted = false;
                $price = false;
                $response = '';
            }
            
            if ($isAccepted) {
                $pingResult = true;
                $bidPrice = max($bidPrice, $price);
            }

            // log reporting data
            $this->logReportingData($partnerId, $isAccepted, $setting, $price, $response);
        }
        
        $bidPrice = $this->job->campaign->processPriceUsingBidResponse($bidPrice, $this->job->campaign);

        if ($this->job->campaign->isAPIResponse()) {
            if ($pingResult) {
                $response = 'SUCCESS'. '|'. $leadId . '|' . $bidPrice;
            } else {
                $response = 'FAIL|No partner accepted';
            }
            throw new EDoneException($response);
        } else {
            $finalUrl = trim($this->engine->processUrlWithCampaignFields($pingResult ? $this->job->campaign->getSuccessUrl() : $this->job->campaign->getFailureUrl()));
            header('x-lw-success: ' . $pingResult);
            header('x-lw-url: ' . $finalUrl);
            throw new ERedirectException($finalUrl);
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function postToPartnersAfterGetDataFromPing() {
        $this->analyzeLogPingDataAndSortDeliverySetting();

        $this->deliveryToPostPartners();
    }
    //--------------------------------------------------------------------------
    
    
    public function analyzeLogPingDataAndSortDeliverySetting() {
        $leadID = $this->data->inputData[Model_CampaignField::FIELD_TYPE_LEAD_ID];
        
        // remove repost/catchup Ping process of Ping / Post campaign
        if (empty($leadID)) {
            foreach ($this->job->deliverySettings as $index => $setting) {
                $this->job->deliverySettings[$index]['price'] = 0;
            }
            return;
        }

        $pingData = SingletonRegistry::getModelLogPing()->getPingDataByLeadID($leadID);

        if (!empty($pingData)) {
            // Ping success
            foreach ($this->job->deliverySettings as $index => $setting) {
                $existPingPartnerData = false;
                
                foreach ($pingData as $partnerData) {
                    if ((int) $setting['partner_id'] === (int) $partnerData['partner_id']) {
                        $existPingPartnerData = true;
                        $this->job->deliverySettings[$index]['price'] = $partnerData['price'];
                        $this->job->deliverySettings[$index]['transaction_id'] = $partnerData['transaction_id'];
                        
                        break;
                    }
                }
                
                if (!$existPingPartnerData && !$this->job->isReposting()) {
                    unset($this->job->deliverySettings[$index]);
                }
                
            }
            
            // auction mode
            if ($this->job->campaign->isAuction()) {
                // sort delivery setting by price desc
                usort($this->job->deliverySettings, array($this, 'comparePingPartner'));
            }

            // round-robin mode
            if ($this->job->campaign->isRoundRobin()) {
                $roundRobinLastIndex = $this->job->campaign->getRoundRobinLastIndex();
                $this->job->deliverySettings = $this->engine->getRoundRobinDeliverySettingAndUpdateIndex($this->job->deliverySettings, $roundRobinLastIndex);
            }
        } else {
            if (!$this->job->isReposting()) {
                // Ping fail
                if ($this->job->campaign->isAPIResponse()) {
                    $response = 'FAIL|No partner accepted';
                    if (!SingletonRegistry::getModelLogPing()->checkLeadIdExist($leadID)) {
                        $response = 'FAIL|Cannot find lead with this Lead ID';
                    }
                    throw new EDoneException($response);
                } else {
                    $finalUrl = trim($this->engine->processUrlWithCampaignFields($this->job->campaign->getFailureUrl()));
                    header('x-lw-url: ' . $finalUrl);
                    throw new ERedirectException($finalUrl);
                }
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function pingToAllDeliveryPartnersBeforeDelivery() {
        $this->pingToAllDeliveryPartners();
        
        $this->analyzePingResponseAndSortDeliverySetting();
        
        $this->deliveryToPostPartners();
    }
    //--------------------------------------------------------------------------
    
    
    private function pingToAllDeliveryPartners() {
        $deliverySettings = $this->job->deliverySettings;

        // curl basic options
        $curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 4
        );
        
        // init curl
        $parallelCurl = new ParallelCurl(count($deliverySettings), $curlOptions);
        
        foreach ($deliverySettings as $index => $delivery) {
            if ($this->job->startNewDelivery($delivery)) {
                $partner = $this->job->currentPartner;
                           
                // get values for delivery
                $this->initializeDelivery();
                $this->transformValuesAndCheckPartnerFilters();
                
                // get delivery address
                $deliveryAddress = $partner->getDeliveryAddr();
                
                if ($this->partnerFiltersOK) {
                    $postFields = $this->getValuesForDelivery($partner);

                    if ($partner->getPostingType() === Model_Partner::POST_TYPE_GET) {
                        $deliveryAddress = $this->buildGetTypeDeliveryAddress($deliveryAddress);
                    }

                    $partnerTimeout     = $partner->getCurlTimeout();
                    $timeout            = (!empty($partnerTimeout)) ? 
                                            intval($partnerTimeout) : (Engine_Settings::$settings->deliveryTimeout/2);
                    
                    //campaign timeout
                    $campaignTimeout = intval($this->job->campaign->getCampaignTimeout());
                    if ($campaignTimeout > 0) {
                        $remainingTime  = $campaignTimeout - (time() - $this->job->startTime);
                        if ($remainingTime <= 0) {
                            break;
                        }
                        $timeout        = min($remainingTime, $timeout);

                    }

                    // start multi-curl
                    $parallelCurl->startRequest($deliveryAddress, array($this, 'getPingResult'), $partner->getId(), $timeout, $postFields, $partner->getPostingType());
                } else {
                    $this->job->deliverySettings[$index]['skipped'] = true;
                }
            } else {
                unset($this->job->deliverySettings[$index]);
            }
        }
        
        // finish requests & destruct ParallelCurl object
        $parallelCurl->finishAllRequests();
    }
    //--------------------------------------------------------------------------
    
    
    private function getValuesForDelivery($partner) {
        $postingType = $partner->getPostingType();
        $values = $this->valuesForDelivery;
        
        switch ($postingType) {
            case Model_Partner::POST_TYPE_POST:
                return http_build_query($values);

            case Model_Partner::POST_TYPE_XML:
            case Model_Partner::POST_TYPE_XML_FIELD:
                return $this->buildXMLValues($partner, $values);

            case Model_Partner::POST_TYPE_JSON:
                return $this->buildJSONValues($partner, $values);

            default:
                // GET
                return array();
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function buildGetTypeDeliveryAddress($deliveryAddress) {
        if (strpos($deliveryAddress, '?') === false) {
            $deliveryAddress .= '?';
        } else {
            $deliveryAddress .= '&';
        }
        $deliveryAddress .= http_build_query($this->valuesForDelivery);
        
        return $deliveryAddress;
    }
    //--------------------------------------------------------------------------
    
    
    private function buildXMLValues($partner, array $values) {
        $xml = $partner->getTemplate();
        $postingType = $partner->getPostingType();
        $partnerId = $partner->getId();
        
        foreach ($values as $k => $v) {
            $xml = str_replace("%$k%", escapeHtml($v), $xml);
        }
        
        // remove newline, replace multiple spaces & tabs with single space
        $xml = trim(preg_replace("/\s+/", ' ', str_replace(array("\n", "\r", "\r\n", "\t"), '', $xml)));
        $reportingXml = $xml; // clone of xml without encoding data.
        
        if ($postingType == Model_Partner::POST_TYPE_XML_FIELD && !empty($xml)) {
            $value = $partner->getXmlFieldName();
            $xml          = '&' . $value . '=' . urlencode($xml);
            $reportingXml = '&' . $value . '=' . $reportingXml;
        }
        
        $reportData = array('processed' => $reportingXml, 'type' => Model_Partner::POST_TYPE_XML);
        $this->engine->report(Engine::ACTION_TEMPLATE_PROCESSED, $reportData);
        
        $this->pingPartnerTemplates[$partnerId] = $reportingXml;
        
        return $xml;
    }
    //--------------------------------------------------------------------------
    
    
    private function buildJSONValues($partner, array $values) {
        $jsonText = $partner->getTemplate();
        $partnerId = $partner->getId();
        
        foreach ($values as $k => $v) {
            $replacement = json_encode($v);
            if ((substr($replacement, 0, 1) == '"')&& (substr($replacement, -1) == '"')) {
                $replacement = substr($replacement, 1, -1);
            }
            $jsonText = str_replace("%$k%", $replacement, $jsonText);
        }

        // remove newline, replace multiple spaces & tabs with single space
        $jsonText = trim(preg_replace("/\s+/", ' ', str_replace(array("\n", "\r", "\r\n", "\t"), '', $jsonText)));
        
        $reportData = array('processed' => $jsonText, 'type' => Model_Partner::POST_TYPE_JSON);
        $this->engine->report(Engine::ACTION_TEMPLATE_PROCESSED, $reportData);
        
        $this->pingPartnerTemplates[$partnerId] = $jsonText;
        
        return $jsonText;
    }
    //--------------------------------------------------------------------------
    
    
    public function getPingResult($content, $url, $ch, $partnerId) {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
        if ($httpcode !== 200) {
            return;
        }
        
        // storing Ping responses
        $this->job->pingPartnerResponses[$partnerId] = $content;
    }
    //--------------------------------------------------------------------------
    
    
    private function analyzePingResponseAndSortDeliverySetting() {

        $campaignId = $this->job->campaign->getID();
        foreach ($this->job->deliverySettings as $index => $setting) {
            $partnerId = $setting['partner_id'];
            
            if (! isset($setting['skipped']) && !empty($this->job->pingPartnerResponses[$partnerId])) {
                $response = $this->job->pingPartnerResponses[$partnerId];
                $responseType = $this->job->partnersAssoc[$partnerId]['response_type'];
                $delimiter = $this->job->partnersAssoc[$partnerId]['delimiter'];
                $pricePath    = $this->job->partnersAssoc[$partnerId]['price_per_lead'];
                $transactionPath = $this->job->partnersAssoc[$partnerId]['auction_transaction_id'];

                if ($responseType == Model_Partner::RESPONSE_TYPE_XML) {
                    $parsedResponse = Helper_XML::removeXmlNamespace($response);
                } else {
                    $parsedResponse = $response;
                }
                $price = $this->getPingPriceFromPartnerResponse($parsedResponse, $responseType, $delimiter, $pricePath); 
                $transactionId = $this->getPingTransactionFromPartnerResponse($parsedResponse, $responseType, $delimiter, $transactionPath);

                $this->job->partnersAssoc[$partnerId]['response'] = $response;
                $isAccepted = $this->hasPartnerAccepted($this->job->partnersAssoc[$partnerId]);
                
                // log data including Ping data of Auction or Round-Robin or Prioritized
                $type = ($this->job->campaign->isAuction()) ? Model_LogPing::TYPE_AUCTION : Model_LogPing::TYPE_OTHER;
                $this->logPingData($this->data->incomingId, $campaignId, $partnerId, $isAccepted, $price, $transactionId, $response, $type);
            } else {
                $isAccepted = false;
                $price = false;
                $response = '';
            }
            
            if ($isAccepted) {
                $this->job->deliverySettings[$index]['price'] = $price;
                $this->job->deliverySettings[$index]['transaction_id'] = $transactionId;
            } else {
                unset($this->job->deliverySettings[$index]);
            }


            // log reporting data
            $this->logReportingData($partnerId, $isAccepted, $setting, $price, $response);
        }
        
        // auction mode
        if ($this->job->campaign->isAuction()) {
            // sort delivery setting by price desc
            usort($this->job->deliverySettings, array($this, 'comparePingPartner'));
        }
        
        // round-robin mode
        if ($this->job->campaign->isRoundRobin()) {
            $roundRobinLastIndex = $this->job->campaign->getRoundRobinLastIndex();
            $this->job->deliverySettings = $this->engine->getRoundRobinDeliverySettingAndUpdateIndex($this->job->deliverySettings, $roundRobinLastIndex);
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function logReportingData($partnerId, $isAccepted, $setting, $price, $response) {
        $logData = array(
            'incoming_id'      => $this->data->incomingId,
            'partner_id'       => $partnerId,
            'is_accepted'      => $isAccepted ? '1' : '0',
            'is_repost'        => $this->job->markAsRepost ? '1' : '0',
            'revenue'          => $price,
            'data'             => http_build_query($this->valuesForDelivery),
            'partner_response' => $response
        );
        
        if (isset($setting['skipped'])) {
            $logData['reject_reason'] = 'skipped';
        } else if (!isset($this->job->pingPartnerResponses[$partnerId])) {
            $logData['reject_reason'] = 'timeout';
        } else {
            $logData['reject_reason'] = '';
        }
        
        $processingShadowCampaign = $this->data->incRow && ($this->data->incRow['campaign_id'] != $this->job->campaign->getId());

        $deliveryId = $this->data->logDelivery->insertWithApiReportData(
            $logData,
            $this->job->campaign->getId(),
            $this->job->campaign->getUserId(),
            $this->data->incomingStatus,
            $this->data->incomingTimestamp,
            $processingShadowCampaign
        );
        $this->engine->report(Engine::ACTION_DELIVERY_LOGGED, $logData);
        
        $this->data->deliveryIDs[] = $deliveryId;
        
        if ($deliveryId && (!empty($this->pingPartnerTemplates[$partnerId]))) {
            $this->data->logDelivery->logDeliveryTemplateSent($deliveryId, $this->pingPartnerTemplates[$partnerId]);
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function logPingData($incomingId, $campaignId, $partnerId, $isAccepted, $price, $transactionId, $response, $type) {
        $data = array(
            'incoming_id'       => $incomingId,
            'campaign_id'       => $campaignId,
            'partner_id'        => $partnerId,
            'is_accepted'       => $isAccepted ? '1' : '0',
            'price'             => $price,
            'transaction_id'    => $transactionId,
            'partner_response'  => $response,
            'type'              => $type
        );
        $this->data->modelLogPing->insert($data);
    }
    //--------------------------------------------------------------------------


    public function comparePingPartner($p1, $p2) {
        $price1 = floatval($p1['price']);
        $price2 = floatval($p2['price']);
        
        if ($price1 < $price2) {
            return 1;
        } else if ($price1 > $price2) {
            return -1;
        } else {
            return intval($p1['order_nr']) - intval($p2['order_nr']);
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function getPingPriceFromPartnerResponse($response, $responseType, $delimiter, $pricePath) {
        $price = 0;
        switch (intval($responseType)) {
            case Model_Partner::RESPONSE_TYPE_TEXT:
                $parsedResponse = explode($delimiter, $response);
                if (preg_match('/^\\$[\\d]{1,}/', $pricePath)) {
                    $priceIndex = intval(substr($pricePath,1)) - 1;
                    if (!empty($parsedResponse[$priceIndex]) && is_numeric($parsedResponse[$priceIndex])) {
                        $price = $parsedResponse[$priceIndex];
                    }
                } else if (is_numeric($pricePath)) {
                    $price = $pricePath;
                }
                break;
                
            case Model_Partner::RESPONSE_TYPE_JSON:
                $parsedResponse = json_decode($response, true);
                $value = jsonPath($parsedResponse, $pricePath);
                if(isset($value[0]) && is_numeric($value[0])) {
                    $price = $value[0];
                } else if (is_numeric($pricePath)) {
                    $price = $pricePath;
                }
                break;
                
            case Model_Partner::RESPONSE_TYPE_XML:
                try {
                    $parsedResponse = new SimpleXMLElement($response);
                    $value = @($parsedResponse->xpath($pricePath));
                    if(isset($value[0]) && is_numeric(trim((string)$value[0][0]))) {
                        $price = trim((string)$value[0][0]);
                    } else if (is_numeric($pricePath)) {
                        $price = $pricePath;
                    }
                } catch (Exception $ex) {
                    return 0;
                }
                break;

        }
        return floatval($price);
    }
    //--------------------------------------------------------------------------
    
    
    private function getPingTransactionFromPartnerResponse($response, $responseType, $delimiter, $transactionPath) 
    {
        if (empty($transactionPath)) {
            return '';
        }
        
        $transactionId = '';
        switch (intval($responseType)) {
            case Model_Partner::RESPONSE_TYPE_TEXT:
                $parsedResponse = explode($delimiter, $response);
                if (preg_match('/^\\$[\\d]{1,}/', $transactionPath)) {
                    $transactionIndex = intval(substr($transactionPath,1)) - 1;
                    if (!empty($parsedResponse[$transactionIndex])) {
                        $transactionId = $parsedResponse[$transactionIndex];
                    }
                }
                break;
                
            case Model_Partner::RESPONSE_TYPE_JSON:
                $parsedResponse = json_decode($response, true);
                $value = jsonPath($parsedResponse, $transactionPath);
                if(isset($value[0])) {
                    $transactionId = $value[0];
                } 
                break;
                
            case Model_Partner::RESPONSE_TYPE_XML:
                try {
                    $parsedResponse = new SimpleXMLElement($response);
                    $value = @($parsedResponse->xpath($transactionPath));
                    if(isset($value[0])) {
                        $transactionId = trim((string)$value[0][0]);
                    } 
                } catch (Exception $ex) {
                    return '';
                }
                break;

        }
        return $transactionId;
    }
    
    
    //--------------------------------------------------------------------------
    private function deliveryToPostPartners() {
        $this->swapPingPostPartner();
        
        $deliverySettings = $this->job->deliverySettings;
        
        foreach ($deliverySettings as $delivery) {
            if ($this->job->startNewDelivery($delivery)) {
                if ($this->job->isReposting()) {
                    $this->engine->repostToCurrentPartner();
                } else {
                    $this->engine->deliverToCurrentPartner();
                }
                if ($this->job->campaign->isAuction() && $this->partnerAccepted) {
                    break;
                }
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function swapPingPostPartner() {

        foreach ($this->job->deliverySettings as $index => $deliverySetting) {
            $partnerId = $deliverySetting['partner_id'];
            if ( ! empty($this->job->partnersAssoc[$partnerId]['post_partner_id'])) {
                $postPartnerId = $this->job->partnersAssoc[$partnerId]['post_partner_id'];
                $this->job->deliverySettings[$index][0] = $postPartnerId;
                $this->job->deliverySettings[$index]['partner_id'] = $postPartnerId;
            } else {
                unset($this->job->deliverySettings[$index]);
            }
        }
        
        if ($this->job->isReposting()) {
            if (!empty($this->job->deliverySettings)) {
                $this->engine->initPartnersListForRepost($this->job->deliverySettings);
            }
               
            if (!empty($this->engine->catchingUpPartnerId)) {
                $this->job->partnersAssoc = $this->data->modelPartner->listGroup($this->job->partnerIDsForDelivery, 'id', true);
            }
            
            $modelPartnerSettings = SingletonRegistry::getSingleInstance('Model_PartnerSettings');
            $this->job->partnersAssoc = $modelPartnerSettings->attachSettingsForPartnerList($this->job->partnersAssoc);
            $this->job->partnersFieldsAssoc = $this->data->modelPartnerField->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc));
            $this->job->partnerFiltersAssoc = $this->data->modelPartnerFilter->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc), true);
        } else {
            $this->job->partnersAssoc = $this->engine->listPartnersForInitialDelivery();
            $this->job->partnerFiltersAssoc = $this->data->modelPartnerFilter->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc), true);
            $this->job->partnersFieldsAssoc = $this->data->modelPartnerField->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc));
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function deliverToCurrentPartner() {
//        if ($this->job->currentPartner->getWrappedCampaignId()) {
//            return $this->deliverToChildCampaign();
//        }
        $this->findPartnerRetryCount();
        if ($this->isPartnerTriedMoreThanCampaignAllows()) {
            return false;
        }

        $this->initializeDelivery();
        $this->transformValuesAndCheckPartnerFilters();
        $this->tryFirstDelivery();
        if ($this->partnerAccepted || $this->isPartnerTriedMoreThanCampaignAllows()) {
            return $this->partnerAccepted;
        }

        $this->retryDeliveryIfNetworkError();
        $this->retryDeliveryIfUsernameProblem();

        return $this->partnerAccepted;
    }
    //--------------------------------------------------------------------------


    public function deliverToChildCampaign() {
        $this->initializeDelivery();
        $this->transformValuesAndCheckPartnerFilters();
        $this->tryFirstDelivery();
        if ($this->partnerAccepted || $this->isPartnerTriedMoreThanCampaignAllows()) {
            return $this->partnerAccepted;
        }

        $this->retryDeliveryIfNetworkError();
        $this->retryDeliveryIfUsernameProblem();

        return $this->partnerAccepted;

//        $this->engine->report(Engine::ACTION_CHILD_CAMPAIGN, $this->job->currentPartner->row);
//        $engine = $this->job->isReposting() ? new Engine_Repost() : new Engine_Submission();
//
//        return false;
    }
    //--------------------------------------------------------------------------


    private function findPartnerRetryCount() {
        if ($this->job->isReposting()) {
            $partnerId = $this->job->currentPartner->getId();
            $this->partnerRetryCount = isset($this->data->attemptsPerPartnerAssoc[$partnerId]) ? (int)$this->data->attemptsPerPartnerAssoc[$partnerId] : 0;
        } else {
            $this->partnerRetryCount = 0;
        }
    }
    //--------------------------------------------------------------------------


    private function isPartnerTriedMoreThanCampaignAllows() {
        return $this->partnerRetryCount > $this->job->campaign->getPartnerRetries();
    }
    //--------------------------------------------------------------------------


    private function initializeDelivery() {
        $this->partnerFields = $this->job->getPartnerFieldsForCurrentPartner();
        $this->calculatePartnerFieldDependence();

        $this->job->lastPartnerWasSkipped = false;
        $this->lastCurlError              = '';
        $this->lastCurlErrorNo            = 0;
    }
    //--------------------------------------------------------------------------

    private function calculatePartnerFieldDependence() {
        // if  exist height_meters, [:change height_centimeters:] to [:height_centimeters_aggregate:]
        // else, change to [:height_centimeters_total:]
        $changeTokenRules = array(
            array('[:height_meters:]', '[:height_centimeters:]', 
                  '[:height_centimeters_aggregate:]', '[:height_centimeters_total:]'),
            array('[:height_feet:]', '[:height_inches:]', 
                   '[:height_inches_aggregate:]', '[:height_inches_total:]')
        );
        
        // get all tokens
        $allTokens = array();
        foreach ($this->partnerFields as $field) {
            $token = $field['value'];
            if (!in_array($token, $allTokens)) {
                $allTokens[] = $token;
            }
        }
        
        // change tokens
        foreach ($this->partnerFields as $fieldName => $field) {
            $currentToken = $field['value'];
            foreach ($changeTokenRules as $rule) {
                if ($currentToken === $rule[1]) {
                    if (in_array($rule[0], $allTokens)) {
                        $this->partnerFields[$fieldName]['value'] = $rule[2];
                    } else {
                        $this->partnerFields[$fieldName]['value'] = $rule[3];
                    }                  
                    break;
                } 
            }
        }
    }
    //--------------------------------------------------------------------------
    
    private function transformValuesAndCheckPartnerFilters() {
        $this->valuesForDelivery = array();
        foreach ($this->partnerFields as $k => $fieldRow) {        
            $this->valuesForDelivery[$k] = $this->fieldHelper->transformValue($fieldRow['value'], $fieldRow['modifier']);
        }

        $this->checkPartnerFilters();
    }
    //--------------------------------------------------------------------------


    private function checkPartnerFilters() {
        $this->partnerFiltersOK = true;

        if (!isset($this->job->partnerFiltersAssoc[$this->job->currentPartner->getId()])) {
            return;
        }

        $filters = $this->job->partnerFiltersAssoc[$this->job->currentPartner->getId()];

        $filters = $this->processSameFieldTypeFilter($filters);

        if ($filters && is_array($filters)) {
            foreach ($filters as $rows) {
                // $rows can be 1 or 2 dimensional array
                if (!is_array($rows['0'])) {
                    $rows = array($rows);
                }
                
                $checkMatching = true;
                
                foreach ($rows as $row) {
                    $ftypeID    = (int)$row['field_type_id'];
                    $sysField   = $row['system_field'];
                    $matchType  = (int)$row['match_type'];
                    $matchValue = $row['match_value'];

                    $dummy = false;
                    $value = isset($this->data->inputData[$ftypeID]) ? $this->data->inputData[$ftypeID] : $this->fieldHelper->valueOfSystemField($sysField, $dummy);
                    if ( ! Engine_Utilities::checkMatching($matchType, $value, $matchValue)) {
                        $checkMatching = false;
                        break;
                    }
                }

                if ($checkMatching) {
                    $action = $rows['0']['filter_action'];
                    switch ($action) {
                        case Model_PartnerFilter::FILTER_ACTION_DONT_DELIVER:
                            $this->partnerFiltersOK = false;
                            $this->job->lastPartnerWasSkipped = true;
                            $this->job->message = 'Partner Rules';
                            // check which rule is not satisfied
                            if (!empty($sysField)) {
                                $this->job->message .= ' - ' . $sysField  . ' - ' . $value;
                            } else if (!empty($ftypeID)) {
                                $fieldTypeModel = new Model_FieldType();
                                $row = $fieldTypeModel->get($ftypeID);
                                isset($row['name']) ? $this->job->message .= ' - ' . $row['name'] . ' - ' . $value : ""; 
                            }
                            break;

                        case Model_PartnerFilter::FILTER_ACTION_CHANGE_SUCCESS_URL:
                            $newSuccessUrl = $row['argument'];
                            $this->job->currentPartner->setSuccessUrl($newSuccessUrl);
                            break;
                        
                        case Model_PartnerFilter::FILTER_ACTION_CHANGE_DELIVERY_URL:
                            $newDeliveryAddr = $row['argument'];
                            $this->job->currentPartner->setDeliveryAddr($newDeliveryAddr);
                            break;
                    }
                }
            }
        }

        $callbackReportData = array($this->partnerFiltersOK);
        $this->engine->report(Engine::ACTION_FILTERED, $callbackReportData);
    }
    //--------------------------------------------------------------------------

    
    public static function processSameFieldTypeFilter($filters, $campaignFilterFlag = null) {
        $result = array();
        $finalResult = array();
        
        foreach ($filters as $index => $row) {
            $fieldTypeId = $row['field_type_id'];
            $action = $row['filter_action'];
            $sameFieldTypeIndex   = self::getSameFieldTypeIndex($fieldTypeId, $action, $result);
            
            if (empty($campaignFilterFlag)) {
                $systemField = $row['system_field'];
                $sameSystemFieldIndex = self::getSameSystemFieldIndex($systemField, $action, $result);
            }

            if ($sameFieldTypeIndex !== false) {
                if (!empty($result[$sameFieldTypeIndex]['0']) && is_array($result[$sameFieldTypeIndex]['0'])) {
                    $result[$sameFieldTypeIndex][] = $row;
                } else {
                    $result[$sameFieldTypeIndex] = array($filters[$sameFieldTypeIndex], $row);
                }
            } elseif (empty($campaignFilterFlag) && $sameSystemFieldIndex !== false) {
                if (!empty($result[$sameSystemFieldIndex]['0']) && is_array($result[$sameSystemFieldIndex]['0'])) {
                    $result[$sameSystemFieldIndex][] = $row;
                } else {
                    $result[$sameSystemFieldIndex] = array($filters[$sameSystemFieldIndex], $row);
                }
            } else {
                $result[$index] = $row;
            }
        }
        
        // group result in seperate groups
        foreach ($result as $index => $row) {
            if (is_array($row['0'])) {
                // sort values in ascending order
                usort($row, array('Engine_Delivery', "compareMatchValue"));

                // index of group in new array
                $newGroupIndex          =   false;
                $notEqualGroupIndex     =   false;
                $notStartWithGroupIndex =   false;
                $notEndWithGroupIndex   =   false;
                $notContainGroupIndex   =   false;
                $notMatchRegexIndex     =   false;
                
                foreach ($row as $rule) {
                    $matchType = intval($rule['match_type']);
                    
                    switch ($matchType) {
                        case Model_PartnerFilter::FILTER_MATCH_GREATER_THAN:
                        case -Model_PartnerFilter::FILTER_MATCH_LESS_THAN:
                            $newGroupIndex = count($finalResult);
                            $finalResult[$newGroupIndex][] = $rule;
                            break;
                        
                        case Model_PartnerFilter::FILTER_MATCH_LESS_THAN:
                        case -Model_PartnerFilter::FILTER_MATCH_GREATER_THAN:
                            $newGroupIndex !== false || $newGroupIndex = count($finalResult);
                            $finalResult[$newGroupIndex][] = $rule;
                            $newGroupIndex = false;
                            break;
                        
                        case -Model_PartnerFilter::FILTER_MATCH_EQUALS:
                            $notEqualGroupIndex !== false || $notEqualGroupIndex = count($finalResult);
                            $finalResult[$notEqualGroupIndex][] = $rule;
                            break;
                            
                        case -Model_PartnerFilter::FILTER_MATCH_STARTS_WITH:
                            $notStartWithGroupIndex !== false || $notStartWithGroupIndex = count($finalResult);
                            $finalResult[$notStartWithGroupIndex][] = $rule;
                            break;
                        
                        case -Model_PartnerFilter::FILTER_MATCH_ENDS_WITH:
                            $notEndWithGroupIndex !== false || $notEndWithGroupIndex = count($finalResult);
                            $finalResult[$notEndWithGroupIndex][] = $rule;
                            break;
                        
                        case -Model_PartnerFilter::FILTER_MATCH_CONTAINS:
                            $notContainGroupIndex !== false || $notContainGroupIndex = count($finalResult);
                            $finalResult[$notContainGroupIndex][] = $rule;
                            break;
                        
                        case -Model_PartnerFilter::FILTER_MATCH_REGEX:
                            $notMatchRegexIndex !== false || $notMatchRegexIndex = count($finalResult);
                            $finalResult[$notMatchRegexIndex][] = $rule;
                            break;
                        
                        default:
                            $finalResult[] = $rule;
                            break;
                        
                    }
                }
            } else {
                $finalResult[] = $row;
            }
        }

        return $finalResult;
    }
    //--------------------------------------------------------------------------
    
    
    public static function compareMatchValue($a, $b) {
        if ($a['match_value'] == $b['match_value']) {
            return 0;
        }
        return ($a['match_value'] < $b['match_value']) ? -1 : 1;
    }
    //--------------------------------------------------------------------------
    
    
    public static function getSameFieldTypeIndex($fieldTypeId, $action, $result) {
        if (empty($fieldTypeId)) {
            return false;
        }
        
        foreach ($result as $index => $rows) {
            if (!is_array($rows['0'])) {
                $rows = array($rows);
            }
            foreach ($rows as $row) {
                if ($row['field_type_id'] == $fieldTypeId && $row['filter_action'] == $action) {
                    return $index;
                }
            }
        }
        
        return false;
    }
    //--------------------------------------------------------------------------
    
    
    public static function getSameSystemFieldIndex($systemField, $action, $result) {
        if (empty($systemField)) {
            return false;
        }

        foreach ($result as $index => $rows) {
            if (!is_array($rows['0'])) {
                $rows = array($rows);
            }
            foreach ($rows as $row) {
                if ($row['system_field'] == $systemField && $row['filter_action'] == $action) {
                    return $index;
                }
            }
        }
        
        return false;
    }
    //--------------------------------------------------------------------------
    
    
    private function tryFirstDelivery() {
        $this->deliveryAttemptType = 1;
        $this->deliverAndLog();
        $this->engine->report($this->partnerAccepted ? Engine::ACTION_DELIVERED : Engine::ACTION_DELIVERY_FAILED, $this->job->currentPartner->row);

        if ($this->job->isReposting()) {
            $this->job->repostCount++;
        }

        if (!$this->lastCurlError) {
            $this->partnerRetryCount++;
        }
    }
    //--------------------------------------------------------------------------


    private function retryDeliveryIfNetworkError() {
        $campaignTimeout = intval($this->job->campaign->getCampaignTimeout());
        if ($campaignTimeout > 0 && $campaignTimeout <= time() - $this->job->startTime) {
            return false;
        }
        
        if (!$this->partnerAccepted && $this->lastCurlError) {
            $this->deliveryAttemptType = 2;
            $this->deliverAndLog();
            $this->engine->report($this->partnerAccepted ? Engine::ACTION_DELIVERED : Engine::ACTION_DELIVERY_FAILED, $this->job->currentPartner->row);
        }
    }
    //--------------------------------------------------------------------------


    private function retryDeliveryIfUsernameProblem() {
        if ($this->partnerAccepted || !$this->usernameProblem) {
            return;
        }

        $originalUsername = isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME]) ? 
                                    $this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME] : NULL;
        $this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME] = $this->suggestDifferentUsername();

        try {
            $this->deliveryAttemptType = 3;

            // Needs to be done again since we now changed submission's input data
            $this->transformValuesAndCheckPartnerFilters();

            $this->deliverAndLog();
            $this->engine->report($this->partnerAccepted ? Engine::ACTION_DELIVERED : Engine::ACTION_DELIVERY_FAILED, $this->job->currentPartner->row);
            $this->job->repostCount++;

            // Finally
            if (!empty($originalUsername)) {
                $this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME] = $originalUsername;
            } else {
                unset($this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME]);
            }
        } catch (Exception $e) {
            // Finally
            if (!empty($originalUsername)) {
                $this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME] = $originalUsername;
            } else {
                unset($this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME]);
            }
            throw $e;
        }

    }
    //--------------------------------------------------------------------------


    private function deliverAndLog() {
        $this->partnerAccepted = false;
        $this->partnerResponse = '';

        if ($this->partnerFiltersOK) {
            $this->deliverAndAnalyzeResponse();
        }

        DB::$db->query("START TRANSACTION");
        $this->logDelivery();
        DB::$db->query("COMMIT");

        $this->data->lastPartnerDeliveredTo     = $this->job->currentPartner->row;
        $this->data->lastDataDeliveredToPartner = $this->valuesForDelivery;
    }
    //--------------------------------------------------------------------------


    private function deliverAndAnalyzeResponse() {
        $this->usernameProblem     = false;
        $this->usernameSuggestions = array();

        $this->partnerResponse = $this->postFieldValuesAndGetResponse();
        $this->engine->report(Engine::ACTION_SENT_VALS, $this->valuesForDelivery);

        $report = array('result' => $this->partnerResponse);
        $this->engine->report(Engine::ACTION_PARTNER_RESPONSE, $report);

        if ($this->job->currentPartner->getWrappedCampaignId()) {
            $this->parseHeader($this->partnerHeader);
        }

        if ($this->partnerResponse === false && !$this->job->currentPartner->getWrappedCampaignId()) {
            $this->partnerAccepted = false;
            $this->partnerResponse = "Error when sending data to partner: " . $this->lastCurlError;
        } else if ($this->job->currentPartner->getWrappedCampaignId()) {
            $this->partnerAccepted = $this->wrapperPartnerAccepted();
            
            $wrappedCampaignId = $this->job->currentPartner->getWrappedCampaignId();
            if (SingletonRegistry::getModelCampaign()->checkCampaignIsQueuedDelivery($wrappedCampaignId)) {
                $this->partnerAccepted = true;
            }
            // skip inactive child campaigns
            if (!SingletonRegistry::getModelCampaign()->checkCampaignIsActive($wrappedCampaignId)) {
                $this->partnerAccepted = false;
                $this->partnerFiltersOK = false;
            }
            
        } else {
            $this->prependHttpResponseCodeToPartnerResponseIfNot2XX();
            $this->parsePartnerResponseIfRequested();

            $this->partnerAccepted = $this->hasPartnerAccepted();
        }
    }
    //--------------------------------------------------------------------------


    private function parseHeader($header) {
        preg_match("/x-lw-success: (.*)/", $header, $success);
        preg_match("/x-lw-url: (.*)/", $header, $url);

        if ( ! empty($success) && trim($success[1]) == '1') {
            if (strpos($url[1],'success_url')) {
                if ($this->data->successUrlAppend != '') {
                    $this->data->successUrlAppend .= "&";
                }

                $urlSplit =  explode('?',$url[1]);
                $this->data->successUrlAppend .= trim($urlSplit[1]);
            } else {
                if ($this->data->successUrlAppend != '') {
                    $this->data->successUrlAppend .= "&";
                }

                $this->data->successUrlAppend .= 'success_url[]=' .urlencode(trim($url[1]));
            }
            $this->data->successUrl        = trim($url[1]);
            $this->data->success           = true;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc If http response code is not 2XX then we add the code to partnerResponse so that our
     * users can see it in reporting.
     */
    private function prependHttpResponseCodeToPartnerResponseIfNot2XX() {
        $rc = $this->curlHelper->getLastHttpResponseCode();
        $httpRC = '' . $rc;
        if ($rc && ($httpRC[0] != '2')) {
            $this->partnerResponse = "HTTP Response Code: " . $rc . "\n\n$this->partnerResponse";
        }
    }
    //--------------------------------------------------------------------------


    private function parsePartnerResponseIfRequested() {
        $responseType = $this->job->currentPartner->getResponseType();
        if ($this->job->currentPartner->isParseResponse()) {
            if ($responseType == Model_Partner::RESPONSE_TYPE_TEXT  && stripcslashes($this->job->currentPartner->getDelimiter())) {
                $this->job->partnerResponseParsed = explode(stripcslashes($this->job->currentPartner->getDelimiter()), $this->partnerResponse);
            } else if ($responseType == Model_Partner::RESPONSE_TYPE_JSON) {
                $decodedResponse = json_decode($this->partnerResponse, true);
                if (!empty($decodedResponse)) {
                    $this->job->partnerResponseParsed = $decodedResponse;
                }
            } else if ($responseType == Model_Partner::RESPONSE_TYPE_XML) {
                try {
                    $response = Helper_XML::removeXmlNamespace($this->partnerResponse);
                    $this->job->partnerResponseParsed = new SimpleXMLElement($response);
                } catch (Exception $ex) {
                    
                    $this->job->partnerResponseParsed = new SimpleXMLElement('<lead></lead>');
                }
                
            } 
        }
    }
    //--------------------------------------------------------------------------


    private function postFieldValuesAndGetResponse() {
        $this->lastCurlError   = '';
        $this->lastCurlErrorNo = 0;
        $url = $this->job->currentPartner->getDeliveryAddr();

        foreach ($this->engine->flags->fakeUrlResponses as $urlPrefix => $fakeResponse) {
            if (stripos($url, $urlPrefix) === 0) {
                return $fakeResponse;
            }
        }

        $campaignTimeout = intval($this->job->campaign->getCampaignTimeout());
        if (!$this->job->campaign->isPingPostCampaign() && $campaignTimeout > 0) {
            $campaignTimeout -= (time() - $this->job->startTime);
            if ($campaignTimeout <= 0) {
                return false;
            }
        }
        
        $result = $this->curlHelper->postToPartner($this->job->currentPartner, $this->valuesForDelivery, $campaignTimeout, $this->job->currentDelivery);
        $this->partnerHeader = $this->curlHelper->getHeader();

        return $result;
    }
    //--------------------------------------------------------------------------


    private function logDelivery() {
        $logData = array(
            'incoming_id'      => $this->data->incomingId,
            'partner_id'       => $this->job->currentPartner->getId(),
            'is_accepted'      => $this->partnerAccepted ? '1' : '0',
            'reject_reason'    => $this->getRejectReason(),
            'is_repost'        => $this->job->markAsRepost ? '1' : '0',
            'revenue'          => $this->getRevenueFromLastDelivery(),
            'data'             => http_build_query($this->valuesForDelivery),
            'partner_response' => $this->partnerResponse . '||HEADER||' . $this->partnerHeader,
            'message'          => $this->job->message
        );

        $processingShadowCampaign = $this->data->incRow && ($this->data->incRow['campaign_id'] != $this->job->campaign->getId());

        $deliveryId = $this->data->logDelivery->insertWithApiReportData(
            $logData,
            $this->job->campaign->getId(),
            $this->job->campaign->getUserId(),
            $this->data->incomingStatus,
            $this->data->incomingTimestamp,
            $processingShadowCampaign
        );
        $this->engine->report(Engine::ACTION_DELIVERY_LOGGED, $logData);

        $this->data->deliveryIDs[] = $deliveryId;

        if ($this->isLastDeliveryTimeout() && $deliveryId) {
            $this->data->logDelivery->markAsTimeout($deliveryId);
            $this->data->logDelivery->updatePartnerTimeoutCount($this->job->currentPartner->getId());
        } else {
            $this->data->logDelivery->updatePartnerResponseTime($this->job->currentPartner->getId(), $this->curlHelper->getLastDuration());
        }

        if ($deliveryId && ($this->curlHelper->getLastProcessedTemplate() !== false)) {
            $this->data->logDelivery->logDeliveryTemplateSent($deliveryId, $this->curlHelper->getLastProcessedTemplate());
        }

        if (Config::$repostThreadLogging) {
            $repostLogData = array(
                'thread_id'    => Config::$repostThreadId,
                'delivery_id'  => $deliveryId,
                'incoming_id'  => (int)$this->data->incomingId,
                'campaign_id'  => (int)$this->job->campaign->getId(),
                'partner_id'   => (int)$this->job->currentPartner->getId(),
                'user_id'      => $this->job->campaign->getUserId(),
                'attempt_type' => $this->deliveryAttemptType,
                'result'       => $this->partnerAccepted ? 1 : 0
            );
            $this->data->logIncomingRepost->logToRepostLog($repostLogData);
        }
    }
    //--------------------------------------------------------------------------


    private function suggestDifferentUsername() {
        if ($this->usernameSuggestions) {
            return $this->usernameSuggestions[rand(0, sizeof($this->usernameSuggestions) - 1)];
        } else if (!empty($this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME])){
            return $this->data->inputData[Model_CampaignField::FIELD_TYPE_USERNAME] . sprintf('%04d', rand(0, 9999));
        } else if (!empty($this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL])) {
            $email = $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL];
            $derivedName =  preg_replace("/[^A-Za-z0-9 ]/", '', strstr($email, '@', true));
            return $derivedName . sprintf('%04d', rand(0, 9999));
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function getRevenueFromLastDelivery() {

        if ($this->partnerAccepted) {
            $responseType    = $this->job->currentPartner->getResponseType();
            $isParseResponse = $this->job->currentPartner->isParseResponse();
            $pricePerLead    = $this->job->currentPartner->getPricePerLead();
            if (
                $responseType == Model_Partner::RESPONSE_TYPE_TEXT 
                && preg_match('/^\\$[\\d]{1,}/', $pricePerLead)
                && $isParseResponse
            ) {
                // We're looking for revenue sum in partner response, which is written in partner settings page
                // as (for example) $4 and it doesn't mean 4 dollars, it means 4th field in partner response when parsed

                // Number after dollar sign
                $responsePartNumber = (int)substr($this->job->currentPartner->getPricePerLead(), 1);
                
                // References are 1-based, like $1 for first field. Substract 1 to get zero-based index
                $responsePartNumber -= 1;

                $revenue = trim(empty($this->job->partnerResponseParsed[$responsePartNumber]) ? 0 : $this->job->partnerResponseParsed[$responsePartNumber]);

                // Make sure we accept numbers with or without leading $ sign
                if (substr($revenue, 0, 1) == '$') {
                    $revenue = substr($revenue, 1);
                }

                // Locale fix, just in case response is from a european partner that separates decimal part with a comma
                $revenue = str_replace(',', '.', $revenue);

                if (is_numeric($revenue)) {
                    return $revenue;
                }

            } else if (
                $responseType == Model_Partner::RESPONSE_TYPE_JSON 
                && Model_Partner::isValidJsonPathExpression($pricePerLead)
                && $isParseResponse
            ) {
                $price = $this->getValueFromPartnerJsonResponseByPath($pricePerLead);
                if (!empty($price)) {
                    return $price;
                } else if (is_numeric($pricePerLead)) {
                    return $pricePerLead;
                }
                
            } else if (
                $responseType == Model_Partner::RESPONSE_TYPE_XML  
                && Model_Partner::isValidXPathExpression($pricePerLead)
                && $isParseResponse
            ) {
                $price = $this->getValueFromPartnerXmlResponseByPath($pricePerLead);
                if (!empty($price)) {
                    return $price;
                } else if (is_numeric($pricePerLead)) {
                    return $pricePerLead;
                }
                
            } else {
                // There was no dollar sign so we assume all leads to this partner generate same revenue
                return $this->job->currentPartner->getPricePerLead();
            }
        }

        return 0;
    }
    //--------------------------------------------------------------------------

    public function getValueFromPartnerJsonResponseByPath($path) {
        $value = jsonPath($this->job->partnerResponseParsed, $path);
        if(isset($value[0])) {
            return $value[0];
        }
        return '';
    }
    //--------------------------------------------------------------------------
    
    public function getValueFromPartnerXmlResponseByPath($path) {
        $value = @($this->job->partnerResponseParsed->xpath($path));
        if(isset($value[0])) {
            return trim((string)$value[0][0]);
        }
        return '';
    }
    //--------------------------------------------------------------------------
    
    
    private function getRejectReason() {
        if (!$this->partnerAccepted && $this->lastCurlError) {
            return 'curl';
        } else {
            return $this->partnerFiltersOK ? ($this->usernameProblem ? 'username' : '') : 'skipped';
        }
    }
    //--------------------------------------------------------------------------


    public function valueOfSystemField($fieldName, & $found) {
        return $this->fieldHelper->valueOfSystemField($fieldName, $found);
    }
    //--------------------------------------------------------------------------


    private function wrapperPartnerAccepted() {
        return $this->data->success;
    }
    //--------------------------------------------------------------------------


    private function hasPartnerAccepted($partnerData = null) {
        $isAccepted       = false;

        $partnerResponse        = $this->partnerResponse;
        $successKeyword         = $this->job->currentPartner->getSuccessKeyword();
        $failureKeyword         = $this->job->currentPartner->getFailureKeyword();
        $isShoudRetry           = $this->job->currentPartner->isShouldRetry();
        $usernameFailedKeyWord  = $this->job->currentPartner->getUsernameFailedKeyword();
        $postingType            = $this->job->currentPartner->getPostingType();
        
        if (!empty($partnerData)) {
            $partnerResponse        = $partnerData['response'];
            $successKeyword         = trim($partnerData['success_keyword']);
            $failureKeyword         = trim($partnerData['failure_keyword']);
            $isShoudRetry           = !empty($partnerData['should_retry']);
            $usernameFailedKeyWord  = trim($partnerData['username_failed_keyword']);
            $postingType            = (int)$partnerData['posting_type'];
        }
        
        if ($partnerResponse) {
            foreach (explode(',', $successKeyword) as $word) {
                if ($word && (strpos($partnerResponse, $word) !== false)) {
                    $isAccepted = true;
                }
            }

            foreach (explode(',', $failureKeyword) as $word) {
                if ($word && (strpos($partnerResponse, $word) !== false)) {
                    $isAccepted = false;
                }
            }

            if (!$isAccepted && $isShoudRetry && $usernameFailedKeyWord) {
                foreach (explode(',', $usernameFailedKeyWord) as $word) {
                    if ($word && (strpos($partnerResponse, $word) !== false)) {
                        $this->usernameProblem = true;
                        $isAccepted = false;
                        if ($postingType == Model_Partner::POST_TYPE_AFF) {
                            $this->usernameSuggestions = Engine_Utilities::readAFFUsernameSuggestions($partnerResponse);
                        }
                        break;
                    }
                }
            }
        }

        return $isAccepted;
    }
    //--------------------------------------------------------------------------


    private function isLastDeliveryTimeout() {
        return $this->lastCurlErrorNo == CURLE_OPERATION_TIMEDOUT;
    }
    //--------------------------------------------------------------------------
    
    
    public function logCapExceededDelivery($partner, $capInfo) {
        // log Cap Exceeded infomation into Reporting tab with corresponding message
        $mpc = SingletonRegistry::getModelPartnerCap();
        $message = '';
        
        if ($capInfo['cap_type'] === Model_PartnerCap::CAP_TYPE_DELIVERY) {
            $message .= 'Delivery Cap: ';
        } else {
            $message .= 'Budget Cap: ';
        }
        
        $intervals = $mpc->getIntervals();
        $capType = $capInfo['interval'];
        $message .= $intervals[$capType];
        
        $logData = array(
            'incoming_id'      => $this->data->incomingId,
            'partner_id'       => $partner['id'],
            'is_accepted'      => '0',
            'reject_reason'    => 'skipped',
            'is_repost'        => $this->job->markAsRepost ? '1' : '0',
            'revenue'          => '0',
            'data'             => '',
            'partner_response' => '',
            'message'          => $message
        );

        $deliveryId = $this->data->logDelivery->insertWithApiReportData(
            $logData,
            $this->job->campaign->getId(),
            $this->job->campaign->getUserId(),
            $this->data->incomingStatus,
            $this->data->incomingTimestamp
        );
    }
    //--------------------------------------------------------------------------
}

