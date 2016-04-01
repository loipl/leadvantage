<?php

class DerivedField_Registry {

    protected static $engines = array();

    public static $fieldTypesAssoc = array();


    public static function listTokens() {
        if (empty(self::$engines)) {
            self::initEnginesList();
        }
        $tokens = array();
        foreach (self::$engines as $name => /* @var $dfe DerivedField_Base */ $dfe) {
            foreach ($dfe->listTokens() as $t) {
                $tokens[$t] = $name;
            }
        }
        return $tokens;
    }
    //--------------------------------------------------------------------------


    public static function ensureFieldTypesAssocIsInitialized() {

        if (empty(self::$fieldTypesAssoc)) {
            /* @var $modelFieldType Model_FieldType */
            $modelFieldType = SingletonRegistry::getSingleInstance('Model_FieldType');
            self::$fieldTypesAssoc = $modelFieldType->listFieldTypesAssoc(true);
        }
    }
    //--------------------------------------------------------------------------


    public static function listAvailableTokens() {
        if (empty(self::$engines)) {
            self::initEnginesList();
        }
        $tokens = array();
        foreach (self::$engines as $name => /* @var $dfe DerivedField_Base */ $dfe) {
            foreach ($dfe->listTokens() as $t) {
                $tokens[] = $t;
            }
        }
        return $tokens;
    }
    //--------------------------------------------------------------------------


    public static function listOverridableTokens() {
        if (empty(self::$engines)) {
            self::initEnginesList();
        }
        $tokens = array();
        foreach (self::$engines as $name => /* @var $dfe DerivedField_Base */ $dfe) {
            foreach ($dfe->listOverridableTokens() as $t) {
                $tokens[$t] = $name;
            }
        }
        return $tokens;
    }
    //--------------------------------------------------------------------------


    public static function listEngines() {
        if (empty(self::$engines)) {
            self::initEnginesList();
        }
    }
    //--------------------------------------------------------------------------


    public static function listVisibleFields(array $fieldTypesData) {
        if (empty(self::$engines)) {
            self::initEnginesList();
        }
        $list = array();
        foreach (self::$engines as $name => /* @var $dfe DerivedField_Base */ $dfe) {
            $arr = $dfe->listVisibleTokens($fieldTypesData);
            $list = array_merge($list, $arr);
        }
        return $list;
    }
    //--------------------------------------------------------------------------


    /**
     * @return DerivedField_Base
     */
    public static function getEngine($name) {
        return isset(self::$engines[$name]) ? self::$engines[$name] : null;
    }
    //--------------------------------------------------------------------------


    protected static function initEnginesList() {
        self::add(new DerivedField_Postal);
        self::add(new DerivedField_BirthYear);
        self::add(new DerivedField_External);
        self::add(new DerivedField_Gender);
        self::add(new DerivedField_IPAddress);
        self::add(new DerivedField_Username);
        self::add(new DerivedField_Fullname);
        self::add(new DerivedField_Phone);
        self::add(new DerivedField_Height);
        self::add(new DerivedField_Weight);
        self::add(new DerivedField_Mobile);
    }
    //--------------------------------------------------------------------------


    protected static function add(DerivedField_Base $dfb) {
        self::$engines[$dfb->getName()] = $dfb;
    }
    //--------------------------------------------------------------------------
}
