<?php

class Validator_LeadSpend extends Validator_Base {
    private static $ErrorMessages = array(
        'undeliverable' => '%s is an invalid email address (Code: LS01)',
        'unreachable'   => '%s is an invalid email address (Code: LS02)',
        'illegitimate'  => '%s is an invalid email address (Code: LS03)',
    );


    public function name() {
        return "LeadSpend.com";
    }
    //--------------------------------------------------------------------------


    public function listSupportedFTypes() {
        return array(
            array(Model_CampaignField::FIELD_TYPE_EMAIL)
        );
    }
    //--------------------------------------------------------------------------


    public function validateFTypesValuesAssoc(array $values) {
        $this->lastWasAmbiguous = false;

        if (array_key_exists(Model_CampaignField::FIELD_TYPE_EMAIL, $values)) {
            $arr = array($values[Model_CampaignField::FIELD_TYPE_EMAIL], '', '', '');

            $result = $this->validate($arr[0], $arr[3], 'email');
            $arr[1] = $this->lastWasAmbiguous ? 'Ambiguous' : ($result ? 'Valid' : 'Invalid');
            $arr[2] = $this->response;

            return array(Model_CampaignField::FIELD_TYPE_EMAIL => $arr);

        } else {
            return array();
        }
    }
    //--------------------------------------------------------------------------


    public function validate($value, & $error, $method = '') {
        $this->lastWasAmbiguous = false;
        if (!$this->apiKey) {
            $error = "Missing API Key";
            return true;
        }
        $error = "";

        $result = $this->requestValidation($value, $error);
        if (is_bool($result)) {
            return $result;
        }

        if ($this->httpResponseCode == 200) {
            return $this->getResponse($error);
        } else {
            $error = '';
            $this->lastWasAmbiguous = true;
            return true;
        }
    }
    //--------------------------------------------------------------------------


    private function requestValidation($value, & $error) {
        $timeout = (int)Engine_Settings::$settings->deliveryTimeout - 1;

        $ch = curl_init();
        $url = 'https://ap02.us.qasemail.qas.com/v2/validity/' . $value . '?timeout=' . $timeout . '&key=' . $this->apiKey;
        $urlRetry = 'https://us2.api2.qasemail.qas.com/v2/validity/' . $value . '?timeout=' . $timeout . '&key=' . $this->apiKey;

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, Engine_Settings::$settings->deliveryTimeout);

        $this->response = curl_exec($ch);
        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->response === false) {
            $error = curl_error($ch);
            $this->lastWasAmbiguous = true;

            Model_LogApiErrors::log(Model_LogApiErrors::API_LEADSPEND, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, 0, '', $error);
            curl_close($ch);
            return true;
        }
        elseif ($this->httpResponseCode == 202) {
            $error = curl_error($ch);
            $this->lastWasAmbiguous = true;

            Model_LogApiErrors::log(Model_LogApiErrors::API_LEADSPEND, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, $this->response, $error);
            curl_close($ch);
            return true;
        }
        elseif($this->httpResponseCode == 400 || $this->httpResponseCode == 401) {
            $error = curl_error($ch);
            $this->lastWasAmbiguous = true;

            Model_LogApiErrors::log(Model_LogApiErrors::API_LEADSPEND, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, $this->response, $error);
            curl_close($ch);
            return true;
        }
        elseif ($this->httpResponseCode != 200) {
            curl_setopt($ch, CURLOPT_URL, $urlRetry);

            $this->response = curl_exec($ch);
            $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($this->response === false || $this->httpResponseCode != 200) {
                $error = curl_error($ch);
                $this->lastWasAmbiguous = true;

                Model_LogApiErrors::log(Model_LogApiErrors::API_LEADSPEND, $url, '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, $this->response, $error);
                curl_close($ch);
                return true;
            }
        }


        curl_close($ch);
        return null;
    }
    //--------------------------------------------------------------------------


    private function getResponse(& $error) {
        $responseSplit = explode('{',$this->response);
        $json = "{" . $responseSplit[1];

        $data = json_decode($json);

        if (!is_object($data)) {
            $error = $this->response;
            return false;
        }
        if (($data->result == 'verified') || ($data->result == 'disposable')) {
            $error = '';
            return true;
        } elseif (($data->result == 'unknown') || (isset($data->retry))) {
            $error = '';
            $this->lastWasAmbiguous = true;
            return true;
        } else {
            $error = isset(self::$ErrorMessages[$data->result]) ? self::$ErrorMessages[$data->result] : self::$ErrorMessages['default'];
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function methods() {
        return array('email');
    }
    //--------------------------------------------------------------------------
}
