<?php

class Validator_Brite extends Validator_Base {
    private static $methods = array('email', 'phone');

    private static $phoneErrors = array(
            'invalid_areacode'   => '%s includes an invalid area code (Code: BV01)',
            'blank_phone_number' => '%s is considered a blank phone number (Code: BV02)',
            'invalid_format'     => '%s is in an invalid phone number format (Code: BV03)',
            'multiple_match'     => '%s includes an invalid area code (Code: BV04)',
            'invalid_prefix'     => '%s includes an invalid phone prefix (Code: BV05)'
    );

    private static $bvError = ' (Code: BV)';

    public function name() {
        return 'BriteVerify.com';
    }
    //--------------------------------------------------------------------------


    public function listSupportedFTypes() {
        return array(
            array(Model_CampaignField::FIELD_TYPE_EMAIL),
            array(Model_CampaignField::FIELD_TYPE_PHONE),
            array(Model_CampaignField::FIELD_TYPE_EMAIL, Model_CampaignField::FIELD_TYPE_PHONE),
        );
    }
    //--------------------------------------------------------------------------


    public function validateFTypesValuesAssoc(array $values) {
        $this->lastWasAmbiguous = false;

        $hasEmail = array_key_exists(Model_CampaignField::FIELD_TYPE_EMAIL, $values);
        $hasPhone = array_key_exists(Model_CampaignField::FIELD_TYPE_PHONE, $values);

        if ($hasEmail && !$hasPhone) {
            return $this->validateEmailForApiReport($values);
        } elseif ($hasPhone && !$hasEmail) {
            return $this->validatePhoneForApiReport($values);
        } elseif ($hasEmail && $hasPhone) {
            return $this->validatePhoneAndEmailForApiReport($values);
        } else {
            return array();
        }
    }
    //--------------------------------------------------------------------------


    private function validateEmailForApiReport(array $values) {
        $arr = array($values[Model_CampaignField::FIELD_TYPE_EMAIL], '', '', '');
        $result = $this->validateEmail($arr[0], $arr[3], $arr[2]);
        $arr[1] = $this->lastWasAmbiguous ? 'Ambiguous' : ($result ? 'Valid' : 'Invalid');

        return array(
            Model_CampaignField::FIELD_TYPE_EMAIL => $arr
        );
    }
    //--------------------------------------------------------------------------


    private function validatePhoneForApiReport(array $values) {
        $arr = array($values[Model_CampaignField::FIELD_TYPE_PHONE], '', '', '');
        $result = $this->validatePhone($arr[0], $arr[3], $arr[2]);
        $arr[1] = $this->lastWasAmbiguous ? 'Ambiguous' : ($result ? 'Valid' : 'Invalid');

        return array(
            Model_CampaignField::FIELD_TYPE_PHONE => $arr
        );
    }
    //--------------------------------------------------------------------------


    private function validatePhoneAndEmailForApiReport(array $values) {
        $response = '';
        $arrEmail = array($values[Model_CampaignField::FIELD_TYPE_EMAIL], '', '', '');
        $arrPhone = array($values[Model_CampaignField::FIELD_TYPE_PHONE], '', '', '');

        $result = $this->validateEmailAndPhone($arrEmail[0], $arrPhone[0], $arrEmail[3], $arrPhone[3], $response);

        $arrEmail[1] = $this->lastWasAmbiguous ? 'Ambiguous' : (empty($arrEmail[3]) ? 'Valid' : 'Invalid');
        $arrPhone[1] = $this->lastWasAmbiguous ? 'Ambiguous' : (empty($arrPhone[3]) ? 'Valid' : 'Invalid');
        $arrEmail[2] = $response;
        $arrPhone[2] = $response;

        return array(
            Model_CampaignField::FIELD_TYPE_EMAIL => $arrEmail,
            Model_CampaignField::FIELD_TYPE_PHONE => $arrPhone,
        );
    }
    //--------------------------------------------------------------------------


    public function validate($value, & $error, $method = '') {
        $this->lastWasAmbiguous = false;
        if ($method == '') {
            $method = 'email';
        } else {
            $method = strtolower($method);
        }

        if (!in_array($method, self::$methods)) {
            $error = 'Invalid method' . self::$bvError;
            return false;
        }
        $dummy = '';
        switch ($method) {
            case 'email':
                return $this->validateEmail($value, $error, $dummy);
            case 'phone':
                return $this->validatePhone($value, $error, $dummy);
            default:
                $error = 'Invalid method' . self::$bvError;
                return false;
        }
    }
    //--------------------------------------------------------------------------


    public function validateEmailAndPhone($email, $phone, & $emailError, & $phoneError, & $response) {
        $url = 'https://bpi.briteverify.com/contacts.json?contact[email]=' . urlencode($email) . '&contact[phone]=' . urlencode($phone) . '&apikey=' . $this->apiKey;
        $error = '';
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);

        $isValid = true;
        if ($data->email->status == 'invalid') {
            $emailError = "%s is an invalid email address (Code: BV01)";
            $isValid = false;
        } else {
            $emailError = '';
        }

        if ($data->phone->status != 'valid') {
            $phoneError = isset(self::$phoneErrors[$data->phone->error_code]) ? self::$phoneErrors[$data->phone->error_code] : $data->phone->error;
            $isValid = false;
        } else {
            $phoneError = '';
        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    private function validateEmail($value, & $error, & $response) {
        $url = 'https://bpi.briteverify.com/emails.json?address=' . urlencode($value) . '&apikey=' . $this->apiKey;
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);
        if ($data->status == 'invalid') {
            $error = "%s is an invalid email address (Code: BV01)";
            return false;
        } else {
            $error = '';
            return true;
        }
    }
    //--------------------------------------------------------------------------


    private function validatePhone($value, & $error, & $response) {
        $url = 'https://bpi.briteverify.com/phones.json?number=' . urlencode($value) . '&apikey=' . $this->apiKey;
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);
        if ($data->status != 'valid') {
            $error = isset(self::$phoneErrors[$data->error_code]) ? self::$phoneErrors[$data->error_code] : ($data->error . self::$bvError);
            return false;
        } else {
            $error = '';
            return true;
        }
    }
    //--------------------------------------------------------------------------


    private function isAmbiguousResponse($url, & $response, & $error) {
        $ch = $this->initCurlFor($url);

        $response    = $this->response = curl_exec($ch);
        $isAmbiguous = $this->isInconclusiveResponse($error, $url, $ch);
        curl_close($ch);
        return $isAmbiguous;
    }
    //--------------------------------------------------------------------------


    private function isInconclusiveResponse(& $error, $url, $ch) {
        if ($this->response === false) {
            $error = curl_error($ch);
            Model_LogApiErrors::log(Model_LogApiErrors::API_BRITE, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, 0, '', $error);
            $this->lastWasAmbiguous = true;
            return true;
        }

        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->httpResponseCode != 200) {
            $error = curl_error($ch);
            Model_LogApiErrors::log(Model_LogApiErrors::API_BRITE, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, '', $error);
            $this->lastWasAmbiguous = true;
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function initCurlFor($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST,0);
        // This shouldn't be necessary, it means that their host has invalid SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, Engine_Settings::$settings->deliveryTimeout / 2);

        return $ch;
    }
    //--------------------------------------------------------------------------


    public function methods() {
        return self::$methods;
    }
    //--------------------------------------------------------------------------
}
