<?php die();?>
# task 432, 2012-11-26 21:20 CEST

ALTER TABLE `catchup_jobs` ADD `start_count` INT NOT NULL DEFAULT 0 AFTER `partner_id`;


# task #455, 2012-12-07 13:00 CEST

CREATE TABLE IF NOT EXISTS `log_delivery_campaigns` (
  `delivery_id` bigint(20) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`delivery_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `log_delivery_campaigns`
  ADD CONSTRAINT `log_delivery_campaigns_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;


# task #342, 2012-12-09 17:30 CEST
ALTER TABLE `campaigns` CHANGE `user_id` `user_id` INT( 10 ) UNSIGNED NULL;


# task #451, 2012-12-14 23:55 CEST

CREATE TABLE IF NOT EXISTS `campaign_fields_shadow_validation` (
  `shadow_id` int(10) unsigned NOT NULL,
  `field_id` int(10) unsigned NOT NULL,
  `validate` varchar(40) NOT NULL,
  PRIMARY KEY (`shadow_id`,`field_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `campaign_fields_shadow_validation`
  ADD CONSTRAINT `campaign_fields_shadow_validation_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `campaign_fields` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_fields_shadow_validation_ibfk_1` FOREIGN KEY (`shadow_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;


# task #379, 2012-12-20 20:30 CEST

ALTER TABLE `repost_queue` ADD `is_ignored` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `catchup_id`;
ALTER TABLE `pingtree`.`repost_queue` ADD INDEX (`user_id` , `is_ignored` , `repost_at`);

CREATE TABLE IF NOT EXISTS `catchup_send_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `process_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`,`process_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `catchup_send_log`
  ADD CONSTRAINT `catchup_send_log_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `catchup_jobs` (`id`) ON DELETE CASCADE;

ALTER TABLE `pingtree`.`repost_queue` DROP INDEX `catchup_id` ,
  ADD INDEX `catchup_id` (`catchup_id` , `is_ignored` , `repost_at`);

# task #419, 2012-12-24 15:00 CEST
ALTER TABLE `campaigns` ADD `throttle_type` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `oob_cutoff_time` ,
  ADD `throttle_value` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `throttle_type`;

CREATE TABLE IF NOT EXISTS `sh_repost` (
  `campaign_id` int(10) unsigned NOT NULL,
  `repost_hour` datetime NOT NULL,
  `repost_count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`repost_hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `sh_repost`
  ADD CONSTRAINT `sh_repost_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

#task #430, 2012-12-30 18:40 CEST

ALTER TABLE `partners` ADD `create_from_tpl_id` INT UNSIGNED NULL DEFAULT NULL AFTER `name`, ADD INDEX (`create_from_tpl_id`);

ALTER TABLE `partners` ADD FOREIGN KEY (`create_from_tpl_id`) REFERENCES `pingtree`.`template_partners` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

ALTER TABLE `notifications` ADD `should_escape` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `content`;

# task #430, 2012-01-012 21:00 CEST
ALTER TABLE `system_messages` ADD `user_id` INT UNSIGNED NULL DEFAULT NULL AFTER `msg_time`,
  ADD INDEX (`user_id`),
  ADD FOREIGN KEY ( `user_id` ) REFERENCES `pingtree`.`users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `system_messages` ADD `should_escape` ENUM('0', '1') NOT NULL DEFAULT '1';

# task #469, 2013-01-09 22:00 CEST

CREATE TABLE `validation_log` (
  `month_nr` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `source_id` int(10) unsigned NOT NULL,
  `success_count` int(10) unsigned NOT NULL DEFAULT '0',
  `failure_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`month_nr`,`campaign_id`,`source_id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `validation_log_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# task #369, 2013-01-11 17:30 CEST
# Triggers for relevant DB tables updating rev_tracking, adding here for completeness

DROP TRIGGER IF EXISTS `trig_admin_tips_insert`;

delimiter |

CREATE TRIGGER `trig_admin_tips_insert` AFTER insert ON `admin_tips`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_admin_tips_update`;

delimiter |

CREATE TRIGGER `trig_admin_tips_update` AFTER update ON `admin_tips`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_admin_tips_delete`;

delimiter |

CREATE TRIGGER `trig_admin_tips_delete` AFTER delete ON `admin_tips`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaigns_insert`;

delimiter |

CREATE TRIGGER `trig_campaigns_insert` AFTER insert ON `campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaigns_update`;

delimiter |

CREATE TRIGGER `trig_campaigns_update` AFTER update ON `campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaigns_delete`;

delimiter |

CREATE TRIGGER `trig_campaigns_delete` AFTER delete ON `campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_delivery_insert`;

delimiter |

CREATE TRIGGER `trig_campaign_delivery_insert` AFTER insert ON `campaign_delivery`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_delivery_update`;

delimiter |

CREATE TRIGGER `trig_campaign_delivery_update` AFTER update ON `campaign_delivery`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_delivery_delete`;

delimiter |

CREATE TRIGGER `trig_campaign_delivery_delete` AFTER delete ON `campaign_delivery`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_insert`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_insert` AFTER insert ON `campaign_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_update`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_update` AFTER update ON `campaign_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_delete`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_delete` AFTER delete ON `campaign_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_shadow_validation_insert`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_shadow_validation_insert` AFTER insert ON `campaign_fields_shadow_validation`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_shadow_validation_update`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_shadow_validation_update` AFTER update ON `campaign_fields_shadow_validation`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_fields_shadow_validation_delete`;

delimiter |

CREATE TRIGGER `trig_campaign_fields_shadow_validation_delete` AFTER delete ON `campaign_fields_shadow_validation`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_oob_ranges_insert`;

delimiter |

CREATE TRIGGER `trig_campaign_oob_ranges_insert` AFTER insert ON `campaign_oob_ranges`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_oob_ranges_update`;

delimiter |

CREATE TRIGGER `trig_campaign_oob_ranges_update` AFTER update ON `campaign_oob_ranges`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_oob_ranges_delete`;

delimiter |

CREATE TRIGGER `trig_campaign_oob_ranges_delete` AFTER delete ON `campaign_oob_ranges`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_settings_insert`;

delimiter |

CREATE TRIGGER `trig_campaign_settings_insert` AFTER insert ON `campaign_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_settings_update`;

delimiter |

CREATE TRIGGER `trig_campaign_settings_update` AFTER update ON `campaign_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_campaign_settings_delete`;

delimiter |

CREATE TRIGGER `trig_campaign_settings_delete` AFTER delete ON `campaign_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_countries_insert`;

delimiter |

CREATE TRIGGER `trig_countries_insert` AFTER insert ON `countries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_countries_update`;

delimiter |

CREATE TRIGGER `trig_countries_update` AFTER update ON `countries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_countries_delete`;

delimiter |

CREATE TRIGGER `trig_countries_delete` AFTER delete ON `countries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_config_posts_per_level_insert`;

delimiter |

CREATE TRIGGER `trig_config_posts_per_level_insert` AFTER insert ON `config_posts_per_level`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_config_posts_per_level_update`;

delimiter |

CREATE TRIGGER `trig_config_posts_per_level_update` AFTER update ON `config_posts_per_level`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_config_posts_per_level_delete`;

delimiter |

CREATE TRIGGER `trig_config_posts_per_level_delete` AFTER delete ON `config_posts_per_level`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_insert`;

delimiter |

CREATE TRIGGER `trig_field_types_insert` AFTER insert ON `field_types`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_update`;

delimiter |

CREATE TRIGGER `trig_field_types_update` AFTER update ON `field_types`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_delete`;

delimiter |

CREATE TRIGGER `trig_field_types_delete` AFTER delete ON `field_types`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_industries_insert`;

delimiter |

CREATE TRIGGER `trig_field_types_industries_insert` AFTER insert ON `field_types_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_industries_update`;

delimiter |

CREATE TRIGGER `trig_field_types_industries_update` AFTER update ON `field_types_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_field_types_industries_delete`;

delimiter |

CREATE TRIGGER `trig_field_types_industries_delete` AFTER delete ON `field_types_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_industries_insert`;

delimiter |

CREATE TRIGGER `trig_industries_insert` AFTER insert ON `industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_industries_update`;

delimiter |

CREATE TRIGGER `trig_industries_update` AFTER update ON `industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_industries_delete`;

delimiter |

CREATE TRIGGER `trig_industries_delete` AFTER delete ON `industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partners_insert`;

delimiter |

CREATE TRIGGER `trig_partners_insert` AFTER insert ON `partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partners_update`;

delimiter |

CREATE TRIGGER `trig_partners_update` AFTER update ON `partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partners_delete`;

delimiter |

CREATE TRIGGER `trig_partners_delete` AFTER delete ON `partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_fields_insert`;

delimiter |

CREATE TRIGGER `trig_partner_fields_insert` AFTER insert ON `partner_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_fields_update`;

delimiter |

CREATE TRIGGER `trig_partner_fields_update` AFTER update ON `partner_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_fields_delete`;

delimiter |

CREATE TRIGGER `trig_partner_fields_delete` AFTER delete ON `partner_fields`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_filters_insert`;

delimiter |

CREATE TRIGGER `trig_partner_filters_insert` AFTER insert ON `partner_filters`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_filters_update`;

delimiter |

CREATE TRIGGER `trig_partner_filters_update` AFTER update ON `partner_filters`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_filters_delete`;

delimiter |

CREATE TRIGGER `trig_partner_filters_delete` AFTER delete ON `partner_filters`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_settings_insert`;

delimiter |

CREATE TRIGGER `trig_partner_settings_insert` AFTER insert ON `partner_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_settings_update`;

delimiter |

CREATE TRIGGER `trig_partner_settings_update` AFTER update ON `partner_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_partner_settings_delete`;

delimiter |

CREATE TRIGGER `trig_partner_settings_delete` AFTER delete ON `partner_settings`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_campaigns_insert`;

delimiter |

CREATE TRIGGER `trig_template_campaigns_insert` AFTER insert ON `template_campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_campaigns_update`;

delimiter |

CREATE TRIGGER `trig_template_campaigns_update` AFTER update ON `template_campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_campaigns_delete`;

delimiter |

CREATE TRIGGER `trig_template_campaigns_delete` AFTER delete ON `template_campaigns`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_partners_insert`;

delimiter |

CREATE TRIGGER `trig_template_partners_insert` AFTER insert ON `template_partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_partners_update`;

delimiter |

CREATE TRIGGER `trig_template_partners_update` AFTER update ON `template_partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_template_partners_delete`;

delimiter |

CREATE TRIGGER `trig_template_partners_delete` AFTER delete ON `template_partners`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplc_industries_insert`;

delimiter |

CREATE TRIGGER `trig_tplc_industries_insert` AFTER insert ON `tplc_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplc_industries_update`;

delimiter |

CREATE TRIGGER `trig_tplc_industries_update` AFTER update ON `tplc_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplc_industries_delete`;

delimiter |

CREATE TRIGGER `trig_tplc_industries_delete` AFTER delete ON `tplc_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplp_industries_insert`;

delimiter |

CREATE TRIGGER `trig_tplp_industries_insert` AFTER insert ON `tplp_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplp_industries_update`;

delimiter |

CREATE TRIGGER `trig_tplp_industries_update` AFTER update ON `tplp_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_tplp_industries_delete`;

delimiter |

CREATE TRIGGER `trig_tplp_industries_delete` AFTER delete ON `tplp_industries`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;

# task 462, 2012-01-11 18:10 CEST

CREATE TABLE IF NOT EXISTS `sh_catchup` (
  `catchup_id` int(10) unsigned NOT NULL,
  `repost_hour` datetime NOT NULL,
  `catchup_count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`catchup_id`,`repost_hour`),
  CONSTRAINT `sh_catchup_ibfk_1` FOREIGN KEY (`catchup_id`) REFERENCES `catchup_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


#task 462, 2012-01-11 21:45 CEST

CREATE TABLE IF NOT EXISTS `sql_deadlock_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `req_time` datetime NOT NULL,
  `query` text NOT NULL,
  `error` text NOT NULL,
  `call_stack` text NOT NULL,
  `tracking_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `sql_log` ADD `error_nr` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `error`;
ALTER TABLE `sql_deadlock_log` ADD `error_nr` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `error`;

# task 473, 2012-01-16 19:30 CEST

ALTER TABLE `campaigns` ADD `is_oob_capped` ENUM('0', '1') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '0' AFTER `throttle_value`,
  ADD `oob_cap_type` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `is_oob_capped`,
  ADD `oob_cap_value` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `oob_cap_type`;

ALTER TABLE `sh_incoming` ADD `snub_count` INT UNSIGNED NOT NULL DEFAULT '0';

# task 482, 2013-01-24 12:50 CEST
ALTER TABLE `users` CHANGE `time_zone` `time_zone` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'America/New_York';

#task 485, 2013-01-28 10:30 CEST

DROP TABLE IF EXISTS `repost_threads`;
CREATE TABLE IF NOT EXISTS `repost_threads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `machine_ip` char(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `start_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `repost_log`;
CREATE TABLE IF NOT EXISTS `repost_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int(10) unsigned NOT NULL,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `incoming_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `attempt_type` tinyint(4) NOT NULL,
  `del_time` datetime NOT NULL,
  `result` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id` (`thread_id`),
  KEY `incoming_id` (`incoming_id`,`partner_id`),
  KEY `incoming_id_2` (`incoming_id`,`campaign_id`,`partner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


#task 472, 2013-01-31 16:00 CEST

ALTER TABLE `field_types` ADD `description` VARCHAR(255) NOT NULL DEFAULT '';

#task 489, 2013-02-04 16:00 CEST
ALTER TABLE `repost_threads` ADD `activity_log` TEXT NULL DEFAULT NULL;

#task 489, 2012:02-05 10:20 CEST
ALTER TABLE `repost_threads` ADD `end_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `start_time`;

#task 496, 2013-02-06 15:40 CEST
CREATE TABLE `repost_queue_mem` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `incoming_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `catchup_id` int(10) unsigned DEFAULT NULL,
  `repost_at` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `is_taken` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `incoming_id` (`incoming_id`,`campaign_id`,`catchup_id`),
  KEY `repost_at` (`repost_at`),
  KEY `catchup_id` (`catchup_id`,`repost_at`),
  KEY `campaign_id` (`campaign_id`,`catchup_id`),
  KEY `is_taken` (`is_taken`,`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

#task 502, 2013-02-19 17:20
ALTER TABLE `campaigns` CHANGE `success_url` `success_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `campaigns` CHANGE `failure_url` `failure_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;


#task 489, Repost Control Panel. 2013-03-05 15:10 CEST

CREATE TABLE IF NOT EXISTS `repost_config` (
  `ip` char(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`ip`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


#task 509, validation daily logging. 2013-03-05 17:30 CEST

CREATE TABLE IF NOT EXISTS `validation_log_daily` (
  `day_nr` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned NOT NULL,
  `source_id` int(10) unsigned NOT NULL,
  `success_count` int(10) unsigned NOT NULL DEFAULT '0',
  `failure_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`day_nr`,`campaign_id`,`source_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `validation_log_daily`
  ADD CONSTRAINT `validation_log_daily_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;


#task 506, Separate timeout errors from other network errors on Reporting tab, 2013-03-05 23:00 CEST

CREATE TABLE IF NOT EXISTS `log_delivery_timeouts` (
  `id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `log_delivery_timeouts`
  ADD CONSTRAINT `log_delivery_timeouts_ibfk_1` FOREIGN KEY (`id`) REFERENCES `log_delivery` (`id`) ON DELETE CASCADE;


#task 510, 2013-03-06 19:30 CEST

CREATE TABLE IF NOT EXISTS `log_delivery_templates_sent` (
  `id` bigint(20) unsigned NOT NULL,
  `sent` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `log_delivery_templates_sent`
  ADD CONSTRAINT `log_delivery_templates_sent_ibfk_1` FOREIGN KEY (`id`) REFERENCES `log_delivery` (`id`) ON DELETE CASCADE;

#task 512, Engine Control Panel. 2013-03-07 19:15 CEST

DROP TRIGGER IF EXISTS `trig_repost_config_insert`;

delimiter |

CREATE TRIGGER `trig_repost_config_insert` AFTER insert ON `repost_config`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_repost_config_update`;

delimiter |

CREATE TRIGGER `trig_repost_config_update` AFTER update ON `repost_config`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;


DROP TRIGGER IF EXISTS `trig_repost_config_delete`;

delimiter |

CREATE TRIGGER `trig_repost_config_delete` AFTER delete ON `repost_config`
FOR EACH ROW BEGIN
   UPDATE `rev_tracking` SET `rev_nr` = `rev_nr` + 1;
END;
|

delimiter ;



#task 479, Partner average response times 2013-03-13 22:30 CEST

CREATE TABLE IF NOT EXISTS `sh_response_times` (
  `day` datetime NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `response_count` int(10) unsigned NOT NULL DEFAULT '0',
  `time_combined` decimal(14,4) unsigned NOT NULL DEFAULT '0.0000',
  `timeout_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`day`,`partner_id`),
  KEY `partner_id` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `sh_response_times`
  ADD CONSTRAINT `sh_response_times_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE;


#task 426, Postback URL and Conversion Tracking 2013 03 15 15:20 CEST

CREATE TABLE IF NOT EXISTS `conversions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `incoming_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `conversion_time` datetime NOT NULL,
  `type` char(64) CHARACTER SET ascii NOT NULL,
  `value` decimal(14,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  KEY `incoming_id` (`incoming_id`,`partner_id`),
  KEY `partner_id` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `conversions`
  ADD CONSTRAINT `conversions_ibfk_1` FOREIGN KEY (`incoming_id`) REFERENCES `log_incoming` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversions_ibfk_2` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE;


#task 489, Engine Control Panel 2013 03 18 19:00 CEST

CREATE TABLE IF NOT EXISTS `nodes` (
  `ip_address` char(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `nodes` (`ip_address`, `name`) VALUES
    ('', 'Default Value');

#task 500, Reporting 2013-03-18 20:50 CEST

CREATE TABLE IF NOT EXISTS `log_incoming_np_errors` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `log_incoming_np_errors`
  ADD CONSTRAINT `log_incoming_np_errors_ibfk_1` FOREIGN KEY (`id`) REFERENCES `log_incoming` (`id`) ON DELETE CASCADE;


#task 527, checkbocx for field types to force to UPPER CASE, 2013-03-20 15:50 CEST

ALTER TABLE `field_types` ADD `force_uppercase` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `validation_data`;

#task 531, Make email/phone validation option on member level admin. 2013-04-12 15:20 CEST

ALTER TABLE `config_posts_per_level` ADD `can_validate_email` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
  ADD `can_validate_phone` ENUM( '0', '1' ) NOT NULL DEFAULT '0';


#task 514, master campaigns

ALTER TABLE `campaigns` ADD `is_master` ENUM( '0', '1' ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '0' AFTER `is_active`;

ALTER TABLE `partners`
  ADD `wrapped_campaign_id` INT UNSIGNED NULL DEFAULT NULL AFTER `created_at`,
  ADD INDEX (`wrapped_campaign_id`);

ALTER TABLE `partners`
  ADD FOREIGN KEY (`wrapped_campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `partners`
  DROP INDEX `user_id`,
  ADD UNIQUE `user_id` (`user_id`, `wrapped_campaign_id`, `name`);
  
# update for test script: TestXmlAndJsonDelivery/ testJSONDelivery, testJSONDeliveryWithQuotes, testJSONDeliveryWithQuotesTwoValues
INSERT INTO `pingtree`.`campaigns` (`id`, `user_id`, `name`, `success_url`, `failure_url`, `shadow_of`, `shadow_delay_hours`, `industry_id`, `skim_percentage`, `is_oob`, `oob_cutoff_time`, `throttle_type`, `throttle_value`, `is_oob_capped`, `oob_cap_type`, `oob_cap_value`, `should_repost`, `repost_hours`, `repost_max`, `leads_per`, `partner_retries`, `capalp`, `stdw`, `append_succ_url`, `email_field_name`, `email_field_description`, `email_field_verify`, `is_active`, `created_at`, `notes`) VALUES ('41', '2', 'UTC35', 'http://www.success.com', 'http://www.failure.com', NULL, '0', NULL, '10', '0', '2013-01-18 22:36:38', '0', '0', '0', '1', '0', '0', '1', '2', '1', '2', '0', '0', '0', 'email', 'Email Address', '1', '1', '2013-01-18 22:36:38', '');
INSERT INTO `pingtree`.`partners` (`id`, `user_id`, `created_at`, `name`, `create_from_tpl_id`, `posting_type`, `success_keyword`, `failure_keyword`, `delivery_addr`, `delivery_cap`, `delivery_ctype`, `parse_response`, `delimiter`, `success_url`, `price_per_lead`, `should_retry`, `username_failed_keyword`, `template`) VALUES ('54', '2', '2013-01-18 22:38:18', 'C35-P1', NULL, '6', 'success', 'FAIL:', 'http://w1.localhost/xml.php', '0', '1', '0', 'x', 'http://w1.localhost/', '0', '0', '', '');
INSERT INTO `pingtree`.`campaign_delivery` (`campaign_id`, `partner_id`, `order_nr`, `always_send`) VALUES ('41', '54', '1', '0');
INSERT INTO `pingtree`.`partner_fields` (`partner_id`, `name`, `value`, `modifier`) VALUES ('54', 'f1', '[Email]', '');
INSERT INTO `pingtree`.`partner_fields` (`partner_id`, `name`, `value`, `modifier`) VALUES ('54', 'f2', '[First_Name]', '');
INSERT INTO `pingtree`.`campaign_fields` (`campaign_id`, `name`, `description`, `field_type`, `is_mandatory`, `validate`) VALUES ('41', 'first_name', '', '7', '0', '');

#update for test script:  TestXmlAndJsonDelivery/ testBirthdayFields
INSERT INTO `pingtree`.`partners` (`id`, `user_id`, `created_at`, `name`, `create_from_tpl_id`, `posting_type`, `success_keyword`, `failure_keyword`, `delivery_addr`, `delivery_cap`, `delivery_ctype`, `parse_response`, `delimiter`, `success_url`, `price_per_lead`, `should_retry`, `username_failed_keyword`, `template`) VALUES ('56', '2', '2012-05-08 00:01:42', 'C16-P2', NULL, '5', 'success', 'ERROR', 'http://w1.localhost/xml.php', '0', '1', '1', '|', '$2', '0', '0', '', 'testing with %f1% and %f2%');
INSERT INTO `pingtree`.`partner_fields` (`id`, `partner_id`, `name`, `value`, `modifier`) VALUES ('1145', '56', 'birthday_year', '[:birth_year:]', NULL);

#update for test script: TestCountryCodeCapitalization.php & TestStateCodeCapitalization.php
UPDATE `field_types` SET `force_uppercase` = '1' WHERE `name` IN ('Country_Code', 'State_Code');

#update for test script: TestChildCampaignDelivery.php
INSERT INTO `pingtree`.`campaigns` (`id`, `user_id`, `name`, `success_url`, `failure_url`, `shadow_of`, `shadow_delay_hours`, `industry_id`, `skim_percentage`, `is_oob`, `oob_cutoff_time`, `throttle_type`, `throttle_value`, `is_oob_capped`, `oob_cap_type`, `oob_cap_value`, `should_repost`, `repost_hours`, `repost_max`, `leads_per`, `partner_retries`, `capalp`, `stdw`, `append_succ_url`, `email_field_name`, `email_field_description`, `email_field_verify`, `is_active`, `created_at`, `notes`) VALUES ('43', '2', 'UTC37 Master', 'http://www.success.com', 'http://www.failure.com', NULL, '0', NULL, '10', '0', '2013-01-18 22:36:38', '0', '0', '0', '1', '0', '0', '1', '2', '1', '2', '0', '0', '0', 'email', 'Email Address', '1', '1', '2013-01-18 22:36:38', '');

#add `curl_timeout` for partner
ALTER TABLE `partners` ADD `curl_timeout` int(10) DEFAULT NULL;

#add `curl_timeout` tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-05-20 16:57:42', '2014-05-21 00:57:42', 'Partner Timeout is the maximum waiting time when sending a lead to a partner.', 'What is the \"Partner Timeout\"?', '', 'publish', 'closed', 'closed', '', 'what-is-partner-timeout', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add field_types mobile
INSERT INTO `pingtree`.`field_types` (`id`, `name`, `validation_type`, `validation_data`, `force_uppercase`, `description`) VALUES ('166', 'Mobile_Lead', '2', '/^(Y|N|y|n)$/', '1', 'Using mobile device or not');

#lengthen Partner rule criteria (match_value) field
ALTER TABLE `partner_filters` MODIFY `match_value` mediumtext;

# add partner_timezone for partner - 2014/08/19
ALTER TABLE `pingtree`.`partners` ADD `partner_timezone` varchar(50) DEFAULT NULL;

#add `timezone_seting` tooltip - 2014/08/19
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Override default system timezone', 'What is the \"Partner Timezone\"?', '', 'publish', 'closed', 'closed', '', 'what-is-partner-timezone', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add `round_robin` into `campaigns` table - 2014/10/22
ALTER TABLE `campaigns` ADD `round_robin` enum('0','1') NOT NULL DEFAULT '0';
ALTER TABLE `campaigns` ADD `round_robin_last_index` int(7) DEFAULT NULL;

#add `round_robin` tooltip - 2014/10/22
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Round-Robin campaign delivery order based on the context of the last time a lead was processed in that campaign. ', 'What is the \"Round-Robin\"?', '', 'publish', 'closed', 'closed', '', 'what-is-round-robin', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add delivery_type & response_type to campaign - 2014/10/27
ALTER TABLE `campaigns` ADD `delivery_type` enum('1','2') NOT NULL DEFAULT '1';
ALTER TABLE `campaigns` ADD `response_type` enum('1','2') NOT NULL DEFAULT '1';
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Live: real-time processing; Queued: queued then repost', 'What is the \"Delivery-type\"?', '', 'publish', 'closed', 'closed', '', 'what-is-delivery-type', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Redirect: based on campaign or partner redirect_URL; API: text response', 'What is the \"Response-type\"?', '', 'publish', 'closed', 'closed', '', 'what-is-response-type', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#convert 'oob' setting into delivery_type & response_type settings
UPDATE `campaigns` set `delivery_type` = '2', `response_type` = '2' WHERE `is_oob` = '1';

# add partner response type 2014/05/11
ALTER TABLE partners ADD `response_type` INT(1) NOT NULL DEFAULT '0' AFTER `parse_response`;
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Partner response type setting: Text or Json or XML', 'What is the \"Partner-Response-type\"?', '', 'publish', 'closed', 'closed', '', 'what-is-partner-response-type', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# fix notice in #654
UPDATE `campaigns` SET `delivery_type` = '1' WHERE `delivery_type` NOT IN ('1', '2');
UPDATE `campaigns` SET `response_type` = '1' WHERE `response_type` NOT IN ('1', '2');

# update partners.price_per_lead. Use to store xpath and jsonpath.
ALTER TABLE partners MODIFY `price_per_lead` VARCHAR(50);

# add `is_pingpost` to `campaigns` table
ALTER TABLE `campaigns` ADD `is_pingpost` INT(1) NOT NULL DEFAULT '0' AFTER `is_master`;

# add `auction` to `campaigns` table
ALTER TABLE `campaigns` ADD `auction` INT(1) NOT NULL DEFAULT '0' AFTER `round_robin_last_index`;

#add campaign distribution tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Round-Robin campaign delivery order based on the context of the last time a lead was processed in that campaign. In Auction mode, Ping request will be sent to partner then the lead will be sent to hightest bid partner', 'What is the \"Campaign-Distribution\"?', '', 'publish', 'closed', 'closed', '', 'what-is-campaign-distribution', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# add ping post column for partner.
ALTER TABLE `partners` 
ADD `partner_type` int(1) DEFAULT 0 AFTER `delivery_ctype`,
ADD `ping_post_type` int(1) DEFAULT 0 AFTER `partner_type`,
ADD `post_partner_id` int(8) DEFAULT NULL AFTER `ping_post_type`

# add parter type tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'If parnter is Post-only type, lead will be delivered to it directly. If a partner is Ping-post type, it will be used in auction: The partner which pays higher price will receive the lead', 'What is the \"Partner-Type\"?', '', 'publish', 'closed', 'closed', '', 'what-is-partner-type', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# add auction transaction id for partner 2014/11/16
ALTER TABLE `partners` ADD `auction_transaction_id` VARCHAR(50) DEFAULT NULL;
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Auction transaction id is an unique string generated by partner and sent to pingtree in ping process together with price. Pingtree will parse this string and send to partner in post process together with lead\'s full info', 'What is the \"Auction-Transaction-Id\"?', '', 'publish', 'closed', 'closed', '', 'what-is-auction-transaction-id', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# add auction table 2014/11/18
CREATE TABLE `log_ping` (
    `id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT (10) UNSIGNED NOT NULL,
    `partner_id` INT (10) UNSIGNED NOT NULL,
    `price` VARCHAR (10) DEFAULT NULL,
    `transaction_id` VARCHAR (20) DEFAULT NULL,
    `partner_response` text NOT NULL,
    `type` INT(1) UNSIGNED NOT NULL DEFAULT '0',
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = INNODB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8;

# add Bid price tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Bid price is the predetermined or expected bid price expected to be received for every lead accepted by this partner\'s API in Ping transaction. It is optional.', 'What is the \"Bid-Price\"?', '', 'publish', 'closed', 'closed', '', 'what-is-bid-price', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add `what-is-auction` tooltip - 2014/12/03
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-12-03 16:57:42', '2014-12-04 00:57:42', 'Auction campaign will send ping request to all partners. The full lead\'s information will then be sent to partner with highest bid price.', 'What is the \"Auction\"?', '', 'publish', 'closed', 'closed', '', 'what-is-auction', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# add incoming_id to log_ping - 2014/12/08
ALTER TABLE `log_ping` ADD `incoming_id` INT(10) DEFAULT NULL AFTER `id`;

# add new campaign type ping/postage 2014/12/15
ALTER TABLE `campaigns` ADD `is_ping_and_post` INT(1) NOT NULL DEFAULT '0' AFTER `is_pingpost`;

#add Lead_ID to campaign fielded 2014/12/15
INSERT INTO `field_types` (`id`, `name`, `validation_type`, `validation_data`, `force_uppercase`, `description`) VALUES ('167', 'Lead_ID', '0', '', '0', 'ID of the lead that is submited to Pingtree (incoming_id)');

#add is_accepted to log_ping 2014/12/17
ALTER TABLE `log_ping` ADD `is_accepted` ENUM('0', '1') NOT NULL DEFAULT '1' AFTER `partner_id`;

# add new bid_response 2014/12/20
ALTER TABLE `campaigns` 
    ADD `bid_response_type` INT(1) DEFAULT NULL,
    ADD `bid_response_value` VARCHAR(10) DEFAULT NULL;
    
#add `bid_response` tooltip - 2014/12/22
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-12-22 16:57:42', '2014-12-22 00:57:42', 'Bid response affects the bid price return to user in Ping process. If type = percent, price = max_bid - x%, if type = flat, price = max_bid - $x, if type = fixed, price = $x.', 'What is the \"Bid Response\"?', '', 'publish', 'closed', 'closed', '', 'what-is-bid-response', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add `campaign_timeout` tooltip - 2014/12/22
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-12-22 16:57:42', '2014-12-22 00:57:42', 'Campaign Timeout forces a max wait-time before issuing a response of all Ping processes in Ping/Post campaign', 'What is the \"Campaign Timeout\"?', '', 'publish', 'closed', 'closed', '', 'what-is-campaign-timeout', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# add `campaign_timeout` field to `campaigns`
ALTER TABLE `campaigns` ADD `campaign_timeout` int(10) DEFAULT NULL;

# add last occurred time to php_log 2014/12/30
ALTER TABLE `php_log` ADD `last_occurred_time` datetime DEFAULT NULL;

#add email_field_mandatory setting 2015/01/07
ALTER TABLE `campaigns` ADD `email_field_mandatory` int(11) DEFAULT '0' AFTER `email_field_description`;

#add index to `sh_repost` table
ALTER TABLE `sh_repost` ADD INDEX `repost_hour` (`repost_hour`);

# standardized field types in `campaigns` and `partners` table
ALTER TABLE `campaigns` MODIFY `email_field_mandatory` enum('0','1','2','3') NOT NULL DEFAULT '0';
ALTER TABLE `campaigns` MODIFY `is_ping_and_post` enum('0','1') NOT NULL DEFAULT '0';
ALTER TABLE `campaigns` MODIFY `is_pingpost` enum('0','1') NOT NULL DEFAULT '0';
ALTER TABLE `campaigns` MODIFY `auction` enum('0','1') NOT NULL DEFAULT '0';

ALTER TABLE `partners`  MODIFY `partner_type` enum('0','1') NOT NULL DEFAULT '0';
ALTER TABLE `partners`  MODIFY `ping_post_type` enum('0','1') NOT NULL DEFAULT '0';

# add `xml_field_name` tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'XML field name', 'What is the \"XML-Field-Name\"?', '', 'publish', 'closed', 'closed', '', 'what-is-xml-field-name', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

#add `xml_field_name` into `partners` table
ALTER TABLE `pingtree`.`partners` ADD `xml_field_name` varchar(50) DEFAULT NULL;

#add `transaction_id_field_name` into `partners` table
ALTER TABLE `pingtree`.`partners` ADD `transaction_id_field_name` varchar(50) DEFAULT NULL;

# add `transaction_id_field_name` tooltip
INSERT INTO `leadwrench`.`wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('59', '2014-08-19 16:57:42', '2014-08-20 00:57:42', 'Transaction id field name is the key of transaction id when posting type = xml field', 'What is the \"Transaction-Id-Field-Name\"?', '', 'publish', 'closed', 'closed', '', 'what-is-transaction-id-field-name', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', 'http://www.leadwrench.com/?post_type=qa_faqs&#038;p=138', '0', 'qa_faqs', '', '0');

# delete Lead_ID field from non-ping/post campaign (1 row affected)
DELETE campaign_fields FROM campaign_fields INNER JOIN campaigns ON campaign_fields.campaign_id = campaigns.id WHERE field_type = '167' AND is_ping_and_post = '0'

# Increase length of fields which store xml path equation 2015/02/11
ALTER TABLE `partners` MODIFY `auction_transaction_id` varchar(255) DEFAULT NULL;
ALTER TABLE `partners` MODIFY `price_per_lead` varchar(255) DEFAULT NULL;

#add message to store skipped reason (and other uses in the future) 2015/02/12
ALTER TABLE `log_delivery` ADD `message` VARCHAR(50) DEFAULT NULL;

# add space to xml_field_name
ALTER TABLE partners MODIFY `xml_field_name` varchar(64) DEFAULT NULL;

# add index to profile related table 2015/03/26
ALTER TABLE `profiles_data` ADD INDEX `profile_id` (`profile_id`);
ALTER TABLE `profiles_inferred` ADD INDEX `profile_id` (`profile_id`);

# update Lead_ID description 2015/04/08
UPDATE `field_types` SET `description` =  'ID of the lead that was returned from Ping stage' WHERE `id` = 167;

#513 add template checking flag.
ALTER TABLE `partners` ADD `valid_template` enum('0','1') NOT NULL DEFAULT '1' AFTER `template`;

#756 add filename to repost queue 2015/05/14
ALTER TABLE `repost_queue` ADD `upload_file` varchar(100) DEFAULT NULL;

# 758 create partner cap table
CREATE TABLE `partner_caps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `is_active` enum('0','1') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '1',
  `cap_type` int(1) NOT NULL DEFAULT '1',
  `cap_value` int(10) DEFAULT NULL DEFAULT '0',
  `interval` int(2) DEFAULT NULL DEFAULT '1',
  `day_of_week` varchar(100) DEFAULT NULL,
  `time_of_day` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_id` (`partner_id`),
  CONSTRAINT `partner_caps_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#760 add filename to repost_queue_mem 2015/05/23
ALTER TABLE `repost_queue_mem` ADD `upload_file` varchar(100) DEFAULT NULL;

#760 Add lock to partner 2015/05/30
ALTER TABLE partners 
ADD `lock_status` enum('0','1') NOT NULL DEFAULT '0',
ADD `lock_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

#762 add campaign rules table 2015/06/04
CREATE TABLE `campaign_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `is_active` enum('0','1') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '1',
  `filter_action` int(10) unsigned NOT NULL,
  `field_type_id` int(10) unsigned DEFAULT NULL,
  `match_type` int(10) NOT NULL,
  `match_value` mediumtext,
  `argument` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `field_type_id` (`field_type_id`),
  CONSTRAINT `campaign_filters_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaign_filters_ibfk_2` FOREIGN KEY (`field_type_id`) REFERENCES `field_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#762 add `reject_reason` to `log_incoming` 2015/06/08
ALTER TABLE `log_incoming` ADD `reject_reason` varchar(100) DEFAULT NULL AFTER `is_success`;

#762 add `system_field` to `campaign_filters` 2015/06/12
ALTER TABLE `campaign_filters` ADD `system_field` varchar(64) DEFAULT NULL AFTER `field_type_id`;

#760 Add lock to partner 2015/06/16
ALTER TABLE partners ADD `lock_thread_id` int(10) DEFAULT NULL;

#768 add tables to storing sub-accounts & accepted campaigns/partners 2015/06/22
CREATE TABLE IF NOT EXISTS `sub_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `username` varchar(250) NOT NULL,
  `sub_account_id` int(10) unsigned NOT NULL,
  `sub_account_username`  varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sub_accounts_rights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sub_account_id` int(10) unsigned NOT NULL,
  `sub_account_username`  varchar(250) NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `object_name` varchar(64) NOT NULL,
  `object_type` enum('1','2') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `sub_account_id` (`sub_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#768 Add `max_sub_accounts` to `config_posts_per_level` 2015/07/06
ALTER TABLE `config_posts_per_level` ADD `max_sub_accounts` int(10) NOT NULL DEFAULT '0' AFTER `max_posts`;

#768 Add foreign key to sub_account_right table 2015/07/07
DELETE FROM sub_accounts_rights WHERE NOT EXISTS (SELECT * FROM sub_accounts WHERE sub_accounts.sub_account_id = sub_accounts_rights.sub_account_id);
ALTER TABLE sub_accounts ADD INDEX `sub_account_id` (`sub_account_id`);
ALTER TABLE `sub_accounts_rights` ADD FOREIGN KEY (`sub_account_id`) REFERENCES `sub_accounts` (`sub_account_id`) ON DELETE CASCADE;

#785 Add option to limit access to Reporting tab by a sub-account
ALTER TABLE `sub_accounts` ADD `revoke_reporting_access` ENUM('0', '1') NOT NULL DEFAULT '0';

#789 Add option to limit campaign control (add/delete)
ALTER TABLE `sub_accounts` ADD `revoke_campaign_control` ENUM('0', '1') NOT NULL DEFAULT '0';

#789 Add option to limit partner control (add/delete)
ALTER TABLE `sub_accounts` ADD `revoke_partner_control` ENUM('0', '1') NOT NULL DEFAULT '0';

#807 Add option to show success leads
ALTER TABLE `sub_accounts` ADD `revoke_success_leads` ENUM('0', '1') NOT NULL DEFAULT '0';

#806 Add option to show failed leads
ALTER TABLE `sub_accounts` ADD `revoke_failed_leads` ENUM('0', '1') NOT NULL DEFAULT '0';

#805 Add option to show skipped leads
ALTER TABLE `sub_accounts` ADD `revoke_skipped_leads` ENUM('0', '1') NOT NULL DEFAULT '0';

#791 Add log_incoming_duplication table
CREATE TABLE `log_incoming_duplication` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `incoming_id` int(10) unsigned DEFAULT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `request_time` datetime NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `http_referer` varchar(255) NOT NULL,
  `remote_ip` char(15) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `is_success` enum('0','1') NOT NULL DEFAULT '1',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `campaign_id` (`campaign_id`,`is_success`,`request_time`),
  KEY `user_id` (`user_id`,`request_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `log_incoming_values_duplication` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `id` (`id`,`name`),
  CONSTRAINT `log_incoming_values_duplication_ibfk_1` FOREIGN KEY (`id`) REFERENCES `log_incoming_duplication` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#815
ALTER TABLE `log_incoming`
ADD COLUMN `full_name` varchar(100) DEFAULT NULL AFTER `email`,
ADD COLUMN `full_address` tinytext DEFAULT NULL  AFTER `full_name`,
ADD COLUMN `phone` varchar(20) DEFAULT NULL  AFTER `full_address`,
ADD COLUMN `campaign` varchar(100) DEFAULT NULL  AFTER `phone`;