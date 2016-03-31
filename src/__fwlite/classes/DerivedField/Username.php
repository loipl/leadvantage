<?php

class DerivedField_Username extends DerivedField_Base {

    protected static $myTokens =  array(
    	':username:'
    );


    public function __construct() {
        $this->name = 'username';

        $this->visibilityMap = array(
            'username' => array(
                ':username:'
            ),
            'email' => array(
                ':username:'
            )
        );
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return self::$myTokens;
    }
    //--------------------------------------------------------------------------


    public function listOverridableTokens() {
        return array(':username:');
    }
    //--------------------------------------------------------------------------


    public function listVisibleTokens(array $fieldTypeData) {
        return array(':username:');
    }
    //--------------------------------------------------------------------------


    public function valueOfToken($token, array $fieldTypeData) {
        $token = self::normalizeTokenName($token);
        if ($token != 'username') {
            return '';
        }

        $usernameFTId = Model_CampaignField::FIELD_TYPE_USERNAME;

        if (isset($fieldTypeData[$usernameFTId]) && !empty($fieldTypeData[$usernameFTId])) {
            return $fieldTypeData[$usernameFTId];
        };

        $emailFTId = Model_CampaignField::FIELD_TYPE_EMAIL;

        if (isset($fieldTypeData[$emailFTId]) && !empty($fieldTypeData[$emailFTId])) {
            return preg_replace("/[^A-Za-z0-9 ]/", '', strstr($fieldTypeData[$emailFTId], '@', true));
        };

        return '';
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        if ($token == 'username') {
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
