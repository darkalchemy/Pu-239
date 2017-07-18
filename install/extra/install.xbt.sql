-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 18, 2014 at 11:13 PM
-- Server version: 5.5.37
-- PHP Version: 5.4.4-14+deb7u11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `09source`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievementist`
--

CREATE TABLE IF NOT EXISTS `achievementist` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `achievname` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `notes` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `clienticon` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `hostname` (`achievname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=87 ;

--
-- Dumping data for table `achievementist`
--

INSERT INTO `achievementist` (`id`, `achievname`, `notes`, `clienticon`) VALUES
(1, 'First Birthday', 'Been a member for at least 1 year.', 'birthday1.png'),
(2, 'Second Birthday', 'Been a member for a period of at least 2 years.', 'birthday2.png'),
(6, 'Fourth Birthday', 'Been a member for a period of at least 4 years.', 'birthday4.png'),
(5, 'Third Birthday', 'Been a member for a period of at least 3 years.', 'birthday3.png'),
(7, 'Fifth Birthday', 'Been a member for a period of at least 5 years.', 'birthday5.png'),
(8, 'Uploader LVL1', 'Uploaded at least 1 torrent to the site.', 'ul1.png'),
(9, 'Uploader LVL2', 'Uploaded at least 50 torrents to the site.', 'ul2.png'),
(10, 'Uploader LVL3', 'Uploaded at least 100 torrents to the site.', 'ul3.png'),
(11, 'Uploader LVL4', 'Uploaded at least 200 torrents to the site.', 'ul4.png'),
(12, 'Uploader LVL5', 'Uploaded at least 300 torrents to the site.', 'ul5.png'),
(13, 'Uploader LVL6', 'Uploaded at least 500 torrents to the site.', 'ul6.png'),
(14, 'Uploader LVL7', 'Uploaded at least 800 torrents to the site.', 'ul7.png'),
(15, 'Uploader LVL8', 'Uploaded at least 1000 torrents to the site.', 'ul8.png'),
(16, 'Uploader LVL9', 'Uploaded at least 1500 torrents to the site.', 'ul9.png'),
(17, 'Uploader LVL10', 'Uploaded at least 2000 torrents to the site.', 'ul10.png'),
(18, 'Inviter LVL1', 'Invited at least 1 new user to the site.', 'invite1.png'),
(19, 'Inviter LVL2', 'Invited at least 2 new users to the site.', 'invite2.png'),
(20, 'Inviter LVL3', 'Invited at least 3 new users to the site.', 'invite3.png'),
(21, 'Inviter LVL4', 'Invited at least 5 new users to the site.', 'invite4.png'),
(22, 'Inviter LVL5', 'Invited at least 10 new users to the site.', 'invite5.png'),
(23, 'Forum Poster LVL1', 'Made at least 1 post in the forums.', 'fpost1.png'),
(24, 'Forum Poster LVL2', 'Made at least 25 posts in the forums.', 'fpost2.png'),
(25, 'Forum Poster LVL3', 'Made at least 50 posts in the forums.', 'fpost3.png'),
(26, 'Forum Poster LVL4', 'Made at least 100 posts in the forums.', 'fpost4.png'),
(27, 'Forum Poster LVL5', 'Made at least 250 posts in the forums.', 'fpost5.png'),
(28, 'Avatar Setter', 'User has successfully set an avatar on profile settings.', 'piratesheep.png'),
(29, 'Old Virginia', 'At the age of 25 still remains a virgin.  (Custom Achievement.)', 'virgin.png'),
(30, 'Forum Poster LVL6', 'Made at least 500 posts in the forums.', 'fpost6.png'),
(31, 'Stick Em Up LVL1', 'Uploading at least 1 sticky torrent to the site.', 'sticky1.png'),
(32, 'Stick Em Up LVL2', 'Uploading at least 5 sticky torrents to the site.', 'sticky2.png'),
(33, 'Stick Em Up LVL3', 'Uploading at least 10 sticky torrents.', 'sticky3.png'),
(34, 'Stick EM Up LVL4', 'Uploading at least 25 sticky torrents.', 'sticky4.png'),
(35, 'Stick EM Up LVL5', 'Uploading at least 50 sticky torrents.', 'sticky5.png'),
(36, 'Gag Da B1tch', 'Getting gagged like he''s Adams Man!', 'gagged.png'),
(37, 'Signature Setter', 'User has successfully set a signature on profile settings.', 'signature.png'),
(38, 'Corruption Counts', 'Transferred at least 1 byte of corrupt data incoming.', 'corrupt.png'),
(40, '7 Day Seeder', 'Seeded a snatched torrent for a total of at least 7 days.', '7dayseed.png'),
(41, '14 Day Seeder', 'Seeded a snatched torrent for a total of at least 14 days.', '14dayseed.png'),
(42, '21 Day Seeder', 'Seeded a snatched torrent for a total of at least 21 days.', '21dayseed.png'),
(43, '28 Day Seeder', 'Seeded a snatched torrent for a total of at least 28 days.', '28dayseed.png'),
(44, '45 Day Seeder', 'Seeded a snatched torrent for a total of at least 45 days.', '45dayseed.png'),
(45, '60 Day Seeder', 'Seeded a snatched torrent for a total of at least 60 days.', '60dayseed.png'),
(46, '90 Day Seeder', 'Seeded a snatched torrent for a total of at least 90 days.', '90dayseed.png'),
(47, '120 Day Seeder', 'Seeded a snatched torrent for a total of at least 120 days.', '120dayseed.png'),
(48, '200 Day Seeder', 'Seeded a snatched torrent for a total of at least 200 days.', '200dayseed.png'),
(49, '1 Year Seeder', 'Seeded a snatched torrent for a total of at least 1 Year.', '365dayseed.png'),
(50, 'Sheep Fondler', 'User has been caught touching the sheep at least 1 time.', 'sheepfondler.png'),
(51, 'Forum Topic Starter LVL1', 'Started at least 1 topic in the forums.', 'ftopic1.png'),
(52, 'Forum Topic Starter LVL2', 'Started at least 10 topics in the forums.', 'ftopic2.png'),
(53, 'Forum Topic Starter LVL3', 'Started at least 25 topics in the forums.', 'ftopic3.png'),
(55, 'Forum Topic Starter LVL4', 'Started at least 50 topics in the forums.', 'ftopic4.png'),
(58, 'Bonus Banker LVL1', 'Earned at least 1 bonus point.', 'bonus1.png'),
(57, 'Forum Topic Starter LVL5', 'Started at least 75 topics in the forums.', 'ftopic5.png'),
(61, 'Bonus Banker LVL3', 'Earned at least 500 bonus points.', 'bonus3.png'),
(60, 'Bonus Banker LVL2', 'Earned at least 100 bonus points.', 'bonus2.png'),
(66, 'Bonus Banker LVL6', 'Earned at least 5000 bonus points.', 'bonus6.png'),
(63, 'Bonus Banker LVL4', 'Earned at least 1000 bonus points.', 'bonus4.png'),
(65, 'Bonus Banker LVL5', 'Earned at least 2000 bonus points.', 'bonus5.png'),
(71, 'Bonus Banker LVL9', 'Earned at least 70000 bonus points.', 'bonus10.png'),
(68, 'Bonus Banker LVL7', 'Earned at least 10000 bonus points.', 'bonus7.png'),
(70, 'Bonus Banker LVL8', 'Earned at least 30000 bonus points.', 'bonus9.png'),
(72, 'Bonus Banker LVL10', 'Earned at least 100000 bonus points.', 'bonus8.png'),
(73, 'Bonus Banker LVL11', 'Earned at least 1000000 bonus points.', 'bonus11.png'),
(74, 'Christmas Achievement', 'User has found the Christmas Achievement in the advent calendar page.', 'christmas.png'),
(75, 'Advent Playa', 'Played the Advent Calendar all 25 days straight.', 'xmasdays.png'),
(76, 'Request Filler LVL1', 'Filled at least 1 request from the request page.', 'reqfiller1.png'),
(77, 'Request Filler LVL2', 'Filled at least 5 requests from the request page.', 'reqfiller2.png'),
(78, 'Request Filler LVL3', 'Filled at least 10 requests from the request page.', 'reqfiller3.png'),
(79, 'Request Filler LVL4', 'Filled at least 25 requests from the request page.', 'reqfiller4.png'),
(80, 'Request Filler LVL5', 'Filled at least 50 requests from the request page.', 'reqfiller5.png'),
(81, 'Adam Punker', 'Officially Punked Adam in the proper forum thread.', 'adampnkr.png'),
(82, 'Shout Spammer LVL1', 'Made at least 10 posts to the shoutbox today.', 'spam1.png'),
(83, 'Shout Spammer LVL2', 'Made at least 25 posts to the shoutbox today.', 'spam2.png'),
(84, 'Shout Spammer LVL3', 'Made at least 50 posts to the shoutbox today.', 'spam3.png'),
(85, 'Shout Spammer LVL4', 'Made at least 75 posts to the shoutbox today.', 'spam4.png'),
(86, 'Shout Spammer LVL5', 'Made at least 100 posts to the shoutbox today.', 'spam5.png');

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(5) NOT NULL DEFAULT '0',
  `achievement` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `achievementid` int(5) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `achievements`
--

-- --------------------------------------------------------

--
-- Table structure for table `ach_bonus`
--

CREATE TABLE IF NOT EXISTS `ach_bonus` (
  `bonus_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_desc` text CHARACTER SET utf8,
  `bonus_type` tinyint(4) NOT NULL DEFAULT '0',
  `bonus_do` text CHARACTER SET utf8,
  PRIMARY KEY (`bonus_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=33 ;

--
-- Dumping data for table `ach_bonus`
--

INSERT INTO `ach_bonus` (`bonus_id`, `bonus_desc`, `bonus_type`, `bonus_do`) VALUES
(1, 'Subtract 10GB From Your Download.', 1, '10737418240'),
(2, 'Subtract 1GB From Your Download.', 1, '1073741824'),
(3, 'Subtract 3GB From Your Download.', 1, '3221225472'),
(4, 'Subtract 5GB From Your Download.', 1, '5368709120'),
(5, 'Subtract 100MB From Your Download.', 1, '107374182'),
(6, 'Subtract 300MB From Your Download.', 1, '322122547'),
(7, 'Subtract 500MB From Your Download.', 1, '536870910'),
(8, 'Subtract 1MB From Your Download.', 1, '1073741'),
(9, 'Add 1GB to your Upload.', 2, '1073741824'),
(10, 'Add 10GB to your Upload.', 2, '10737418240'),
(11, 'Add 3GB to your Upload.', 2, '3221225472'),
(12, 'Add 5GB to your Upload.', 2, '5368709120'),
(13, 'Add 100MB to your Upload.', 2, '107374182'),
(14, 'Add 300MB to your Upload.', 2, '322122547'),
(15, 'Add 500MB to your Upload.', 2, '536870910'),
(16, 'Add 1MB to your Upload.', 2, '1073741'),
(17, 'Add 1 Invite.', 3, '1'),
(18, 'Add 2 Invites.', 3, '2'),
(19, 'Add 100 Bonus Points to your Total.', 4, '100'),
(20, 'Add 200 Bonus Points to your Total.', 4, '200'),
(21, 'Add 500 Bonus Points to your Total.', 4, '500'),
(22, 'Add 750 Bonus Points to your Total.', 4, '750'),
(23, 'Add 1000 Bonus Points to your Total.', 4, '1000'),
(24, 'Add 50 Bonus Points to your Total.', 4, '50'),
(25, 'Add 25 Bonus Points to your Total.', 4, '25'),
(26, 'Add 75 Bonus Points to your Total.', 4, '75'),
(27, 'Add 10 Bonus Points to your Total.', 4, '10'),
(28, 'Nothing', 5, '0'),
(29, 'Nothing', 5, '0'),
(30, 'Nothing', 5, '0'),
(31, 'Nothing', 5, '0'),
(32, 'Nothing', 5, '0');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_main`
--

CREATE TABLE IF NOT EXISTS `announcement_main` (
  `main_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `expires` int(11) NOT NULL DEFAULT '0',
  `sql_query` text CHARACTER SET utf8,
  `subject` text CHARACTER SET utf8,
  `body` text CHARACTER SET utf8,
  PRIMARY KEY (`main_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_process`
--

CREATE TABLE IF NOT EXISTS `announcement_process` (
  `process_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `main_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`process_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `file` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  `extension` enum('zip','rar') CHARACTER SET utf8 NOT NULL DEFAULT 'zip',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `times_downloaded` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `attachments`
--

-- --------------------------------------------------------

--
-- Table structure for table `avps`
--

CREATE TABLE IF NOT EXISTS `avps` (
  `arg` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `value_s` text CHARACTER SET utf8,
  `value_i` int(11) NOT NULL DEFAULT '0',
  `value_u` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`arg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `avps`
--

INSERT INTO `avps` (`arg`, `value_s`, `value_i`, `value_u`) VALUES
('loadlimit', '0.39-1404324894', 0, 0),
('inactivemail', '1', 1341778326, 1),
('sitepot', '0', 0, 1359295634),
('bestfilmofweek', '0', 1402495922, 20),
('last24', '0', 50, 1303875421);

-- --------------------------------------------------------

--
-- Table structure for table `bannedemails`
--

CREATE TABLE IF NOT EXISTS `bannedemails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL DEFAULT '0',
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=55 ;

--
-- Dumping data for table `bannedemails`
--

INSERT INTO `bannedemails` (`id`, `added`, `addedby`, `comment`, `email`) VALUES
(1, 1282299331, 1, 'Fake provider', '*@emailias.com'),
(2, 1282299331, 1, 'Fake provider', '*@e4ward.com'),
(3, 1282299331, 1, 'Fake provider', '*@dumpmail.de'),
(4, 1282299331, 1, 'Fake provider', '*@dontreg.com'),
(5, 1282299331, 1, 'Fake provider', '*@disposeamail.com'),
(6, 1282299331, 1, 'Fake provider', '*@antispam24.de'),
(7, 1282299331, 1, 'Fake provider', '*@trash-mail.de'),
(8, 1282299331, 1, 'Fake provider', '*@spambog.de'),
(9, 1282299331, 1, 'Fake provider', '*@spambog.com'),
(10, 1282299331, 1, 'Fake provider', '*@discardmail.com'),
(11, 1282299331, 1, 'Fake provider', '*@discardmail.de'),
(12, 1282299331, 1, 'Fake provider', '*@mailinator.com'),
(13, 1282299331, 1, 'Fake provider', '*@wuzup.net'),
(14, 1282299331, 1, 'Fake provider', '*@junkmail.com'),
(15, 1282299331, 1, 'Fake provider', '*@clarkgriswald.net'),
(16, 1282299331, 1, 'Fake provider', '*@2prong.com'),
(17, 1282299331, 1, 'Fake provider', '*@jrwilcox.com'),
(18, 1282299331, 1, 'Fake provider', '*@10minutemail.com'),
(19, 1282299331, 1, 'Fake provider', '*@pookmail.com'),
(20, 1282299331, 1, 'Fake provider', '*@golfilla.info'),
(21, 1282299331, 1, 'Fake provider', '*@afrobacon.com'),
(22, 1282299331, 1, 'Fake provider', '*@senseless-entertainment.com'),
(23, 1282299331, 1, 'Fake provider', '*@put2.net'),
(24, 1282299331, 1, 'Fake provider', '*@temporaryinbox.com'),
(25, 1282299331, 1, 'Fake provider', '*@slaskpost.se'),
(26, 1282299331, 1, 'Fake provider', '*@haltospam.com'),
(27, 1282299331, 1, 'Fake provider', '*@h8s.org'),
(28, 1282299331, 1, 'Fake provider', '*@ipoo.org'),
(29, 1282299331, 1, 'Fake provider', '*@oopi.org'),
(30, 1282299331, 1, 'Fake provider', '*@poofy.org'),
(31, 1282299331, 1, 'Fake provider', '*@jetable.org'),
(32, 1282299331, 1, 'Fake provider', '*@kasmail.com'),
(33, 1282299331, 1, 'Fake provider', '*@mail-filter.com'),
(34, 1282299331, 1, 'Fake provider', '*@maileater.com'),
(35, 1282299331, 1, 'Fake provider', '*@mailexpire.com'),
(36, 1282299331, 1, 'Fake provider', '*@mailnull.com'),
(37, 1282299331, 1, 'Fake provider', '*@mailshell.com'),
(38, 1282299331, 1, 'Fake provider', '*@mymailoasis.com'),
(39, 1282299331, 1, 'Fake provider', '*@mytrashmail.com'),
(40, 1282299331, 1, 'Fake provider', '*@mytrashmail.net'),
(41, 1282299331, 1, 'Fake provider', '*@shortmail.net'),
(42, 1282299331, 1, 'Fake provider', '*@sneakemail.com'),
(43, 1282299331, 1, 'Fake provider', '*@sofort-mail.de'),
(44, 1282299331, 1, 'Fake provider', '*@spamcon.org'),
(45, 1282299331, 1, 'Fake provider', '*@spamday.com'),
(46, 1282299331, 1, 'fake provider', '*@spamex.com'),
(47, 1282299307, 1, 'fake provider', '*@spamgourmet.com'),
(48, 1282299289, 1, 'fake provider', '*@spamhole.com'),
(49, 1282299331, 1, 'Fake provider', '*@spammotel.com'),
(50, 1282299331, 1, 'Fake provider', '*@tempemail.net'),
(51, 1282299331, 1, 'Fake provider', '*@tempinbox.com'),
(52, 1282299331, 1, 'Fake provider', '*@throwaway.de'),
(53, 1282299331, 1, 'Fake provider', '*@woodyland.org'),
(54, 1282299331, 1, 'Fake provider', '*@trbvm.com');

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL,
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `first` bigint(11) DEFAULT NULL DEFAULT '0',
  `last` bigint(11) DEFAULT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `first_last` (`first`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `bans`
--


-- --------------------------------------------------------

--
-- Table structure for table `blackjack`
--

CREATE TABLE IF NOT EXISTS `blackjack` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'playing',
  `cards` text CHARACTER SET utf8,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blackjack`
--


-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE IF NOT EXISTS `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `blockid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `blocks`
--

-- --------------------------------------------------------

--
-- Table structure for table `bonus`
--

CREATE TABLE IF NOT EXISTS `bonus` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `bonusname` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `points` decimal(10,1) NOT NULL DEFAULT '0.0',
  `description` text CHARACTER SET utf8,
  `art` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `menge` bigint(20) unsigned NOT NULL DEFAULT '0',
  `pointspool` decimal(10,1) NOT NULL DEFAULT '1.0',
  `enabled` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'yes' COMMENT 'This will determined a switch if the bonus is enabled or not! enabled by default',
  `minpoints` decimal(10,1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

--
-- Dumping data for table `bonus`
--

INSERT INTO `bonus` (`id`, `bonusname`, `points`, `description`, `art`, `menge`, `pointspool`, `enabled`, `minpoints`) VALUES
(1, '1.0GB Uploaded', 275.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 1073741824, 1.0, 'yes', 275.0),
(2, '2.5GB Uploaded', 350.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 2684354560, 1.0, 'yes', 350.0),
(3, '5GB Uploaded', 550.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 5368709120, 1.0, 'yes', 550.0),
(4, '3 Invites', 650.0, 'With enough bonus points acquired, you are able to exchange them for a few invites. The points are then removed from your Bonus Bank and the invitations are added to your invites amount.', 'invite', 3, 1.0, 'yes', 650.0),
(5, 'Custom Title!', 50.0, 'For only 50.0 Karma Bonus Points you can buy yourself a custom title. the only restrictions are no foul or offensive language or userclass can be entered. The points are then removed from your Bonus Bank and your special title is changed to the title of your choice', 'title', 1, 1.0, 'yes', 50.0),
(6, 'VIP Status', 5000.0, 'With enough bonus points acquired, you can buy yourself VIP status for one month. The points are then removed from your Bonus Bank and your status is changed.', 'class', 1, 1.0, 'yes', 5000.0),
(7, 'Give A Karma Gift', 100.0, 'Well perhaps you dont need the upload credit, but you know somebody that could use the Karma boost! You are now able to give your Karma credits as a gift! The points are then removed from your Bonus Bank and added to the account of a user of your choice!\r\n\r\nAnd they recieve a PM with all the info as well as who it came from...', 'gift_1', 1073741824, 1.0, 'yes', 100.0),
(8, 'Custom Smilies', 300.0, 'With enough bonus points acquired, you can buy yourself a set of custom smilies for one month! The points are then removed from your Bonus Bank and with a click of a link, your new smilies are available whenever you post or comment!', 'smile', 1, 1.0, 'yes', 300.0),
(9, 'Remove Warning', 1000.0, 'With enough bonus points acquired... So you have been naughty... tsk tsk :P Yep now for the Low Low price of only 1000 points you can have that warning taken away lol.!', 'warning', 1, 1.0, 'yes', 1000.0),
(10, 'Ratio Fix', 500.0, 'With enough bonus points acquired, you can bring the ratio of one torrent to a 1 to 1 ratio! The points are then removed from your Bonus Bank and your status is changed.', 'ratio', 1, 1.0, 'yes', 500.0),
(11, 'FreeLeech', 30000.0, 'The Ultimate exchange if you have over 30000 Points - Make the tracker freeleech for everyone for 3 days: Upload will count but no download.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!', 'freeleech', 1, 5000.0, 'yes', 1.0),
(12, 'Doubleupload', 30000.0, 'The ultimate exchange if you have over 30000 points - Make the tracker double upload for everyone for 3 days: Upload will count double.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!', 'doubleup', 1, 1000.0, 'yes', 1.0),
(13, 'Halfdownload', 30000.0, 'The ultimate exchange if you have over 30000 points - Make the tracker Half Download for everyone for 3 days: Download will count only half.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!', 'halfdown', 1, 1000.0, 'yes', 1.0),
(14, '1.0GB Download Removal', 150.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 1073741824, 1.0, 'yes', 150.0),
(15, '2.5GB Download Removal', 300.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 2684354560, 1.0, 'yes', 300.0),
(16, '5GB Download Removal', 500.0, 'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.', 'traffic2', 5368709120, 1.0, 'yes', 500.0),
(17, 'Anonymous Profile', 750.0, 'With enough bonus points acquired, you are able to exchange them for Anonymous profile for 14 days. The points are then removed from your Bonus Bank and the Anonymous switch will show on your profile.', 'anonymous', 1, 1.0, 'yes', 750.0),
(18, 'Freeleech for 1 Year', 80000.0, 'With enough bonus points acquired, you are able to exchange them for Freelech for one year for yourself. The points are then removed from your Bonus Bank and the freeleech will be enabled on your account.', 'freeyear', 1, 1.0, 'yes', 80000.0),
(19, '3 Freeleech Slots', 1000.0, 'With enough bonus points acquired, you are able to exchange them for some Freeleech Slots. The points are then removed from your Bonus Bank and the slots are added to your free slots amount.', 'freeslots', 3, 0.0, 'yes', 1000.0),
(20, '200 Bonus Points - Invite trade-in', 1.0, 'If you have 1 invite and dont use them click the button to trade them in for 200 Bonus Points.', 'itrade', 200, 0.0, 'yes', 0.0),
(21, 'Freeslots - Invite trade-in', 1.0, 'If you have 1 invite and dont use them click the button to trade them in for 2 Free Slots.', 'itrade2', 2, 0.0, 'yes', 0.0),
(22, 'Pirate Rank for 2 weeks', 50000.0, 'With enough bonus points acquired, you are able to exchange them for Pirates status and Freeleech for 2 weeks. The points are then removed from your Bonus Bank and the Pirate icon will be displayed throughout, freeleech will then be enabled on your account.', 'pirate', 1, 1.0, 'yes', 50000.0),
(23, 'King Rank for 1 month', 70000.0, 'With enough bonus points acquired, you are able to exchange them for Kings status and Freeleech for 1 month. The points are then removed from your Bonus Bank and the King icon will be displayed throughout,  freeleech will then be enabled on your account.', 'king', 1, 1.0, 'yes', 70000.0),
(24, '10GB Uploaded', 1000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 10737418240, 0.0, 'yes', 1000.0),
(25, '25GB Uploaded', 2000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 26843545600, 0.0, 'yes', 2000.0),
(26, '50GB Uploaded', 4000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 53687091200, 0.0, 'yes', 4000.0),
(27, '100GB Uploaded', 8000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 107374182400, 0.0, 'yes', 8000.0),
(28, '520GB Uploaded', 40000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 558345748480, 0.0, 'yes', 40000.0),
(29, '1TB Uploaded', 80000.0, 'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.', 'traffic', 1099511627776, 0.0, 'yes', 80000.0),
(30, 'Parked Profile', 75000.0, 'With enough bonus points acquired, you are able to unlock the parked option within your profile which will ensure your account will be safe. The points are then removed from your Bonus Bank and the parked switch will show on your profile.', 'parked', 1, 1.0, 'yes', 75000.0),
(31, 'Pirates bounty', 50000.0, 'With enough bonus points acquired, you are able to exchange them for Pirates bounty which will select random users and deduct random amount of reputation points from them. The points are removed from your Bonus Bank and the reputation points will be deducted from the selected users then credited to you.', 'bounty', 1, 1.0, 'yes', 50000.0),
(32, '100 Reputation points', 40000.0, 'With enough bonus points acquired, you are able to exchange them for some reputation points. The points are then removed from your Bonus Bank and the rep is added to your total reputation amount.', 'reputation', 100, 0.0, 'yes', 40000.0),
(33, 'Userblocks', 50000.0, 'With enough bonus points acquired and a minimum of 50 reputation points, you are able to exchange them for userblocks access. The points are then removed from your Bonus Bank and the user blocks configuration link will appear on your menu.', 'userblocks', 0, 0.0, 'yes', 50000.0),
(34, 'Bump a Torrent!', 5000.0, 'With enough bonus points acquired, you can Bump a torrent back to page 1 of the torrents page, bringing it back to life! \r\nThe torrent will then appear on page 1 again! The points are then removed from your Bonus Bank and the torrent is Bumped!\r\n** note there is an option to either view Bumped torrents or not.', 'bump', 1, 0.0, 'yes', 5000.0),
(35, 'Immunity', 150000.0, 'With enough bonus points acquired, you are able to exchange them for immunity for one year. The points are then removed from your Bonus Bank and the immunity switch is enabled on your account.', 'immunity', 1, 0.0, 'yes', 150000.0),
(36, 'User Unlocks', 500.0, 'With enough bonus points acquired and a minimum of 50 reputation points, you are able to exchange them for bonus locked moods. The points are then removed from your Bonus Bank and the user unlocks configuration link will appear on your menu.', 'userunlock', 1, 0.0, 'yes', 500.0);

-- --------------------------------------------------------

--
-- Table structure for table `bonuslog`
--

CREATE TABLE IF NOT EXISTS `bonuslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donation` decimal(10,1) NOT NULL,
  `type` varchar(44) CHARACTER SET utf8 DEFAULT NULL,
  `added_at` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `added_at` (`added_at`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='log of contributors towards freeleech etc...' ;

--
-- Dumping data for table `bonuslog`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `private` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE IF NOT EXISTS `bugs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sender` int(10) NOT NULL DEFAULT '0',
  `added` int(12) NOT NULL DEFAULT '0',
  `priority` enum('low','high','veryhigh') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'low',
  `problem` text CHARACTER SET utf8,
  `status` enum('fixed','ignored','na') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'na',
  `staff` int(10) NOT NULL DEFAULT '0',
  `title` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE IF NOT EXISTS `cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `points` int(11) NOT NULL DEFAULT '0',
  `pic` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=53 ;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `points`, `pic`) VALUES
(1, 2, '2p.bmp'),
(2, 3, '3p.bmp'),
(3, 4, '4p.bmp'),
(4, 5, '5p.bmp'),
(5, 6, '6p.bmp'),
(6, 7, '7p.bmp'),
(7, 8, '8p.bmp'),
(8, 9, '9p.bmp'),
(9, 10, '10p.bmp'),
(10, 10, 'vp.bmp'),
(11, 10, 'dp.bmp'),
(12, 10, 'kp.bmp'),
(13, 1, 'tp.bmp'),
(14, 2, '2b.bmp'),
(15, 3, '3b.bmp'),
(16, 4, '4b.bmp'),
(17, 5, '5b.bmp'),
(18, 6, '6b.bmp'),
(19, 7, '7b.bmp'),
(20, 8, '8b.bmp'),
(21, 9, '9b.bmp'),
(22, 10, '10b.bmp'),
(23, 10, 'vb.bmp'),
(24, 10, 'db.bmp'),
(25, 10, 'kb.bmp'),
(26, 1, 'tb.bmp'),
(27, 2, '2k.bmp'),
(28, 3, '3k.bmp'),
(29, 4, '4k.bmp'),
(30, 5, '5k.bmp'),
(31, 6, '6k.bmp'),
(32, 7, '7k.bmp'),
(33, 8, '8k.bmp'),
(34, 9, '9k.bmp'),
(35, 10, '10k.bmp'),
(36, 10, 'vk.bmp'),
(37, 10, 'dk.bmp'),
(38, 10, 'kk.bmp'),
(39, 1, 'tk.bmp'),
(40, 2, '2c.bmp'),
(41, 3, '3c.bmp'),
(42, 4, '4c.bmp'),
(43, 5, '5c.bmp'),
(44, 6, '6c.bmp'),
(45, 7, '7c.bmp'),
(46, 8, '8c.bmp'),
(47, 9, '9c.bmp'),
(48, 10, '10c.bmp'),
(49, 10, 'vc.bmp'),
(50, 10, 'dc.bmp'),
(51, 10, 'kc.bmp'),
(52, 1, 'tc.bmp');

-- --------------------------------------------------------

--
-- Table structure for table `casino`
--

CREATE TABLE IF NOT EXISTS `casino` (
  `userid` int(10) NOT NULL DEFAULT '0',
  `win` bigint(20) NOT NULL DEFAULT '0',
  `lost` bigint(20) NOT NULL DEFAULT '0',
  `trys` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `enableplay` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `deposit` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `casino`
--


-- --------------------------------------------------------

--
-- Table structure for table `casino_bets`
--

CREATE TABLE IF NOT EXISTS `casino_bets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `proposed` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `challenged` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `amount` bigint(20) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `winner` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `casino_bets`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `cat_desc` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `parent_id` mediumint(5) NOT NULL DEFAULT '-1',
  `tabletype` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `cat_desc`, `parent_id`, `tabletype`) VALUES
(2, 'Games', 'cat_games.png', 'No Description', -1, 1),
(3, 'Movies', 'cat_dvd.png', 'No Description', -1, 2),
(4, 'Music', 'cat_music.png', 'No Description', -1, 4),
(5, 'Episodes', 'cat_tveps.png', 'No Description', 3, 2),
(6, 'XXX', 'cat_xxx.png', 'No Description', 3, 2),
(7, 'Games/PSP', 'cat_psp.png', 'No Description', 2, 1),
(8, 'Games/PS2', 'cat_ps2.png', 'No Description', 2, 1),
(9, 'Anime', 'cat_anime.png', 'No Description', 3, 2),
(10, 'Movies/XviD', 'cat_xvid.png', 'No Description', 3, 2),
(11, 'Movies/HDTV', 'cat_hdtv.png', 'No Description', 3, 2),
(12, 'Games/PC Rips', 'cat_pcrips.png', 'No Description', 2, 1),
(13, 'Apps', 'cat_misc.png', 'No Description', -1, 3),
(1, 'Apps', 'cat_appz.png', 'No Description', 13, 3),
(14, 'Music', 'cat_music.png', 'No Description', 4, 4);

-- --------------------------------------------------------

--
-- Table structure for table `cheaters`
--

CREATE TABLE IF NOT EXISTS `cheaters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL DEFAULT '0',
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `client` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `rate` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `beforeup` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `upthis` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `timediff` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `userip` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `cheaters`
--


-- --------------------------------------------------------

--
-- Table structure for table `class_config`
--

CREATE TABLE IF NOT EXISTS `class_config` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` int(3) DEFAULT NULL,
  `classname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `classcolor` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `classpic` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Dumping data for table `class_config`
--

INSERT INTO `class_config` (`id`, `name`, `value`, `classname`, `classcolor`, `classpic`) VALUES
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
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `min_ratio` decimal(10,2) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `low_ratio` decimal(10,2) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `class_promo`
--

INSERT INTO `class_promo` (`id`, `name`, `min_ratio`, `uploaded`, `time`, `low_ratio`) VALUES
(6, '1', 1.20, 50, 20, 0.85);

-- --------------------------------------------------------

--
-- Table structure for table `cleanup`
--

CREATE TABLE IF NOT EXISTS `cleanup` (
  `clean_id` int(10) NOT NULL AUTO_INCREMENT,
  `clean_title` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `clean_file` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `clean_time` int(11) NOT NULL DEFAULT '0',
  `clean_increment` int(11) NOT NULL DEFAULT '0',
  `clean_cron_key` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `clean_log` tinyint(1) NOT NULL DEFAULT '0',
  `clean_desc` text CHARACTER SET utf8,
  `clean_on` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`clean_id`),
  KEY `clean_time` (`clean_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=81 ;

--
-- Dumping data for table `cleanup`
--

INSERT INTO `cleanup` (`clean_id`, `clean_title`, `clean_file`, `clean_time`, `clean_increment`, `clean_cron_key`, `clean_log`, `clean_desc`, `clean_on`) VALUES
(4, 'Lottery Autoclean', 'lotteryclean.php', 1359812894, 86400, 'd6704d582b136ea1ed13635bb9059f57', 1, 'Lottery Autoclean - Lottery clean up here every X days', 0),
(5, 'Optimze Db Auto', 'optimizedb.php', 1405715247, 172800, 'd6704d582b136ea1ed13635bb9059f57', 1, 'Auto Optimize - Runs every 2 days', 1),
(6, 'Auto Backup Db', 'backupdb.php', 1405742261, 86400, 'd6704d582b136ea1ed13635bb9059f57', 1, 'Auto Backup - Runs every 1 day', 1),
(8, 'Irc bonus', 'irc_update.php', 1405717294, 1800, 'c06a074cd6403bcc1f292ce864c3cdd5', 1, 'Irc idle bonus update', 1),
(9, 'Statistics', 'sitestats_update.php', 1405722386, 3600, '2a2afb82d82cc4ddcb6ff1753a40dfe9', 1, 'SIte statistics update', 1),
(10, 'Karma Bonus', 'karma_update.php', 1405717612, 1800, 'd0df8a38cfba26ece2c285189a656ad0', 0, 'Seedbonus award update', 1),
(11, 'Forums', 'forum_update.php', 1405721151, 900, 'c9c58a0d43b02cd5358115673bc04c9e', 0, 'Forum online and count update', 1),
(12, 'Torrents', 'torrents_update.php', 1394919050, 900, '81875d0e7b63771ae2a59f2a48755da4', 1, 'Torrents update', 0),
(13, 'Normalize', 'torrents_normalize.php', 1394919677, 900, '1274dd2d9ffd203e6d489db25d0f28fe', 1, 'File, comment, torrent update', 0),
(14, 'Ips', 'ip_update.php', 1405725772, 86400, '0b4f34774259b5069d220c485aa10eba', 1, 'Ip clean', 1),
(15, 'Signups', 'expired_signup_update.php', 1405801239, 259200, 'bdde41096f769d1a01251813cc2c1353', 1, 'Expired signups update', 1),
(16, 'Peers', 'peer_update.php', 1394919107, 900, '72181fc6214ddc556d71066df031d424', 1, 'Peers update', 0),
(17, 'Visible', 'visible_update.php', 1405719080, 900, '77c523eab12be5d0342e4606188cd2ca', 1, 'Torrents visible update', 1),
(18, 'Announcements', 'announcement_update.php', 1405770206, 86400, 'b73c139b4defbc031e201b91fef29a4c', 1, 'Old announcement updates', 1),
(19, 'Readposts', 'readpost_update.php', 1405793882, 86400, '3e0c8bc6b6e6cc61fdfe8b26f8268b77', 1, 'Old Readposts updates', 1),
(20, 'Happyhour', 'happyhour_update.php', 1396565647, 43200, 'a7c422bc9f17b3fba5dab2d0129acd32', 1, 'HappyHour Updates', 0),
(21, 'Customsmilies', 'customsmilie_update.php', 1405720147, 86400, '9e8a41be2b0a56d83e0d0c0b00639f66', 1, 'Custom Smilie Update', 1),
(22, 'Karma Vips', 'karmavip_update.php', 1405794046, 86400, 'c444f13b95998c98a851714673ff6b84', 1, 'Karma VIp Updates', 1),
(23, 'Anonymous Profile', 'anonymous_update.php', 1405804463, 86400, '25146aec76a7b163ac6955685ff667d9', 1, 'Anonymous Profile Updates', 1),
(24, 'Delete Torrents', 'delete_torrents_update.php', 1395005525, 86400, '52f8e3c9fd438d4a86062f88f1146098', 1, 'Delete Old Torrents Update', 0),
(25, 'Funds', 'funds_update.php', 1405806713, 86400, '5f50f43a9e640cd6203e1964c17361ba', 1, 'Funds And Donation Updates', 1),
(26, 'Leechwarns', 'leechwarn_update.php', 1405715844, 86400, '0303a05302fadf30fc18f987d2a5b285', 1, 'Leechwarnings Update', 1),
(27, 'Auto Invite', 'autoinvite_update.php', 1405718135, 86400, '48839ced75a612d41d9278718075dbb2', 1, 'Auto Invite Updates', 1),
(28, 'Hit And Run', 'hitrun_update.php', 1394921211, 3600, '3ab445bbff84f87e8dc5a16489d7ca31', 1, 'Hit And Run Updates', 0),
(29, 'Freeslots Update', 'freeslot_update.php', 1395005532, 86400, '63db6b0519eccbfe0b06d87b8f0bcaad', 1, 'Freeslots Stuffs Update', 0),
(30, 'Backup Clean', 'backup_update.php', 1405719881, 86400, '2c0d1a9ffa04937255344b97e2c9706f', 1, 'Backups Clean Update', 1),
(31, 'Inactive Clean', 'inactive_update.php', 1405794146, 86400, 'a401de097e031315b751b992ee40d733', 1, 'Inactive Users Update', 1),
(32, 'Shout Clean', 'shout_update.php', 1405761109, 172800, '13515c22103b5b916c3d86023220cd61', 1, 'Shoutbox Clean Update', 1),
(33, 'Power User Clean', 'pu_update.php', 1405721579, 86400, '4751425b1c765360a5f8bab14c6b9a47', 1, 'Power User Clean Updates', 1),
(34, 'Power User Demote Clean', 'pu_demote_update.php', 1405722059, 86400, 'e9249b5f653f03ed425d68947155056b', 1, 'Power User Demote Clean Updates', 1),
(35, 'Bugs Clean', 'bugs_update.php', 1405774783, 1209600, '1e9734cdf50408a7739b7b03272aeab3', 1, 'Bugs Update Clean', 1),
(36, 'Sitepot Clean', 'sitepot_update.php', 1396628641, 86400, '29dae941216f1bdb81f69dce807b3501', 1, 'Sitepot Update Clean', 0),
(37, 'Userhits Clean', 'userhits_update.php', 1405794345, 86400, 'd0cec8e7adb50290db6cf911a5c74339', 1, 'Userhits Clean Updates', 1),
(38, 'Process Kill', 'processkill_update.php', 1405795280, 86400, 'b7c0f14c9482a14e9f5cb0d467dfd7c6', 1, 'Mysql Process KIll Updates', 1),
(39, 'Cleanup Log', 'cleanlog_update.php', 1405796149, 86400, '7dc0b72fc8c12b264fad1613fbea2489', 1, 'Cleanup Log Updates', 1),
(40, 'Pirate Cleanup', 'pirate_update.php', 1405799774, 86400, 'e5f20d43425832e9397841be6bc92be2', 1, 'Pirate Stuffs Update', 1),
(41, 'King Cleanup', 'king_update.php', 1405800001, 86400, '12b5c6c9f9919ca09816225c29fddaeb', 1, 'King Stuffs Update', 1),
(42, 'Free User Cleanup', 'freeuser_update.php', 1396565083, 3900, '37f9de0443159bf284a1c7a703e96cf9', 1, 'Free User Stuffs Update', 0),
(43, 'Download Possible Cleanup', 'downloadpos_update.php', 1405804020, 86400, 'e20bcc6d07c6ec493e106adb8d2a8227', 1, 'Download Possible Stuffs Update', 1),
(44, 'Upload Possible Cleanup', 'uploadpos_update.php', 1405807913, 86400, 'fd1110b750af878faccaf672fe53876d', 1, 'Upload Possible Stuffs Update', 1),
(45, 'Free Torrents Cleanup', 'freetorrents_update.php', 1396566633, 3600, '20390090ac784fee830d19bd708cfcad', 1, 'Free Torrents Stuffs Update', 0),
(46, 'Chatpost Cleanup', 'chatpost_update.php', 1405804100, 86400, 'bab6f1de36dc97dff02745051e076a39', 1, 'Chatpost Stuffs Update', 1),
(47, 'Immunity Cleanup', 'immunity_update.php', 1405715322, 86400, '11bf6f41c659b9f49f6ccdfa616e9f82', 1, 'Immunity Stuffs Update', 1),
(48, 'Warned Cleanup', 'warned_update.php', 1405716859, 86400, '6e558b89ac60454eaa3a45243347c977', 1, 'Warned Stuffs Update', 1),
(49, 'Games Update', 'gameaccess_update.php', 1405722654, 86400, '33704fd97f8840ff08ef4e6ff236b3e4', 1, 'Games Stuffs Updates', 1),
(50, 'Pm Update', 'sendpmpos_update.php', 1405724044, 86400, '32784b9c2891f022a91d5007f068f7d9', 1, 'Pm Stuffs Updates', 1),
(51, 'Avatar Update', 'avatarpos_update.php', 1405799877, 86400, 'f257794129ee772f5cfe00b33b363100', 1, 'Avatar Stuffs Updates', 1),
(52, 'Birthday Pms', 'birthday_update.php', 1405800457, 86400, '1fd167bf236ea5e74e835224d1cc36e9', 1, 'Pm all members with birthdays.', 1),
(53, 'Movie of the week', 'mow_update.php', 1406138137, 604800, '716274782f2f7229d960a6661fb06b60', 1, 'Updates movie of the week', 1),
(54, 'Silver torrents', 'silvertorrents_update.php', 1396563248, 3600, '3e1aab005271870d69934ebe37e28819', 1, 'Clean expired silver', 0),
(55, 'Failed Logins', 'failedlogin_update.php', 1405718159, 86400, 'c90f0f030d7914db6ae1263de1730541', 1, 'Delete expired failed logins', 1),
(56, 'Christmas Gift Rest', 'gift_update.php', 1435256312, 31556926, '4bdd6190a0ba3420d21b50b79945c06b', 1, 'Reset all users yearly xmas gift', 1),
(58, 'Achievements Update', 'achievement_avatar_update.php', 1405799732, 86400, '0c5889bab74e7ff8f920ec524423f627', 1, 'Updates user avatar achievements', 1),
(59, 'Achievements Update', 'achievement_bday_update.php', 1405718380, 86400, '2b95ff34a27d540f61ceca3ee1424216', 1, 'Updates user birthday achievements', 1),
(60, 'Achievements Update', 'achievement_corrupt_update.php', 1405724125, 86400, 'afefaecc0e31e412c28dbab154e43f9d', 1, 'Updates user corrupt achievements', 1),
(61, 'Achievements Update', 'achievement_fpost_update.php', 1405726185, 86400, 'f466ff2246e7e84bc60210aa947185da', 1, 'Updates user forum post achievements', 1),
(62, 'Achievements Update', 'achievement_ftopics_update.php', 1405727610, 86400, '825f6cac5fa992f505ceea3992db5483', 1, 'Updates user forum topic achievements', 1),
(63, 'Achievements Update', 'achievement_invite_update.php', 1405733242, 86400, '02e56c3aeba0b1e3e4bcca11699f23eb', 1, 'Updates user invite achievements', 1),
(64, 'Achievements Update', 'achievement_karma_update.php', 1405734309, 86400, '3827839629ade62f03a9fccbacb8402a', 1, 'Updates user Karma achievements', 1),
(65, 'Achievements Update', 'achievement_request_update.php', 1405736075, 86400, '48ec70ecc00c88b37977e2743d294888', 1, 'Updates user request achievements', 1),
(66, 'Achievements Update', 'achievement_seedtime_update.php', 1405736690, 86400, '158fb134b7a1487bdda67d42544693fc', 1, 'Updates user seedtime achievements', 1),
(67, 'Achievements Update', 'achievement_sheep_update.php', 1405736867, 86400, '97c3919a5947e00952bf82d1dc6f5c58', 1, 'Updates user sheep achievements', 1),
(68, 'Achievements Update', 'achievement_shouts_update.php', 1405738701, 86400, 'b07151b274bb6d568ab1bc3b3364cb6c', 1, 'Updates user shout achievements', 1),
(69, 'Achievements Update', 'achievement_sig_update.php', 1405740740, 86400, '82c3ff41b8e45a96bcd1582345d6dca9', 1, 'Updates user signature achievements', 1),
(70, 'Achievements Update', 'achievement_sreset_update.php', 1405770945, 86400, 'b51582111414701c0bd512fd2b4f0507', 1, 'Updates user achievements - Reset shouts', 1),
(71, 'Achievements Update', 'achievement_sticky_update.php', 1405728108, 86400, '00aaf60d3806924a42e95e64ee00c5fb', 1, 'Updates user sticky torrents achievements', 1),
(72, 'Achievements Update', 'achievement_up_update.php', 1405794142, 86400, 'b0feb2e2c22dbf9f1575c798a5d1560d', 1, 'Updates user uploader achievements', 1),
(73, 'Referrer cleans', 'referrer_update.php', 1398091653, 86400, '36bc2469228c1e0c8269ee9d309be37f', 1, 'Referrer Autoclean - Removes expired referrer entrys', 0),
(74, 'Snatch list admin', 'snatchclean_update.php', 1396631629, 86400, 'cfb8afef5b7a1c41e047dc791b0f1de0', 1, 'Clean old dead data', 0),
(76, 'Normalize XBT', 'torrents_normalize_xbt.php', 1405720207, 900, 'bd4f4ae7d7499aefbce82971a3b1cbbd', 1, 'XBT normalize query updates', 1),
(77, 'Delete torrents', 'delete_torrents_xbt_update.php', 1405731392, 86400, '2d47cfeddfd61ed4529e0d4a25ca0d12', 1, 'Delete torrent xbt update', 1),
(78, 'XBT Torrents', 'torrents_update_xbt.php', 1405721775, 900, '79e243cf24e92a13441b381d033d03a9', 1, 'XBT Torrents update', 1),
(79, 'XBT Peers', 'peer_update_xbt.php', 1403459321, 900, '3a0245bc43e2cad94ac7966bb3fe75f3', 1, 'XBT Peers update - Not required', 0),
(80, 'XBT hit and run system', 'hitrun_xbt_update.php', 1405444631, 3600, 'a6804b0f6d5ce68ac390d4d261a82d85', 1, 'XBT hit and run detection', 0),
(81, 'Clean cheater data', 'cheatclean_update.php', 1408382495, 86400, '9b0112ad44b0135220ef539804447d49', 1, 'Clean abnormal upload speed entrys', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cleanup_log`
--

CREATE TABLE IF NOT EXISTS `cleanup_log` (
  `clog_id` int(10) NOT NULL AUTO_INCREMENT,
  `clog_event` char(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `clog_time` int(11) NOT NULL DEFAULT '0',
  `clog_ip` char(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `clog_desc` text CHARACTER SET utf8,
  PRIMARY KEY (`clog_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `cleanup_log`
--

-- --------------------------------------------------------

--
-- Table structure for table `coins`
--

CREATE TABLE IF NOT EXISTS `coins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `coins`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8,
  `ori_text` text CHARACTER SET utf8,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` int(11) NOT NULL DEFAULT '0',
  `anonymous` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `request` int(10) unsigned NOT NULL DEFAULT '0',
  `offer` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_likes` text CHARACTER SET utf8,
  `checked_by` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `checked_when` int(11) NOT NULL DEFAULT '0',
  `checked` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `flagpic` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=103 ;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `flagpic`) VALUES
(1, 'Sweden', 'sweden.gif'),
(2, 'United States of America', 'usa.gif'),
(3, 'Russia', 'russia.gif'),
(4, 'Finland', 'finland.gif'),
(5, 'Canada', 'canada.gif'),
(6, 'France', 'france.gif'),
(7, 'Germany', 'germany.gif'),
(8, 'China', 'china.gif'),
(9, 'Italy', 'italy.gif'),
(10, 'Denmark', 'denmark.gif'),
(11, 'Norway', 'norway.gif'),
(12, 'United Kingdom', 'uk.gif'),
(13, 'Ireland', 'ireland.gif'),
(14, 'Poland', 'poland.gif'),
(15, 'Netherlands', 'netherlands.gif'),
(16, 'Belgium', 'belgium.gif'),
(17, 'Japan', 'japan.gif'),
(18, 'Brazil', 'brazil.gif'),
(19, 'Argentina', 'argentina.gif'),
(20, 'Australia', 'australia.gif'),
(21, 'New Zealand', 'newzealand.gif'),
(22, 'Spain', 'spain.gif'),
(23, 'Portugal', 'portugal.gif'),
(24, 'Mexico', 'mexico.gif'),
(25, 'Singapore', 'singapore.gif'),
(67, 'India', 'india.gif'),
(62, 'Albania', 'albania.gif'),
(26, 'South Africa', 'southafrica.gif'),
(27, 'South Korea', 'southkorea.gif'),
(28, 'Jamaica', 'jamaica.gif'),
(29, 'Luxembourg', 'luxembourg.gif'),
(30, 'Hong Kong', 'hongkong.gif'),
(31, 'Belize', 'belize.gif'),
(32, 'Algeria', 'algeria.gif'),
(33, 'Angola', 'angola.gif'),
(34, 'Austria', 'austria.gif'),
(35, 'Yugoslavia', 'yugoslavia.gif'),
(36, 'Western Samoa', 'westernsamoa.gif'),
(37, 'Malaysia', 'malaysia.gif'),
(38, 'Dominican Republic', 'dominicanrep.gif'),
(39, 'Greece', 'greece.gif'),
(40, 'Guatemala', 'guatemala.gif'),
(41, 'Israel', 'israel.gif'),
(42, 'Pakistan', 'pakistan.gif'),
(43, 'Czech Republic', 'czechrep.gif'),
(44, 'Serbia', 'serbia.gif'),
(45, 'Seychelles', 'seychelles.gif'),
(46, 'Taiwan', 'taiwan.gif'),
(47, 'Puerto Rico', 'puertorico.gif'),
(48, 'Chile', 'chile.gif'),
(49, 'Cuba', 'cuba.gif'),
(50, 'Congo', 'congo.gif'),
(51, 'Afghanistan', 'afghanistan.gif'),
(52, 'Turkey', 'turkey.gif'),
(53, 'Uzbekistan', 'uzbekistan.gif'),
(54, 'Switzerland', 'switzerland.gif'),
(55, 'Kiribati', 'kiribati.gif'),
(56, 'Philippines', 'philippines.gif'),
(57, 'Burkina Faso', 'burkinafaso.gif'),
(58, 'Nigeria', 'nigeria.gif'),
(59, 'Iceland', 'iceland.gif'),
(60, 'Nauru', 'nauru.gif'),
(61, 'Slovenia', 'slovenia.gif'),
(103, 'Slovakia', 'slovakia.gif'),
(63, 'Turkmenistan', 'turkmenistan.gif'),
(64, 'Bosnia Herzegovina', 'bosniaherzegovina.gif'),
(65, 'Andorra', 'andorra.gif'),
(66, 'Lithuania', 'lithuania.gif'),
(68, 'Netherlands Antilles', 'nethantilles.gif'),
(69, 'Ukraine', 'ukraine.gif'),
(70, 'Venezuela', 'venezuela.gif'),
(71, 'Hungary', 'hungary.gif'),
(72, 'Romania', 'romania.gif'),
(73, 'Vanuatu', 'vanuatu.gif'),
(74, 'Vietnam', 'vietnam.gif'),
(75, 'Trinidad & Tobago', 'trinidadandtobago.gif'),
(76, 'Honduras', 'honduras.gif'),
(77, 'Kyrgyzstan', 'kyrgyzstan.gif'),
(78, 'Ecuador', 'ecuador.gif'),
(79, 'Bahamas', 'bahamas.gif'),
(80, 'Peru', 'peru.gif'),
(81, 'Cambodia', 'cambodia.gif'),
(82, 'Barbados', 'barbados.gif'),
(83, 'Bangladesh', 'bangladesh.gif'),
(84, 'Laos', 'laos.gif'),
(85, 'Uruguay', 'uruguay.gif'),
(86, 'Antigua Barbuda', 'antiguabarbuda.gif'),
(87, 'Paraguay', 'paraguay.gif'),
(89, 'Thailand', 'thailand.gif'),
(88, 'Union of Soviet Socialist Republics', 'ussr.gif'),
(90, 'Senegal', 'senegal.gif'),
(91, 'Togo', 'togo.gif'),
(92, 'North Korea', 'northkorea.gif'),
(93, 'Croatia', 'croatia.gif'),
(94, 'Estonia', 'estonia.gif'),
(95, 'Colombia', 'colombia.gif'),
(96, 'Lebanon', 'lebanon.gif'),
(97, 'Latvia', 'latvia.gif'),
(98, 'Costa Rica', 'costarica.gif'),
(99, 'Egypt', 'egypt.gif'),
(100, 'Bulgaria', 'bulgaria.gif'),
(101, 'Scotland', 'scotland.gif'),
(102, 'United Arab Emirates', 'uae.gif');

-- --------------------------------------------------------

--
-- Table structure for table `dbbackup`
--

CREATE TABLE IF NOT EXISTS `dbbackup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `dbbackup`
--

-- --------------------------------------------------------

--
-- Table structure for table `deathrow`
--

CREATE TABLE IF NOT EXISTS `deathrow` (
  `uid` int(10) NOT NULL,
  `username` char(80) CHARACTER SET utf8 NOT NULL,
  `tid` int(10) NOT NULL,
  `torrent_name` char(140) CHARACTER SET utf8 NOT NULL,
  `reason` tinyint(1) NOT NULL,
  `notify` tinyint(1) unsigned NOT NULL DEFAULT '1',
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `deathrow`
--


-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `startTime` int(11) NOT NULL DEFAULT '0',
  `endTime` int(11) NOT NULL DEFAULT '0',
  `overlayText` text CHARACTER SET utf8,
  `displayDates` tinyint(1) NOT NULL,
  `freeleechEnabled` tinyint(1) NOT NULL,
  `duploadEnabled` tinyint(1) NOT NULL,
  `hdownEnabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `startTime` (`startTime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `userid`, `startTime`, `endTime`, `overlayText`, `displayDates`, `freeleechEnabled`, `duploadEnabled`, `hdownEnabled`) VALUES
(1, 1, 1371323531, 1371582731, 'HalfDownload [ON]', 1, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `failedlogins`
--

CREATE TABLE IF NOT EXISTS `failedlogins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  `banned` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `attempts` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`),
  KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `files`
--

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `min_class_read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `min_class_write` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `post_count` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0',
  `min_class_create` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `parent_forum` tinyint(4) NOT NULL DEFAULT '0',
  `forum_id` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `forums`
--

-- --------------------------------------------------------

--
-- Table structure for table `forum_config`
--

CREATE TABLE IF NOT EXISTS `forum_config` (
  `id` smallint(1) NOT NULL DEFAULT '1',
  `delete_for_real` smallint(6) NOT NULL DEFAULT '0',
  `min_delete_view_class` smallint(2) unsigned NOT NULL DEFAULT '7',
  `readpost_expiry` smallint(3) NOT NULL DEFAULT '14',
  `min_upload_class` smallint(2) unsigned NOT NULL DEFAULT '2',
  `accepted_file_extension` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `accepted_file_types` varchar(280) CHARACTER SET utf8 DEFAULT NULL,
  `max_file_size` int(10) unsigned NOT NULL DEFAULT '2097152',
  `upload_folder` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`readpost_expiry`),
  KEY `delete_for_real` (`delete_for_real`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `forum_config`
--

INSERT INTO `forum_config` (`id`, `delete_for_real`, `min_delete_view_class`, `readpost_expiry`, `min_upload_class`, `accepted_file_extension`, `accepted_file_types`, `max_file_size`, `upload_folder`) VALUES
(13, 1, 4, 7, 6, 'a:3:{i:0;s:3:"zip";i:1;s:3:"rar";i:2;s:0:"";}', 'a:3:{i:0;s:15:"application/zip";i:1;s:15:"application/rar";i:2;s:0:"";}', 2097152, '/var/www/uploads');

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll`
--

CREATE TABLE IF NOT EXISTS `forum_poll` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `question` varchar(280) CHARACTER SET utf8 DEFAULT NULL,
  `poll_answers` text CHARACTER SET utf8,
  `number_of_options` smallint(2) unsigned NOT NULL DEFAULT '0',
  `poll_starts` int(11) NOT NULL DEFAULT '0',
  `poll_ends` int(11) NOT NULL DEFAULT '0',
  `change_vote` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `multi_options` smallint(2) unsigned NOT NULL DEFAULT '1',
  `poll_closed` enum('yes','no') CHARACTER SET utf8 DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `forum_poll`
--

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll_votes`
--

CREATE TABLE IF NOT EXISTS `forum_poll_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `option` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `forum_poll_votes`
--

-- --------------------------------------------------------

--
-- Table structure for table `freeleech`
--

CREATE TABLE IF NOT EXISTS `freeleech` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `var` int(10) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8,
  `type` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Dumping data for table `freeleech`
--

INSERT INTO `freeleech` (`id`, `name`, `var`, `description`, `type`, `amount`) VALUES
(1, 'Contribute 1 to Site Countdown Pot', 1, 'Donate 1 coin and 1 minute will be removed from the Countdown.', 'contribute', 60),
(2, 'Contribute 5 to Site Countdown Pot', 5, 'Donate 5 coins and 5 minutes will be removed from the Countdown.', 'contribute', 300),
(3, 'Contribute 10 to Site Countdown Pot', 10, 'Donate 10 coins and 10 minutes will be removed from the Countdown.', 'contribute', 600),
(4, 'Contribute 25 to Site Countdown Pot', 25, 'Donate 25 coins and 25 minutes will be removed from the Countdown.', 'contribute', 1500),
(5, 'Contribute 50 to Site Countdown Pot', 50, 'Donate 50 coins and 50 minutes will be removed from the Countdown.', 'contribute', 3000),
(6, 'Contribute 100 to Site Countdown Pot', 100, 'Donate 100 coins and 1 hour and 40 minutes will be removed from the Countdown.', 'contribute', 6000),
(7, 'Contribute 500 to Site Countdown Pot', 500, 'Donate 500 coins and 8 hours and 20 minutes will be removed from the Countdown.', 'contribute', 30000),
(8, 'Contribute 1000 to Site Countdown Pot', 1000, 'Donate 1000 coins and 16 hours and 40 minutes will be removed from the Countdown.', 'contribute', 60000),
(9, 'Contribute to Site Countdown Pot', 0, 'Enter a custom amount to donate. ', 'contribut3', 0),
(10, 'Freeleech', 0, 'Freeleech Sunday is enabled', 'countdown', 1362355200),
(11, 'Sitewide Freeleech', 0, 'set by', 'manual', 0),
(12, 'Sitewide Doubleseed', 0, 'set by ', 'manual', 0),
(13, 'Sitewide Freeleech and Doubleseed', 0, 'set by', 'manual', 0),
(15, 'Crazy Hour', 1395007265, 'Freeleech and Double Upload credit for 24 Hours', 'crazyhour', 0);

-- --------------------------------------------------------

--
-- Table structure for table `freeslots`
--

CREATE TABLE IF NOT EXISTS `freeslots` (
  `torrentid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `doubleup` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `free` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `addedup` int(11) NOT NULL DEFAULT '0',
  `addedfree` int(11) NOT NULL DEFAULT '0',
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `freeslots`
--

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `friendid` int(10) unsigned NOT NULL DEFAULT '0',
  `confirmed` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `friends`
--


-- --------------------------------------------------------

--
-- Table structure for table `funds`
--

CREATE TABLE IF NOT EXISTS `funds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cash` decimal(8,2) NOT NULL DEFAULT '0.00',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `happyhour`
--

CREATE TABLE IF NOT EXISTS `happyhour` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `multiplier` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `happyhour`
--

-- --------------------------------------------------------

--
-- Table structure for table `happylog`
--

CREATE TABLE IF NOT EXISTS `happylog` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `multi` float NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `happylog`
--

-- --------------------------------------------------------

--
-- Table structure for table `hit_and_run_settings`
--

CREATE TABLE IF NOT EXISTS `hit_and_run_settings` (
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hit_and_run_settings`
--

INSERT INTO `hit_and_run_settings` (`name`, `value`) VALUES
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
-- Table structure for table `infolog`
--

CREATE TABLE IF NOT EXISTS `infolog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) DEFAULT '0',
  `txt` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `infolog`
--

-- --------------------------------------------------------

--
-- Table structure for table `invite_codes`
--

CREATE TABLE IF NOT EXISTS `invite_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `code` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `invite_added` int(10) NOT NULL,
  `status` enum('Pending','Confirmed') CHARACTER SET latin1 NOT NULL DEFAULT 'Pending',
  `email` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sender` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `invite_codes`
--

-- --------------------------------------------------------

--
-- Table structure for table `ips`
--

CREATE TABLE IF NOT EXISTS `ips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `userid` int(10) DEFAULT NULL,
  `type` enum('login','announce','browse','like') CHARACTER SET latin1 NOT NULL,
  `seedbox` tinyint(1) NOT NULL DEFAULT '0',
  `lastbrowse` int(11) NOT NULL DEFAULT '0',
  `lastlogin` int(11) NOT NULL DEFAULT '0',
  `lastannounce` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `ips`
--

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `likes` int(11) NOT NULL DEFAULT '0',
  `post_id` int(11) NOT NULL DEFAULT '0',
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `user_comment_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `userip` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `likes`
--

-- --------------------------------------------------------

--
-- Table structure for table `lottery_config`
--

CREATE TABLE IF NOT EXISTS `lottery_config` (
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lottery_config`
--

INSERT INTO `lottery_config` (`name`, `value`) VALUES
('ticket_amount', '10000'),
('ticket_amount_type', 'seedbonus'),
('user_tickets', '10'),
('class_allowed', '0|1|2|3|4|5|6'),
('total_winners', '5'),
('prize_fund', '10000000'),
('start_date', '1328458121'),
('end_date', '1328542721'),
('use_prize_fund', '1'),
('enable', '0'),
('lottery_winners', ''),
('lottery_winners_amount', '2000000'),
('lottery_winners_time', '1334782914');

-- --------------------------------------------------------

--
-- Table structure for table `manage_likes`
--

CREATE TABLE IF NOT EXISTS `manage_likes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `disabled_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `manage_likes`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `msg` text CHARACTER SET utf8,
  `unread` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `poster` bigint(20) unsigned NOT NULL DEFAULT '0',
  `location` smallint(6) NOT NULL DEFAULT '1',
  `saved` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `urgent` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `draft` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `messages`
--

-- --------------------------------------------------------

--
-- Table structure for table `modscredits`
--

CREATE TABLE IF NOT EXISTS `modscredits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` enum('Addon','Forum','Message/Email','Display/Style','Staff/Tools','Browse/Torrent/Details','Misc') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Misc',
  `status` enum('Complete','In-Progress') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Complete',
  `u232lnk` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `credit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(120) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `modscredits`
--

INSERT INTO `modscredits` (`id`, `name`, `category`, `status`, `u232lnk`, `credit`, `description`) VALUES
(1, 'Ratio Free', 'Addon', 'Complete', 'https://forum.u-232.com/index.php/topic,1060.0.html', 'Mindless', 'V3 Ratio free modification; A true ratio free system =]');

-- --------------------------------------------------------

--
-- Table structure for table `moods`
--

CREATE TABLE IF NOT EXISTS `moods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `bonus` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=204 ;

--
-- Dumping data for table `moods`
--

INSERT INTO `moods` (`id`, `name`, `image`, `bonus`) VALUES
(67, 'is a slurpee ninja', 'ninja.gif', 1),
(66, 'is headbanging', 'punk.gif', 0),
(65, 'is grooving to the music', 'music.gif', 0),
(63, 'is farting', 'fart3.gif', 0),
(64, 'is hard at work', 'elektrik.gif', 0),
(62, 'is feeling artistic', 'graffiti.gif', 0),
(61, 'is feeling Good!', 'good.gif', 0),
(59, 'is having a cig', 'cigar.gif', 0),
(60, 'is eating cookies', 'cookies.gif', 0),
(58, 'is telling a story', 'talk2.gif', 0),
(57, 'is pissed drunk', 'drinks.gif', 0),
(56, 'Is old', 'oldman.gif', 0),
(55, 'is in bed', 'sleeping.gif', 0),
(54, 'is kenny', 'kenny.gif', 0),
(53, 'is feeling lucky', 'clover.gif', 1),
(52, 'is feeling super', 'super.gif', 1),
(51, 'is bouncing', 'trampoline.gif', 1),
(50, 'is drinking cola', 'pepsi.gif', 1),
(49, 'is hitting the bong', 'bong.gif', 1),
(48, 'is spidey', 'spidey.gif', 0),
(47, 'is taz!', 'taz.gif', 1),
(133, 'is wanted', 'wanted.gif', 0),
(131, 'is a wizard', 'wizard.gif', 0),
(132, 'is a pissed off', 'soapbox1.gif', 0),
(108, 'is da bomb', 'bomb.gif', 0),
(123, 'hitting the bhong', 'bhong.gif', 0),
(121, 'is smiling', 'smile2.gif', 0),
(122, 'is cheerful', 'clapper1.gif', 0),
(107, 'is crazy', 'crazy.gif', 0),
(105, 'Is banned', 'banned.gif', 0),
(106, 'is teasing', 'blum.gif', 0),
(104, 'is headbanging', 'mini4.gif', 0),
(203, 'is wacko', 'wacko.gif', 0),
(102, 'woof woof!', 'pish.gif', 0),
(101, 'is crabby', 'evilmad.gif', 0),
(100, 'is dead', 'wink_skull.gif', 0),
(46, 'is bored', 'tumbleweed.gif', 0),
(45, 'is in shock', 'sheesh.gif', 0),
(44, 'is feeling weird', 'weirdo.gif', 0),
(43, 'is stoned', 'smokin.gif', 0),
(42, 'is feeling smart', 'smart.gif', 0),
(41, 'is feeling sly', 'sly.gif', 0),
(40, 'is feeling like shit', 'shit.gif', 0),
(39, 'is feeling like a pimp', 'pimp.gif', 0),
(38, 'is feeling old', 'oldtimer.gif', 0),
(37, 'is a ninja', 'ninja.gif', 0),
(36, 'is into the music', 'music.gif', 0),
(35, 'is feeling like a king', 'king.gif', 0),
(34, 'is feeling lazy', 'smoke2.gif', 0),
(33, 'is feeling like kissing', 'kissing2.gif', 0),
(32, 'is laughing out loud', 'laugh.gif', 0),
(31, 'is feeling innocent', 'innocent.gif', 0),
(30, 'is feeling like a winner', 'hooray.gif', 0),
(29, 'is having fun', 'fun.gif', 0),
(28, 'has gone fishing', 'fishing.gif', 0),
(27, 'is drunk', 'drunk.gif', 0),
(26, 'is feeling crazy', 'crazy.gif', 0),
(25, 'is dancing', 'mml.gif', 0),
(24, 'is feeling like crying', 'cry.gif', 0),
(23, 'needs coffee', 'cuppa.gif', 0),
(22, 'is feeling bossy', 'cigar.gif', 0),
(103, 'is feeling like an angel', 'angeldevil.gif', 0),
(21, 'is feeling like an angel', 'angel.gif', 0),
(20, 'is drinking', 'beer.gif', 0),
(19, 'is drinking with friends', 'beer2.gif', 0),
(18, 'is feeling bananas', 'bananadance.gif', 0),
(17, 'is feeling awesome', 'w00t.gif', 0),
(16, 'is feeling like a tease', 'tease.gif', 0),
(15, 'is feeling happy', 'smile1.gif', 0),
(14, 'yarrr matey', 'pirate2.gif', 0),
(13, 'is feeling yucky', 'yucky.gif', 0),
(202, 'devil', 'devil.gif', 0),
(12, 'is feeling devilish', 'devil.gif', 0),
(11, 'is feeling like ranting', 'rant.gif', 0),
(10, 'is a pirate', 'pirate.gif', 0),
(9, 'in love', 'love.gif', 0),
(8, 'is feeling silly', 'clown.gif', 0),
(7, 'is feeling sad', 'wavecry.gif', 0),
(6, 'in wub', 'wub.gif', 0),
(5, 'is feeling angry', 'angry.gif', 0),
(4, 'is feeling tired', 'yawn.gif', 0),
(3, 'is feeling good', 'grin.gif', 0),
(2, 'is feeling bad', 'wall.gif', 0),
(1, 'is feeling neutral', 'noexpression.gif', 0);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `body` text CHARACTER SET utf8,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `sticky` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `anonymous` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `news`
--

-- --------------------------------------------------------

--
-- Table structure for table `notconnectablepmlog`
--

CREATE TABLE IF NOT EXISTS `notconnectablepmlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(11) DEFAULT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `now_viewing`
--

CREATE TABLE IF NOT EXISTS `now_viewing` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE IF NOT EXISTS `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_name` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(180) CHARACTER SET utf8 DEFAULT NULL,
  `description` text CHARACTER SET utf8,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `offered_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_torrent_id` int(10) NOT NULL DEFAULT '0',
  `vote_yes_count` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_no_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `link` varchar(240) CHARACTER SET utf8 DEFAULT NULL,
  `status` enum('approved','pending','denied') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `id_added` (`id`),
  KEY `offered_by_name` (`offer_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `offers`
--

-- --------------------------------------------------------

--
-- Table structure for table `offer_votes`
--

CREATE TABLE IF NOT EXISTS `offer_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vote` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `user_offer` (`offer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `offer_votes`
--

-- --------------------------------------------------------

--
-- Table structure for table `over_forums`
--

CREATE TABLE IF NOT EXISTS `over_forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `min_class_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `over_forums`
--

-- --------------------------------------------------------

--
-- Table structure for table `paypal_config`
--

CREATE TABLE IF NOT EXISTS `paypal_config` (
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `paypal_config`
--

INSERT INTO `paypal_config` (`name`, `value`) VALUES
('email', ''),
('gb', '3'),
('weeks', '4'),
('invites', '1'),
('enable', '1'),
('freeslots', '5'),
('freeleech', '1'),
('immunity', '1'),
('seedbonus', '100'),
('reputation', '100'),
('multiplier', '5'),
('currency', '''GBP'''),
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

--
-- Table structure for table `peers`
--

CREATE TABLE IF NOT EXISTS `peers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent_pass` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `peer_id` binary(20) NOT NULL,
  `ip` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `started` int(11) NOT NULL DEFAULT '0',
  `last_action` int(11) NOT NULL DEFAULT '0',
  `prev_action` int(11) NOT NULL DEFAULT '0',
  `connectable` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `agent` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `finishedat` int(10) unsigned NOT NULL DEFAULT '0',
  `downloadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `corrupt` int(10) NOT NULL DEFAULT '0',
  `compact` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torrent_peer_id` (`torrent`,`peer_id`,`ip`),
  KEY `torrent` (`torrent`),
  KEY `last_action` (`last_action`),
  KEY `connectable` (`connectable`),
  KEY `userid` (`userid`),
  KEY `torrent_pass` (`torrent_pass`),
  KEY `torrent_connect` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `pmboxes`
--

CREATE TABLE IF NOT EXISTS `pmboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `boxnumber` tinyint(4) NOT NULL DEFAULT '2',
  `name` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `pmboxes`
--


-- --------------------------------------------------------

--
-- Table structure for table `poll`
--

CREATE TABLE IF NOT EXISTS `poll` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `question` varchar(320) CHARACTER SET utf8 DEFAULT NULL,
  `answers` text CHARACTER SET utf8,
  `votes` int(5) NOT NULL DEFAULT '0',
  `multi` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `poll`
--

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE IF NOT EXISTS `polls` (
  `pid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `start_date` int(10) DEFAULT NULL,
  `choices` mediumtext CHARACTER SET utf8,
  `starter_id` mediumint(8) NOT NULL DEFAULT '0',
  `starter_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `votes` smallint(5) NOT NULL DEFAULT '0',
  `poll_question` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `polls`
--

-- --------------------------------------------------------

--
-- Table structure for table `poll_voters`
--

CREATE TABLE IF NOT EXISTS `poll_voters` (
  `vid` int(10) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `vote_date` int(10) NOT NULL DEFAULT '0',
  `poll_id` int(10) NOT NULL DEFAULT '0',
  `user_id` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`vid`),
  KEY `poll_id` (`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `poll_voters`
--

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `body` text CHARACTER SET utf8,
  `edited_by` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_date` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `post_title` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `bbcode` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'yes',
  `post_history` text CHARACTER SET utf8,
  `edit_reason` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `status` enum('deleted','recycled','postlocked','ok') CHARACTER SET utf8 NOT NULL DEFAULT 'ok',
  `staff_lock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `anonymous` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topic_id`),
  KEY `userid` (`user_id`),
  KEY `body` (`post_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `posts`
--
-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE IF NOT EXISTS `promo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `added` int(10) NOT NULL DEFAULT '0',
  `days_valid` int(2) NOT NULL DEFAULT '0',
  `accounts_made` int(3) NOT NULL DEFAULT '0',
  `max_users` int(3) NOT NULL DEFAULT '0',
  `link` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `creator` int(10) NOT NULL DEFAULT '0',
  `users` text CHARACTER SET utf8,
  `bonus_upload` bigint(10) NOT NULL DEFAULT '0',
  `bonus_invites` int(2) NOT NULL DEFAULT '0',
  `bonus_karma` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `promo`
--

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE IF NOT EXISTS `rating` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `topic` int(10) NOT NULL DEFAULT '0',
  `torrent` int(10) NOT NULL DEFAULT '0',
  `rating` int(1) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `rating`
--

-- --------------------------------------------------------

--
-- Table structure for table `read_posts`
--

CREATE TABLE IF NOT EXISTS `read_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post_read` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `read_posts`
--

-- --------------------------------------------------------

--
-- Table structure for table `referrers`
--

CREATE TABLE IF NOT EXISTS `referrers` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `browser` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `referer` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `page` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `referrers`
--

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reported_by` int(10) unsigned NOT NULL DEFAULT '0',
  `reporting_what` int(10) unsigned NOT NULL DEFAULT '0',
  `reporting_type` enum('User','Comment','Request_Comment','Offer_Comment','Request','Offer','Torrent','Hit_And_Run','Post') CHARACTER SET utf8 NOT NULL DEFAULT 'Torrent',
  `reason` text CHARACTER SET utf8,
  `who_delt_with_it` int(10) unsigned NOT NULL DEFAULT '0',
  `delt_with` tinyint(1) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `how_delt_with` text CHARACTER SET utf8,
  `2nd_value` int(10) unsigned NOT NULL DEFAULT '0',
  `when_delt_with` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `delt_with` (`delt_with`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `reports`
--

-- --------------------------------------------------------

--
-- Table structure for table `reputation`
--

CREATE TABLE IF NOT EXISTS `reputation` (
  `reputationid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reputation` int(10) NOT NULL DEFAULT '0',
  `whoadded` int(10) NOT NULL DEFAULT '0',
  `reason` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `dateadd` int(10) NOT NULL DEFAULT '0',
  `locale` enum('posts','comments','torrents','users') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'posts',
  `postid` int(10) NOT NULL DEFAULT '0',
  `userid` mediumint(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reputationid`),
  KEY `userid` (`userid`),
  KEY `whoadded` (`whoadded`),
  KEY `multi` (`postid`),
  KEY `dateadd` (`dateadd`),
  KEY `locale` (`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `reputation`
--

-- --------------------------------------------------------

--
-- Table structure for table `reputationlevel`
--

CREATE TABLE IF NOT EXISTS `reputationlevel` (
  `reputationlevelid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `minimumreputation` int(10) NOT NULL DEFAULT '0',
  `level` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`reputationlevelid`),
  KEY `reputationlevel` (`minimumreputation`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Dumping data for table `reputationlevel`
--

INSERT INTO `reputationlevel` (`reputationlevelid`, `minimumreputation`, `level`) VALUES
(1, -999999, 'is infamous around these parts'),
(2, -50, 'can only hope to improve'),
(3, -10, 'has a little shameless behaviour in the past'),
(4, 0, 'is an unknown quantity at this point'),
(5, 15, 'is on a distinguished road'),
(6, 50, 'will become famous soon enough'),
(7, 250, 'has a spectacular aura about'),
(8, 150, 'is a jewel in the rough'),
(9, 350, 'is just really nice'),
(10, 450, 'is a glorious beacon of light'),
(11, 550, 'is a name known to all'),
(12, 650, 'is a splendid one to behold'),
(13, 1000, 'has much to be proud of'),
(14, 1500, 'has a brilliant future'),
(15, 2000, 'has a reputation beyond repute');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_name` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(180) CHARACTER SET utf8 DEFAULT NULL,
  `description` text CHARACTER SET utf8,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `requested_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_torrent_id` int(10) NOT NULL DEFAULT '0',
  `vote_yes_count` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_no_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `link` varchar(240) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_added` (`id`),
  KEY `requested_by_name` (`request_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `requests`
--

-- --------------------------------------------------------

--
-- Table structure for table `request_votes`
--

CREATE TABLE IF NOT EXISTS `request_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vote` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `user_request` (`request_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `request_votes`
--

-- --------------------------------------------------------

--
-- Table structure for table `searchcloud`
--

CREATE TABLE IF NOT EXISTS `searchcloud` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `searchedfor` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `howmuch` int(10) NOT NULL,
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchedfor` (`searchedfor`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `searchcloud`
--

INSERT INTO `searchcloud` (`id`, `searchedfor`, `howmuch`, `ip`) VALUES
(1, 'Testing', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `shit_list`
--

CREATE TABLE IF NOT EXISTS `shit_list` (
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `suspect` int(10) unsigned NOT NULL DEFAULT '0',
  `shittyness` int(2) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8,
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `shit_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `shoutbox`
--

CREATE TABLE IF NOT EXISTS `shoutbox` (
  `id` bigint(40) NOT NULL AUTO_INCREMENT,
  `userid` bigint(6) NOT NULL DEFAULT '0',
  `to_user` int(10) NOT NULL DEFAULT '0',
  `username` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8,
  `text_parsed` text CHARACTER SET utf8,
  `staff_shout` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `for` (`to_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `shoutbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `sitelog`
--

CREATE TABLE IF NOT EXISTS `sitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL DEFAULT '0',
  `txt` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `sitelog`
--

-- --------------------------------------------------------

--
-- Table structure for table `site_config`
--

CREATE TABLE IF NOT EXISTS `site_config` (
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `site_config`
--

INSERT INTO `site_config` (`name`, `value`) VALUES
('site_online', '1'),
('autoshout_on', '1'),
('seedbonus_on', '1'),
('openreg', 'true'),
('forums_online', '0'),
('maxusers', '10000'),
('invites', '5000'),
('openreg_invites', 'true'),
('failedlogins', '5'),
('ratio_free', 'false'),
('captcha_on', 'true'),
('dupeip_check_on', 'true'),
('totalneeded', '60'),
('bonus_per_duration', '0.225'),
('bonus_per_download', '20'),
('bonus_per_comment', '3'),
('bonus_per_upload', '15'),
('bonus_per_rating', '5'),
('bonus_per_topic', '8'),
('bonus_per_post', '5'),
('bonus_per_delete', '15'),
('bonus_per_thanks', '5');;

-- --------------------------------------------------------

--
-- Table structure for table `snatched`
--

CREATE TABLE IF NOT EXISTS `snatched` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `connectable` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `agent` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `peer_id` binary(20) NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `upspeed` bigint(20) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downspeed` bigint(20) NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `seedtime` int(11) unsigned NOT NULL DEFAULT '0',
  `leechtime` int(11) unsigned NOT NULL DEFAULT '0',
  `start_date` int(11) NOT NULL DEFAULT '0',
  `last_action` int(11) NOT NULL DEFAULT '0',
  `complete_date` int(11) NOT NULL DEFAULT '0',
  `timesann` int(10) unsigned NOT NULL DEFAULT '0',
  `hit_and_run` int(11) NOT NULL DEFAULT '0',
  `mark_of_cain` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `finished` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `tr_usr` (`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `staffmessages`
--

CREATE TABLE IF NOT EXISTS `staffmessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) DEFAULT '0',
  `msg` text CHARACTER SET utf8,
  `subject` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `answeredby` int(10) unsigned NOT NULL DEFAULT '0',
  `answered` int(1) NOT NULL DEFAULT '0',
  `answer` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `answeredby` (`answeredby`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `staffmessages`
--

-- --------------------------------------------------------

--
-- Table structure for table `staffpanel`
--

CREATE TABLE IF NOT EXISTS `staffpanel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `file_name` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `type` enum('user','settings','stats','other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `av_class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `added_by` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`),
  KEY `av_class` (`av_class`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=105 ;

--
-- Dumping data for table `staffpanel`
--

INSERT INTO `staffpanel` (`id`, `page_name`, `file_name`, `description`, `type`, `av_class`, `added_by`, `added`) VALUES
(1, 'Flood Control', 'staffpanel.php?tool=floodlimit', 'Manage flood limits', 'settings', 5, 1, 1277910147),
(2, 'Coders Log', 'staffpanel.php?tool=editlog', 'Coders site file edit log', 'other', 6, 1, 1277909868),
(3, 'Bonus Manager', 'staffpanel.php?tool=bonusmanage', 'Site karma bonus manager', 'settings', 5, 1, 1277910813),
(4, 'Non Connectables', 'staffpanel.php?tool=findnotconnectable', 'Find - Pm non-connectable users', 'user', 4, 1, 1277911274),
(5, 'Staff Shout History', 'staffpanel.php?tool=staff_shistory', 'View staff shoutbox history', 'other', 4, 1, 1328723553),
(6, 'Edit Events', 'staffpanel.php?tool=events', 'Edit - Add Freeleech/doubleseed/halfdownload events', 'settings', 6, 1, 1277911847),
(7, 'Site Log', 'staffpanel.php?tool=log', 'View site log', 'other', 4, 1, 1277912694),
(8, 'Poll Manager', 'staffpanel.php?tool=polls_manager', 'Add - Edit site polls', 'settings', 5, 1, 1277912814),
(9, 'Ban Ips', 'staffpanel.php?tool=bans', 'Cached ip ban manager', 'user', 5, 1, 1277912935),
(10, 'Add user', 'staffpanel.php?tool=adduser', 'Add new users from site', 'user', 5, 1, 1277912999),
(11, 'Extra Stats', 'staffpanel.php?tool=stats_extra', 'View graphs of site stats', 'stats', 4, 1, 1277913051),
(12, 'Template Manager', 'staffpanel.php?tool=themes', 'Manage themes', 'settings', 6, 1, 1339372213),
(13, 'Tracker Stats', 'staffpanel.php?tool=stats', 'View uploader and category activity', 'stats', 4, 1, 1277913435),
(14, 'Shoutbox History', 'staffpanel.php?tool=shistory', 'View shout history', 'other', 4, 1, 1277913521),
(15, 'Backup Db', 'staffpanel.php?tool=backup', 'Manual Mysql Database Back Up', 'other', 6, 1, 1277913720),
(16, 'Usersearch', 'staffpanel.php?tool=usersearch', 'Announcement system + Usersearch', 'user', 4, 1, 1277913916),
(17, 'Mysql Stats', 'staffpanel.php?tool=mysql_stats', 'Mysql server stats', 'other', 4, 1, 1277914654),
(18, 'Failed Logins', 'staffpanel.php?tool=failedlogins', 'Clear Failed Logins', 'user', 4, 1, 1277914881),
(19, 'Uploader Applications', 'staffpanel.php?tool=uploadapps&action=app', 'Manage Uploader Applications', 'user', 4, 1, 1325807155),
(20, 'Inactive Users', 'staffpanel.php?tool=inactive', 'Manage inactive users', 'user', 5, 1, 1277915991),
(21, 'Reset Passwords', 'staffpanel.php?tool=reset', 'Reset lost passwords', 'user', 6, 1, 1277916104),
(22, 'Forum Manager', 'staffpanel.php?tool=forum_manage', 'Forum admin and management', 'settings', 6, 1, 1277916172),
(23, 'Overforum Manager', 'staffpanel.php?tool=over_forums', 'Over Forum admin and management', 'settings', 6, 1, 1277916240),
(24, 'Edit Categories', 'staffpanel.php?tool=categories', 'Manage site categories', 'settings', 6, 1, 1277916351),
(25, 'Reputation Admin', 'staffpanel.php?tool=reputation_ad', 'Reputation system admin', 'settings', 6, 1, 1277916398),
(26, 'Reputation Settings', 'staffpanel.php?tool=reputation_settings', 'Manage reputation settings', 'settings', 6, 1, 1277916443),
(27, 'News Admin', 'staffpanel.php?tool=news&mode=news', 'Add - Edit site news', 'settings', 4, 1, 1277916501),
(28, 'Freeleech Manage', 'staffpanel.php?tool=freeleech', 'Manage site wide freeleech', 'settings', 5, 1, 1277916603),
(29, 'Freeleech Users', 'staffpanel.php?tool=freeusers', 'View freeleech users', 'stats', 4, 1, 1277916636),
(30, 'Site Donations', 'staffpanel.php?tool=donations', 'View all/current site donations', 'stats', 6, 1, 1277916690),
(31, 'View Reports', 'staffpanel.php?tool=reports', 'Respond to site reports', 'other', 4, 1, 1278323407),
(32, 'Delete', 'staffpanel.php?tool=delacct', 'Delete user accounts', 'user', 4, 1, 1278456787),
(33, 'Username change', 'staffpanel.php?tool=namechanger', 'Change usernames here.', 'user', 4, 1, 1278886954),
(34, 'Blacklist', 'staffpanel.php?tool=nameblacklist', 'Control username blacklist.', 'settings', 5, 1, 1279054005),
(35, 'System Overview', 'staffpanel.php?tool=system_view', 'Monitor load averages and view phpinfo', 'other', 6, 1, 1277910147),
(36, 'Snatched Overview', 'staffpanel.php?tool=snatched_torrents', 'View all snatched torrents', 'stats', 4, 1, 1277910147),
(37, 'Banned emails.', 'staffpanel.php?tool=bannedemails', 'Manage banned emails.', 'settings', 4, 1, 1333817312),
(38, 'Data Reset', 'staffpanel.php?tool=datareset', 'Reset download stats for nuked torrents', 'user', 5, 1, 1277910147),
(39, 'Dupe Ip Check', 'staffpanel.php?tool=ipcheck', 'Check duplicate ips', 'stats', 4, 1, 1277910147),
(40, 'Lottery', 'lottery.php', 'Configure lottery', 'settings', 5, 1, 1282824272),
(41, 'Group Pm', 'staffpanel.php?tool=grouppm', 'Send grouped pms', 'user', 4, 1, 1282838663),
(42, 'Client Ids', 'staffpanel.php?tool=allagents', 'View all client id', 'stats', 4, 1, 1283592994),
(43, 'Forum Config', 'staffpanel.php?tool=forum_config', 'Configure forums', 'settings', 6, 1, 1284303053),
(44, 'Sysop log', 'staffpanel.php?tool=sysoplog', 'View staff actions', 'other', 6, 1, 1284686084),
(45, 'Server Load', 'staffpanel.php?tool=load', 'View current server load', 'other', 4, 1, 1284900585),
(46, 'Promotions', 'promo.php', 'Add new signup promotions', 'settings', 5, 1, 1286231384),
(47, 'Account Manage', 'staffpanel.php?tool=acpmanage', 'Account manager - Conifrm pending users', 'stats', 5, 1, 1289950651),
(48, 'Block Manager', 'staffpanel.php?tool=block.settings', 'Manage Global site block settings', 'settings', 4, 1, 1292185077),
(49, 'Advanced Mega Search', 'staffpanel.php?tool=mega_search', 'Search by ip, invite code, username', 'user', 4, 1, 1292333576),
(50, 'Warnings', 'staffpanel.php?tool=warn&mode=warn', 'Warning Management', 'stats', 4, 1, 1294788655),
(51, 'Leech Warnings', 'staffpanel.php?tool=leechwarn', 'Leech Warning Management', 'stats', 4, 1, 1294794876),
(52, 'Hnr Warnings', 'staffpanel.php?tool=hnrwarn', 'Hit And Run Warning Management', 'stats', 5, 1, 1294794904),
(53, 'Site Peers', 'staffpanel.php?tool=view_peers', 'Site Peers Overview', 'stats', 4, 1, 1296099600),
(54, 'Top Uploaders', 'staffpanel.php?tool=uploader_info', 'View site top uploaders', 'stats', 4, 1, 1297907345),
(55, 'Watched User', 'staffpanel.php?tool=watched_users', 'Manage all watched users here', 'user', 4, 1, 1321020749),
(56, 'Paypal Settings', 'staffpanel.php?tool=paypal_settings', 'Adjust global paypal settings here', 'settings', 6, 1, 1304288197),
(57, 'Update staff arrays - *Member must be offline*', 'staffpanel.php?tool=staff_config', 'Hit once to update allowed staff arrays after member promotion', 'settings', 6, 1, 1330807776),
(58, 'Site Settings', 'staffpanel.php?tool=site_settings', 'Adjust site settings here', 'settings', 6, 1, 1304422497),
(59, 'Hit and run manager', 'staffpanel.php?tool=hit_and_run_settings', 'Manage all hit and run settings here', 'settings', 6, 1, 1373110790),
(60, 'Opcode Manage', 'staffpanel.php?tool=op', 'View Opcode manager', 'other', 6, 1, 1305728681),
(61, 'Memcache Manage', 'staffpanel.php?tool=memcache', 'View memcache manager', 'other', 6, 1, 1305728711),
(62, 'Edit Moods', 'staffpanel.php?tool=edit_moods', 'Edit site usermoods here', 'user', 4, 1, 1308914441),
(63, 'Search Cloud Manage', 'staffpanel.php?tool=cloudview', 'Manage searchcloud entries', 'settings', 4, 1, 1311359588),
(64, 'Mass Bonus Manager', 'staffpanel.php?tool=mass_bonus_for_members', 'MassUpload, MassSeedbonus, MassFreeslot, MassInvite', 'settings', 6, 1, 1311882635),
(65, 'Hit And Runs', 'staffpanel.php?tool=hit_and_run', 'View All Hit And Runs', 'stats', 4, 1, 1312682819),
(66, 'View Possible Cheats', 'staffpanel.php?tool=cheaters', 'View All Cheat Information', 'stats', 4, 1, 1312682871),
(67, 'Cleanup Manager', 'staffpanel.php?tool=cleanup_manager', 'Clean up interval manager', 'settings', 6, 1, 1315001255),
(68, 'Deathrow', 'staffpanel.php?tool=deathrow', 'Torrents on Deathrow', 'user', 4, 1, 1394313792),
(69, 'Referrers', 'staffpanel.php?tool=referrers', 'View referals here', 'stats', 4, 1, 1362000677),
(70, 'Class Configurations', 'staffpanel.php?tool=class_config', 'Configure site user groups', 'settings', 6, 1, 1366566489),
(71, 'Class Promotions', 'staffpanel.php?tool=class_promo', 'Set Promotion Critera', 'settings', 6, 1, 1396513263),
(72, 'Comment viewer', 'staffpanel.php?tool=comments', 'Comment overview page', 'user', 4, 1, 1403735418),
(73, 'Moderated torrents', 'staffpanel.php?tool=modded_torrents', 'Manage moderated torrents here', 'other', 4, 1, 1406722110);

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `regusers` int(10) unsigned NOT NULL DEFAULT '0',
  `unconusers` int(10) unsigned NOT NULL DEFAULT '0',
  `torrents` int(10) unsigned NOT NULL DEFAULT '0',
  `seeders` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentstoday` int(10) unsigned NOT NULL DEFAULT '0',
  `donors` int(10) unsigned NOT NULL DEFAULT '0',
  `unconnectables` int(10) unsigned NOT NULL DEFAULT '0',
  `forumtopics` int(10) unsigned NOT NULL DEFAULT '0',
  `forumposts` int(10) unsigned NOT NULL DEFAULT '0',
  `numactive` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentsmonth` int(10) unsigned NOT NULL DEFAULT '0',
  `gender_na` int(10) unsigned NOT NULL DEFAULT '1',
  `gender_male` int(10) unsigned NOT NULL DEFAULT '1',
  `gender_female` int(10) unsigned NOT NULL DEFAULT '1',
  `powerusers` int(10) unsigned NOT NULL DEFAULT '1',
  `disabled` int(10) unsigned NOT NULL DEFAULT '1',
  `uploaders` int(10) unsigned NOT NULL DEFAULT '1',
  `moderators` int(10) unsigned NOT NULL DEFAULT '1',
  `administrators` int(10) unsigned NOT NULL DEFAULT '1',
  `sysops` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `stats`
--

INSERT INTO `stats` (`id`, `regusers`, `unconusers`, `torrents`, `seeders`, `leechers`, `torrentstoday`, `donors`, `unconnectables`, `forumtopics`, `forumposts`, `numactive`, `torrentsmonth`, `gender_na`, `gender_male`, `gender_female`, `powerusers`, `disabled`, `uploaders`, `moderators`, `administrators`, `sysops`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `stylesheets`
--

CREATE TABLE IF NOT EXISTS `stylesheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `name` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `stylesheets`
--

INSERT INTO `stylesheets` (`id`, `uri`, `name`) VALUES
(1, '1.css', 'V3 Default');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `subscriptions`
--

-- --------------------------------------------------------

--
-- Table structure for table `subtitles`
--

CREATE TABLE IF NOT EXISTS `subtitles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `filename` varchar(36) CHARACTER SET utf8 DEFAULT NULL,
  `imdb` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lang` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `comment` text CHARACTER SET utf8,
  `fps` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `poster` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `cds` int(3) NOT NULL DEFAULT '0',
  `hits` int(10) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `owner` int(10) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `subtitles`
--

-- --------------------------------------------------------

--
-- Table structure for table `thanks`
--

CREATE TABLE IF NOT EXISTS `thanks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrentid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `thanks`
--

-- --------------------------------------------------------

--
-- Table structure for table `thankyou`
--

CREATE TABLE IF NOT EXISTS `thankyou` (
  `tid` bigint(10) NOT NULL AUTO_INCREMENT,
  `uid` bigint(10) NOT NULL DEFAULT '0',
  `torid` bigint(10) NOT NULL DEFAULT '0',
  `thank_date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `thankyou`
--

-- --------------------------------------------------------

--
-- Table structure for table `thumbsup`
--

CREATE TABLE IF NOT EXISTS `thumbsup` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('torrents','posts','comments','users') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'torrents',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `commentid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `thumbsup`
--

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_name` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `locked` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `poll_id` int(10) unsigned NOT NULL DEFAULT '0',
  `num_ratings` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_desc` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `post_count` int(10) unsigned NOT NULL DEFAULT '0',
  `first_post` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('deleted','recycled','ok') CHARACTER SET utf8 NOT NULL DEFAULT 'ok',
  `main_forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `anonymous` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `userid` (`user_id`),
  KEY `subject` (`topic_name`),
  KEY `lastpost` (`last_post`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `topics`
--

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` binary(20) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `save_as` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `search_text` text CHARACTER SET utf8,
  `descr` text CHARACTER SET utf8,
  `ori_descr` text CHARACTER SET utf8,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `type` enum('single','multi') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'single',
  `numfiles` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `times_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `seeders` int(10) unsigned NOT NULL DEFAULT '0',
  `last_action` int(11) NOT NULL DEFAULT '0',
  `visible` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `banned` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `num_ratings` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `nfo` text CHARACTER SET utf8,
  `client_created_by` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unknown',
  `free` int(11) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','fly','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `anonymous` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `url` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `checked_by` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `points` int(10) NOT NULL DEFAULT '0',
  `allow_comments` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `poster` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `nuked` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `nukereason` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `last_reseed` int(11) NOT NULL DEFAULT '0',
  `release_group` enum('scene','p2p','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `subs` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `vip` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `newgenre` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `pretime` int(11) NOT NULL DEFAULT '0',
  `bump` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `request` int(10) unsigned NOT NULL DEFAULT '0',
  `offer` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `thanks` int(10) NOT NULL DEFAULT '0',
  `description` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `youtube` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `tags` text CHARACTER SET utf8,
  `recommended` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `silver` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_when` int(11) NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `freetorrent` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `owner` (`owner`),
  KEY `visible` (`visible`),
  KEY `category_visible` (`category`),
  KEY `newgenre` (`newgenre`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `torrents`
--

-- --------------------------------------------------------

--
-- Table structure for table `uploadapp`
--

CREATE TABLE IF NOT EXISTS `uploadapp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `applied` int(11) NOT NULL DEFAULT '0',
  `speed` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `offer` longtext CHARACTER SET utf8,
  `reason` longtext CHARACTER SET utf8,
  `sites` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `sitenames` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
  `scene` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `creating` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `seeding` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `connectable` enum('yes','no','pending') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `status` enum('accepted','rejected','pending') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `moderator` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `comment` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users` (`userid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `uploadapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `passhash` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `secret` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `torrent_pass` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `status` enum('pending','confirmed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `added` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_access` int(11) NOT NULL DEFAULT '0',
  `curr_ann_last_check` int(10) unsigned NOT NULL DEFAULT '0',
  `curr_ann_id` int(10) unsigned NOT NULL DEFAULT '0',
  `editsecret` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `privacy` enum('strong','normal','low') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal',
  `stylesheet` int(10) NOT NULL DEFAULT '1',
  `info` text CHARACTER SET utf8,
  `acceptpms` enum('yes','friends','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `ip` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `override_class` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `language` int(11) NOT NULL DEFAULT '1',
  `avatar` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `av_w` smallint(3) unsigned NOT NULL DEFAULT '0',
  `av_h` smallint(3) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `country` int(10) unsigned NOT NULL DEFAULT '0',
  `notifs` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `modcomment` text CHARACTER SET utf8,
  `enabled` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `donor` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `warned` int(11) NOT NULL DEFAULT '0',
  `torrentsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `deletepms` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `savepms` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `reputation` int(10) NOT NULL DEFAULT '10',
  `time_offset` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `dst_in_use` tinyint(1) NOT NULL DEFAULT '0',
  `auto_correct_dst` tinyint(1) NOT NULL DEFAULT '1',
  `show_shout` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `shoutboxbg` enum('1','2','3','4') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '1',
  `chatpost` int(11) NOT NULL DEFAULT '1',
  `smile_until` int(10) NOT NULL DEFAULT '0',
  `seedbonus` decimal(10,1) NOT NULL DEFAULT '200.0',
  `bonuscomment` text CHARACTER SET utf8,
  `vip_added` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `vip_until` int(10) NOT NULL DEFAULT '0',
  `freeslots` int(11) unsigned NOT NULL DEFAULT '5',
  `free_switch` int(11) unsigned NOT NULL DEFAULT '0',
  `invites` int(10) unsigned NOT NULL DEFAULT '1',
  `invitedby` int(10) unsigned NOT NULL DEFAULT '0',
  `invite_rights` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `anonymous` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `uploadpos` int(11) NOT NULL DEFAULT '1',
  `forumpost` int(11) NOT NULL DEFAULT '1',
  `downloadpos` int(11) NOT NULL DEFAULT '1',
  `immunity` int(11) NOT NULL DEFAULT '0',
  `leechwarn` int(11) NOT NULL DEFAULT '0',
  `disable_reason` text CHARACTER SET utf8,
  `clear_new_tag_manually` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `last_browse` int(11) NOT NULL DEFAULT '0',
  `sig_w` smallint(3) unsigned NOT NULL DEFAULT '0',
  `sig_h` smallint(3) unsigned NOT NULL DEFAULT '0',
  `signatures` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `signature` varchar(225) CHARACTER SET utf8 DEFAULT NULL,
  `forum_access` int(11) NOT NULL DEFAULT '0',
  `forum_sort` enum('ASC', 'DESC') NOT NULL DEFAULT 'DESC',
  `highspeed` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `hnrwarn` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `hit_and_run_total` int(9) DEFAULT '0',
  `donoruntil` int(11) unsigned NOT NULL DEFAULT '0',
  `donated` int(3) NOT NULL DEFAULT '0',
  `total_donated` decimal(8,2) NOT NULL DEFAULT '0.00',
  `vipclass_before` int(10) NOT NULL DEFAULT '0',
  `parked` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `passhint` int(10) unsigned NOT NULL,
  `hintanswer` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `avatarpos` int(11) NOT NULL DEFAULT '1',
  `support` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `supportfor` text CHARACTER SET utf8,
  `language_new` int(11) NOT NULL DEFAULT '1',
  `sendpmpos` int(11) NOT NULL DEFAULT '1',
  `invitedate` int(11) NOT NULL DEFAULT '0',
  `invitees` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `invite_on` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `subscription_pm` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `gender` enum('Male','Female','NA') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NA',
  `anonymous_until` int(10) NOT NULL DEFAULT '0',
  `viewscloud` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `tenpercent` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `avatars` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `offavatar` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `pirate` int(11) unsigned NOT NULL DEFAULT '0',
  `king` int(11) unsigned NOT NULL DEFAULT '0',
  `hidecur` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `ssluse` int(1) NOT NULL DEFAULT '1',
  `signature_post` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `forum_post` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `avatar_rights` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `offensive_avatar` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `view_offensive_avatar` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `paranoia` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `google_talk` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `msn` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `aim` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `yahoo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `icq` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `show_email` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `parked_until` int(10) NOT NULL DEFAULT '0',
  `gotgift` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `hash1` varchar(96) CHARACTER SET utf8 DEFAULT NULL,
  `suspended` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `bjwins` int(10) NOT NULL DEFAULT '0',
  `bjlosses` int(10) NOT NULL DEFAULT '0',
  `warn_reason` text CHARACTER SET utf8,
  `onirc` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `irctotal` bigint(20) unsigned NOT NULL DEFAULT '0',
  `birthday` date DEFAULT '0000-00-00',
  `got_blocks` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `last_access_numb` bigint(30) NOT NULL DEFAULT '0',
  `onlinetime` bigint(30) NOT NULL DEFAULT '0',
  `pm_on_delete` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `commentpm` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `split` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `browser` text CHARACTER SET utf8,
  `hits` int(10) NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `categorie_icon` int(10) DEFAULT '1',
  `perms` int(11) NOT NULL DEFAULT '0',
  `mood` int(10) NOT NULL DEFAULT '1',
  `got_moods` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `pms_per_page` tinyint(3) unsigned DEFAULT '20',
  `show_pm_avatar` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `watched_user` int(11) NOT NULL DEFAULT '0',
  `watched_user_reason` text CHARACTER SET utf8,
  `staff_notes` text CHARACTER SET utf8,
  `game_access` int(11) NOT NULL DEFAULT '1',
  `where_is` text CHARACTER SET utf8,
  `show_staffshout` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `request_uri` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `browse_icons` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `numuploads` int(10) NOT NULL DEFAULT '0',
  `corrupt` int(10) NOT NULL DEFAULT '0',
  `opt1` int(11) NOT NULL DEFAULT '182927957',
  `opt2` int(11) NOT NULL DEFAULT '224',
  `torrent_pass_version` int(11) NOT NULL DEFAULT '0',
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `wait_time` int(11) NOT NULL DEFAULT '0',
  `peers_limit` int(11) DEFAULT '1000',
  `torrents_limit` int(11) DEFAULT '1000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `ip` (`ip`),
  KEY `uploaded` (`uploaded`),
  KEY `downloaded` (`downloaded`),
  KEY `country` (`country`),
  KEY `last_access` (`last_access`),
  KEY `enabled` (`enabled`),
  KEY `warned` (`warned`),
  KEY `T_Pass` (`torrent_pass`),
  KEY `free_switch` (`free_switch`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------
--
-- Table structure for table `usercomments`
--

CREATE TABLE IF NOT EXISTS `usercomments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8,
  `ori_text` text CHARACTER SET utf8,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `usercomments`
--

-- --------------------------------------------------------

--
-- Table structure for table `userhits`
--

CREATE TABLE IF NOT EXISTS `userhits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `hitid` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `added` (`added`),
  KEY `hitid` (`hitid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `userhits`
--

-- --------------------------------------------------------

--
-- Table structure for table `usersachiev`
--

CREATE TABLE IF NOT EXISTS `usersachiev` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `totalshoutlvl` tinyint(2) NOT NULL DEFAULT '0',
  `username` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `snatchmaster` tinyint(1) NOT NULL DEFAULT '0',
  `invited` int(3) NOT NULL DEFAULT '0',
  `bday` tinyint(1) NOT NULL DEFAULT '0',
  `ul` tinyint(1) NOT NULL DEFAULT '0',
  `inviterach` tinyint(1) NOT NULL DEFAULT '0',
  `forumposts` int(10) NOT NULL DEFAULT '0',
  `postachiev` tinyint(2) NOT NULL DEFAULT '0',
  `avatarset` tinyint(1) NOT NULL DEFAULT '0',
  `avatarach` tinyint(1) NOT NULL DEFAULT '0',
  `stickyup` int(5) NOT NULL DEFAULT '0',
  `stickyachiev` tinyint(1) NOT NULL DEFAULT '0',
  `sigset` tinyint(1) NOT NULL DEFAULT '0',
  `sigach` tinyint(1) NOT NULL DEFAULT '0',
  `corrupt` tinyint(1) NOT NULL DEFAULT '0',
  `dayseed` tinyint(3) NOT NULL DEFAULT '0',
  `sheepyset` tinyint(1) NOT NULL DEFAULT '0',
  `sheepyach` tinyint(1) NOT NULL DEFAULT '0',
  `spentpoints` int(3) NOT NULL DEFAULT '0',
  `achpoints` int(3) NOT NULL DEFAULT '1',
  `forumtopics` int(10) NOT NULL DEFAULT '0',
  `topicachiev` tinyint(2) NOT NULL DEFAULT '0',
  `bonus` tinyint(2) NOT NULL DEFAULT '0',
  `bonusspent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `christmas` tinyint(1) NOT NULL DEFAULT '0',
  `xmasdays` int(2) NOT NULL DEFAULT '0',
  `reqfilled` int(5) NOT NULL DEFAULT '0',
  `reqlvl` tinyint(2) NOT NULL DEFAULT '0',
  `dailyshouts` int(5) NOT NULL DEFAULT '0',
  `dailyshoutlvl` tinyint(2) NOT NULL DEFAULT '0',
  `weeklyshouts` int(5) NOT NULL DEFAULT '0',
  `weeklyshoutlvl` tinyint(2) NOT NULL DEFAULT '0',
  `monthlyshouts` int(5) NOT NULL DEFAULT '0',
  `monthlyshoutlvl` tinyint(2) NOT NULL DEFAULT '0',
  `totalshouts` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `usersachiev`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_blocks`
--

CREATE TABLE IF NOT EXISTS `user_blocks` (
  `userid` int(10) unsigned NOT NULL,
  `index_page` int(10) unsigned NOT NULL DEFAULT '585727',
  `global_stdhead` int(10) unsigned NOT NULL DEFAULT '1023',
  `userdetails_page` bigint(20) unsigned NOT NULL DEFAULT '4294967295',
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_blocks`
--

-- --------------------------------------------------------

--
-- Table structure for table `ustatus`
--

CREATE TABLE IF NOT EXISTS `ustatus` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `last_status` varchar(140) CHARACTER SET utf8 DEFAULT NULL,
  `last_update` int(11) NOT NULL DEFAULT '0',
  `archive` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `ustatus`
--


-- --------------------------------------------------------

--
-- Table structure for table `xbt_announce_log`
--

CREATE TABLE IF NOT EXISTS `xbt_announce_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(10) unsigned NOT NULL DEFAULT '0',
  `port` int(11) NOT NULL DEFAULT '0',
  `event` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob NOT NULL,
  `peer_id` blob NOT NULL,
  `downloaded` bigint(20) NOT NULL,
  `left0` bigint(20) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `useragent` varchar(51) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_client_whitelist`
--

CREATE TABLE IF NOT EXISTS `xbt_client_whitelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `vstring` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_id` (`peer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `xbt_client_whitelist`
--

INSERT INTO `xbt_client_whitelist` (`id`, `peer_id`, `vstring`) VALUES
(1, '-', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `xbt_config`
--

CREATE TABLE IF NOT EXISTS `xbt_config` (
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `xbt_config`
--

INSERT INTO `xbt_config` (`name`, `value`) VALUES
('torrent_pass_private_key', 'MG58LNj5LHHz49A9PKhAkxIH8Aa');

-- --------------------------------------------------------

--
-- Table structure for table `xbt_deny_from_hosts`
--

CREATE TABLE IF NOT EXISTS `xbt_deny_from_hosts` (
  `begin` int(11) NOT NULL,
  `end` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_files`
--

CREATE TABLE IF NOT EXISTS `xbt_files` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `info_hash` blob NOT NULL,
  `leechers` int(11) NOT NULL DEFAULT '0',
  `seeders` int(11) NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL DEFAULT '0',
  `announced_http` int(11) NOT NULL DEFAULT '0',
  `announced_http_compact` int(11) NOT NULL DEFAULT '0',
  `announced_http_no_peer_id` int(11) NOT NULL DEFAULT '0',
  `announced_udp` int(11) NOT NULL DEFAULT '0',
  `scraped_http` int(11) NOT NULL DEFAULT '0',
  `scraped_udp` int(11) NOT NULL DEFAULT '0',
  `started` int(11) NOT NULL DEFAULT '0',
  `stopped` int(11) NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `balance` int(11) NOT NULL DEFAULT '0',
  `freetorrent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xbt_files_users`
--

CREATE TABLE IF NOT EXISTS `xbt_files_users` (
  `fid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `announced` int(11) NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `left` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `leechtime` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seedtime` bigint(20) unsigned NOT NULL DEFAULT '0',
  `upspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `downspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `peer_id` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `completedtime` int(11) unsigned NOT NULL DEFAULT '0',
  `ipa` int(11) unsigned NOT NULL DEFAULT '0',
  `connectable` tinyint(4) NOT NULL DEFAULT '1',
  `mark_of_cain` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `hit_and_run` int(11) NOT NULL DEFAULT '0',
  `started` int(11) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `fid` (`fid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `xbt_files_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `xbt_scrape_log`
--

CREATE TABLE IF NOT EXISTS `xbt_scrape_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(11) NOT NULL,
  `info_hash` blob,
  `uid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Dumping data for table `xbt_scrape_log`
--

-- --------------------------------------------------------
--
-- Table structure for table `xbt_users`
--

CREATE TABLE IF NOT EXISTS `xbt_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `wait_time` int(11) NOT NULL DEFAULT '0',
  `peers_limit` int(11) NOT NULL DEFAULT '0',
  `torrents_limit` int(11) NOT NULL DEFAULT '0',
  `torrent_pass` char(32) CHARACTER SET utf8 NOT NULL,
  `torrent_pass_version` int(11) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `wiki` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `body` longtext CHARACTER SET utf8,
  `userid` int(10) unsigned DEFAULT '0',
  `time` int(11) NOT NULL,
  `lastedit` int(10) unsigned DEFAULT NULL,
  `lastedituser` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;
INSERT INTO `wiki` (`id`, `name`, `body`, `userid`, `time`, `lastedit`, `lastedituser`) VALUES
(1, 'index', '[align=center][size=6]Welcome to the [b]Wiki[/b][/size][/align]', 0, 1228076412, 1281610709, 1);

CREATE TABLE IF NOT EXISTS `staffmessages_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `answeredby` int(10) unsigned NOT NULL DEFAULT '0',
  `answer` text CHARACTER SET utf8,
  `added` int(11) NOT NULL,
  `subject` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `messages` ADD `staff_id` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `staffmessages` ADD `new`  enum('yes','no') NOT NULL default 'no';
