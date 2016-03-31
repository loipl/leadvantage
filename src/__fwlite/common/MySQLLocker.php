<?php

class MySQLLocker {

    private $lockName = '';

    /**
     * @var DB
     */
    private $db;

    private $acquired = false;

    private static $instanceCount = 0;

    private static $previousLockName = '';


    public function __construct($lockName, $timeout = 10, DB $db = null) {
        if (self::$instanceCount) {
            throw new EFWException("Lock $lockName is cancelling out previous GET_LOCK(), " . self::$previousLockName);
        }
        $this->db = $db ? $db : DB::$db;

        $r = $this->db->getTopLeft("SELECT GET_LOCK(?, ?)", array($lockName, (int)$timeout));
        if ($r !== '1') {
            throw new EServerError("Unable to get a lock");
        }
        $this->lockName = $lockName;
        $this->acquired = true;

        self::$instanceCount++;
        self::$previousLockName = $lockName;
    }
    //--------------------------------------------------------------------------


    public function isLocked() {
        return $this->acquired;
    }
    //--------------------------------------------------------------------------


    public function release() {
        if ($this->acquired) {
            $this->db->query("SELECT RELEASE_LOCK(?)", array($this->lockName));
            self::$instanceCount--;
            self::$previousLockName = '';
            $this->acquired = false;
        }
    }
    //--------------------------------------------------------------------------


    public function __destruct() {
        $this->release();
    }
    //--------------------------------------------------------------------------
}
