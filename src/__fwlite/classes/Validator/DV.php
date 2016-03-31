<?php

class Validator_DV extends Validator_Base {
    private static $codes2ErrorMessages = array(
            1  => '%s is an invalid email address (syntax)',
            2  => '%s is an invalid email address (host name)',
            3  => '%s is an invalid email address (email server)',
            4  => '%s is an invalid email address (user)',
            5  => '%s is an invalid email address (length)',
            6  => '%s is an invalid email address (not found)',
            7  => '%s is an invalid email address (unknown)',
    );


    public function name() {
        return "DataValidation.com";
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
        // Fake ambiguous for unit tests
        if (!empty($this->extraSettings['DV_ambiguous'])) {
            $this->lastWasAmbiguous = true;
            return true;
        }

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

        if ($this->httpResponseCode == 201) {
            return $this->readResponse($error);
        } else {
            $error = '';
            $this->lastWasAmbiguous = true;
            return true;
        }
    }
    //--------------------------------------------------------------------------


    private function requestValidation($value, & $error) {
        $ch = curl_init();
        $url = 'http://leadw.dvapi.com/email/validate';
        $postText = '{
	"settings":[],
	"emails":
	[
		{
			"email":"' . $value . '"
		}
	]
}';
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apikey: ' . $this->apiKey, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, Engine_Settings::$settings->deliveryTimeout / 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postText);

        $this->response = curl_exec($ch);
        if ($this->response === false) {
            $error = curl_error($ch);
            $this->lastWasAmbiguous = true;
            Model_LogApiErrors::log(Model_LogApiErrors::API_DV, $url, $postText, curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, 0, '', $error);
            curl_close($ch);
            return true;
        }

        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->httpResponseCode != 201) {
            Model_LogApiErrors::log(Model_LogApiErrors::API_DV, $url, $postText, curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, $this->httpResponseCode, $this->response, curl_error($ch));
        }

        curl_close($ch);
        return null;
    }
    //--------------------------------------------------------------------------


    private function readResponse(& $error) {
        $url = $this->findRedirectionUrlInResponse();
        if (!$url) {
            return true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '.json');

        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apikey: ' . $this->apiKey));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, Engine_Settings::$settings->deliveryTimeout / 2);

        $this->response = curl_exec($ch);
        if ($this->response === false) {
            $error = curl_error($ch);
            $this->lastWasAmbiguous = true;
            Model_LogApiErrors::log(Model_LogApiErrors::API_DV, $url . '.json', '', curl_errno($ch) == CURLE_OPERATION_TIMEDOUT, 0, '', $error);
            curl_close($ch);
            return true;
        }

        $this->httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return $this->getResponse($error);
    }
    //--------------------------------------------------------------------------


    private function findRedirectionUrlInResponse() {
        $arr = explode("\n", $this->response);
        foreach ($arr as $s) {
            $s = trim($s);
            if (strpos($s, 'Location: ') === 0) {
                $url = substr($s, strlen('Location: '));
                if ($url) {
                    if (strpos($url, '://') === false) {
                        if (substr($url, 0, 1) != '/') {
                            $url = '/email/' . $url;
                        }
                        $url = 'http://leadw.dvapi.com' . $url;
                    }
                    return $url;
                }
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function getResponse(& $error) {
        $data = json_decode($this->response);
        if (!is_object($data)) {
            $error = $this->response;
            return false;
        }
        if (($data->code == 8) || ($data->code == 9)) {
            $error = '';
            return true;
        } elseif (($data->code == 10) || ($data->code == 7)) {
            $error = '';
            $this->lastWasAmbiguous = true;
            return true;
        } else {
            $error = isset(self::$codes2ErrorMessages[$data->code]) ? self::$codes2ErrorMessages[$data->code] : $data->message;
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function methods() {
        return array('email');
    }
    //--------------------------------------------------------------------------
}
