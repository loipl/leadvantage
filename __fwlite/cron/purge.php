<?php

require_once dirname(__FILE__) . '/cron.inc';

set_time_limit(1798);

$purge = new Helper_Purge(1600);
$purge->run();
