<?php

require_once dirname(__FILE__) . '/cron.inc';

SingletonRegistry::getModelUser()->recreateUserCapCache();
