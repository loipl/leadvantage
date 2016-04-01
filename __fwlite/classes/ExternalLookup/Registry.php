<?php

class ExternalLookup_Registry {

    protected static $features = array();

    protected static $defaultAdded = false;


    public static function addFeature(ExternalLookup_Base $feature) {
        $name = $feature->getName();
        self::$features[$name] = $feature;
    }
    //--------------------------------------------------------------------------


    public static function getFeatures() {
        if (!self::$defaultAdded) {
            self::addDefaultFeatures();
        }
        $arr = array_keys(self::$features);
        sort($arr);
        return $arr;
    }
    //--------------------------------------------------------------------------


    protected static function addDefaultFeatures() {
        self::addFeature(new ExternalLookup_RapLeaf);

        self::$defaultAdded = true;
    }
    //--------------------------------------------------------------------------


    /**
     * @return ExternalLookup_Base
     */
    public static function getNamedFeature($featureName) {
        return isset(self::$features[$featureName]) ? self::$features[$featureName] : null;
    }
    //--------------------------------------------------------------------------


    public static function listTokens2Names() {
        if (!self::$defaultAdded) {
            self::addDefaultFeatures();
        }
        
        // disable RapLeaf tokens if configured
        $rapLeaf = new ExternalLookup_RapLeaf;
        $rapLeafName = $rapLeaf->getName();
        if (Config::$disableRapLeaf && isset(self::$features[$rapLeafName])) {
            unset(self::$features[$rapLeafName]);
        }

        $result = array();
        foreach (self::$features as $name => $feature) {
            /* @var $feature ExternalLookup_Base */
            foreach ($feature->listTokens() as $token) {
                // suppress showing rapleaf_* tokens
                if (substr($token,0,9) != ':rapleaf_') {
                    $result[$token] = $name;
                }
            }
        }
        asort($result);
        return $result;
    }
    //--------------------------------------------------------------------------


    /**
     * @param array $tokens should be array where keys are token names
     * @param string $maxCap
     * @param array $fieldTypes list of fields used in user campaign
     */
    public static function removeTokensBasedOnUserCap(array & $tokens, $maxCap, array $fieldTypes) {
        if ($maxCap == Auth::L_ADMIN) {
            return;
        }
        $maxCapNr = Auth::userLevelAsNumber($maxCap);

        foreach (array_keys($tokens) as $k) {
            $tn = ExternalLookup_Base::normalizeTokenName($k);
            if (isset(Config::$requiredLevelsForExternalLookupTokens[$tn]['level'])) {
                if ($maxCapNr < Auth::userLevelAsNumber(Config::$requiredLevelsForExternalLookupTokens[$tn]['level'])) {
                    if (is_array(Config::$requiredLevelsForExternalLookupTokens[$tn]['ftypes'])) {
                        foreach (Config::$requiredLevelsForExternalLookupTokens[$tn]['ftypes'] as $ftype) {
                            if (isset($fieldTypes[$ftype])) {
                                continue 2;
                            }
                        }
                    }

                    unset($tokens[$k]);
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    /**
     * @param string $token the field token we're trying to use
     * @param string $maxCap
     */
    public static function checkTokenAccess($token, $maxCap) {
        if ($maxCap == Auth::L_ADMIN) {
            return true;
        }
        $maxCapNr = Auth::userLevelAsNumber($maxCap);

        // Exclude level s2member_level5 and s2member_level6 users from external lookups
        if ($maxCapNr == 6 || $maxCapNr == 7) {
            return false;
        }

        $tn = ExternalLookup_Base::normalizeTokenName($token);

        if (isset(Config::$requiredLevelsForExternalLookupTokens[$tn]['level'])) {
            if ($maxCapNr >= Auth::userLevelAsNumber(Config::$requiredLevelsForExternalLookupTokens[$tn]['level'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    //--------------------------------------------------------------------------
}
