<?php

class DerivedField_Gender extends DerivedField_Base {

    protected static $myTokens =  array(
    	':real_gender:'
    );


    public function __construct() {
        $this->name = 'gender';

        $this->visibilityMap = array();
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return self::$myTokens;
    }
    //--------------------------------------------------------------------------


    public function listOverridableTokens() {
        return array(':gender:');
    }
    //--------------------------------------------------------------------------


    public function valueOfToken($token, array $fieldTypeData) {
        $token = self::normalizeTokenName($token);

        if (($token == 'gender') && !empty($fieldTypeData[Model_CampaignField::FIELD_TYPE_GENDER])) {
            return $fieldTypeData[Model_CampaignField::FIELD_TYPE_GENDER];
        }

        return '';
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        if ($token == 'gender') {
            $s = $this->valueOfToken($token, $fieldTypeData);
            if ($s) {
                $value = $s;
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------
}