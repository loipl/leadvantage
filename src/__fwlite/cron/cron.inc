<?php

require_once dirname(dirname(__FILE__)) . '/guess-vhost.php';

chdir(dirname(__FILE__) . '/');

// Needed to prevent session_start() in App::init() and undefined index for REQUEST_URI
$_SERVER['REQUEST_URI'] = '/cron/';
$_SERVER['REQUEST_METHOD'] = 'GET';

$_SERVER['SERVER_PORT']    = 80;

define('PINGTREE_STARTED', 1);
require_once dirname(dirname(dirname(__FILE__))) . '/boot.php';


App::init();


