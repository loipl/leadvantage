<?php

abstract class DbCache {

    const CACHE_TTL        = 300;
    const REV_NUMBER_INDEX = 'rev';
    const PAYLOAD_INDEX    = 'data';

    /**
     * @var DbCache
     */
    private static $instance;


    /**
     * @return DbCache
     */
    public static function getCache() {
        if (!self::$instance) {
            self::$instance = new DbCache_Combined();
        }
        return self::$instance;
    }
    //--------------------------------------------------------------------------


    /**
     * @return bool
     */
    abstract public function set($key, array $value);
    //--------------------------------------------------------------------------


    /**
     * @desc returns array or false on error.
     */
    abstract public function get($key);
    //--------------------------------------------------------------------------


    public abstract function getRevNr();
    //--------------------------------------------------------------------------


    abstract public function updateRevNr();
    //--------------------------------------------------------------------------


    public function getWithCallback(CrudModelCaching $modelObject, $functionName, array & $functionArguments) {
        $cacheKey   = get_class($modelObject) . '::' . $functionName . '(' . serialize($functionArguments) . ')';
        $cachedData = $this->get($cacheKey);
        $revNumber  = $this->getRevNr();

        if ($this->isCachedDataInvalid($cachedData, $revNumber)) {
            $cachedData = array(
                self::PAYLOAD_INDEX    => call_user_func_array(array($modelObject, $functionName), $functionArguments),
                self::REV_NUMBER_INDEX => $revNumber
            );
            $this->set($cacheKey, $cachedData);
        }

        return $cachedData[self::PAYLOAD_INDEX];
    }
    //--------------------------------------------------------------------------


    private function isCachedDataInvalid($cachedData, $revNumber) {
        if ($cachedData === false) {
            return true;
        }
        if (!is_array($cachedData)) {
            return true;
        }
        if (!isset($cachedData[self::REV_NUMBER_INDEX])) {
            return true;
        }
        return $cachedData[self::REV_NUMBER_INDEX] < $revNumber;
    }
    //--------------------------------------------------------------------------
}
