INSERT IGNORE INTO `avps` (`arg`, `value_s`, `value_i`, `value_u`) VALUES
  ('bestfilmofweek', '0', 0, 0);

-- --------------------------------------------------------
--
-- Table structure for table `class_config`
--

CREATE TABLE IF NOT EXISTS `class_config` (
  `id`         INT(5) NOT NULL                 AUTO_INCREMENT,
  `name`       VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  `value`      INT(3)                          DEFAULT NULL,
  `classname`  VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  `classcolor` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  `classpic`   VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 11;

--
-- Dumping data for table `class_config`
--

INSERT IGNORE INTO `class_config` (`id`, `name`, `value`, `classname`, `classcolor`, `classpic`) VALUES
  (1, 'UC_USER', '0', 'USER', '8e35ef', 'user.gif'),
  (2, 'UC_POWER_USER', '1', 'POWER USER', 'f9a200', 'power.gif'),
  (3, 'UC_VIP', '2', 'VIP', '009f00', 'vip.gif'),
  (4, 'UC_UPLOADER', '3', 'UPLOADER', '0000ff', 'uploader.gif'),
  (5, 'UC_MODERATOR', '4', 'MODERATOR', 'fe2e2e', 'moderator.gif'),
  (6, 'UC_ADMINISTRATOR', '5', 'ADMINISTRATOR', 'b000b0', 'administrator.gif'),
  (7, 'UC_SYSOP', '6', 'SYS0P', '0c27e4', 'sysop.gif'),
  (8, 'UC_MIN', '0', '', '', ''),
  (9, 'UC_MAX', '6', '', '', ''),
  (10, 'UC_STAFF', '4', '', '', '');

-- --------------------------------------------------------
--
-- Table structure for table `class_promo`
--

CREATE TABLE IF NOT EXISTS `class_promo` (
  `id`        INT(10)        NOT NULL                AUTO_INCREMENT,
  `name`      VARCHAR(250) CHARACTER SET utf8        DEFAULT NULL,
  `min_ratio` DECIMAL(10, 2) NOT NULL,
  `uploaded`  BIGINT(20)     NOT NULL,
  `time`      INT(11)        NOT NULL,
  `low_ratio` DECIMAL(10, 2) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 2;

--
-- Dumping data for table `class_promo`
--

INSERT IGNORE INTO `class_promo` (`id`, `name`, `min_ratio`, `uploaded`, `time`, `low_ratio`) VALUES
  (6, '1', 1.20, 50, 20, 0.85);

-- --------------------------------------------------------

/*UPDATE `cleanup` SET `clean_on`='0'*/

INSERT IGNORE INTO `cleanup` (`clean_id`, `clean_title`, `clean_file`, `clean_time`, `clean_increment`, `clean_cron_key`, `clean_log`, `clean_desc`, `clean_on`)
VALUES
  (68, 'Referrer cleans', 'referrer_update.php', 1398091653, 86400, '36bc2469228c1e0c8269ee9d309be37f', 1,
   'Referrer Autoclean - Removes expired referrer entrys', 0),
  (69, 'Snatch list admin', 'snatchclean_update.php', 1396631629, 86400, 'cfb8afef5b7a1c41e047dc791b0f1de0', 1,
   'Clean old dead data', 0),
  (70, 'Normalize XBT', 'torrents_normalize_xbt.php', 1405720207, 900, 'bd4f4ae7d7499aefbce82971a3b1cbbd', 1,
   'XBT normalize query updates', 0),
  (71, 'Delete torrents', 'delete_torrents_xbt_update.php', 1405731392, 86400, '2d47cfeddfd61ed4529e0d4a25ca0d12', 1,
   'Delete torrent xbt update', 0),
  (72, 'XBT Torrents', 'torrents_update_xbt.php', 1405721775, 900, '79e243cf24e92a13441b381d033d03a9', 1,
   'XBT Torrents update', 0),
  (73, 'XBT hit and run system', 'hitrun_xbt_update.php', 1405444631, 3600, 'a6804b0f6d5ce68ac390d4d261a82d85', 1,
   'XBT hit and run detection', 0),
  (74, 'Clean cheater data', 'cheatclean_update.php', 1408382495, 86400, '9b0112ad44b0135220ef539804447d49', 1,
   'Clean abnormal upload speed entrys', 1);

-- --------------------------------------------------------

ALTER TABLE `comments`
  ADD `user_likes` TEXT CHARACTER SET utf8;

-- --------------------------------------------------------

--
-- Table structure for table `deathrow`
--

CREATE TABLE IF NOT EXISTS `deathrow` (
  `uid`          INT(10)                      NOT NULL,
  `username`     CHAR(80) CHARACTER SET utf8  NOT NULL,
  `tid`          INT(10)                      NOT NULL,
  `torrent_name` CHAR(140) CHARACTER SET utf8 NOT NULL,
  `reason`       TINYINT(1)                   NOT NULL,
  `notify`       TINYINT(1) UNSIGNED          NOT NULL DEFAULT '1',
  UNIQUE KEY `uid` (`uid`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `deathrow`
--


-- ----------------------------------------------------------

--
-- Table structure for table `hit_and_run_settings`
--

CREATE TABLE IF NOT EXISTS `hit_and_run_settings` (
  `name`  VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `hit_and_run_settings`
--

INSERT IGNORE INTO `hit_and_run_settings` (`name`, `value`) VALUES
  ('firstclass', 'UC_POWER_USER'),
  ('secondclass', 'UC_VIP'),
  ('thirdclass', 'UC_MODERATOR'),
  ('_3day_first', '48'),
  ('_14day_first', '30'),
  ('_14day_over_first', '18'),
  ('_3day_second', '48'),
  ('_14day_second', '30'),
  ('_14day_over_second', '18'),
  ('_3day_third', '48'),
  ('_14day_third', '30'),
  ('_14day_over_third', '18'),
  ('torrentage1', '1'),
  ('torrentage2', '7'),
  ('torrentage3', '7'),
  ('cainallowed', '3'),
  ('caindays', '0.5'),
  ('hnr_online', '1');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id`              INT(11) NOT NULL                AUTO_INCREMENT,
  `likes`           INT(11) NOT NULL,
  `post_id`         INT(11) NOT NULL,
  `comment_id`      INT(11) NOT NULL,
  `user_comment_id` INT(11) NOT NULL,
  `user_id`         INT(11) NOT NULL,
  `userip`          VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `likes`
--

-- --------------------------------------------------------

--
-- Table structure for table `manage_likes`
--

CREATE TABLE IF NOT EXISTS `manage_likes` (
  `id`            INT(10) NOT NULL AUTO_INCREMENT,
  `user_id`       INT(10) NOT NULL,
  `disabled_time` INT(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 5;

--
-- Dumping data for table `manage_likes`
--

-- --------------------------------------------------------

ALTER TABLE `news`
  ADD `anonymous` ENUM ('yes', 'no') CHARACTER SET utf8
COLLATE utf8_bin NOT NULL DEFAULT 'no';

-- --------------------------------------------------------

--
-- Table structure for table `modscredits`
--

CREATE TABLE IF NOT EXISTS `modscredits` (
  `id`          INT(10) UNSIGNED                                                                                                                   NOT NULL            AUTO_INCREMENT,
  `name`        VARCHAR(255) COLLATE utf8_unicode_ci                                                                                                                   DEFAULT NULL,
  `category`    ENUM ('Addon', 'Forum', 'Message/Email', 'Display/Style', 'Staff/Tools', 'Browse/Torrent/Details', 'Misc') COLLATE utf8_unicode_ci NOT NULL            DEFAULT 'Misc',
  `status`      ENUM ('Complete', 'In-Progress') COLLATE utf8_unicode_ci                                                                           NOT NULL            DEFAULT 'Complete',
  `u232lnk`     VARCHAR(255) COLLATE utf8_unicode_ci                                                                                                                   DEFAULT NULL,
  `credit`      VARCHAR(255) COLLATE utf8_unicode_ci                                                                                               NOT NULL,
  `description` VARCHAR(120) COLLATE utf8_unicode_ci                                                                                               NOT NULL            DEFAULT '',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 2;

--
-- Dumping data for table `modscredits`
--

INSERT IGNORE INTO `modscredits` (`id`, `name`, `category`, `status`, `u232lnk`, `credit`, `description`) VALUES
  (1, 'Ratio Free', 'Addon', 'Complete', 'https://forum.u-232.com/index.php/topic,1060.0.html', 'Mindless',
   'V3 Ratio free modification; A true ratio free system =]');
-- --------------------------------------------------------

INSERT IGNORE INTO `paypal_config` (`name`, `value`) VALUES
  ('freeslots', '5'),
  ('freeleech', '1'),
  ('immunity', '1'),
  ('seedbonus', '100'),
  ('reputation', '100'),
  ('multiplier', '5'),
  ('currency', 'GBP'),
  ('staff', '1'),
  ('sandbox', ''),
  ('gb_donated_1', '2'),
  ('gb_donated_2', '4'),
  ('gb_donated_3', '7'),
  ('gb_donated_4', '13'),
  ('gb_donated_5', '20'),
  ('gb_donated_6', '40'),
  ('vip_dur_1', '1'),
  ('donor_dur_1', '1'),
  ('free_dur_1', '1'),
  ('up_amt_1', '1'),
  ('kp_amt_1', '200'),
  ('vip_dur_2', '2'),
  ('donor_dur_2', '2'),
  ('free_dur_2', '2'),
  ('up_amt_2', '2'),
  ('kp_amt_2', '400'),
  ('vip_dur_3', '4'),
  ('donor_dur_3', '4'),
  ('free_dur_3', '4'),
  ('up_amt_3', '5'),
  ('kp_amt_3', '600'),
  ('vip_dur_4', '8'),
  ('donor_dur_4', '8'),
  ('free_dur_4', '9'),
  ('up_amt_4', '9'),
  ('kp_amt_4', '900'),
  ('vip_dur_5', '12'),
  ('donor_dur_5', '12'),
  ('free_dur_5', '12'),
  ('up_amt_5', '350'),
  ('kp_amt_5', '3000'),
  ('vip_dur_6', '24'),
  ('donor_dur_6', '24'),
  ('free_dur_6', '24'),
  ('up_amt_6', '450'),
  ('kp_amt_6', '4000'),
  ('duntil_dur_1', '1'),
  ('imm_dur_1', '1'),
  ('duntil_dur_2', '2'),
  ('imm_dur_2', '2'),
  ('duntil_dur_3', '4'),
  ('imm_dur_3', '4'),
  ('duntil_dur_4', '8'),
  ('imm_dur_4', '8'),
  ('duntil_dur_5', '12'),
  ('imm_dur_5', '12'),
  ('duntil_dur_6', '24'),
  ('imm_dur_6', '24'),
  ('inv_amt_1', '1'),
  ('inv_amt_2', '2'),
  ('inv_amt_3', '3'),
  ('inv_amt_4', '4'),
  ('inv_amt_5', '5'),
  ('inv_amt_6', '6');

-- --------------------------------------------------------

ALTER TABLE `peers`
  CHANGE `passkey` `torrent_pass` VARCHAR(32) CHARACTER SET utf8 DEFAULT NULL;
/*ALTER TABLE `peers` ADD `torrent_pass` varchar(32) CHARACTER SET utf8 DEFAULT NULL;*/
ALTER TABLE `peers`
  ADD KEY `torrent_pass` (`torrent_pass`);

-- --------------------------------------------------------

--
-- Table structure for table `referrers`
--

CREATE TABLE IF NOT EXISTS `referrers` (
  `id`      INT(100) NOT NULL               AUTO_INCREMENT,
  `browser` VARCHAR(500) CHARACTER SET utf8 DEFAULT NULL,
  `ip`      VARCHAR(60) CHARACTER SET utf8  DEFAULT NULL,
  `referer` VARCHAR(500) CHARACTER SET utf8 DEFAULT NULL,
  `page`    VARCHAR(200) CHARACTER SET utf8 DEFAULT NULL,
  `date`    INT(11)  NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `referrers`
--

-- --------------------------------------------------------

INSERT IGNORE INTO `site_config` (`name`, `value`) VALUES
  ('bonus_per_duration', '0.225'),
  ('bonus_per_download', '20'),
  ('bonus_per_comment', '3'),
  ('bonus_per_upload', '15'),
  ('bonus_per_rating', '5'),
  ('bonus_per_topic', '8'),
  ('bonus_per_post', '5'),
  ('bonus_per_delete', '15'),
  ('bonus_per_thanks', '5');

-- --------------------------------------------------------

ALTER TABLE `staffpanel`
  ADD `type` ENUM ('user', 'settings', 'stats', 'other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user'
  AFTER description;
INSERT IGNORE INTO `staffpanel` (`id`, `page_name`, `file_name`, `description`, `type`, `av_class`, `added_by`, `added`)
VALUES
  (67, 'Hit and run manager', 'staffpanel.php?tool=hit_and_run_settings', 'Manage all hit and run settings here',
   'settings', 6, 1, 1373110790),
  (68, 'Deathrow', 'staffpanel.php?tool=deathrow', 'Torrents on Deathrow', 'user', 4, 1, 1394313792),
  (69, 'Referrers', 'staffpanel.php?tool=referrers', 'View referals here', 'stats', 4, 1, 1362000677),
  (70, 'Class Configurations', 'staffpanel.php?tool=class_config', 'Configure site user groups', 'settings', 6, 1,
   1366566489),
  (71, 'Class Promotions', 'staffpanel.php?tool=class_promo', 'Set Promotion Critera', 'settings', 6, 1, 1396513263),
  (72, 'Comment viewer', 'staffpanel.php?tool=comments', 'Comment overview page', 'user', 4, 1, 1403735418),
  (73, 'Moderated torrents', 'staffpanel.php?tool=modded_torrents', 'Manage moderated torrents here', 'other', 4, 1,
   1406722110),
  (74, 'Opcode Manage', 'staffpanel.php?tool=op', 'View Opcode manager', 'other', 6, 1, 1305728681);

-- --------------------------------------------------------

ALTER TABLE `torrents`
  ADD `new_info_hash` BINARY(20) NOT NULL;
UPDATE torrents
SET new_info_hash = unhex(info_hash);
ALTER TABLE `torrents`
  DROP `info_hash`;
ALTER TABLE `torrents`
  CHANGE `new_info_hash` `info_hash` BINARY(20) NOT NULL;
ALTER TABLE `torrents`
  ADD `checked_when` INT(11) NOT NULL;
ALTER TABLE `torrents`
  ADD `flags` INT(11) NOT NULL;
ALTER TABLE `torrents`
  ADD `mtime` INT(11) NOT NULL;
ALTER TABLE `torrents`
  ADD `ctime` INT(11) NOT NULL;
ALTER TABLE `torrents`
  ADD `freetorrent` TINYINT(4) NOT NULL DEFAULT '0';

-- --------------------------------------------------------

ALTER TABLE `users`
  ADD `request_uri` VARCHAR(40) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `users`
  ADD `where_is` TEXT CHARACTER SET utf8;
ALTER TABLE `users`
  ADD `opt1` INT(11) NOT NULL DEFAULT '182927957';
ALTER TABLE `users`
  ADD `opt2` INT(11) NOT NULL DEFAULT '224';
ALTER TABLE `users`
  ADD `torrent_pass_version` INT(11) NOT NULL;
ALTER TABLE `users`
  CHANGE `passkey` `torrent_pass` VARCHAR(32) CHARACTER SET utf8 DEFAULT NULL;
/*ALTER TABLE `users` ADD `torrent_pass` varchar(32) CHARACTER SET utf8 DEFAULT NULL;*/
ALTER TABLE `users`
  ADD `can_leech` TINYINT(4) NOT NULL DEFAULT '1';
ALTER TABLE `users`
  ADD `wait_time` INT(11) NOT NULL;
ALTER TABLE `users`
  ADD `peers_limit` INT(11) DEFAULT '1000';
ALTER TABLE `users`
  ADD `torrents_limit` INT(11) DEFAULT '1000';

-- --------------------------------------------------------

--
-- Table structure for table `xbt_announce_log`
--

CREATE TABLE IF NOT EXISTS `xbt_announce_log` (
  `id`         INT(11)          NOT NULL      AUTO_INCREMENT,
  `ipa`        INT(10) UNSIGNED NOT NULL,
  `port`       INT(11)          NOT NULL,
  `event`      INT(11)          NOT NULL,
  `info_hash`  BLOB             NOT NULL,
  `peer_id`    BLOB             NOT NULL,
  `downloaded` BIGINT(20)       NOT NULL,
  `left0`      BIGINT(20)       NOT NULL,
  `uploaded`   BIGINT(20)       NOT NULL,
  `uid`        INT(11)          NOT NULL,
  `mtime`      INT(11)          NOT NULL,
  `useragent`  VARCHAR(51) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_client_whitelist`
--

CREATE TABLE IF NOT EXISTS `xbt_client_whitelist` (
  `id`      INT(10) UNSIGNED NOT NULL       AUTO_INCREMENT,
  `peer_id` VARCHAR(20) CHARACTER SET utf8  DEFAULT NULL,
  `vstring` VARCHAR(200) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_id` (`peer_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 2;

--
-- Dumping data for table `xbt_client_whitelist`
--

INSERT IGNORE INTO `xbt_client_whitelist` (`id`, `peer_id`, `vstring`) VALUES
  (1, '-', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `xbt_config`
--

CREATE TABLE IF NOT EXISTS `xbt_config` (
  `name`  VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `xbt_config`
--

INSERT IGNORE INTO `xbt_config` (`name`, `value`) VALUES
  ('torrent_pass_private_key', 'MG58LNj5LHHz49A9PKhAkxIH8Aa');

-- --------------------------------------------------------

--
-- Table structure for table `xbt_deny_from_hosts`
--

CREATE TABLE IF NOT EXISTS `xbt_deny_from_hosts` (
  `begin` INT(11) NOT NULL,
  `end`   INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_files`
--

CREATE TABLE IF NOT EXISTS `xbt_files` (
  `fid`                       INT(11) NOT NULL AUTO_INCREMENT,
  `info_hash`                 BLOB    NOT NULL,
  `leechers`                  INT(11) NOT NULL,
  `seeders`                   INT(11) NOT NULL,
  `completed`                 INT(11) NOT NULL,
  `announced_http`            INT(11) NOT NULL,
  `announced_http_compact`    INT(11) NOT NULL,
  `announced_http_no_peer_id` INT(11) NOT NULL,
  `announced_udp`             INT(11) NOT NULL,
  `scraped_http`              INT(11) NOT NULL,
  `scraped_udp`               INT(11) NOT NULL,
  `started`                   INT(11) NOT NULL,
  `stopped`                   INT(11) NOT NULL,
  `flags`                     INT(11) NOT NULL,
  `mtime`                     INT(11) NOT NULL,
  `ctime`                     INT(11) NOT NULL,
  `balance`                   INT(11) NOT NULL,
  `freetorrent`               INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_files_users`
--

CREATE TABLE IF NOT EXISTS `xbt_files_users` (
  `fid`           INT(11)                                    NOT NULL,
  `uid`           INT(11)                                    NOT NULL,
  `active`        TINYINT(4)                                 NOT NULL,
  `announced`     INT(11)                                    NOT NULL,
  `completed`     INT(11)                                    NOT NULL,
  `downloaded`    BIGINT(20) UNSIGNED                        NOT NULL,
  `left`          BIGINT(20) UNSIGNED                        NOT NULL,
  `uploaded`      BIGINT(20) UNSIGNED                        NOT NULL,
  `mtime`         INT(11)                                    NOT NULL,
  `leechtime`     BIGINT(20) UNSIGNED                        NOT NULL DEFAULT '0',
  `seedtime`      BIGINT(20) UNSIGNED                        NOT NULL DEFAULT '0',
  `upspeed`       INT(10) UNSIGNED                           NOT NULL,
  `downspeed`     INT(10) UNSIGNED                           NOT NULL,
  `peer_id`       CHAR(8) COLLATE utf8_unicode_ci            NOT NULL,
  `completedtime` INT(11) UNSIGNED                           NOT NULL,
  `ipa`           INT(11) UNSIGNED                           NOT NULL,
  `connectable`   TINYINT(4)                                 NOT NULL DEFAULT '1',
  `mark_of_cain`  ENUM ('yes', 'no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `hit_and_run`   INT(11)                                    NOT NULL,
  `started`       INT(11) UNSIGNED                           NOT NULL,
  UNIQUE KEY `fid` (`fid`, `uid`),
  KEY `uid` (`uid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = FIXED;

--
-- Dumping data for table `xbt_files_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `xbt_scrape_log`
--

CREATE TABLE IF NOT EXISTS `xbt_scrape_log` (
  `id`        INT(11) NOT NULL AUTO_INCREMENT,
  `ipa`       INT(11) NOT NULL,
  `info_hash` BLOB,
  `uid`       INT(11) NOT NULL,
  `mtime`     INT(11) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Dumping data for table `xbt_scrape_log`
--

-- --------------------------------------------------------

--
-- Table structure for table `xbt_users`
--

CREATE TABLE IF NOT EXISTS `xbt_users` (
  `uid`                  INT(11)                     NOT NULL AUTO_INCREMENT,
  `can_leech`            TINYINT(4)                  NOT NULL DEFAULT '1',
  `wait_time`            INT(11)                     NOT NULL DEFAULT '0',
  `peers_limit`          INT(11)                     NOT NULL DEFAULT '0',
  `torrents_limit`       INT(11)                     NOT NULL DEFAULT '0',
  `torrent_pass`         CHAR(32) CHARACTER SET utf8 NOT NULL,
  `torrent_pass_version` INT(11)                     NOT NULL DEFAULT '0',
  `downloaded`           BIGINT(20) UNSIGNED         NOT NULL DEFAULT '0',
  `uploaded`             BIGINT(20) UNSIGNED         NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;

ALTER TABLE `bans`
  CHANGE `first` `first` BIGINT(11) NOT NULL;
ALTER TABLE `bans`
  CHANGE `last` `last` BIGINT(11) NOT NULL;
ALTER TABLE `cleanup_log`
  CHANGE `clog_ip` `clog_ip` CHAR(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0';
ALTER TABLE `comments`
  ADD `edit_name` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `comments`
  ADD `checked_by` VARCHAR(40) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `comments`
  ADD `checked_when` INT(11) NOT NULL;
ALTER TABLE `comments`
  ADD `checked` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `failedlogins`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `forum_poll_votes`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `ips`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `peers`
  CHANGE `peer_id` `peer_id` BINARY(20) NOT NULL;
ALTER TABLE `peers`
  DROP KEY `torrent_seeder`;
ALTER TABLE `peers`
  DROP KEY `torrent_peer_id`;
ALTER TABLE `peers`
  ADD UNIQUE KEY `torrent_peer_id` (`torrent`, `peer_id`, `ip`);
ALTER TABLE `poll_voters`
  CHANGE `ip_address` `ip_address` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `posts`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `referrers`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `searchcloud`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `snatched`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `snatched`
  CHANGE `peer_id` `peer_id` BINARY(20) NOT NULL;
ALTER TABLE `torrents`
  CHANGE `youtube` `youtube` VARCHAR(45) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `users`
  CHANGE `ip` `ip` VARCHAR(60) CHARACTER SET utf8 DEFAULT NULL;
ALTER TABLE `users`
  ADD `forum_sort` ENUM ('ASC', 'DESC') NOT NULL DEFAULT 'DESC'
  AFTER `acceptpms`;

CREATE TABLE IF NOT EXISTS `wiki` (
  `id`           INT(10) UNSIGNED NOT NULL       AUTO_INCREMENT,
  `name`         VARCHAR(100) CHARACTER SET utf8 DEFAULT NULL,
  `body`         LONGTEXT CHARACTER SET utf8,
  `userid`       INT(10) UNSIGNED                DEFAULT '0',
  `time`         INT(11)          NOT NULL,
  `lastedit`     INT(10) UNSIGNED                DEFAULT NULL,
  `lastedituser` INT(10) UNSIGNED                DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 2;
INSERT IGNORE INTO `wiki` (`id`, `name`, `body`, `userid`, `time`, `lastedit`, `lastedituser`) VALUES
  (1, 'index', '[align=center][size=6]Welcome to the [b]Wiki[/b][/size][/align]', 0, 1228076412, 1281610709, 1);

CREATE TABLE IF NOT EXISTS `staffmessages_answers` (
  `id`         INT(10) UNSIGNED NOT NULL      AUTO_INCREMENT,
  `staff_id`   INT(10) UNSIGNED NOT NULL      DEFAULT '0',
  `sender`     INT(10) UNSIGNED NOT NULL      DEFAULT '0',
  `answeredby` INT(10) UNSIGNED NOT NULL      DEFAULT '0',
  `answer`     TEXT CHARACTER SET utf8,
  `added`      INT(11)          NOT NULL,
  `subject`    VARCHAR(30) CHARACTER SET utf8 DEFAULT NULL,
  KEY `id` (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;

ALTER TABLE `messages`
  ADD `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `staffmessages`
  ADD `new` ENUM ('yes', 'no') NOT NULL DEFAULT 'no';
