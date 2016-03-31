<?php

abstract class Validator_Base {
    protected $apiKey = '';
    private   $key = false;
    protected $lastWasAmbiguous = false;

    protected $httpResponseCode = 0;
    protected $response = '';
    protected $extraSettings = array();


    public function __construct() {
        $this->key = strtolower(substr(get_class($this), strlen('Validator_')));
        if (isset(Config::$validatorKeys[$this->key])) {
            $this->apiKey = Config::$validatorKeys[$this->key];
        }
    }
    //--------------------------------------------------------------------------


    abstract public function name();
    //--------------------------------------------------------------------------


    public function lastWasAmbiguous() {
        return $this->lastWasAmbiguous;
    }
    //--------------------------------------------------------------------------


    public function getLastHttpResponseCode() {
        return $this->httpResponseCode;
    }
    //--------------------------------------------------------------------------


    public function setExtraSettings(array $settings) {
        $this->extraSettings = $settings;
    }
    //--------------------------------------------------------------------------


    public function key() {
        return $this->key;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc list of methods, meant for drop-down in UI where users can pick type of verification,
     * or maybe for field types
     *
     * @return array
     */
    public function methods() {
        return array();
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Returns array of arrays of field_type_ids - if API supports validating more than
     * one field type in single call
     */
    abstract public function listSupportedFTypes();
    //--------------------------------------------------------------------------


    /**
     * @desc Validate a set of values indexed by field_type_id. Returns array with ft_id => (original value,
     * validation result, their response, our error message)
     *
     * @param array $values array(ft_id_email => email@address.com, ft_id_phone => 555-123456) etc
     */
    abstract public function validateFTypesValuesAssoc(array $values);
    //--------------------------------------------------------------------------


    /**
     * @param string $value
     * @param string $error
     *
     * @return bool
     */
    abstract public function validate($value, & $error, $method = '');
    //--------------------------------------------------------------------------
}
