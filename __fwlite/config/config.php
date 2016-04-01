<?php

defined('FWLITE_INCLUDED') or die('Access Denied');

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

Config::$siteTitle      = 'Lead Vantage';
Config::$checkNoncePost = true;

Config::$useApc         = function_exists('apc_store') && (PHP_SAPI != 'cli');

Config::$smtpHost      = '';
Config::$smtpAuth      = true;
Config::$smtpPassword  = '';
Config::$smtpUsername  = '';
Config::$smtpFromName  = '';
Config::$smtpFromEmail = '';
Config::$smtpSecure    = '';

DB::$db   = new DB('localhost', 'root', '', 'pingtree');
DB::$wpDb = new DB('localhost', 'root', '', 'leadwrench');

Config::$wordpressPath = CFG_ROOT_DIR;
Config::$wordpressUrl  = '/';

Config::$rapLeafApiKey  = '83ea2d2fdf35e1c8cbbc78e056f50798';
Config::$botScoutKey    = 'xVpWZuXPq9LcEgM';

Config::$validatorKeys = array(
    'dv'        => 'SWjbOfI8L3SD9viuDuvM',
    'brite'     => '413ab163-71d7-4a63-8afc-d8fbe92ba801',
    'xverify'   => '1000323-F374262F',
    'leadspend' => 'MQVzOtsf3tUqRhgBkVUbyuCqtabKTUa59omoa6wcBhT'
);


Config::$quotaPercentageWarning = 80;

Config::$useSummaryTablesForDashboard = true;

// This turns on debug redirect in case of SQL error - so you see the error
Config::$debugRedirect  = false;
Config::$logSQL         = false;
Config::$devEnvironment = false;
Config::$timeZone       = 'America/Los_Angeles';

// Config::$errorLogDir    = '/home/httpd/html/leadwrench.com/bogdan/';

Config::$serverIPAddresses = array(
    '199.182.105.226',
    '199.182.105.227'
);
