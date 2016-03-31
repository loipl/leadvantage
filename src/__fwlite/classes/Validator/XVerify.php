<?php

class Validator_XVerify extends Validator_Base {
    private static $methods = array('email', 'phone');

    private static $emailErrors = array(
        '2'  => '%s is an invalid email address (code: XV02)',
        '3'  => '%s is an unknown email address (code: XV03)',
        '4'  => '%s is a blocked email address (code: XV04)',
        '5'  => '%s is a blocked email address (code: XV05)',
        '6'  => '%s is a blocked email address (code: XV06)',
        '7'  => '%s is a blocked email address (code: XV07)',
        '8'  => '%s is a blocked email address (code: XV08)',
        '9'  => '%s is a blocked email address (code: XV09)',
        '10' => '%s is a blocked email address (code: XV10)',
        '11' => '%s is a blocked email address (code: XV11)',
        '12' => '%s is a blocked email address (code: XV12)',
    );
    
    private static $xvError = ' (code: XV)';

    public function name() {
        return 'XVerify.com';
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
            $error = 'Invalid method' . self::$xvError;
            return false;
        }
        $dummy = '';
        switch ($method) {
            case 'email':
                return $this->validateEmail($value, $error, $dummy);
            case 'phone':
                return $this->validatePhone($value, $error, $dummy);
            default:
                $error = 'Invalid method';
                return false;
        }
    }
    //--------------------------------------------------------------------------


    public function validateEmailAndPhone($email, $phone, & $emailError, & $phoneError, & $response) {
        $url = 'http://www.xverify.com/services/allservices/verify/?services[email]=' . urlencode(trim($email)) .  '&services[phone]=' . urlencode(trim($phone)) . '&domain=leadwrench.com&type=json&apikey=' . $this->apiKey;
        $error = '';
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);

        $isValid = true;
        if ($data->xvresponse->email->status != 'valid') {
            $emailError = isset(self::$emailErrors[$data->xvresponse->email->responsecode]) ? self::$emailErrors[$data->xvresponse->email->responsecode] : $data->xvresponse->email->message . self::$xvError;
            $isValid = false;
        } else {
            $emailError = '';
        }

        if ($data->xvresponse->phone->status != 'valid') {
            $phoneError = $data->xvresponse->phone->message . self::$xvError;
            $isValid = false;
        } else {
            $phoneError = '';
        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    private function validateEmail($value, & $error, & $response) {
        $url = 'http://www.xverify.com/services/emails/verify/?email=' . urlencode(trim($value)) . '&domain=leadwrench.com&type=json&apikey=' . $this->apiKey;
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);
        if ($data->email->status != 'valid') {
            $error = isset(self::$emailErrors[$data->email->responsecode]) ? self::$emailErrors[$data->email->responsecode] : $data->email->message . self::$xvError;
            return false;
        } else {
            $error = '';
            return true;
        }
    }
    //--------------------------------------------------------------------------


    private function validatePhone($value, & $error, & $response) {
        $url = 'http://www.xverify.com/services/phone/verify/?phone=' . urlencode(trim($value)) . '&phonetype=phone&domain=leadwrench.com&type=json&apikey=' . $this->apiKey;
        if ($this->isAmbiguousResponse($url, $response, $error)) {
            return true;
        }

        $data = json_decode($this->response);
        if ($data->phone->status != 'valid') {
            $error = $data->phone->message . self::$xvError;
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
            Model_LogApiErrors::log(Model_LogApiErrors::API_XVERIFY, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, 0, '', $error);
            $this->lastWasAmbiguous = true;
            return true;
        }

        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->httpResponseCode != 200) {
            $error = curl_error($ch);
            Model_LogApiErrors::log(Model_LogApiErrors::API_XVERIFY, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, '', $error);
            $this->lastWasAmbiguous = true;
            return true;
        }
        
        // add for the case that validating server return a blank page or specific error text (resolve ticket 582, 583)
        $data = json_decode($this->response);
        if ( ! is_object($data)) {
            $error = $this->response;
            Model_LogApiErrors::log(Model_LogApiErrors::API_XVERIFY, $url, '', 0, 200, $this->response, $error);
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
