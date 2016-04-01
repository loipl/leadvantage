<?php

class DerivedField_IPAddress extends DerivedField_Base {


    public function __construct() {
        $this->name = 'ip';

        $this->visibilityMap = array(
            'ip_address' => array
            (
                ':ip_address:'
            )
        );
    }
    //--------------------------------------------------------------------------


    public function listOverridableTokens() {
        return array(':ip_address:');
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);

        if (($token == 'ip_address') && !empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS])) {
            $value = $fieldTypeData[Model_CampaignField::FIELD_TYPE_IP_ADDRESS];
            return true;
        }

        return false;
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return array();
    }
    //--------------------------------------------------------------------------
}
