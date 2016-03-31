<?php

class ExternalLookup_RapLeaf extends ExternalLookup_Base {

    protected $name   = 'RapLeaf';

    protected $tokens = array('age',
                              'rapleaf_age',
                              'gender',
                              'rapleaf_gender',
                              'education',
                              'rapleaf_education',
                              'home_owner',
                              'rapleaf_home_owner_status',
                              'years_at_address',
                              'rapleaf_length_of_residence',
                              'months_at_address',
                              'married',
                              'rapleaf_marital_status',
                              'occupation',
                              'rapleaf_occupation',
                              'children',
                              'rapleaf_children',
                              'home_value',
                              'rapleaf_home_market_value',
                              'high_net_worth',
                              'rapleaf_high_net_worth',
                              'annual_income',
                              'rapleaf_household_income',
                              'monthly_income',
                              'postal_code',
                              'rapleaf_zip',
                              'city',
                              'state_or_region',
                              'state_or_region_code',
                              'interests',
                              'rapleaf_interests'
                              );


    protected function executeLookup(array $fieldTypeValues, $token) {
        /* @var $modelExternalLookupCache Model_ExternalLookupCache */
        $modelExternalLookupCache = SingletonRegistry::getSingleInstance('Model_ExternalLookupCache');

        $key = $this->requestKey($fieldTypeValues, '');
        $key .= 'api_response';

        $cachedResult = $modelExternalLookupCache->cachedValueFor($this->name, $key, $this->cacheTimeout);

        if ($cachedResult !== false) {
            $jsonObject = json_decode($cachedResult);
        } else {
            $apiParams = $this->getParamsForRapleafRequest($fieldTypeValues);
            $url       = "https://personalize.rapleaf.com/v4/dr?" . http_build_query($apiParams);
            $result    = '';

            $ch        = $this->initCurl($url);
            $response  = curl_exec($ch);

            if ((curl_errno($ch) == 0) && (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200)) {

                $data = array(
                    'engine' => $this->name,
                    'key'    => $key,
                    'result' => $response
                );
                $modelExternalLookupCache->insert($data);

                $jsonObject = json_decode($response);
            }
        }

        if (isset($jsonObject)) {
            switch (self::normalizeTokenName($token)) {
                case 'age':
                case 'rapleaf_age':
                    $result = isset($jsonObject->age)                 ? self::getAgeFromResponse($jsonObject->age) : '';
                    break;
                case 'gender':
                case 'rapleaf_gender':
                    $result = isset($jsonObject->gender)              ? self::getGenderFromResponse($jsonObject->gender) : '';
                    break;
                case 'education':
                case 'rapleaf_education':
                    $result = isset($jsonObject->education)           ? self::getEducationFromResponse($jsonObject->education) : '';
                    break;
                case 'home_owner':
                case 'rapleaf_home_owner_status':
                    $result = isset($jsonObject->home_owner_status)   ? self::getHomeOwnerStatusFromResponse($jsonObject->home_owner_status) : '';
                    break;
                case 'years_at_address':
                case 'rapleaf_length_of_residence':
                    $result = isset($jsonObject->length_of_residence) ? self::getLengthOfResidenceFromResponse($jsonObject->length_of_residence) : '';
                    break;
                case 'months_at_address':
                    $result = isset($jsonObject->length_of_residence) ? (self::getLengthOfResidenceFromResponse($jsonObject->length_of_residence)*12) : '';
                    break;
                case 'married':
                case 'rapleaf_marital_status':
                    $result = isset($jsonObject->marital_status)      ? self::getMaritalStatusFromResponse($jsonObject->marital_status) : '';
                    break;
                case 'occupation':
                case 'rapleaf_occupation':
                    $result = isset($jsonObject->occupation)          ? self::getOccupationFromResponse($jsonObject->occupation) : '';
                    break;
                case 'children':
                case 'rapleaf_children':
                    $result = isset($jsonObject->children)            ? self::getChildrenFromResponse($jsonObject->children) : '';
                    break;
                case 'home_value':
                case 'rapleaf_home_market_value':
                    $result = isset($jsonObject->home_market_value)   ? self::getHomeMarketValueFromResponse($jsonObject->home_market_value) : '';
                    break;
                case 'high_net_worth':
                case 'rapleaf_high_net_worth':
                    $result = isset($jsonObject->high_net_worth)      ? self::getHighNetWorthFromResponse($jsonObject->high_net_worth) : '';
                    break;
                case 'annual_income':
                case 'rapleaf_household_income':
                    $result = isset($jsonObject->household_income)    ? self::getHouseholdIncomeFromResponse($jsonObject->household_income) : '';
                    break;
                case 'monthly_income':
                    $result = isset($jsonObject->household_income)    ? round(self::getHouseholdIncomeFromResponse($jsonObject->household_income)/12) : '';
                    break;
                case 'postal_code':
                case 'rapleaf_zip':
                    $result = isset($jsonObject->zip)                 ? $jsonObject->zip : '';
                    break;
                case 'city':
                    $result = isset($jsonObject->zip)                 ? self::getCityFromResponse($jsonObject->zip) : '';
                    break;
                case 'state_or_region':
                    $result = isset($jsonObject->zip)                 ? self::getStateFromResponse($jsonObject->zip) : '';
                    break;
                case 'state_or_region_code':
                    $result = isset($jsonObject->zip)                 ? self::getStateCodeFromResponse($jsonObject->zip) : '';
                    break;
                case 'interests':
                case 'rapleaf_interests':
                    $result = isset($jsonObject->interests)           ? self::getInterestsFromResponse($jsonObject->interests) : '';
                    break;
            }
        } else {
            if (isset($ch) && isset($url) && isset($response)) {
                $this->logApiError($ch, $url, $response);
                curl_close($ch);
                $curl_closed = true;
            }
        }

        if (isset($url) && isset($ch) && isset($curl_closed) && !$curl_closed) {
            curl_close($ch);
        }

        if (!empty($result)) {
           return $result;
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public static function getAgeFromResponse($response) {
        if (strpos($response, '-') !== false) {
            $values = explode('-', $response);
            return round(array_sum($values) / sizeof($values));
        } else {
            $response = str_replace('+','',$response);
            return (int)$response;
        }
    }
    //--------------------------------------------------------------------------


    public static function getGenderFromResponse($response) {
        if ($response == 'Male') {
            return '1';
        } else if ($response == 'Female') {
            return '2';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getHighNetWorthFromResponse($response) {
        if ($response == 'Yes') {
            return 'Y';
        } else if ($response == 'No') {
            return 'N';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getEducationFromResponse($response) {
        if ($response == 'Completed High School') {
            return 'High School';
        } else if ($response == 'Completed College') {
            return 'College';
        } else if ($response == 'Attended College') {
            return 'Some College';
        } else if ($response == 'Completed Graduate School') {
            return 'Graduate School';
        } else if ($response == 'Attended Vocational/Technical') {
            return 'Some College';
        }
        return 'Other';
    }
    //--------------------------------------------------------------------------


    public static function getHomeOwnerStatusFromResponse($response) {
        if ($response == 'Own') {
            return 'Y';
        } else if ($response == 'Rent') {
            return 'N';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getLengthOfResidenceFromResponse($response) {
        if (strpos($response, '-') !== false) {
            $values = explode('-', $response);
            return round(array_sum($values) / sizeof($values));
        } else {
            return (int)$response;
        }
    }
    //--------------------------------------------------------------------------


    public static function getMaritalStatusFromResponse($response) {
        if ($response == 'Single') {
            return 'Single';
        } else if ($response == 'Married') {
            return 'Married';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getOccupationFromResponse($response) {
        switch ($response) {
            case 'Blue Collar Worker' :
                return 'Blue Collar';
                break;
            case 'Business Owner' :
                return 'Business Owner';
                break;
            case 'Civil Service' :
                return 'Civil Service';
                break;
            case 'Executive/Upper Management' :
                return 'Executive';
                break;
            case 'Health Services' :
                return 'Health Services';
                break;
            case 'Homemaker' :
                return 'Homemaker';
                break;
            case 'Middle Management' :
                return 'Management';
                break;
            case 'Military Personnel' :
                return 'Military';
                break;
            case 'Nurse' :
                return 'Nurse';
                break;
            case 'Part Time' :
                return 'Part-time';
                break;
            case 'Professional' :
                return 'Professional';
                break;
            case 'Retired' :
                return 'Retired';
                break;
            case 'Secretary' :
                return 'Secretary';
                break;
            case 'Student' :
                return 'Student';
                break;
            case 'Teacher' :
                return 'Teacher';
                break;
            case 'Technology' :
                return 'Technology';
                break;
            case 'White Collar Worker' :
                return 'White Collar';
                break;
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getChildrenFromResponse($response) {
        if ($response == 'Yes') {
            return 'Y';
        } else if ($response == 'No') {
            return 'N';
        }
        return '';
    }
    //--------------------------------------------------------------------------


    public static function getHouseholdIncomeFromResponse($response) {
        if (strpos($response, '-') !== false) {
            $values = explode('-', $response);
            $result = str_replace('k','000',$values[1]);

            return $result;
        } else {
            $result = str_replace('+','',$response);
            $result = str_replace('k','000',$result);

            return $result;
        }
    }
    //--------------------------------------------------------------------------


    public static function getHomeMarketValueFromResponse($response) {
        if (strpos($response, '-') !== false) {
            $values = explode('-', $response);
            $result = $values[1];
            $result = str_replace('k','000',$result);
            $result = str_replace('mm','000000',$result);

            return $result;
        } else {
            $response = str_replace('+','',$response);
            $response = str_replace('mm','000000',$response);
            return (int)$response;
        }
    }
    //--------------------------------------------------------------------------


    public static function getInterestsFromResponse($response) {
        $result = '';
        foreach($response as $key=>$value) {
            if($value) {
                $result .= $key . ', ';
            }
        }
        return rtrim($result, ', ');
    }
    //--------------------------------------------------------------------------


    public static function getCityFromResponse($response) {
        $modelUSZipCode = SingletonRegistry::getModelUsZipCode();

        $result = $modelUSZipCode->getCityFromZip($response);

        $result = ucwords(strtolower($result));

        return $result;
    }
    //--------------------------------------------------------------------------


    public static function getStateFromResponse($response) {
        $modelUSZipCode = SingletonRegistry::getModelUsZipCode();

        $result = $modelUSZipCode->getStateFromZip($response);

        return $result;
    }
    //--------------------------------------------------------------------------


    public static function getStateCodeFromResponse($response) {
        $modelUSZipCode = SingletonRegistry::getModelUsZipCode();

        $result = $modelUSZipCode->getStateAbbrFromZip($response);

        return $result;
    }
    //--------------------------------------------------------------------------


    private function getNormalizedAddress($address) {
        if (!$address) {
            return false;
        }

        $normalized = strtoupper($address);

        if (strpos($address, 'STREET')) {
            $normalized = preg_replace('/\bSTREET\b/', 'ST', $normalized);
        }

        if (strpos($address, 'LANE')) {
            $normalized = preg_replace('/\bLANE\b/', 'LN', $normalized);
        }

        if (strpos($address, 'AVENUE')) {
            $normalized = preg_replace('/\bAVENUE\b/', 'AVE', $normalized);
        }

        if (strpos($address, 'BOULEVARD')) {
            $normalized = preg_replace('/\bBOULEVARD\b/', 'BLVD', $normalized);
        }

        if (strpos($address, 'PLACE')) {
            $normalized = preg_replace('/\bPLACE\b/', 'PL', $normalized);
        }

        if (strpos($address, 'APARTMENT')) {
            $normalized = preg_replace('/\bAPARTMENT\b/', 'APT', $normalized);
        }

        if (strpos($address, 'BUILDING')) {
            $normalized = preg_replace('/\bBUILDING\b/', 'BLDG', $normalized);
        }

        if (strpos($address, 'FLOOR')) {
            $normalized = preg_replace('/\bFLOOR\b/', 'FL', $normalized);
        }

        if (strpos($address, 'SUITE')) {
            $normalized = preg_replace('/\bSUITE\b/', 'STE', $normalized);
        }

        if (strpos($address, 'ROOM')) {
            $normalized = preg_replace('/\bROOM\b/', 'RM', $normalized);
        }

        if (strpos($address, 'DEPARTMENT')) {
            $normalized = preg_replace('/\bDEPARTMENT\b/', 'DEPT', $normalized);
        }

        if (strpos($address, '#')) {
            $normalized = preg_replace('/\s*#(\d)\s/', ' # $1', $normalized);
        }

        $normalized = str_replace('.','',$normalized);
        $normalized = str_replace(',','',$normalized);

        return strtoupper($normalized);
    }
    //--------------------------------------------------------------------------


    private function logApiError($ch, $url, $response) {
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $isTimeout = curl_errno($ch) == CURLE_OPERATION_TIMEDOUT;
        Model_LogApiErrors::log(Model_LogApiErrors::API_RAPLEAF, $url, '', $isTimeout, $httpCode, $response ? $response : '', curl_error(($ch)));
    }
    //--------------------------------------------------------------------------


    private function getParamsForRapleafRequest(array $fieldTypeValues) {
        $apiParams = array();

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_FIRST_NAME])) {
            $apiParams['first'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_FIRST_NAME]);
        } else if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_FIRST_INITIAL])) {
            $apiParams['first'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_FIRST_INITIAL]);
        }

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_MIDDLE_NAME])) {
            $apiParams['middle'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_MIDDLE_NAME]);
        } else if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_MIDDLE_INITIAL])) {
            $apiParams['middle'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_MIDDLE_INITIAL]);
        }

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_LAST_NAME])) {
            $apiParams['last'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_LAST_NAME]);
        } else if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_LAST_INITIAL])) {
            $apiParams['last'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_LAST_INITIAL]);
        }

        if (empty($apiParams['first']) && empty($apiParams['last']) && !empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_FULL_NAME])) {
            $splitName = explode(' ',$fieldTypeValues[Model_CampaignField::FIELD_TYPE_LAST_NAME]);
            $apiParams['first'] = strtoupper($splitName[0]);
            $apiParams['last']  = strtoupper($splitName[1]);
        }

        $geoipLocation = '';
        $modelGeoipLocation = SingletonRegistry::getModelGeoipLocation();
        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_IP_ADDRESS]) && (ip2long(trim($fieldTypeValues[Model_CampaignField::FIELD_TYPE_IP_ADDRESS])) !== false)) {
            $remoteIP = $fieldTypeValues[Model_CampaignField::FIELD_TYPE_IP_ADDRESS];
            $geoipLocation = $modelGeoipLocation->getLocationForIP($remoteIP);
        } else if(!empty($_SERVER['REMOTE_ADDR'])){
            $remoteIP = $_SERVER['REMOTE_ADDR'];
            $geoipLocation = $modelGeoipLocation->getLocationForIP($remoteIP);
            if (in_array($remoteIP, Config::$serverIPAddresses)) {
                unset($geoipLocation);
            }
        }

        $modelUSZipCode = SingletonRegistry::getModelUsZipCode();

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE])) {
            $apiParams['zip'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
        } else if (!empty($geoipLocation)) {
            $apiParams['zip'] = isset($geoipLocation['zip']) ? $geoipLocation['zip'] : null;
        }

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_CITY])) {
            $apiParams['city'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_CITY]);
        } else if ((!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]))) {
            $apiParams['city'] = $modelUSZipCode->getCityFromZip($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
        }
        if (empty($apiParams['city']) && !empty($geoipLocation)) {
            $apiParams['city'] = isset($geoipLocation['city']) ? $geoipLocation['city'] : null;
        }

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_STATE_CODE])) {
            $apiParams['state'] = strtoupper($fieldTypeValues[Model_CampaignField::FIELD_TYPE_STATE_CODE]);
        } else if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_STATE])) {
            $stateModel = SingletonRegistry::getModelUsState();

            $stateCode = $stateModel->getStateAbbrFromState($fieldTypeValues[Model_CampaignField::FIELD_TYPE_STATE]);

            if (!empty($stateCode)) {
                $apiParams['state'] = $stateCode;
            }
        } else if ((!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]))) {
            $apiParams['state'] = $modelUSZipCode->getStateAbbrFromZip($fieldTypeValues[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
        }
        if (empty($apiParams['state']) && !empty($geoipLocation)) {
            $apiParams['state'] = isset($geoipLocation['region']) ? $geoipLocation['region'] : null;
        }

        if (!empty($fieldTypeValues[Model_CampaignField::FIELD_TYPE_ADDRESS_1])) {
            $normalizedAddress = $this->getNormalizedAddress($fieldTypeValues[Model_CampaignField::FIELD_TYPE_ADDRESS_1]);
            $apiParams['street'] = $normalizedAddress;
        }

        $apiParams['sha1_email'] = sha1(trim(strtolower($fieldTypeValues[Model_CampaignField::FIELD_TYPE_EMAIL])));
        $apiParams['api_key']    = Config::$rapLeafApiKey;

        return $apiParams;
    }
    //--------------------------------------------------------------------------


    private function initCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // These shouldn't be here, they mean that there's some kind of problem with rapleaf SSL certificate
        // and curl is ignoring it - perhaps cert registered for www.rapleaf.com, not personalize.rapleaf.com ?
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        return $ch;
    }
    //--------------------------------------------------------------------------


    public function requestKey(array $fieldTypeValues, $token) {
        $apiParams['email'] = trim(strtolower($fieldTypeValues[Model_CampaignField::FIELD_TYPE_EMAIL]));

        return http_build_query($apiParams) . "\n" . $token;
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return array(
                     ':rapleaf_age:',
                     ':age:',
                     ':rapleaf_gender:',
                     ':gender:',
                     ':education:',
                     ':rapleaf_education:',
                     ':home_owner:',
                     ':rapleaf_home_owner_status:',
                     ':years_at_address:',
                     ':rapleaf_length_of_residence:',
                     ':months_at_address:',
                     ':married:',
                     ':rapleaf_marital_status:',
                     ':occupation:',
                     ':rapleaf_occupation:',
                     ':children:',
                     ':rapleaf_children:',
                     ':home_value:',
                     ':rapleaf_home_market_value:',
                     ':high_net_worth:',
                     ':rapleaf_high_net_worth:',
                     ':annual_income:',
                     ':rapleaf_household_income:',
                     ':monthly_income:',
                     ':postal_code:',
                     ':rapleaf_zip:',
                     ':city:',
                     ':state_or_region:',
                     ':state_or_region_code:',
                     ':interests:',
                     ':rapleaf_interests:'
                    );
    }
    //--------------------------------------------------------------------------
}
