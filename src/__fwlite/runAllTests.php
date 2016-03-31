<?php

define ('TEST_START_TIME', microtime(true));

define('RUN_ALL_TESTS', 1);
chdir(dirname(__FILE__) . '/');

$_SERVER['REMOTE_ADDR'] = '85.222.134.142';
$_SERVER['REQUEST_URI'] = '/api/';

if (!isset($skipOtherTests)) {
    $skipOtherTests = false;
}

require_once 'guess-vhost.php';
require_once 'startup.php';
App::init();

SingletonRegistry::getModelEngineConfig()->initializeEngineConfigObject(Engine_Settings::$settings, gethostbyname(gethostname()));
require_once CFG_FWLITE_HOME . 'testing/SetupTestData.php';

if (Config::$codeCoverageOutputFile) {
    xdebug_start_code_coverage();
}

require_once Config::$wordpressPath . 'wp-config.php';
require_once Config::$wordpressPath . 'wp-admin/includes/user.php';
is_user_logged_in();

SetupTestData::preFlight();

// Validation tests slow down running of the script and cost money
// Only activate this if you are working on external validation.
SetupTestData::$runValidationTests = false;

// require_once 'testUtils.php';
// exportTestData();
// die;

if (empty($skipOtherTests)) {
    foreach (Lib::listFiles(CFG_FWLITE_HOME . 'testing/self_check/') as $fileName) {
        if (substr($fileName, -4) == '.php') {
            require_once $fileName;
        }
    }

    foreach (Lib::listFiles(CFG_FWLITE_HOME . 'unit_tests/', true) as $fileName) {
        if (substr($fileName, -4) == '.php') {
            require_once $fileName;
        }
    }
}

echo number_format(microtime(true) - TEST_START_TIME, 4) . "s startup time\n";


