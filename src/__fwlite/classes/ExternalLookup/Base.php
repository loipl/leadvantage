<?php

abstract class ExternalLookup_Base {

    protected $name;

    protected $tokens = array();

    /**
     * @desc Whether we should cache the result
     */
    protected $cache = true;

    /**
     * @desc How long it should stay in cache, in seconds
     */
    protected $cacheTimeout = 86400;


    public function getName() {
        return $this->name;
    }
    //--------------------------------------------------------------------------


    public final function lookup(array $fieldTypeValues, $token) {
        $token = self::normalizeTokenName($token);

        if (in_array($token, $this->tokens)) {
            if ($this->cache) {
                $key = $this->requestKey($fieldTypeValues, $token);

                /* @var $modelExternalLookupCache Model_ExternalLookupCache */
                $modelExternalLookupCache = SingletonRegistry::getSingleInstance('Model_ExternalLookupCache');

                $result = $modelExternalLookupCache->cachedValueFor($this->name, $key, $this->cacheTimeout);
                if ($result !== false) {
                    return $result;
                } else {
                    $value = $this->executeLookup($fieldTypeValues, $token);
                    if ($value) {
                        $data = array(
                            'engine' => $this->name,
                            'key'    => $key,
                            'result' => $value
                        );
                        $modelExternalLookupCache->insert($data);
                    }
                    return $value;
                }
            } else {
                return $this->executeLookup($fieldTypeValues, $token);
            }
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return $this->tokens;
    }
    //--------------------------------------------------------------------------


    public function listVisibleTokens(array $fieldTypeData) {
        DerivedField_Registry::ensureFieldTypesAssocIsInitialized();

        $visibleTokens = array();
        foreach ($this->tokens as $token) {
            if(!mb_stristr($token, $this->name . '_')) {
                $ftIndex = array_search($token, DerivedField_Registry::$fieldTypesAssoc);
                if (($ftIndex !== false) && isset($fieldTypeData[$ftIndex])) {
                    $visibleTokens[] = $token;
                }
            }
        }
        $visibleTokens = array_unique($visibleTokens);
        return $visibleTokens;
    }
    //--------------------------------------------------------------------------


    public abstract function requestKey(array $fieldTypeValues, $token);
    //--------------------------------------------------------------------------


    protected abstract function executeLookup(array $fieldTypeValues, $token);
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
