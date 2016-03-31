<?php

class Helper_Archiver {

    /**
     * @desc Delete data from log_incoming and log_delivery (and all tables that have FK to these two
     *  This is done 10K rows at a time until time limit is reached
     *
     * @param int $timeLimit How long the script will run
     */
    public function trimData($timeLimit = 120) {
        try {
            $lock = new MySQLLocker(Config::$lockPrefix . 'cron.' . basename(__FILE__), ($timeLimit + 600));
        } catch (EServerError $e) {
            return;
        }

        $logDelivery = SingletonRegistry::getModelLogDelivery();
        $logDelivery->dailyApiReportSmallPartitionMaintenance(Config::$keepApiReport);
        $logDelivery->deleteSummaryDataOlderThan(Config::$keepDelivery);

        $logIncoming = SingletonRegistry::getModelLogIncoming();

        $incomingDone = false;
        $deliveryDone = false;

        $startTime = time();

        for(;;) {

            $cycleStartTimestamp = microtime(true);

            if (!$incomingDone) {
                $incomingDone = $logIncoming->deleteDataOlderThan(Config::$keepIncoming);
            }

            if (!$deliveryDone) {
                $deliveryDone = $logDelivery->deleteDataOlderThan(Config::$keepDelivery);
            }

            if ((time() > ($startTime + $timeLimit)) || ($deliveryDone && $incomingDone)) {
                break;
            }

            if ((microtime(true) - $cycleStartTimestamp) > 2) {
                sleep(2);
                $cycleStartTimestamp = microtime(true);
            }
        }

        $lock->release();
    }
    //--------------------------------------------------------------------------

}
