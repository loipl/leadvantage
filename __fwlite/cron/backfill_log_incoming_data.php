<?php
require_once dirname(__FILE__) . '/cron.inc';
set_time_limit(108000); // 30 hours

$minIndex = SingletonRegistry::getModelLogIncoming()->getMinIncomingId();
$maxIndex = SingletonRegistry::getModelLogIncoming()->getMaxIncomingId();
// number of records per SQL
$step = 10000;

for ($i = $minIndex; $i < $maxIndex + $step; $i = $i + $step) {
    try {
        $updateResult = SingletonRegistry::getModelLogIncoming()->backfillStandardLeadFields($i, $i + $step);
        echo "Finished updating incoming_id from $i to " . ($i + $step) . "\n";
    } catch(Exception $e) {
        echo "ERROR happened incoming_id from $i to " . ($i + $step) . "\n";
        echo $e->getMessage();
    }
}

echo "FINISH!";