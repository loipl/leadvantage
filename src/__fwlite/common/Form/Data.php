<?php

/**
 * @desc Data from a form
 */
class Form_Data {

    protected static $uniqueCounter = 0;

    /**
     * @desc Values of form elements
     */
    protected $values = array();

    protected $errors = array();

    /**
     * @desc Form Elements
     */
    protected $elements = array();

    protected $elementsByName = array();

    protected $hasFileUpload = false;

    protected $typeImportCallbacks = array();

    protected $customData = array();

    protected $action = '';

    protected $method = 'post';


    public function __construct() {
        //
    }
    //--------------------------------------------------------------------------


    public function getAction() {
        return $this->action;
    }
    //--------------------------------------------------------------------------


    public function setAction($action) {
        $this->action = $action;
    }
    //--------------------------------------------------------------------------


    public function getMethod() {
        return $this->method;
    }
    //--------------------------------------------------------------------------


    public function setMethod($method) {
        $method = strtolower(trim($method));
        if (($method != 'post') && ($method != 'get')) {
            throw new EServerError("Form method can be POST or GET");
        }
        $this->method = $method;
    }
    //--------------------------------------------------------------------------


    public function addRaw(array $attributes = array()) {
        $name = isset($attributes['name']) ? trim($attributes['name']) : '';
        $attributes['* unique_id'] = self::$uniqueCounter++;
        $this->elements[] = $attributes;
        if ($attributes['* type'] == 'file') {
            $this->hasFileUpload = true;
        }
        if ($name) {
            $this->elementsByName[$name][] = $attributes;
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Magic keys:
     * * hint
     * * trim
     * * required
     * * filter
     * * filter_error
     * * items (for select)
     * * trattrs (for html)
     * * tdattrs (for html)
     */
    public function add($type, $name = '', $label = '', array $attributes = array()) {
        $attributes['name']    = $name;
        $attributes['* label'] = $label;
        $attributes['* type']  = strtolower($type);
        $this->addRaw($attributes);
    }
    //--------------------------------------------------------------------------


    public function __get($fieldName) {
        return isset($this->values[$fieldName]) ? $this->values[$fieldName] : null;
    }
    //--------------------------------------------------------------------------


    public function __set($fieldName, $value) {
        $this->values[$fieldName] = $value;
    }
    //--------------------------------------------------------------------------


    public function __isset($fieldName) {
        return isset($this->values[$fieldName]);
    }
    //--------------------------------------------------------------------------


    public function setCustom($name, $value) {
        $this->customData[$name] = $value;
    }
    //--------------------------------------------------------------------------


    public function getCustom($name) {
        return isset($this->customData[$name]) ? $this->customData[$name] : null;
    }
    //--------------------------------------------------------------------------


    public function getValues() {
        return $this->values;
    }
    //--------------------------------------------------------------------------


    public function getElements() {
        return $this->elements;
    }
    //--------------------------------------------------------------------------


    public function getElementByName($name) {
        if (isset($this->elementsByName[$name])) {
            return $this->elementsByName[$name][0];
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function getHasFileUpload() {
        return $this->hasFileUpload;
    }
    //--------------------------------------------------------------------------


    public function importFromArray(array $data, $isRequest = true) {
        foreach ($this->elements as & $elem) {
            if (empty($elem['name'])) {
                continue;
            }
            $name = $elem['name'];

            $func = 'import' . ucfirst($elem['* type']);
            if (isset($this->typeImportCallbacks[$elem['* type']])) {
                $this->values[$name] = call_user_func($this->typeImportCallbacks[$elem['* type']], $elem, $name, $data, $isRequest);
            } elseif(is_callable(array($this, $func))) {
                $this->values[$name] = $this->$func($elem, $name, $data, $isRequest);
            } else {
                if (!empty($elem['* trim'])) {
                    $this->values[$name] = isset($data[$name]) ? trim($data[$name]) : '';
                } else {
                    $this->values[$name] = isset($data[$name]) ? $data[$name] : '';
                }
            }

            if ($isRequest) {
                $isRequired = !empty($elem['* required']);
                $isEmpty    = $this->values[$name] == '';
                if ($isRequired && $isEmpty) {
                    $this->errors[$name][] = 'This field is required';
                } elseif (!empty($elem['* filter']) && ($isRequired || !$isEmpty)) {
                    $v = filter_var($this->values[$name], $elem['* filter']);
                    if (!$v) {
                        $this->errors[$name][] = isset($elem['* filter_error']) ? $elem['* filter_error'] : 'Invalid Value';
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    public function importPostRequestAndThrowEDoneOnError() {
        $this->importFromArray($_POST, true);
        $this->throwDoneIfErrors();
    }
    //--------------------------------------------------------------------------


    public function throwDoneIfErrors() {
        if ($this->errors) {
            throw new EDoneException();
        }
    }
    //--------------------------------------------------------------------------


    public function importCheckbox(array $attributes, $name, array $arr, $isRequest) {
        return !empty($arr[$name]);
    }
    //--------------------------------------------------------------------------


    public function importSelect(array $attributes, $name, array $arr, $isRequest) {
        if (!isset($arr[$name], $attributes['* items'])) {
            return null;
        }
        $isMulti = !empty($attributes['multiple']);
        if ($isMulti) {
            return $this->importArrayField($attributes, $name, $arr);
        } else {
            if (isset($attributes['* items'][$arr[$name]])) {
                return $arr[$name];
            }
        }
        return null;
    }
    //--------------------------------------------------------------------------


    protected function importArrayField(array $attributes, $name, array $arr) {
        $values = array();
        if (is_array($arr[$name])) {
            foreach ($arr[$name] as $k) {
                if (isset($attributes['* items'][$k])) {
                    $values[] = $k;
                }
            }
        }
        return empty($values) ? null : $values;
    }
    //--------------------------------------------------------------------------


    ////////////////////////////////////////////////////////////////////////////
    //
    // ERRORS
    //
    ////////////////////////////////////////////////////////////////////////////


    public function addError($fieldName, $error) {
        $this->errors[$fieldName][] = $error;
    }
    //--------------------------------------------------------------------------


    public function addErrorAndThrowEDone($fieldName, $error) {
        $this->errors[$fieldName][] = $error;
        throw new EDoneException();
    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    public function getFieldErrors($fieldName) {
        return empty($this->errors[$fieldName]) ? array() : $this->errors[$fieldName];
    }
    //--------------------------------------------------------------------------


    /**
     * @return array
     */
    public function getFieldErrorsImploded($fieldName, $delimiter = "<br />") {
        return empty($this->errors[$fieldName]) ? '' : implode($delimiter, $this->errors[$fieldName]);
    }
    //--------------------------------------------------------------------------


    public function getAllErrors() {
        return $this->errors;
    }
    //--------------------------------------------------------------------------


    public function clearErrors() {
        $this->errors = array();
    }
    //--------------------------------------------------------------------------


    public function hasErrors() {
        return !empty($this->errors);
    }
    //--------------------------------------------------------------------------


    public function filterVarIfNotEmpty($fieldName, $filter, $error = 'Invalid value') {
        if (!$this->$fieldName) {
            return;
        }
        $v = filter_var($this->$fieldName, $filter);
        if (!$v) {
            $this->addError($fieldName, $error);
        }
    }
    //--------------------------------------------------------------------------


    public function checkNumeric($fieldName, $min = false, $max = false) {
        $v = $this->$fieldName;
        $error = "Please enter a number";
        if ($min !== false && $max !== false) {
            $error .= " between $min and $max";
        } elseif ($min !== false) {
            $error .=", min $min";
        } elseif ($max !== false) {
            $error .= ", max $max";
        }
        if (!is_numeric($v)) {
            $this->addError($fieldName, $error);
        } elseif ($min !== false && $v < $min) {
            $this->addError($fieldName, $error);
        } elseif ($max !== false && $v > $max) {
            $this->addError($fieldName, $error);
        }
    }
    //--------------------------------------------------------------------------
}
