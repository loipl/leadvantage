<?php

require_once dirname(__FILE__) . '/cron.inc';

$startTime = time();
$startTime -= ($startTime % 60);
try {
    $lock = new MySQLLocker(Config::$lockPrefix . 'cron.' . basename(__FILE__), 10);
} catch (EServerError $e) {
    return;
}

$ts = microtime(true);
$updateInterval = 1.0;

for(;;) {
    $ch = curl_init($_SERVER["HTTP_HOST"] . '/updateRevNr.php');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $result = curl_exec($ch);
    curl_close($ch);

    while (microtime(true) < ($ts + $updateInterval)) {
        usleep(50000);
    }
    $ts = $ts + $updateInterval;

    $t = time();
    $t -= ($t % 60);
    if ($t > $startTime) {
        break;
    }
}
$lock->release();
