<?php


define('NO_WORDPRESS', 1);
require_once dirname(__FILE__) . '/cron.inc';

$startTime = time();

DB::$db->addListener('Helper_Repost::sqlErrorListener', true);
SingletonRegistry::getModelEngineConfig()->initializeEngineConfigObject(Engine_Settings::$settings, gethostbyname(gethostname()));

$mli = SingletonRegistry::getModelLogIncoming();
$mli->deleteDeadCatchupJobs();

if (Helper_Repost::$mysqlServerLinkGone) {
    return;
}


for (;;) {
    if ((time() - $startTime) >= Engine_Settings::$settings->cronTimeout) {
        break;
    }
    if (Helper_Repost::$mysqlServerLinkGone) {
        if (Config::$logDbConnectivityIssuesToFileName) {
            $fp = fopen(Config::$logDbConnectivityIssuesToFileName, 'ab');
            if ($fp) {
                flock($fp, LOCK_EX);
                $logLine = date('Y-m-d H:i:s') . " - Database connection lost\n";
                fwrite($fp, $logLine);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
        break;
    }
    $helper = new Helper_Repost();
    $helper->doRepost($startTime, Engine_Settings::$settings->oneRepostBatchSize);
    SingletonRegistry::getModelEngineConfig()->initializeEngineConfigObject(Engine_Settings::$settings, gethostbyname(gethostname()));
}
