<?php

// This has to be done from wget
if (PHP_SAPI == 'cli') {
    die;
}

require_once dirname(__FILE__) . '/__fwlite/startup.php';
App::init();

DbCache::getCache()->updateRevNr();
