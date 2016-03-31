<?php

class DerivedField_Mobile extends DerivedField_Base {
    

    protected static $myTokens =  array(
    	':mobile_lead:'
    ); 


    public function __construct() {
        $this->name = 'mobile_lead';

        $this->visibilityMap = array(
            'mobile_lead' => array(
                ':mobile_lead:'
            )  
        );
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return self::$myTokens;
    }
    //--------------------------------------------------------------------------


    public function listOverridableTokens() {
        return self::$myTokens;
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);

        $s = $this->valueOfMobile($token, $fieldTypeData);    
        if ($s) {
            $value = $s;
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------

    public function valueOfMobile($token, array $fieldTypeData) {
        if ($token != 'mobile_lead') {
            return '';
        }
        
        // get mobile directly
        $mobileFTId = Model_CampaignField::FIELD_TYPE_MOBILE;
        if (!empty($fieldTypeData[$mobileFTId])) {
            return $fieldTypeData[$mobileFTId];
        }; 
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------

    
}
