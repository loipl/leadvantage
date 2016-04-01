<?php

define ('TEST_START_TIME', microtime(true));

define('RUN_ALL_TESTS', 1);
chdir(dirname(__FILE__) . '/');

require_once '../../startup.php';

$testDir = CFG_FWLITE_HOME . 'unit_tests/';
App::$classPaths[] = CFG_FWLITE_HOME . 'testing/';
// App::$classPaths[] = CFG_FWLITE_HOME . 'unit_tests/';

$_SERVER['REMOTE_ADDR'] = '85.222.134.142';
$_SERVER['REQUEST_URI'] = '/api/';

// Guess what machine we're on
if (getenv('BOBAN_DEV_MACHINE')) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.w5.localhost');
} elseif (getenv('DOM_DEV_MACHINE')) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.w5.localhost');
} elseif (!empty($_ENV['staging_environment'])) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'staging.leadwrench.com');
} else {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.leadwrench.com');
}
App::init();

if (!empty(Config::$testDbLw)) {
    $link = mysql_connect(Config::$testDbLw[0], Config::$testDbLw[1], Config::$testDbLw[2]);
    if (!$link) {
        die;
    }
    $db = Config::$testDbLw[3];
    mysql_query("DROP DATABASE IF EXISTS `$db`", $link) or die(mysql_errno($link));
    mysql_query("CREATE DATABASE `$db` DEFAULT CHARACTER SET = utf8", $link) or die(mysql_errno($link));

    $db = new DB(Config::$testDbLw[0], Config::$testDbLw[1], Config::$testDbLw[2], Config::$testDbLw[3]);
}

$arr = DB::$db->getArray1v("SHOW TABLES", array(), MYSQL_NUM);
sort($arr);
$db->query("SET FOREIGN_KEY_CHECKS=0;");
for (;;) {
    foreach ($arr as $index => $tableName) {
        $cr = DB::$db->getTopArray("SHOW CREATE TABLE `$tableName`", array(), MYSQL_NUM);
        $res = $db->query($cr[1]);
        if ($res) {
            unset($arr[$index]);
        }
    }
    if (!$arr) {
        break;
    }
};
$db->query("SET FOREIGN_KEY_CHECKS=1;");

$arr = DB::$db->getArray("SHOW TRIGGERS");
foreach ($arr as $row) {
    $sql = DB::$db->getTopArray("SHOW CREATE TRIGGER `$row[0]`");
    $db->query($sql[2]);
};

/*

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE `api_report_inc`;
TRUNCATE `api_report_small`;
TRUNCATE `email_send_log`;
TRUNCATE `external_lookup_cache`;
TRUNCATE `geoip_ip_cache`;
TRUNCATE `log_api_errors`;
TRUNCATE `log_delivery`;
TRUNCATE `log_delivery_campaigns`;
TRUNCATE `log_incoming`;
TRUNCATE `log_incoming_reposts`;
TRUNCATE `log_incoming_tracking`;
TRUNCATE `log_incoming_values`;
TRUNCATE `notifications`;
TRUNCATE `notifications_keyed`;
TRUNCATE `php_log`;
TRUNCATE `profiles`;
TRUNCATE `profiles_data`;
TRUNCATE `profiles_inferred`;
TRUNCATE `repost_log`;
TRUNCATE `repost_queue`;
TRUNCATE `repost_queue_mem`;
TRUNCATE `repost_threads`;
TRUNCATE `sh_catchup`;
TRUNCATE `sh_delivery`;
TRUNCATE `sh_incoming`;
TRUNCATE `sh_repost`;
TRUNCATE `sql_deadlock_log`;
TRUNCATE `sql_log`;
TRUNCATE `submission_log`;
TRUNCATE `summary_acceptance`;
TRUNCATE `system_messages`;
TRUNCATE `user_rights_cache`;
TRUNCATE `validation_cache`;
TRUNCATE `validation_log`;
TRUNCATE `visitors`;
SET FOREIGN_KEY_CHECKS=1;


SET FOREIGN_KEY_CHECKS=0;
TRUNCATE `campaigns`;
TRUNCATE `campaign_delivery`;
TRUNCATE `campaign_fields`;
TRUNCATE `campaign_fields_shadow_validation`;
TRUNCATE `campaign_oob_ranges`;
TRUNCATE `campaign_settings`;
TRUNCATE `partners`;
TRUNCATE `partner_fields`;
TRUNCATE `partner_filters`;
TRUNCATE `partner_settings`;
SET FOREIGN_KEY_CHECKS=1;

 */
