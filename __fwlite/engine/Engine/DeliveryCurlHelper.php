<?php

class Engine_DeliveryCurlHelper {

    /**
     * @var Engine_Delivery
     */
    private $deliveryEngine = null;

    /**
     * @var Engine
     */
    private $engine = null;

    /**
     * @desc as returned by curl_info() for last curl execution
     */
    private $lastHttpResponseCode = '200';

    /**
     * @desc Will be false by default, if we processed XML or JSON template this will hold the
     * text after processing
     */
    private $lastProcessedTemplate = false;

    /**
     * @var Partner
     */
    private $partner;

    private $header;

    private $duration = 0;


    public function __construct(Engine_Delivery $engineDelivery, Engine $engine) {
        $this->deliveryEngine = $engineDelivery;
        $this->engine         = $engine;
    }
    //--------------------------------------------------------------------------


    public function getLastHttpResponseCode() {
        return $this->lastHttpResponseCode;
    }
    //--------------------------------------------------------------------------


    public function getLastProcessedTemplate() {
        return $this->lastProcessedTemplate;
    }
    //--------------------------------------------------------------------------


    public function getLastDuration() {
        return $this->duration;
    }
    //--------------------------------------------------------------------------


    public function postToPartner(Partner $partner, array $fieldsAssoc, $campaignTimeout, $delivery = array()) {
        $this->partner = $partner;
        $this->deliveryEngine->setLastCurlError('', 0);

        $this->lastProcessedTemplate = false;

        $postingType = $this->partner->getPostingType();
        $deliveryUrl = $this->partner->getDeliveryAddr();

        switch ($postingType) {

            case Model_Partner::POST_TYPE_GET:
                return $this->makeGetRequest($deliveryUrl, $fieldsAssoc, $campaignTimeout);

            case Model_Partner::POST_TYPE_POST:
                return $this->makePostRequest($deliveryUrl, $fieldsAssoc, $campaignTimeout);

            case Model_Partner::POST_TYPE_AFF:
                return $this->makeAdultFriendFinderRequest($deliveryUrl, $fieldsAssoc);

            case Model_Partner::POST_TYPE_XML:
            case Model_Partner::POST_TYPE_XML_FIELD:
                return $this->makeXMLRequest($postingType, $deliveryUrl, $fieldsAssoc, $campaignTimeout, $delivery);

            case Model_Partner::POST_TYPE_JSON:
                return $this->makeJsonRequest($deliveryUrl, $fieldsAssoc, $campaignTimeout);

            default:
                return '';
        }
    }
    //--------------------------------------------------------------------------


    protected function makePostRequest($deliveryUrl, array $values, $campaignTimeout) {
        $this->lastProcessedTemplate = $deliveryUrl . " " . http_build_query($values);
        
        $ch = $this->createCurl($deliveryUrl, $campaignTimeout);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
        curl_setopt($ch, CURLOPT_HEADER,false);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,array($this,'header_callback'));

        $result = $this->exec($ch);

        return $result;
    }
    //--------------------------------------------------------------------------


    protected function makeGetRequest($deliveryUrl, array $values, $campaignTimeout) {
        $this->lastProcessedTemplate = $deliveryUrl . " " . http_build_query($values);
        
        if (strpos($deliveryUrl, '?') === false) {
            $deliveryUrl .= '?';
        } else {
            $deliveryUrl .= '&';
        }
        $deliveryUrl .= http_build_query($values);

        $ch = $this->createCurl($deliveryUrl, $campaignTimeout);

        curl_setopt($ch, CURLOPT_HEADER,false);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,array($this,'header_callback'));

        $result = $this->exec($ch);

        return $result;
    }
    //--------------------------------------------------------------------------


    public function header_callback($ch, $header_line) {
        $this->header .= $header_line;

        return strlen($header_line);
    }
    //--------------------------------------------------------------------------


    private function createCurl($deliveryUrl, $campaignTimeout) {
        $this->header = '';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        
        // check HTTPAUTH
        $urlComponents = parse_url($deliveryUrl);
        if (!empty($urlComponents['user']) && !empty($urlComponents['pass'])) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC|CURLAUTH_NTLM);
            curl_setopt($ch, CURLOPT_USERPWD, $urlComponents['user']. ':' . $urlComponents['pass']);
            
            $deliveryUrl = str_replace($urlComponents['user']. ':' . $urlComponents['pass'].'@', '', $deliveryUrl);
        }

        curl_setopt($ch, CURLOPT_URL, $deliveryUrl);
        
        // get timeout setting.
            // if parter timeout is set, use partner time time
            // otherwise, use default timeout
        $partnerTimeout = $this->partner->getCurlTimeout();
        $timeout = (!empty($partnerTimeout)) ? 
                        intval($partnerTimeout) : (Engine_Settings::$settings->deliveryTimeout/2);
        
        // campaign timeout
        if ($campaignTimeout > 0) {
            $timeout = min($campaignTimeout, $timeout);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        if (strpos($deliveryUrl, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        return $ch;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc executes the curl request and returns partner's response
     */
    private function exec($ch) {
        $start    = microtime(true);
        $response = curl_exec($ch);

        if ($response === false) {
            $this->deliveryEngine->setLastCurlError(curl_error($ch), curl_errno($ch));
            $this->duration = 0;
        } else {
            $this->lastHttpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->duration = microtime(true) - $start;
        }

        curl_close($ch);
        return $response;
    }
    //--------------------------------------------------------------------------


    protected function makeXMLRequest($postingType, $deliveryUrl, array $values, $campaignTimeout, $delivery = array()) {
        $xml = $this->partner->getTemplate();
        foreach ($values as $k => $v) {
            $xml = str_replace("%$k%", escapeHtml($v), $xml);
        }
        
        // remove newline, replace multiple spaces & tabs with single space
        $xml = trim(preg_replace("/\s+/", ' ', str_replace(array("\n", "\r", "\r\n", "\t"), '', $xml)));
        $reportingXml = $xml; // clone of xml without encoding data.
        
        // xml field posting type
        if ($postingType == Model_Partner::POST_TYPE_XML_FIELD && !empty($xml)) {
            $value        = $this->partner->getXmlFieldName();
            $xml          = '&' . $value . '=' . urlencode($xml);
            $reportingXml = '&' . $value . '=' . $reportingXml;
            
            $trans = $this->partner->getTransactionIdFieldName();
            if (!empty($trans) && isset($delivery['transaction_id'])) {
                $xml            .= '&' . $trans . '=' . $delivery['transaction_id'];
                $reportingXml   .= '&' . $trans . '=' . $delivery['transaction_id'];
            }
        }
        
        $this->lastProcessedTemplate = $reportingXml;

        $ch = $this->createCurl($deliveryUrl, $campaignTimeout);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        if ($postingType == Model_Partner::POST_TYPE_XML_FIELD) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                         "Accept: text/xml",
                         "Cache-Control: no-cache",
                         "Pragma: no-cache",
                         //"SOAPAction: \"\"",
                         'Content-Length: ' . strlen($xml)));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                             "Accept: text/xml",
                             "Cache-Control: no-cache",
                             "Pragma: no-cache",
                             //"SOAPAction: \"\"",
                             "Content-Type: text/xml",
                             'Content-Length: ' . strlen($xml)));
        }

        $cookiePath = tempnam('/tmp', 'cookie');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);

        $reportData = array('processed' => $reportingXml, 'type' => Model_Partner::POST_TYPE_XML);
        $this->engine->report(Engine::ACTION_TEMPLATE_PROCESSED, $reportData);
        $response = $this->exec($ch);
        unlink($cookiePath);

        return $response;
    }
    //--------------------------------------------------------------------------


    protected function makeJsonRequest($deliveryUrl, array $values, $campaignTimeout) {
        $jsonText = $this->partner->getTemplate();
        foreach ($values as $k => $v) {
            $replacement = json_encode($v);
            if ((substr($replacement, 0, 1) == '"')&& (substr($replacement, -1) == '"')) {
                $replacement = substr($replacement, 1, -1);
            }
            $jsonText = str_replace("%$k%", $replacement, $jsonText);
        }
        
        // remove newline, replace multiple spaces & tabs with single space
        $jsonText = trim(preg_replace("/\s+/", ' ', str_replace(array("\n", "\r", "\r\n", "\t"), '', $jsonText)));
        
        $this->lastProcessedTemplate = $jsonText;

        $ch = $this->createCurl($deliveryUrl, $campaignTimeout);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonText);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                         "Cache-Control: no-cache",
                         "Pragma: no-cache",
                         'Content-Type: application/json',
                         'Content-Length: ' . strlen($jsonText)));

        $reportData = array('processed' => $jsonText, 'type' => Model_Partner::POST_TYPE_JSON);
        $this->engine->report(Engine::ACTION_TEMPLATE_PROCESSED, $reportData);
        $response = $this->exec($ch);

        return $response;
    }
    //--------------------------------------------------------------------------


    protected function makeAdultFriendFinderRequest($deliveryUrl, array $values) {
        $ffFields = explode(',', "uname,fname,lname,password,email,zip,city,state,country,sex,birthday,newpassword,subpid,title,profile,looking_for_desc,looking_for_person,ip_address,language");
        $ffValues = array();
        foreach ($ffFields as $field) {
            $ffValues[] = isset($values[$field]) ? $values[$field] : '';
        }
        $docket = call_user_func_array(array($this->partner, 'FF_Encrypt_Ticket'), $ffValues);
        $data = array('docket' => $docket, 'ip_address' => $_SERVER['REMOTE_ADDR']);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $deliveryUrl . '?' . http_build_query($data));
        curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12');
        curl_setopt($ch,CURLOPT_REFERER, 'http://www.google.com');
        curl_setopt($ch,CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT, 60);

        return $this->exec($ch);
    }
    //--------------------------------------------------------------------------


    public function getHeader() {
        return ($this->header) ? $this->header : false;
    }
    //--------------------------------------------------------------------------
}
