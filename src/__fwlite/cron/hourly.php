<?php

require_once dirname(__FILE__) . '/cron.inc';

set_time_limit(3600);

$mli = SingletonRegistry::getModelLogIncoming();

$repostRows = $mli->listMissingRepostRecords();

foreach ($repostRows AS $row) {

    $sql = "SELECT repost_hours,should_repost,repost_max FROM `campaigns` WHERE `id` = ? LIMIT 1";

    $campaignData = DB::$db->query($sql,array($row['campaign_id']));

    $delayHours  = $campaignData['repost_hours'];
    $repostCount = $campaignData['should_repost'] ? $campaignData['repost_max'] : 0;

    $mli->insertIntoRepostQueue($row['id'], $delayHours, $repostCount, $row['campaign_id'], $row['user_id'], true);

    unset($campaignData);
}

$mli->deleteOldDataFromSummaryAcceptance();

$mailer = new Helper_MailWarning();
$mailer->sendTrafficDropWarning(1, 20000);
