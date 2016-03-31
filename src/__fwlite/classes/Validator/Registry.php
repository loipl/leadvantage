<?php

class Validator_Registry {
    private static $validators  = array();
    private static $initialized = false;
    private static $tried       = array();
    private static $httpCodes   = array();

    const VAL_DV        = 'DV';
    const VAL_BRITE     = 'Brite';
    const VAL_XVERIFY   = 'XVerify';
    const VAL_LEADSPEND = 'LeadSpend';

    // These orders are not currently implemented; changes here don't have any effect
    private static $emailOrder         = array(self::VAL_LEADSPEND,
                                               self::VAL_XVERIFY,
                                               self::VAL_BRITE);

    private static $phoneOrder         = array(self::VAL_XVERIFY,
                                               self::VAL_BRITE);

    private static $emailAndPhoneOrder = array(self::VAL_XVERIFY,
                                               self::VAL_BRITE);


    public static $valNames = array(
        self::VAL_DV        => 1,
        self::VAL_BRITE     => 2,
        self::VAL_XVERIFY   => 3,
        self::VAL_LEADSPEND => 4
    );

    /**
     * @var Model_ValidationCache
     */
    private static $model = false;


    private static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        //self::add(new Validator_DV());
        self::add(new Validator_Brite());
        self::add(new Validator_XVerify());
        self::add(new Validator_LeadSpend());
    }
    //--------------------------------------------------------------------------


    private static function add(Validator_Base $v) {
        self::$validators[strtolower($v->key())] = $v;
    }
    //--------------------------------------------------------------------------


    public static function getTriedValidatorsOnLastRun() {
        return self::$tried;
    }
    //--------------------------------------------------------------------------


    public static function getHttpCodesOnLastRun() {
        return self::$httpCodes;
    }
    //--------------------------------------------------------------------------


    /**
     * @return Validator_Base
     */
    public static function getByName($name) {
        if (!self::$initialized) {
            self::init();
        }
        $n = strtolower($name);
        return isset(self::$validators[$n]) ? self::$validators[$n] : null;
    }
    //--------------------------------------------------------------------------


    public static function validatePhoneAndEmailWithBrite($emailValue, & $emailError, $phoneValue, & $phoneError, array $extraSettings = array()) {
        if (Config::$validatorCache) {
            $resultEmail = $resultPhone = false;
            if (self::cached($emailValue, Model_CampaignField::FIELD_TYPE_EMAIL, $emailError, $resultEmail) &&
                   self::cached($phoneValue, Model_CampaignField::FIELD_TYPE_PHONE, $phoneError, $resultPhone)) {
                return $resultEmail && $resultPhone;
            }
        }

        /* @var $xvValidator Validator_XVerify */
        $xvValidator = self::getByName(self::VAL_XVERIFY);
        if ($extraSettings) {
            $xvValidator->setExtraSettings($extraSettings);
        }

        $dummy = '';
        $isValid = $xvValidator->validateEmailAndPhone(trim($emailValue), trim($phoneValue), $emailError, $phoneError, $dummy);
        self::$tried[] = self::VAL_XVERIFY;
        self::$httpCodes[] = $xvValidator->getLastHttpResponseCode();

        if ($xvValidator->lastWasAmbiguous()) {
            /* @var $briteValidator Validator_Brite */
            $briteValidator = self::getByName(self::VAL_BRITE);
            if ($extraSettings) {
                $briteValidator->setExtraSettings($extraSettings);
            }

            $dummy = '';
            $isValid = $briteValidator->validateEmailAndPhone(trim($emailValue), trim($phoneValue), $emailError, $phoneError, $dummy);
            self::$tried[] = self::VAL_BRITE;
            self::$httpCodes[] = $briteValidator->getLastHttpResponseCode();
        }

        if (($isValid || ($emailError === '')) && Config::$validatorCache) {
            self::store($emailValue, Model_CampaignField::FIELD_TYPE_EMAIL, '', true);
        }
        if (($isValid || ($phoneError === '')) && Config::$validatorCache) {
            self::store($phoneValue, Model_CampaignField::FIELD_TYPE_PHONE, '', true);
        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    public static function validate($value, $fieldType, & $error, array $extraSettings = array()) {
        if (Config::$validatorCache) {
            $result = false;
            if (self::cached($value, $fieldType, $error, $result)) {
                return $result;
            }
        }
        $skipped = false;
        $result = self::innerValidate(trim($value), $fieldType, $error, $extraSettings, $skipped);
        if ($result && Config::$validatorCache && !$skipped) {
            self::store($value, $fieldType, $error, $result);
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    private static function innerValidate($value, $fieldType, & $error, array $extraSettings, & $skipped) {
        self::$tried     = array();
        self::$httpCodes = array();
        $skipped = false;

        switch ($fieldType) {
            case Model_CampaignField::FIELD_TYPE_PHONE:
                return self::innerValidatePhone($value, $error, $extraSettings);

            case Model_CampaignField::FIELD_TYPE_EMAIL:
                return self::innerValidateEmail($value, $error, $extraSettings);

            default:
                // Everything else we don't validate, we just assume it is OK
                $error = '';
                $skipped = true;
                return true;
        }
    }
    //--------------------------------------------------------------------------


    private static function innerValidatePhone($value, & $error, array $extraSettings) {
        /* @var $xvValidator Validator_XVerify */
        $xvValidator = self::getByName(self::VAL_XVERIFY);
        if ($extraSettings) {
            $xvValidator->setExtraSettings($extraSettings);
        }
        $isValid = $xvValidator->validate($value, $error, 'phone');
        self::$tried[]     = self::VAL_XVERIFY;
        self::$httpCodes[] = $xvValidator->getLastHttpResponseCode();

        if ($xvValidator->lastWasAmbiguous()) {
            /* @var $briteValidator Validator_Brite */
            $briteValidator = self::getByName(self::VAL_BRITE);
            if ($extraSettings) {
                $briteValidator->setExtraSettings($extraSettings);
            }
            $isValid = $briteValidator->validate($value, $error, 'phone');
            self::$tried[]     = self::VAL_BRITE;
            self::$httpCodes[] = $briteValidator->getLastHttpResponseCode();
        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    private static function innerValidateEmail($value, & $error, array $extraSettings) {
        /* @var $lsValidator Validator_LEADSPEND */
//        $lsValidator = self::getByName(self::VAL_LEADSPEND);
//        if ($extraSettings) {
//            $lsValidator->setExtraSettings($extraSettings);
//        }
//        $isValid = $lsValidator->validate($value, $error);
//        self::$tried[]     = self::VAL_LEADSPEND;
//        self::$httpCodes[] = $lsValidator->getLastHttpResponseCode();
//
//        if ($lsValidator->lastWasAmbiguous()) {
            /* @var $xvValidator Validator_XVerify */
            $xvValidator = self::getByName(self::VAL_XVERIFY);
            if ($extraSettings) {
                $xvValidator->setExtraSettings($extraSettings);
            }
            $isValid = $xvValidator->validate($value, $error);
            self::$tried[]     = self::VAL_XVERIFY;
            self::$httpCodes[] = $xvValidator->getLastHttpResponseCode();

            if ($xvValidator->lastWasAmbiguous()) {
                /* @var $briteValidator Validator_Brite */
                $briteValidator = self::getByName(self::VAL_BRITE);
                if ($extraSettings) {
                    $briteValidator->setExtraSettings($extraSettings);
                }
                $isValid = $briteValidator->validate($value, $error, 'email');
                self::$tried[]     = self::VAL_BRITE;
                self::$httpCodes[] = $briteValidator->getLastHttpResponseCode();
            }
//        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    public static function cached($value, $fieldType, & $error, & $result) {
        self::$model or self::$model = SingletonRegistry::getSingleInstance('Model_ValidationCache');
        $isValid = false;
        $message = '';
        if (self::$model->isCached($fieldType, trim($value), $isValid, $message, Config::$validCacheHrs)) {
            $result = $isValid;
            $error  = $message;
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public static function store($value, $fieldType, $error, $result) {
        self::$model or self::$model = SingletonRegistry::getSingleInstance('Model_ValidationCache');
        self::$model->store(trim($value), $fieldType, $error, $result);
    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    public static function listValidators() {
        if (!self::$initialized) {
            self::init();
        }
        return self::$validators;
    }
    //--------------------------------------------------------------------------


    public static function clearGlobalCounters() {
        self::$tried = array();
        self::$httpCodes = array();
    }
    //--------------------------------------------------------------------------
}