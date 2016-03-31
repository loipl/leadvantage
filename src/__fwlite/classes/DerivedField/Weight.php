<?php

class DerivedField_Weight extends DerivedField_Base {
    
    private static $KGS_TO_LBS = 2.20462262;
    private static $PRECISION = 2;

    protected static $myTokens =  array(
    	':weight_kgs:', ':weight_lbs:'
    ); 


    public function __construct() {
        $this->name = 'weight';

        $this->visibilityMap = array(
            'weight_kgs' => array(
                ':weight_lbs:'
            ),
            'weight_lbs' => array(
                ':weight_kgs:'
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

        switch($token) {
            case 'weight_kgs':           
                $s = $this->valueOfWeightKGS($token, $fieldTypeData);
                break;
            case 'weight_lbs':           
                $s = $this->valueOfWeightLBS($token, $fieldTypeData);
                break;
            default:
                $s = '';
                break;
        }
        
        if ($s) {
            $value = $s;
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------

    public function valueOfWeightKGS($token, array $fieldTypeData) {
        if ($token != 'weight_kgs') {
            return '';
        }
        
        // get weight_kgs directly
        $weightKgsFTId = Model_CampaignField::FIELD_TYPE_WEIGHT_KGS;
        if (!empty($fieldTypeData[$weightKgsFTId])) {
            return $fieldTypeData[$weightKgsFTId];
        };
        
        // get weight_kgs from weight_lbs
        $weightLbsFTId = Model_CampaignField::FIELD_TYPE_WEIGHT_LBS;
        if (!empty($fieldTypeData[$weightLbsFTId])) {
            return round((floatval($fieldTypeData[$weightLbsFTId]) / self::$KGS_TO_LBS), self::$PRECISION);
        };
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------
    
    public function valueOfWeightLBS($token, array $fieldTypeData) {
        if ($token != 'weight_lbs') {
            return '';
        }
        
        // get weight_lbs directly
        $weightLbsFTId = Model_CampaignField::FIELD_TYPE_WEIGHT_LBS;
        if (!empty($fieldTypeData[$weightLbsFTId])) {
            return $fieldTypeData[$weightLbsFTId];
        };
        
        // get weight_lbs from weight_kgs
        $weightKgsFTId = Model_CampaignField::FIELD_TYPE_WEIGHT_KGS;
        if (!empty($fieldTypeData[$weightKgsFTId])) {
            return round((floatval($fieldTypeData[$weightKgsFTId]) * self::$KGS_TO_LBS), self::$PRECISION);
        };
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------
    
}
