<?php

class DerivedField_Postal extends DerivedField_Base {
    const TITLE_CASE = 1;
    const UPPER_CASE = 2;


    protected $myTokens =  array(
        ':postal_region:',
        ':postal_region_code:',
        ':postal_country:',
        ':postal_country_code:',
        ':postal_city:',
        ':postal_area_code:',
    );


    public function __construct() {
        $this->name = 'postal';

        $this->visibilityMap = array(
    	'postal_code' => array (
            ':state_or_region:',
            ':state_or_region_code:',
            ':county:',
            ':country:',
            ':country_code:',
            ':city:',
            ':area_code:',
            ':time_zone:',
            ':latitude:',
            ':longitude:',
        )
        );
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return $this->myTokens;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc list of tokens that this engine can optionally override
     */
    public function listOverridableTokens() {
        return array(
            ':postal_code:',
            ':state_or_region:',
            ':state_or_region_code:',
            ':county:',
            ':country:',
            ':country_code:',
            ':country_code_3chars:',
            ':city:',
            ':area_code:',
            ':time_zone:',
            ':latitude:',
            ':longitude:',
        );
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);

        switch($token) {
            case 'postal_code':
                return $this->overrideZipCodeIfAmerican($fieldTypeData, $value);

            case 'country':
                return $this->overrideCountry($fieldTypeData, $value);
            case 'country_code':
                return $this->overrideCountryCode($fieldTypeData, $value);
            case 'country_code_3chars':
                return $this->overrideCountryCode3Chars($fieldTypeData, $value);

            case 'state_or_region':
                return $this->overrideState($fieldTypeData, $value);

            case 'state_or_region_code':
                return $this->overrideStateCode($fieldTypeData, $value);

            case 'city':
                return $this->overrideCity($fieldTypeData, $value);

            case 'area_code':
            case 'latitude':
            case 'longitude':
                return $this->overrideFieldFromUsZipCode($fieldTypeData, $token, $value);

            case 'county':
                return $this->overrideFieldFromUsZipCode($fieldTypeData, $token, $value, self::TITLE_CASE);

            case 'time_zone':
                return $this->overrideFieldFromUsZipCode($fieldTypeData, $token, $value, self::UPPER_CASE);

            default:
                return false;
        }
    }
    //--------------------------------------------------------------------------


    private function overrideZipCodeIfAmerican(array $fieldTypeData, & $value) {
        if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]) && $this->engData->isCountryUSA()) {
            $row = SingletonRegistry::getModelUsZipCode()->getRowFromZip($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
            if ($row) {
                $value = $fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE];
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function overrideCountry(array $fieldTypeData, & $value) {
        if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY])) {
            $value = $fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY];
            return true;
        }

        if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE])) {
            $countryCode = strtoupper($fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE]);

            static $countriesAssoc = false;
            if ($countriesAssoc === false) {
                $countriesAssoc = SingletonRegistry::getModelCountry()->listCountriesAssoc();
            }

            if (isset($countriesAssoc[$countryCode])) {
                $value = $countriesAssoc[$countryCode];
                return true;
            }
        }

        return $this->countryFromZip('country', $fieldTypeData, $value);
    }
    //--------------------------------------------------------------------------


    private function overrideCountryCode(array $fieldTypeData, & $value) {
        if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE])) {
            $value = $fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY_CODE];
            return true;
        }

        if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY])) {
            $countryName = $fieldTypeData[Model_CampaignField::FIELD_TYPE_COUNTRY];

            static $countriesAssoc = false;
            if ($countriesAssoc === false) {
                $countriesAssoc = SingletonRegistry::getModelCountry()->listCountriesAssoc();
            }

            foreach ($countriesAssoc as $countryCode => $fullName) {
                if (strcasecmp(trim($countryName), $fullName) == 0) {
                    $value = $countryCode;
                    return true;
                }
            }
        }

        return $this->countryFromZip('country_code', $fieldTypeData, $value);
    }
    //--------------------------------------------------------------------------


    private function overrideCountryCode3Chars(array $fieldTypeData, & $value) {
        $oldValue = $value;

        if($this->overrideCountryCode($fieldTypeData, $value)) {
            $code3Chars = SingletonRegistry::getModelCountry()->countryCode2charsTo3Chars($value);
            if ($code3Chars) {
                $value = $code3Chars;
                return true;
            } else {
                $value = $oldValue;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    protected function countryFromZip($token, array $fieldTypeData, & $value) {
        if (isset($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE])) {
            $zipCode = $fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE];
            $row     = SingletonRegistry::getModelUsZipCode()->getRowFromZip($zipCode);

            if ($row) {
                if ($token == 'country') {
                    $value = 'United States';
                    return true;
                } elseif ($token == 'country_code') {
                    $value = 'US';
                    return true;
                }
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function overrideState(array $fieldTypeData, & $value) {
        $index = Model_CampaignField::FIELD_TYPE_STATE;
        if (isset($fieldTypeData[$index]) && (trim($fieldTypeData[$index]) != '')) {
            $value = $fieldTypeData[$index];
            return true;
        }
        return $this->overrideFieldFromUsZipCode($fieldTypeData, 'state_name', $value);
    }
    //--------------------------------------------------------------------------


    private function overrideFieldFromUsZipCode(array $fieldTypeData, $field, & $value, $modificator = false) {
        if (empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]) || !$this->engData->isCountryUSA()) {
            return false;
        }

        $row = SingletonRegistry::getModelUsZipCode()->getRowFromZip($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
        if ($row) {
            switch ($modificator) {
                case self::UPPER_CASE:
                    $row[$field] = $this->getUTCTimeZone(intval($row[$field]));
                    $value = strtoupper($row[$field]);
                    break;
                case self::TITLE_CASE:
                    $value = ucwords(strtolower($row[$field]));
                    break;
                default:
                    $value = $row[$field];
            }
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function getUTCTimeZone($timeZone) {
        $utcTimezone = '00:00';
        
        if ($timeZone === 0) {
            return $utcTimezone;
        }
        
        if ($timeZone < 12) {
            if ($timeZone < 10) {
                $timeZone = '0'.$timeZone;
            }
            $utcTimezone = '-'.$timeZone.':00';
        } elseif($timeZone > 12) {
            $timezoneDiff = 24 - $timeZone;
            
            if ($timezoneDiff < 10) {
                $timezoneDiff = '0'.$timezoneDiff;
            }
            $utcTimezone = '+'.$timezoneDiff.':00';
        }
        
        return $utcTimezone;
    }
    //--------------------------------------------------------------------------
    

    private function overrideStateCode(array $fieldTypeData, & $value) {
        $index = Model_CampaignField::FIELD_TYPE_STATE_CODE;

        if (isset($fieldTypeData[$index]) && (trim($fieldTypeData[$index]) != '')) {
            $value = $fieldTypeData[$index];
            return true;
        }

        return $this->overrideFieldFromUsZipCode($fieldTypeData, 'state_abbr', $value);
    }
    //--------------------------------------------------------------------------


    private function overrideCity(array $fieldTypeData, & $value) {
        $index = Model_CampaignField::FIELD_TYPE_CITY;

        if (isset($fieldTypeData[$index]) && (trim($fieldTypeData[$index]) != '')) {
            $value = $fieldTypeData[$index];
            return true;
        }

        return $this->overrideFieldFromUsZipCode($fieldTypeData, 'city_name', $value, self::TITLE_CASE);
    }
    //--------------------------------------------------------------------------


    public function valueOfToken($token, array $fieldTypeData) {
        $token = self::normalizeTokenName($token);
        if (!in_array(":$token:", $this->myTokens)) {
            return '';
        }

        if (strpos($token, 'postal_country') === 0) {
            return $this->countryBasedValue($token, $fieldTypeData);
        } else {
            return $this->zipBasedValue($token, $fieldTypeData);
        }
    }
    //--------------------------------------------------------------------------


    private function countryBasedValue($token, array $fieldTypeData) {
        $countryIndex  = Model_CampaignField::FIELD_TYPE_COUNTRY;
        $ccIndex       = Model_CampaignField::FIELD_TYPE_COUNTRY_CODE;

        if (empty($fieldTypeData[$countryIndex]) && empty($fieldTypeData[$ccIndex])) {
            return '';
        }

        switch ($token) {
            case 'postal_country':
                if (isset($fieldTypeData[$countryIndex])) {
                    return strtoupper($fieldTypeData[$countryIndex]);
                } else {
                    $countryCode    = strtoupper($fieldTypeData[$ccIndex]);
                    $countriesAssoc = SingletonRegistry::getModelCountry()->listCountriesAssoc();

                    return isset($countriesAssoc[$countryCode]) ? $countriesAssoc[$countryCode] : '';
                }
                break;

            case 'postal_country_code':
                if (isset($fieldTypeData[$ccIndex])) {
                    return $fieldTypeData[$ccIndex];
                } else {
                    $countryName    = trim(strtolower($fieldTypeData[$countryIndex]));
                    $countriesAssoc = SingletonRegistry::getModelCountry()->listCountriesAssoc();

                    foreach ($countriesAssoc as $countryCode => $fullName) {
                        if (strcasecmp($countryName, $fullName) == 0) {
                            return $countryCode;
                        }
                    }
                }
                break;

        }
        return '';
    }
    //--------------------------------------------------------------------------


    protected function zipBasedValue($token, array $fieldTypeData) {
        if (empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE])) {
            return '';
        }

        $row = SingletonRegistry::getModelUsZipCode()->getRowFromZip($fieldTypeData[Model_CampaignField::FIELD_TYPE_POSTAL_CODE]);
        if (!$row) {
            return '';
        }

        switch($token) {
            case 'postal_region':
                return $row['state_name'];
                break;
            case 'postal_region_code':
                return $row['state_abbr'];
                break;
            case 'postal_city':
                return ucwords(strtolower($row['city_name']));
                break;
            case 'postal_area_code':
                return $row['area_code'];
                break;

        }

        return '';
    }
    //--------------------------------------------------------------------------
}
