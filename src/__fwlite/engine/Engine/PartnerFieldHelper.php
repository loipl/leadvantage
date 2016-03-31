<?php


/**
 * @desc For modifying partner fields, [Email], [:system_field:] etc
 *
 */
class Engine_PartnerFieldHelper {
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

    protected $externalTokens = false;

    /**
     * @desc this is for "Dictionaries" feature, which means change text from original submission using given dictionary from DB
     */
    protected $dictionariesAssoc = array();


    public function __construct(Engine $engine, Engine_Data $data, Engine_Job $job) {
        $this->engine = $engine;
        $this->data   = $data;
        $this->job    = $job;
    }
    //--------------------------------------------------------------------------


    public function transformValue($value, $modifier) {
        preg_match_all('/\\[([\\w]{1,}|:[\\w]{1,}:)\\]/i', $value, $matches);

        foreach (array_unique($matches[1]) as $ftype) {
            $isSystemField = ($ftype[0] == ':') && (substr($ftype, -1) == ':');
            if ($isSystemField) {
                $dummy       = false;
                $replacement = $this->valueOfSystemField($ftype, $dummy);
                $ftypeId     = 0;
            } else {
                $ftypeId     = array_search($ftype, $this->data->fieldTypes);
                $replacement = isset($this->data->inputData[$ftypeId]) ? $this->data->inputData[$ftypeId] : '';
            }

            if ($modifier) {
                $replacement = $this->modifyValue($ftypeId, $replacement, $modifier);
            }

            $value = str_replace("[$ftype]", $replacement, $value);
        }
        return $value;
    }
    //--------------------------------------------------------------------------


    public function valueOfSystemField($fieldName, & $found) {
        return trim($this->valueOfSystemFieldInner($fieldName, $found));
    }
    //--------------------------------------------------------------------------


    private function valueOfSystemFieldInner($fieldName, & $found) {
        $value = $this->tryToOverrideField($fieldName, $found);
        if (!empty($value) && $found) {
            return $value;
        }

        if (in_array($fieldName, Model_CampaignField::listBuiltInSystemFields())) {
            $value = $this->builtInValue($fieldName, $found);
            if (!empty($value) && $found) {
                return $value;
            }
        }
        if (isset($this->data->derivedTokens[$fieldName])) {
            $value = $this->derivedValue($fieldName, $found);
            if (!empty($value) && $found) {
                return $value;
            }
        }

        $value = $this->geoipValue($fieldName, $found);
        if (!empty($value) && $found) {
            return $value;
        }

        $email = $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL];

        $normalizedToken = ExternalLookup_Base::normalizeTokenName($fieldName);

        /* @var $modelCampaignField Model_CampaignField */
        $modelCampaignField = SingletonRegistry::getSingleInstance('Model_CampaignField');
        $fieldTypeId = $modelCampaignField->getFieldIdFromName($normalizedToken);

        if (!empty($email) && !empty($fieldTypeId)) {
            /* @var $modelProfile Model_Profile */
            $modelProfile = SingletonRegistry::getSingleInstance('Model_Profile');
            $profileData = $modelProfile->getProfileDataForEmail($email);
            if (!empty($profileData[$fieldTypeId])) {
                $found = true;
                return $profileData[$fieldTypeId];
            }

            $profileInferredData = $modelProfile->getProfileInferredDataForEmail($email);
            if (!empty($profileInferredData[$normalizedToken])) {
                $found = true;
                return $profileInferredData[$normalizedToken];
            }
        }

        $this->externalTokens = ExternalLookup_Registry::listTokens2Names();
        ExternalLookup_Registry::removeTokensBasedOnUserCap($this->externalTokens, Auth::maxCap($this->data->ownerUserCaps), $this->data->usedFieldTypes);

        if (isset($this->externalTokens[$fieldName])) {
            if (ExternalLookup_Registry::checkTokenAccess($fieldName, Auth::maxCap($this->data->ownerUserCaps))) {
                if ($this->tryToOverrideField(':country_code:', $found) == 'US' || $this->geoIpValue(':country_code:', $found) == 'US') {
                    $found = true;
                    return $this->executeExternalLookup($fieldName);
                }
            }
        }
        
        if ($fieldName === Model_CampaignField::SYS_TRANSACTION_ID 
            && isset($this->job->currentDelivery['transaction_id'])
        ) {
            $found = true;
            return $this->job->currentDelivery['transaction_id'];
        }

        return false;
    }
    //--------------------------------------------------------------------------


    private function executeExternalLookup($fieldName) {
        $lookupEngine = ExternalLookup_Registry::getNamedFeature($this->externalTokens[$fieldName]);
        $value        = $lookupEngine->lookup($this->data->inputData, $fieldName);

        if ($value) {
            $inferred = array(str_replace(':', '', $fieldName) => $value);
            $this->data->modelProfile->writeProfileInferredData((int)$this->job->campaign->getUserId(), $this->data->inputData, $inferred, $this->data->profileId);
        }

        return $value;
    }
    //--------------------------------------------------------------------------


    private function tryToOverrideField($fieldName, & $found) {
        if (isset($this->data->overridableTokens[$fieldName])) {
            $engineName = $this->data->overridableTokens[$fieldName];
            $derivedFieldEngine = DerivedField_Registry::getEngine($engineName);
            $derivedFieldEngine->setEngineData($this->data);

            $value = '';
            if ($derivedFieldEngine->tryToOverrideValue($fieldName, $this->data->inputData, $value)) {
                $found = true;
                return $value;
            } else {
                $found = false;
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------


    protected function derivedValue($fieldName, & $found) {
        $engineName   = $this->data->derivedTokens[$fieldName];
        $deriveEngine = DerivedField_Registry::getEngine($engineName);
        if ($deriveEngine) {
            $found = true;
            $deriveEngine->setEngineData($this->data);
            return $deriveEngine->valueOfToken($fieldName, $this->data->inputData);
        } else {
            $found = false;
            return '';
        }
    }
    //--------------------------------------------------------------------------


    protected function geoipValue($fieldName, & $found) {
        $modelGeoipLocation = SingletonRegistry::getModelGeoipLocation();
        $geoipFields = $modelGeoipLocation->listGeoipFieldsFull();
        if (strpos($fieldName, ':geoip_') === 0) {
            $fieldName = ':' . substr($fieldName, 7);
        }

        $index = array_search($fieldName, $geoipFields);
        if ($index === false) {
            $found = false;
            return '';
        }


        if (isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS]) && (ip2long(trim($this->data->inputData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS])) !== false)) {
            $remoteIP = $this->data->inputData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS];
        } else {
            $remoteIP = $_SERVER['REMOTE_ADDR'];
            if (in_array($remoteIP, Config::$serverIPAddresses)) {
                $found = false;
                return '';
            }
        }

        $found = true;

        $row = $modelGeoipLocation->getLocationForIP($remoteIP);

        switch ($index) {
            case Model_GeoipLocation::GEOIP_REGION:
            case Model_GeoipLocation::GEOIP_STATE:
            case Model_GeoipLocation::GEOIP_SR:
                if ($row && isset($row['country']) && ($row['country'] == 'US')) {
                    $usStates = $modelGeoipLocation->listUsStates();
                    $region = isset($row['region']) ? $row['region'] : '';
                    return isset($usStates[$region]) ? $usStates[$region] : '';
                }
                return isset($row['state_name']) ? $row['state_name'] : '';

            case Model_GeoipLocation::GEOIP_REGION_CODE:
            case Model_GeoipLocation::GEOIP_STATE_CODE:
            case Model_GeoipLocation::GEOIP_SR_CODE:
                return isset($row['region']) ? $row['region'] : '';

            case Model_GeoipLocation::GEOIP_COUNTRY:
                return isset($row['country_name']) ? $row['country_name'] : '';

            case Model_GeoipLocation::GEOIP_COUNTRY_CODE:
                return isset($row['country']) ? $row['country'] : '';

            case Model_GeoipLocation::GEOIP_COUNTRY_CODE_3:
                return isset($row['country_code_3chars']) ? $row['country_code_3chars'] : '';

            case Model_GeoipLocation::GEOIP_CITY:
                return isset($row['city']) ? $row['city'] : '';

            case Model_GeoipLocation::GEOIP_AREA_CODE:
                return '';

            case Model_GeoipLocation::GEOIP_POSTAL_CODE:
                return isset($row['zip']) ? $row['zip'] : '';

            default:
                $found = false;
                return '';
        }
    }
    //--------------------------------------------------------------------------


    protected function builtInValue($fieldName, & $found) {
        $defaultTimezone = Config::$timeZone;
        $timezoneSetting = $this->job->currentPartner->getPartnerTimezone();
        
        $found = true;
        switch ($fieldName) {
            case Model_CampaignField::SYS_FIELD_IPADDRESS:
                return (isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], Config::$serverIPAddresses)) ? $_SERVER['REMOTE_ADDR'] : '';

            case Model_CampaignField::SYS_FIELD_SUBMIT_TIME:
                return $this->findTimeFromSubmitDate($defaultTimezone, $timezoneSetting);

            case Model_CampaignField::SYS_FIELD_SUBMIT_DATE:
                return $this->findDateFromSubmitDate($defaultTimezone, $timezoneSetting);

            case Model_CampaignField::SYS_FIELD_SUBMIT_DAY:
            case Model_CampaignField::SYS_FIELD_SUBMIT_MONTH:
            case Model_CampaignField::SYS_FIELD_SUBMIT_YEAR:
            case Model_CampaignField::SYS_FIELD_SUBMIT_DAY_OF_WEEK:
                return $this->findDatePartFromSubmitDate($fieldName, $defaultTimezone, $timezoneSetting);

            case Model_CampaignField::SYS_FIELD_USER_AGENT:
                return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

            case Model_CampaignField::SYS_FIELD_OS:
            case Model_CampaignField::SYS_FIELD_BROWSER:
            case Model_CampaignField::SYS_FIELD_MOBILE:
                return $this->getValueDerivedFromUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', $fieldName);

            case Model_CampaignField::SYS_LEAD_ID:
                return ((int)$this->data->incomingId);
            case Model_CampaignField::SYS_FIELD_LIVE:
                $countExistingDeliveries  = $this->data->logDelivery->countDeliveriesForIncomingIdAndCampaignId($this->data->incomingId, $this->job->campaign->getId());

                if ($countExistingDeliveries != 0 || ($countExistingDeliveries == 0 && $this->job->campaign->isQueuedDelivery())) {
                    return 'N';
                } else {
                    return 'Y';
                }
            case Model_CampaignField::SYS_FIELD_BID_PRICE:
                if ($this->job->currentPartner->getId()) {
                    $incomingId = !empty($this->data->inputData[Model_CampaignField::FIELD_TYPE_LEAD_ID]) ? 
                            $this->data->inputData[Model_CampaignField::FIELD_TYPE_LEAD_ID] : $this->data->incomingId;
                    
                    $pingPartnerId = $this->data->modelPartner->getPingPartnerId($this->job->currentPartner->getId());
                    $bidPrice = floatval($this->data->modelLogPing->getBidPriceByIncomingIdAndPartner($incomingId, $pingPartnerId));
                    
                    return $bidPrice;
                } else {
                    return 0;
                }
                
            default:
                $found = false;
                return '';
        }
    }
    //--------------------------------------------------------------------------


    private function findTimeFromSubmitDate($defaultTimezone, $timezoneSetting = null) {
        $index  = Model_CampaignField::FIELD_TYPE_SUBMIT_DATE;

        if (!empty($this->data->inputData[$index])) {
            $time     = trim($this->data->inputData[$index]);
            $spacePos = strpos($time, ' ');

            if ($spacePos !== false) {
                return trim(substr($time, $spacePos + 1));
            } else {
                return '00:00:01';
            }
        }

        if (!empty($timezoneSetting)) {
            $date = new DateTime($this->data->incomingTimestamp, new DateTimeZone($defaultTimezone)); 
            $date->setTimezone(new DateTimeZone($timezoneSetting)); 
            
            return $date->format('H:i:s');
        }
        return date('H:i:s', $this->originalTimestamp());
    }
    //--------------------------------------------------------------------------


    private function originalTimestamp() {
        return strtotime($this->data->incomingTimestamp);
    }
    //--------------------------------------------------------------------------


    private function findDateFromSubmitDate($defaultTimezone, $timezoneSetting = null) {

        $index  = Model_CampaignField::FIELD_TYPE_SUBMIT_DATE;

        if (!empty($this->data->inputData[$index])) {
            $time     = trim($this->data->inputData[$index]);
            $spacePos = strpos($time, ' ');
            if ($spacePos !== false) {
                $date = trim(substr($time, 0, $spacePos));
            } else {
                $date = trim($time);
            }

            // assume date was in mm/dd/yyyy form
            $arr = explode('/', $date);
            if (sizeof($arr) == 3) {
                return sprintf('%04d-%02d-%02d', $arr[2], $arr[0], $arr[1]);
            }
        }
        
        if (!empty($timezoneSetting)) {
            $date = new DateTime($this->data->incomingTimestamp, new DateTimeZone($defaultTimezone)); 
            $date->setTimezone(new DateTimeZone($timezoneSetting)); 
            
            return $date->format('Y-m-d');
        }
        
        return date('Y-m-d', $this->originalTimestamp());
    }
    //--------------------------------------------------------------------------


    private function findDatePartFromSubmitDate($fieldName, $defaultTimezone, $timezoneSetting = null) {

        $index  = Model_CampaignField::FIELD_TYPE_SUBMIT_DATE;

        if (!empty($this->data->inputData[$index])) {
            $time     = trim($this->data->inputData[$index]);
            $spacePos = strpos($time, ' ');
            if ($spacePos !== false) {
                $date = trim(substr($time, 0, $spacePos));
            } else {
                $date = trim($time);
            }

            // assume date was in mm/dd/yyyy form
            $arr = explode('/', $date);
            if (sizeof($arr) == 3) {
                switch ($fieldName) {
                    case Model_CampaignField::SYS_FIELD_SUBMIT_DAY:
                        return sprintf('%02d', $arr[1]);
                    case Model_CampaignField::SYS_FIELD_SUBMIT_MONTH:
                        return sprintf('%02d', $arr[0]);
                    case Model_CampaignField::SYS_FIELD_SUBMIT_YEAR:
                        return sprintf('%04d', $arr[2]);
                    case Model_CampaignField::SYS_FIELD_SUBMIT_DAY_OF_WEEK:
                        return $this->getDayOfWeek(strtotime($date));
                }
            }
        }
        
        if (!empty($timezoneSetting)) {
            $date = new DateTime($this->data->incomingTimestamp, new DateTimeZone($defaultTimezone)); 
            $date->setTimezone(new DateTimeZone($timezoneSetting)); 
            
            switch ($fieldName) {
                case Model_CampaignField::SYS_FIELD_SUBMIT_DAY:
                    return $date->format('d');
                case Model_CampaignField::SYS_FIELD_SUBMIT_MONTH:
                    return $date->format('m');
                case Model_CampaignField::SYS_FIELD_SUBMIT_YEAR:
                    return $date->format('Y');
                case Model_CampaignField::SYS_FIELD_SUBMIT_DAY_OF_WEEK:
                    return $this->getDayOfWeek(time(), $date->format('w'));
            }
        }
        
        $originalTimestamp = $this->originalTimestamp();
        
        switch ($fieldName) {
            case Model_CampaignField::SYS_FIELD_SUBMIT_DAY:
                return date('d', $originalTimestamp);
            case Model_CampaignField::SYS_FIELD_SUBMIT_MONTH:
                return date('m', $originalTimestamp);
            case Model_CampaignField::SYS_FIELD_SUBMIT_YEAR:
                return date('Y', $originalTimestamp);
            case Model_CampaignField::SYS_FIELD_SUBMIT_DAY_OF_WEEK:
                return $this->getDayOfWeek($originalTimestamp);
        }
    }
    //--------------------------------------------------------------------------


    private function getValueDerivedFromUserAgent($userAgent, $fieldName) {
        if (!$userAgent) {
            return '';
        }

        $browser = new Browser($userAgent);
        switch ($fieldName) {
            case Model_CampaignField::SYS_FIELD_OS:
                $os = $browser->getPlatform();
                if ($os == 'Windows') {
                    $this->tryToFigureOutWindowsVersion($userAgent, $os);

                } elseif ($os == 'Apple') {
                    preg_match('/;[^;]{1,}OS X.*;/i', $userAgent, $matches);
                    if (!empty($matches[0])) {
                        $os = trim(substr($matches[0], 1, -1));
                    }

                }
                return $os;

            case Model_CampaignField::SYS_FIELD_BROWSER:
                return $browser->getBrowser() . ' ' . $browser->getVersion();
            case Model_CampaignField::SYS_FIELD_MOBILE:   
                return ($browser->isMobile() || $browser->isTablet()) ? 'Y' : 'N';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    protected function tryToFigureOutWindowsVersion($userAgent, & $os) {

        preg_match('/windows[\\s\\w\\.]{1,}/i', $userAgent, $matches);
        if (empty($matches[0])) {
            return;
        }
        $winStr = $matches[0];

        if (substr($winStr, -1) == ';') {
            $winStr = substr($winStr, 0, -1);
        }
        $winStr = strtolower($winStr);
        $matches = array(
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows Server 2003; Windows XP x64 Edition',
            'windows nt 5.1' => 'Windows XP',
            'windows nt 5.0' => 'Windows 2000',
        );
        if (isset($matches[$winStr])) {
            $os = $matches[$winStr];
        }
    }
    //--------------------------------------------------------------------------


    protected function modifyValue($ftypeId, $value, $modifier) {
        $modifySettings = explode("\n", $modifier);
        $modifierType   = array_shift($modifySettings);
        $modified       = false;

        switch ($modifierType) {
            case Engine::MODIFY_REPLACEMENT:
                $modified = $this->modifyReplacement($ftypeId, $value, $modifySettings);
                break;

            case Engine::MODIFY_DICTIONARY:
                $modified = $this->modifyDictionary($ftypeId, $value, $modifySettings);
                break;

            default:
                return $value;
        }

        if ($modified) {
            $value = $this->replaceTokens($value);
        }
        return $value;
    }
    //--------------------------------------------------------------------------


    private function replaceTokens($value) {
        preg_match_all('/\\[([\\w]{1,}|:[\\w]{1,}:)\\]/i', $value, $matches);
        foreach (array_unique($matches[1]) as $token) {
            $isSystemField = (substr($token, 0, 1) == ':') && (substr($token, -1) == ':');

            if ($isSystemField) {
                $dummy       = false;
                $replacement = $this->valueOfSystemField($token, $dummy);
            } else {
                $ftypeId     = array_search($token, $this->data->fieldTypes);
                $replacement = isset($this->data->inputData[$ftypeId]) ? $this->data->inputData[$ftypeId] : '';
            }

            $value = str_replace("[$token]", $replacement, $value);
        }
        return $value;
    }
    //--------------------------------------------------------------------------


    protected function modifyReplacement($ftypeId, & $value, array $modifySettings) {
        $sourceFieldType = array_shift($modifySettings);
        if ($ftypeId && $this->data->fieldTypes[$ftypeId] != $sourceFieldType) {
            return false;
        }

        foreach (array_chunk($modifySettings, 3) as $ruleRow) {
            if (sizeof($ruleRow) != 3) {
                return false ;
            }

            $matchType    = (int)$ruleRow[0];
            $searchValue  = urldecode($ruleRow[1]);
            $replaceValue = urldecode($ruleRow[2]);

            if (Engine_Utilities::checkMatching($matchType, $value, $searchValue)) {
                $value = $replaceValue;
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    protected function modifyDictionary($ftypeId, & $value, array $modifySettings) {
        $sourceFieldType = $modifySettings[0];
        if ($ftypeId && $this->data->fieldTypes[$ftypeId] != $sourceFieldType) {
            return false;
        }

        $dictionaryId = (int)$modifySettings[1];
        $columnId     = (int)$modifySettings[2];

        if ($this->modifyUsingVirtualDictionary($dictionaryId, $columnId, $value)) {
            return true;
        }

        if (!isset($this->dictionariesAssoc[$dictionaryId])) {
            $dict = $this->data->modelDictionary->get($dictionaryId);
            $dict['* columns'] = $this->data->modelDictionaryColumn->listColumnsAssoc($dictionaryId);

            $this->dictionariesAssoc[$dictionaryId] = $dict;
        }

        if (isset($this->dictionariesAssoc[$dictionaryId])) {
            $dictionary = $this->dictionariesAssoc[$dictionaryId];
            if (isset($dictionary['* columns'][$columnId])) {
                $value = $this->data->modelDictionaryValue->getValueForKey($dictionaryId, $value, $columnId);
                return true;
            }
        }

        $value = '';
        return true;
    }
    //--------------------------------------------------------------------------


    private function modifyUsingVirtualDictionary($dictionaryId, $columnId, & $value) {
        if ($dictionaryId == Model_Dictionary::VDICT_ZIP_CODES) {
            $value = $this->zipDictionaryValue($value, $columnId);
            return true;

        } elseif ($dictionaryId == Model_Dictionary::VDICT_STATE_ABBRS) {
            if (isset($this->data->statesAssoc[$value])) {
                $value = $this->data->statesAssoc[$value];
                return true;
            }

        } elseif ($dictionaryId == Model_Dictionary::VDICT_STATE_NAMES) {
            $abbreviation = array_search($value, $this->data->statesAssoc);
            if ($abbreviation !== false) {
                $value = $abbreviation;
                return true;
            }
        }

        return false;
    }
    //--------------------------------------------------------------------------


    protected function zipDictionaryValue($value, $columnId) {

        $modelZipCode = SingletonRegistry::getModelUsZipCode();

        switch ($columnId) {
            case Model_Dictionary::COL_ZIP_CODES_CITY:
                return $modelZipCode->getCityFromZip($value);
                break;
            case Model_Dictionary::COL_ZIP_CODES_STATE_ABBR:
                return $modelZipCode->getStateAbbrFromZip($value);
                break;
            case Model_Dictionary::COL_ZIP_CODES_STATE:
                return $modelZipCode->getStateFromZip($value);
                break;
            default:
                return '';
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function getDayOfWeek($timestamp, $dayIndex = 0) {
        $days = array ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        if (empty($dayIndex)) {
            $dayIndex = date('w', $timestamp);
        } 
        return $days[$dayIndex];
    }
}
