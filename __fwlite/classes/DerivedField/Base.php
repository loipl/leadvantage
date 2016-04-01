<?php

abstract class DerivedField_Base {
    protected $name;

    protected $visibilityMap = array();

    /**
     * @var Engine_Data
     */
    protected $engData;


    public function getName() {
        return $this->name;
    }
    //--------------------------------------------------------------------------


    public function setEngineData(Engine_Data $data) {
        $this->engData = $data;
    }
    //--------------------------------------------------------------------------


    public abstract function listTokens();
    //--------------------------------------------------------------------------


    public function listVisibleTokens(array $fieldTypeData) {
        DerivedField_Registry::ensureFieldTypesAssocIsInitialized();

        $visibleTokens = array();
        foreach ($this->visibilityMap as $ftypeName => $list) {
            $ftIndex = array_search($ftypeName, DerivedField_Registry::$fieldTypesAssoc);
            if (($ftIndex !== false) && isset($fieldTypeData[$ftIndex])) {
                $visibleTokens = array_merge($visibleTokens, $list);
            }
        }
        $visibleTokens = array_unique($visibleTokens);
        return $visibleTokens;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc list of tokens that this engine can optionally override
     */
    public function listOverridableTokens() {
        return array();
    }
    //--------------------------------------------------------------------------


    public function valueOfToken($token, array $fieldTypeData) {
        return '';
    }
    //--------------------------------------------------------------------------


    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        return false;
    }
    //--------------------------------------------------------------------------


    public static function normalizeTokenName($token) {
        $token = strtolower($token);
        if (substr($token, 0, 1) == ':') {
            $token = substr($token, 1);
        }
        if (substr($token, -1) == ':') {
            $token = substr($token, 0, -1);
        }
        return $token;
    }
    //--------------------------------------------------------------------------
}
