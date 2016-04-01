<?php

$checks = array();

$version = version_compare(PHP_VERSION, '5.2.0', '>=');
$success = true;

$ok = $version;
$success  = $success && $ok;
$checks[] = 'PHP Version at least 5.2.0 (' . PHP_VERSION . ') ' . ($version ? 'OK' : 'Fail');

$ok = function_exists('json_encode');
$success  = $success && $ok;
$checks[] = 'json_encode functions present ' . ($ok ? 'OK' : 'Fail');

if (!$success) {
    echo "Sanity checks failed:\n\n" . implode("\n", $checks);
    die;
}

if (defined('USE_DOMAIN_FOR_TESTING')) {
    $_SERVER['HTTP_HOST'] = USE_DOMAIN_FOR_TESTING;
}

require_once dirname(__FILE__) . '/runAllTests.php';

