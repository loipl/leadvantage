<?php

class DerivedField_Fullname extends DerivedField_Base {

    protected static $myTokens =  array(
        ':full_name:',
        ':first_name:', 
        ':middle_name:', 
        ':last_name:',     
        ':first_initial:', 
        ':middle_initial:', 
        ':last_initial:'
    );


    public function __construct() {
        $this->name = 'fullname';

        $this->visibilityMap = array(
            'full_name' => self::$myTokens,
            'first_name' => array(
                ':full_name:', ':first_initial:'
            ),
            'middle_name' => array(
                ':full_name:', ':middle_initial:'
            ),
            'last_name' => array(
                ':full_name:', ':last_initial:'
            ),
            'first_initial' => array(
                ':full_name:', ':first_name:'
            ),
            'middle_initial' => array(
                ':full_name:',':middle_name:'
            ),
            'last_initial' => array(
                ':full_name:', ':last_name:'
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

    public function valueOfFirstName($token, $fieldTypeData) {
        if ($token != 'first_name') {
            return '';
        }
        
        // get first_name directly from user input
        $firstNameFTId = Model_CampaignField::FIELD_TYPE_FIRST_NAME;
        if (!empty($fieldTypeData[$firstNameFTId])) {
            return $fieldTypeData[$firstNameFTId];
        } 
        
        // get first name from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            $namePart = explode(' ', $fullname, 3);
            if (count($namePart) > 0) {
                return $namePart[0];
            }
        };
        
        // get first name for first initial
        $firstInitialFTId = Model_CampaignField::FIELD_TYPE_FIRST_INITIAL;
        if (!empty($fieldTypeData[$firstInitialFTId])) {
            return $fieldTypeData[$firstInitialFTId];
        }
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfFirstInitial($token, $fieldTypeData) {
        if ($token != 'first_initial') {
            return '';
        }
          
        // get first_initial directly from user input
        $firstInitialFTId = Model_CampaignField::FIELD_TYPE_FIRST_INITIAL;
        if (!empty($fieldTypeData[$firstInitialFTId])) {
            return $fieldTypeData[$firstInitialFTId];
        } 
        
        // get first_initial directly from first name
        $firstNameFTId = Model_CampaignField::FIELD_TYPE_FIRST_NAME;
        if (!empty($fieldTypeData[$firstNameFTId])) {
            return $this->getInitial($fieldTypeData[$firstNameFTId]);
        } 
        
        // get first initial from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            return $this->getInitial($fullname);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfMiddleName($token, $fieldTypeData) {
        if ($token != 'middle_name') {
            return '';
        }
        
        // get middle name directly from user input
        $middleNameFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_NAME;
        if (!empty($fieldTypeData[$middleNameFTId])) {
            return $fieldTypeData[$middleNameFTId];
        }
        
        // get middle name from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            $namePart = explode(' ', $fullname, 3);
            if (count($namePart) > 2) {
                return $namePart[1];
            }
        };
        
        // get middle name from middle initial
        $middleInitialFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_INITIAL;
        if (!empty($fieldTypeData[$middleInitialFTId])) {
            return $fieldTypeData[$middleInitialFTId];
        }
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfMiddleInitial($token, $fieldTypeData) {
        if ($token != 'middle_initial') {
            return '';
        }
        
        // get middle initial directly
        $middleInitialFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_INITIAL;
        if (!empty($fieldTypeData[$middleInitialFTId])) {
            return $fieldTypeData[$middleInitialFTId];
        }
        
        // get middle initial from middle name
        $middleNameFTId = Model_CampaignField::FIELD_TYPE_MIDDLE_NAME;
        if (!empty($fieldTypeData[$middleNameFTId])) {
            return $this->getInitial($fieldTypeData[$middleNameFTId]);
        } else 
        
        // get middle initial from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            $namePart = explode(' ', $fullname, 3);
            if (count($namePart) > 2) {
                return $this->getInitial($namePart[1]);
            }
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfLastName($token, $fieldTypeData) {
        if ($token != 'last_name') {
            return '';
        }
           
        // get last name directly from user input
        $lastNameFTId = Model_CampaignField::FIELD_TYPE_LAST_NAME;
        if (!empty($fieldTypeData[$lastNameFTId])) {
            return $fieldTypeData[$lastNameFTId];
        }
          
        // get first name from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            $namePart = explode(' ', $fullname, 3);
            if (count($namePart) > 2) {
                return $namePart[2];
            }
        };
        
        // get last name from last initial
        $lastInitialFTId = Model_CampaignField::FIELD_TYPE_LAST_INITIAL;
        if (!empty($fieldTypeData[$lastInitialFTId])) {
            return $fieldTypeData[$lastInitialFTId];
        }
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfLastInitial($token, $fieldTypeData) {
        if ($token != 'last_initial') {
            return '';
        }
        
        // get last initial directly from user input
        $lastInitialFTId = Model_CampaignField::FIELD_TYPE_LAST_INITIAL;
        if (!empty($fieldTypeData[$lastInitialFTId])) {
            return $fieldTypeData[$lastInitialFTId];
        }
        
        // get last initial from last name
        $lastNameFTId = Model_CampaignField::FIELD_TYPE_LAST_NAME;
        if (!empty($fieldTypeData[$lastNameFTId])) {
            return $this->getInitial($fieldTypeData[$lastNameFTId]);
        } 
        
        // get last initial from full name
        $fullnameFTId = Model_CampaignField::FIELD_TYPE_FULL_NAME;
        if (!empty($fieldTypeData[$fullnameFTId])) {
            $fullname = trim($fieldTypeData[$fullnameFTId]);
            $namePart = explode(' ', $fullname, 3);
            if (count($namePart) > 2) {
                return $this->getInitial($namePart[2]);
            }
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfFullname($token, array $fieldTypeData) {
        if ($token != 'full_name') {
            return '';
        }
        
        return Engine_Utilities::getFullNameValue($fieldTypeData);
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        switch($token) {
            case 'first_name':           
                $s = $this->valueOfFirstName($token, $fieldTypeData);
                break;
            case 'first_initial':
                $s = $this->valueOfFirstInitial($token, $fieldTypeData);
                break;
            case 'middle_name':
                $s = $this->valueOfMiddleName($token, $fieldTypeData);
                break;
            case 'middle_initial': 
                $s = $this->valueOfMiddleInitial($token, $fieldTypeData);
                break;
            case 'last_name':
                $s = $this->valueOfLastName($token, $fieldTypeData);
                break;
            case 'last_initial':
                $s = $this->valueOfLastInitial($token, $fieldTypeData);
                break;
            case 'full_name':
                $s = $this->valueOfFullName($token, $fieldTypeData);
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
    
    public function getInitial($name) {
        if (strlen($name) > 0) {
            $firstChar = substr($name,0,1);
            return strtoupper($firstChar);
        } else {
            return "";
        }
    }
    // -------------------------------------------------------------------------
}
