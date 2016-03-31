<?php

class DerivedField_Height extends DerivedField_Base {
    
    private static $METER_TO_CENTIMETER = 100;
    private static $FOOT_TO_INCH = 12;

    protected static $myTokens =  array(
    	':height_meters:', ':height_centimeters:', 
        ':height_centimeters_total:', ':height_centimeters_aggregate:',
        ':height_feet:', ':height_inches:', 
        ':height_inches_total:', ':height_inches_aggregate:'
    ); 


    public function __construct() {
        $this->name = 'height';

        $this->visibilityMap = array(
            'height_meters' => array(
                ':height_centimeters:'
            ),
            'height_centimeters' => array(
                ':height_meters:', ':height_centimeters:'
            ),
            'height_feet' => array(
                ':height_inches:'
            ),
            'height_inches' => array(
                ':height_feet:', ':height_inches:'
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
            case 'height_meters':           
                $s = $this->valueOfHeightMeters($token, $fieldTypeData);
                break;
            case 'height_centimeters_aggregate':           
                $s = $this->valueOfHeightCentimetersAggregate($token, $fieldTypeData);
                break;
            case 'height_centimeters_total':           
                $s = $this->valueOfHeightCentimetersTotal($token, $fieldTypeData);
                break;
            case 'height_feet':           
                $s = $this->valueOfHeightFeet($token, $fieldTypeData);
                break;
            case 'height_inches_aggregate':           
                $s = $this->valueOfHeightInchesAggregate($token, $fieldTypeData);
                break;
            case 'height_inches_total':           
                $s = $this->valueOfHeightInchesTotal($token, $fieldTypeData);
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

    public function valueOfHeightMeters($token, array $fieldTypeData) {
        if ($token != 'height_meters') {
            return '';
        }
        // get height meter directly
        $heightMetersFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_METERS;
        if (!empty($fieldTypeData[$heightMetersFTId])) {
            return $fieldTypeData[$heightMetersFTId];
        };
        
        // get height meter from height centimeter
        $heightCentiMetersFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS;
        if (!empty($fieldTypeData[$heightCentiMetersFTId])) {
            return floor(intval($fieldTypeData[$heightCentiMetersFTId]) / self::$METER_TO_CENTIMETER);
        };
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------

    public function valueOfHeightCentimetersAggregate($token, array $fieldTypeData) {
        if ($token != 'height_centimeters_aggregate') {
            return '';
        }
        
        // get height centimeter directly
        $heightCentiMetersFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS;
        if (!empty($fieldTypeData[$heightCentiMetersFTId])) {
            return intval($fieldTypeData[$heightCentiMetersFTId]) % self::$METER_TO_CENTIMETER;
        };
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------
    
    public function valueOfHeightCentimetersTotal($token, array $fieldTypeData) {
        if ($token != 'height_centimeters_total') {
            return '';
        }
        
        $total = 0;
        // get height centimeter directly
        $heightCentiMetersFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_CENTIMETERS;
        if (!empty($fieldTypeData[$heightCentiMetersFTId])) {
            $total += intval($fieldTypeData[$heightCentiMetersFTId]);
        };

        // get height centimeter from height meter
        $heightMetersFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_METERS;
        if (!empty($fieldTypeData[$heightMetersFTId])) {
            $total += intval($fieldTypeData[$heightMetersFTId]) * self::$METER_TO_CENTIMETER;
        };
        
        return $total;
    }
    //--------------------------------------------------------------------------
    
    public function valueOfHeightFeet($token, array $fieldTypeData) {
        if ($token != 'height_feet') {
            return '';
        }
        
        // get height feet directly
        $heightFeedFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_FEET;
        if (!empty($fieldTypeData[$heightFeedFTId])) {
            return $fieldTypeData[$heightFeedFTId];
        };
        
        // get height feet from height inches
        $heightCentiInchesFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_INCHES;
        if (!empty($fieldTypeData[$heightCentiInchesFTId])) {
            return floor(intval($fieldTypeData[$heightCentiInchesFTId]) / self::$FOOT_TO_INCH);
        };
        
        // Nothing to get
        return "";
    }
    //--------------------------------------------------------------------------
    
    public function valueOfHeightInchesAggregate($token, array $fieldTypeData) {
        if ($token != 'height_inches_aggregate') {
            return '';
        }
        
        // get height inches directly
        $heightInchesFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_INCHES;
        if (!empty($fieldTypeData[$heightInchesFTId])) {
            return (intval($fieldTypeData[$heightInchesFTId]) % self::$FOOT_TO_INCH);
        };
        
        return "";
    }
    //--------------------------------------------------------------------------
    
    public function valueOfHeightInchesTotal($token, array $fieldTypeData) {
        if ($token != 'height_inches_total') {
            return '';
        }

        $total = 0;
        // get height centimeter directly
        $heightInchesFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_INCHES;
        if (!empty($fieldTypeData[$heightInchesFTId])) {
            $total += intval($fieldTypeData[$heightInchesFTId]);
        };

        // get height centimeter from height meter
        $heightFeetFTId = Model_CampaignField::FIELD_TYPE_HEIGHT_FEET;
        if (!empty($fieldTypeData[$heightFeetFTId])) {
            $total += intval($fieldTypeData[$heightFeetFTId]) * self::$FOOT_TO_INCH;
        };

        return $total;
    }
    //--------------------------------------------------------------------------
}
