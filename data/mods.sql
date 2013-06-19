--
-- Local templates need to check filesystem for updates.
--
UPDATE exp2_templates SET save_template_file = 'y' WHERE site_id = 1;

-- For MSM sites, run the following UPDATE instead:
-- UPDATE exp2_templates SET save_template_file = 'y' WHERE site_id IN (ADD MSM SITE_IDS HERE);
--
-- Blue Upload Matrix Shim needs to be enabled locally
--
INSERT INTO `exp2_fieldtypes` (`name`, `version`, `settings`, `has_global_settings`)
VALUES
  ('blue_upload', '2.0', 'YTowOnt9', 'n');


--
-- Tables whose production data we do not need but need to be present
--
CREATE TABLE IF NOT EXISTS `exp2_entry_versioning` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` int(10) unsigned NOT NULL,
  `channel_id` int(4) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `version_date` int(10) NOT NULL,
  `version_data` mediumtext NOT NULL,
  PRIMARY KEY (`version_id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `exp2_revision_tracker` (
  `tracker_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `item_table` varchar(20) NOT NULL,
  `item_field` varchar(20) NOT NULL,
  `item_date` int(10) NOT NULL,
  `item_author_id` int(10) unsigned NOT NULL,
  `item_data` mediumtext NOT NULL,
  PRIMARY KEY (`tracker_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Bypass HTTP Authentication locally
--
UPDATE exp2_templates SET enable_http_auth = 'n';

