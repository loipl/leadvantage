<?php

class Engine_Utilities {


    public static function toFQDN($domain) {
        $domain = strtolower($domain);
        if (substr($domain, -3) == '.uk') {
            $domain = substr_replace($domain, '-uk', -3);
        }
        $domainParts = array();
        foreach (explode('.', $domain) as $part) {
            if (trim($part)) {
                $domainParts[] = trim($part);
            }
        }
        while (sizeof($domainParts) > 3) {
            array_shift($domainParts);
        }
        if (sizeof($domainParts) == 2) {
            array_unshift($domainParts, 'www');
        }
        return implode('.', $domainParts);
    }
    //--------------------------------------------------------------------------


    public static function readAFFUsernameSuggestions($response) {
        $usernameSuggestions = array();
        $phrasePosition = strpos($response, "\nSUGGESTIONS: ");

        if ($phrasePosition !== false) {
            $newLinePosition = strpos($response, "\n", $phrasePosition + 1);

            if ($newLinePosition) {
                $relevantPart = substr($response, $phrasePosition, $newLinePosition - $phrasePosition);
            } else {
                $relevantPart = substr($response, $phrasePosition);
            }

            $relevantPart = trim(substr($relevantPart, strlen("\nSUGGESTIONS: ")));

            foreach (explode(",", $relevantPart) as $suggestion) {
                $usernameSuggestions[] = trim($suggestion);
            }
        }
        return $usernameSuggestions;
    }
    //--------------------------------------------------------------------------


    /**
     * @param array $levelLimitsAssoc array like('subscriber' => 20, 'power_user' => 100)
     * @param array $userLevels array with user capabilities, like ('subscriber', 'administrator');
     *
     * @return int Result is -1 if user has no rights at all, 0 for unlimited and
     * actual number of submissions user can process if everything works out
     */
    public static function getMaxDeliveriesForUserLevel(array $levelLimitsAssoc, array $userLevels) {
        if (empty($userLevels)) {
            return -1;
        }
        $max = -1;
        foreach ($levelLimitsAssoc as $level => $limit) {
            if (in_array($level, $userLevels)) {
                if ($limit == 0) {
                    return 0;
                }
                if ($limit > $max) {
                    $max = $limit;
                }
            }
        }
        return $max;
    }
    //--------------------------------------------------------------------------


    public static function checkMatching($matchType, $value, $matchAgainst) {
        $invert    = $matchType < 0;
        $matchType = abs($matchType);
        $value     = trim($value);

        $result = false;
        foreach (explode('|', $matchAgainst) as $searchVal) {
            $searchVal = trim($searchVal);
            switch ($matchType) {
                case Model_PartnerFilter::FILTER_MATCH_EQUALS:
                    $result = strtolower($value) == strtolower($searchVal);
                    break;
                case Model_PartnerFilter::FILTER_MATCH_STARTS_WITH:
                    $result = stripos($value, $searchVal) === 0;
                    break;
                case Model_PartnerFilter::FILTER_MATCH_ENDS_WITH:
                    $result = strripos($value, $searchVal) === (strlen($value) - strlen($searchVal));
                    break;
                case Model_PartnerFilter::FILTER_MATCH_CONTAINS:
                    $result = stripos($value, $searchVal) !== false;
                    break;
                case Model_PartnerFilter::FILTER_MATCH_GREATER_THAN:
                    $result = $value > $searchVal;
                    break;
                case Model_PartnerFilter::FILTER_MATCH_LESS_THAN:
                    $result = $value < $searchVal;
                    break;
                case Model_PartnerFilter::FILTER_MATCH_STRLEN:
                    $result = strlen($value) >= (int)$searchVal;
                    break;
                case Model_PartnerFilter::FILTER_MATCH_REGEX:
                    $result = preg_match($searchVal, $value);
                    break;
                case Model_PartnerFilter::FILTER_IN_DATA_LIST:
                    $result = SingletonRegistry::getSingleInstance('Model_DataListValue')->checkValueExistInDataList($searchVal, $value);
                    break;
            }
            if ($result) {
                break;
            }
        }
        return $invert ? !$result : $result;
    }
    //--------------------------------------------------------------------------

    
    public static function getQueryRandomModifier() {
        $number = mt_rand(1, 100);
        return $number . '=' . $number;
    }
    //--------------------------------------------------------------------------
    
    
    public static function getFullNameValue($fieldTypeData) {
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;;

        if (!empty($fieldTypeData[$fullnameFTId])) {
            return $fieldTypeData[$fullnameFTId];
        };
        
        // field id (defined in campaign campaignField.php)
        $firstNameFTId = Model_CampaignField::FIELD_TYPE_FIRST_NAME;
        $firstInitialFTId = Model_CampaignField::FIELD_TYPE_FIRST_INITIAL;

        $middleNameFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_NAME;
        $middleInitialFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_INITIAL;
        
        $lastNameFTId = Model_CampaignField::FIELD_TYPE_LAST_NAME;
        $lastInitialFTId = Model_CampaignField::FIELD_TYPE_LAST_INITIAL;
        
        // get first name
        if (!empty($fieldTypeData[$firstNameFTId])){
            $firstName = $fieldTypeData[$firstNameFTId];
        } else if (!empty($fieldTypeData[$firstInitialFTId])) {
            $firstName = $fieldTypeData[$firstInitialFTId];
        } else {
            $firstName = "";
        }
        
        // get middle name
        if (!empty($fieldTypeData[$middleNameFTId])){
            $middleName = $fieldTypeData[$middleNameFTId];
        } else if (!empty($fieldTypeData[$middleInitialFTId])) {
            $middleName = $fieldTypeData[$middleInitialFTId];
        } else {
            $middleName = "";
        }
        
         // get last name
        if (!empty($fieldTypeData[$lastNameFTId])){
            $lastName = $fieldTypeData[$lastNameFTId];
        } else if (!empty($fieldTypeData[$lastInitialFTId])) {
            $lastName = $fieldTypeData[$lastInitialFTId];
        } else {
            $lastName = "";
        }
                     
        return trim($firstName . " " . $middleName . " ". $lastName);
    }
    //--------------------------------------------------------------------------
}
