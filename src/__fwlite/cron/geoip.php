<?php

require_once dirname(__FILE__) . '/cron.inc';

$c = new Helper_GeoipUpdater();
$c->run();
