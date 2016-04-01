<?php

class DbCache_Combined extends DbCache {

    const TIMESTAMP_INDEX  = 'mts';


    public function set($key, array $data) {
        if (PHP_SAPI != 'cli') {
            apc_store(__CLASS__ . '/' . $key, $data, self::CACHE_TTL);
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------


    public function get($key) {
        $foundInCache = false;
        $cachedData = apc_fetch(__CLASS__ . '/' . $key, $foundInCache);
        return $foundInCache ? $cachedData : false;
    }
    //--------------------------------------------------------------------------


    public function getRevNr() {
        $cachedRevNumber = apc_fetch(__CLASS__ . '/!rev_nr', $foundInCache);

        if (!$foundInCache) {
            return $this->updateRevNr();
        }

        // Cronjob for updating rev nr should be set to 1 seconds. This here is fallback.
        // In case rev nr was updated more than two seconds ago then we update it here.
        if (($cachedRevNumber[self::TIMESTAMP_INDEX] + 2) < microtime(true)) {
            return $this->updateRevNr();
        }

        return $cachedRevNumber[self::REV_NUMBER_INDEX];
    }
    //--------------------------------------------------------------------------


    public function updateRevNr() {
        $revNrFromDb = DB::$db->getTopLeftInt("SELECT `rev_nr` FROM `rev_tracking`");
        apc_store(__CLASS__ . '/!rev_nr', array(self::REV_NUMBER_INDEX => $revNrFromDb, self::TIMESTAMP_INDEX => microtime(true)), self::CACHE_TTL);
        return $revNrFromDb;
    }
    //--------------------------------------------------------------------------
}
