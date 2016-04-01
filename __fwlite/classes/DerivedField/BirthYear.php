<?php

class DerivedField_BirthYear extends DerivedField_Base {

    protected static $myTokens =  array(
        ':age:',
    	':birth_year:'
    );


    public function __construct() {
        $this->name = 'birth_year';

        $this->visibilityMap = array(
            'age' => array(
                ':age:',
                ':birth_year:',
            ),
            'birthday_year' => array(
                ':age:',
                ':birth_year:',
            )
        );
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return self::$myTokens;
    }
    //--------------------------------------------------------------------------


    public function valueOfToken($token, array $fieldTypeData) {
        $token = self::normalizeTokenName($token);

        switch ($token) {
            case 'birth_year':
                return $this->deduceYearFromInputData($fieldTypeData);
            case 'age':
                return $this->deduceAgeFromInputData($fieldTypeData);
            default:
                return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceYearFromInputData(array $fieldTypeData) {
        $byrIndex  = Model_CampaignField::FIELD_TYPE_YEAR;
        $ageIndex  = Model_CampaignField::FIELD_TYPE_AGE;

        if (isset($fieldTypeData[$byrIndex]) && is_numeric($fieldTypeData[$byrIndex])) {
            return (int)$fieldTypeData[$byrIndex];
        } elseif (isset($fieldTypeData[$ageIndex]) && is_numeric($fieldTypeData[$ageIndex])) {
            return date('Y') - (int)$fieldTypeData[$ageIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceAgeFromInputData(array $fieldTypeData) {
        $byrIndex  = Model_CampaignField::FIELD_TYPE_YEAR;
        $ageIndex  = Model_CampaignField::FIELD_TYPE_AGE;

        if (isset($fieldTypeData[$ageIndex]) && is_numeric($fieldTypeData[$ageIndex])) {
            return (int)$fieldTypeData[$ageIndex];
        } elseif (isset($fieldTypeData[$byrIndex]) && is_numeric($fieldTypeData[$byrIndex])) {
            return  date('Y') - (int)$fieldTypeData[$byrIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        if ($token == 'age') {
            $deducedValue = $this->valueOfToken($token, $fieldTypeData);
            if ($deducedValue) {
                $value = $deducedValue;
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc List of tokens that this engine can optionally override
     */
    public function listOverridableTokens() {
        return array(':age:');
    }
    //--------------------------------------------------------------------------
}