<?php

class DerivedField_External extends DerivedField_Base {

    protected static $myTokens =  array(
        ':education:',
        ':home_owner:',
        ':years_at_address:',
        ':months_at_address:',
        ':married:',
        ':occupation:',
        ':children:',
        ':home_value:',
        ':high_net_worth:',
        ':annual_income:',
        ':monthly_income:',
        ':interests:',
    );


    public function __construct() {
        $this->name = 'external';

        $this->visibilityMap = array(
            'education' => array(
                ':education:',
            ),
            'home_owner' => array(
                ':home_owner:',
            ),
            'years_at_address' => array(
                ':years_at_address:',
            ),
            'months_at_address' => array(
                ':months_at_address:',
            ),
            'married' => array(
                ':married:',
            ),
            'occupation' => array(
                ':occupation:',
            ),
            'children' => array(
                ':children:',
            ),
            'home_value' => array(
                ':home_value:',
            ),
            'high_net_worth' => array(
                ':high_net_worth:',
            ),
            'annual_income' => array(
                ':annual_income:',
            ),
            'monthly_income' => array(
                ':monthly_income:',
            ),
            'interests' => array(
                ':interests:',
            ),
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
            case 'education':
                return $this->deduceEducationFromInputData($fieldTypeData);
            case 'home_owner':
                return $this->deduceHomeOwnerFromInputData($fieldTypeData);
            case 'years_at_address':
                return $this->deduceYearsAtAddressFromInputData($fieldTypeData);
            case 'months_at_address':
                return $this->deduceMonthsAtAddressFromInputData($fieldTypeData);
            case 'married':
                return $this->deduceMarriedFromInputData($fieldTypeData);
            case 'occupation':
                return $this->deduceOccupationFromInputData($fieldTypeData);
            case 'children':
                return $this->deduceChildrenFromInputData($fieldTypeData);
            case 'home_value':
                return $this->deduceHomeValueFromInputData($fieldTypeData);
            case 'high_net_worth':
                return $this->deduceHighNetWorthFromInputData($fieldTypeData);
            case 'annual_income':
                return $this->deduceAnnualIncomeFromInputData($fieldTypeData);
            case 'monthly_income':
                return $this->deduceMonthlyIncomeFromInputData($fieldTypeData);
            case 'interests':
                return $this->deduceInterestsFromInputData($fieldTypeData);
            default:
                return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceEducationFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_EDUCATION;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceHomeOwnerFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_HOME_OWNER;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceYearsAtAddressFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_YEARS_AT_ADDRESS;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceMonthsAtAddressFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_MONTHS_AT_ADDRESS;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceMarriedFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_MARRIED;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceOccupationFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_OCCUPATION;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceChildrenFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_CHILDREN;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceHomeValueFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_HOME_VALUE;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceHighNetWorthFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_HIGH_NET_WORTH;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceAnnualIncomeFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_ANNUAL_INCOME;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceMonthlyIncomeFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_MONTHLY_INCOME;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    private function deduceInterestsFromInputData(array $fieldTypeData) {
        $fieldIndex  = Model_CampaignField::FIELD_TYPE_INTERESTS;

        if (isset($fieldTypeData[$fieldIndex])) {
            return $fieldTypeData[$fieldIndex];
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        if ($token == 'high_net_worth') {
            if (isset($fieldTypeData[Model_CampaignField::FIELD_TYPE_HIGH_NET_WORTH])) {
                if (!empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_HIGH_NET_WORTH]))
                    $value = $fieldTypeData[Model_CampaignField::FIELD_TYPE_HIGH_NET_WORTH];
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
        return array(':education:',
                     ':home_owner:',
                     ':years_at_address:',
                     ':months_at_address:',
                     ':married:',
                     ':occupation:',
                     ':children:',
                     ':home_value:',
                     ':high_net_worth:',
                     ':annual_income:',
                     ':monthly_income:',
                     ':interests:',
                    );
    }
    //--------------------------------------------------------------------------
}