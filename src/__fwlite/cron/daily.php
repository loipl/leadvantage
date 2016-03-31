<?php

require_once dirname(__FILE__) . '/cron.inc';

set_time_limit(10800); // 3 hours

SingletonRegistry::getSingleInstance('Model_ExternalLookupCache')->flushRapleafCacheEntries();
SingletonRegistry::getSingleInstance('Model_ExternalLookupCache')->flushCacheEntries();
SingletonRegistry::getSingleInstance('Model_GeoipLocation')->flushCacheEntries();

$mailer = new Helper_MailWarning();
$mailer->sendWarningsToUsersOver();

/* @var $mvc Model_ValidationCache */
$mvc = SingletonRegistry::getSingleInstance('Model_ValidationCache');
$mvc->deleteOldData(Config::$validCacheHrs);

//$arh = new Helper_Archiver();
//$arh->trimData(7200);
