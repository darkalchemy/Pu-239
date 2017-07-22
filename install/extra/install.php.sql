-- MySQL dump 10.13  Distrib 5.7.18-15, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: temp
-- ------------------------------------------------------
-- Server version	5.7.18-15

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!50717 SET @rocksdb_bulk_load_var_name='rocksdb_bulk_load' */;
/*!50717 SELECT COUNT(*) INTO @rocksdb_has_p_s_session_variables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'performance_schema' AND TABLE_NAME = 'session_variables' */;
/*!50717 SET @rocksdb_get_is_supported = IF (@rocksdb_has_p_s_session_variables, 'SELECT COUNT(*) INTO @rocksdb_is_supported FROM performance_schema.session_variables WHERE VARIABLE_NAME=?', 'SELECT 0') */;
/*!50717 PREPARE s FROM @rocksdb_get_is_supported */;
/*!50717 EXECUTE s USING @rocksdb_bulk_load_var_name */;
/*!50717 DEALLOCATE PREPARE s */;
/*!50717 SET @rocksdb_enable_bulk_load = IF (@rocksdb_is_supported, 'SET SESSION rocksdb_bulk_load = 1', 'SET @rocksdb_dummy_bulk_load = 0') */;
/*!50717 PREPARE s FROM @rocksdb_enable_bulk_load */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;

--
-- Table structure for table `ach_bonus`
--

DROP TABLE IF EXISTS `ach_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ach_bonus` (
  `bonus_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_desc` mediumtext COLLATE utf8mb4_unicode_ci,
  `bonus_type` tinyint(4) NOT NULL DEFAULT '0',
  `bonus_do` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`bonus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ach_bonus`
--

LOCK TABLES `ach_bonus` WRITE;
/*!40000 ALTER TABLE `ach_bonus` DISABLE KEYS */;
INSERT INTO `ach_bonus` VALUES (1,'Subtract 10GB From Your Download.',1,'10737418240'),(2,'Subtract 1GB From Your Download.',1,'1073741824'),(3,'Subtract 3GB From Your Download.',1,'3221225472'),(4,'Subtract 5GB From Your Download.',1,'5368709120'),(5,'Subtract 100MB From Your Download.',1,'107374182'),(6,'Subtract 300MB From Your Download.',1,'322122547'),(7,'Subtract 500MB From Your Download.',1,'536870910'),(8,'Subtract 1MB From Your Download.',1,'1073741'),(9,'Add 1GB to your Upload.',2,'1073741824'),(10,'Add 10GB to your Upload.',2,'10737418240'),(11,'Add 3GB to your Upload.',2,'3221225472'),(12,'Add 5GB to your Upload.',2,'5368709120'),(13,'Add 100MB to your Upload.',2,'107374182'),(14,'Add 300MB to your Upload.',2,'322122547'),(15,'Add 500MB to your Upload.',2,'536870910'),(16,'Add 1MB to your Upload.',2,'1073741'),(17,'Add 1 Invite.',3,'1'),(18,'Add 2 Invites.',3,'2'),(19,'Add 100 Bonus Points to your Total.',4,'100'),(20,'Add 200 Bonus Points to your Total.',4,'200'),(21,'Add 500 Bonus Points to your Total.',4,'500'),(22,'Add 750 Bonus Points to your Total.',4,'750'),(23,'Add 1000 Bonus Points to your Total.',4,'1000'),(24,'Add 50 Bonus Points to your Total.',4,'50'),(25,'Add 25 Bonus Points to your Total.',4,'25'),(26,'Add 75 Bonus Points to your Total.',4,'75'),(27,'Add 10 Bonus Points to your Total.',4,'10'),(28,'Nothing',5,'0'),(29,'Nothing',5,'0'),(30,'Nothing',5,'0'),(31,'Nothing',5,'0'),(32,'Nothing',5,'0');
/*!40000 ALTER TABLE `ach_bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `achievementist`
--

DROP TABLE IF EXISTS `achievementist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievementist` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `achievname` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clienticon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `hostname` (`achievname`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievementist`
--

LOCK TABLES `achievementist` WRITE;
/*!40000 ALTER TABLE `achievementist` DISABLE KEYS */;
INSERT INTO `achievementist` VALUES (1,'First Birthday','Been a member for at least 1 year.','birthday1.png'),(2,'Second Birthday','Been a member for a period of at least 2 years.','birthday2.png'),(5,'Third Birthday','Been a member for a period of at least 3 years.','birthday3.png'),(6,'Fourth Birthday','Been a member for a period of at least 4 years.','birthday4.png'),(7,'Fifth Birthday','Been a member for a period of at least 5 years.','birthday5.png'),(8,'Uploader LVL1','Uploaded at least 1 torrent to the site.','ul1.png'),(9,'Uploader LVL2','Uploaded at least 50 torrents to the site.','ul2.png'),(10,'Uploader LVL3','Uploaded at least 100 torrents to the site.','ul3.png'),(11,'Uploader LVL4','Uploaded at least 200 torrents to the site.','ul4.png'),(12,'Uploader LVL5','Uploaded at least 300 torrents to the site.','ul5.png'),(13,'Uploader LVL6','Uploaded at least 500 torrents to the site.','ul6.png'),(14,'Uploader LVL7','Uploaded at least 800 torrents to the site.','ul7.png'),(15,'Uploader LVL8','Uploaded at least 1000 torrents to the site.','ul8.png'),(16,'Uploader LVL9','Uploaded at least 1500 torrents to the site.','ul9.png'),(17,'Uploader LVL10','Uploaded at least 2000 torrents to the site.','ul10.png'),(18,'Inviter LVL1','Invited at least 1 new user to the site.','invite1.png'),(19,'Inviter LVL2','Invited at least 2 new users to the site.','invite2.png'),(20,'Inviter LVL3','Invited at least 3 new users to the site.','invite3.png'),(21,'Inviter LVL4','Invited at least 5 new users to the site.','invite4.png'),(22,'Inviter LVL5','Invited at least 10 new users to the site.','invite5.png'),(23,'Forum Poster LVL1','Made at least 1 post in the forums.','fpost1.png'),(24,'Forum Poster LVL2','Made at least 25 posts in the forums.','fpost2.png'),(25,'Forum Poster LVL3','Made at least 50 posts in the forums.','fpost3.png'),(26,'Forum Poster LVL4','Made at least 100 posts in the forums.','fpost4.png'),(27,'Forum Poster LVL5','Made at least 250 posts in the forums.','fpost5.png'),(28,'Avatar Setter','User has successfully set an avatar on profile settings.','piratesheep.png'),(29,'Old Virginia','At the age of 25 still remains a virgin.  (Custom Achievement.)','virgin.png'),(30,'Forum Poster LVL6','Made at least 500 posts in the forums.','fpost6.png'),(31,'Stick Em Up LVL1','Uploading at least 1 sticky torrent to the site.','sticky1.png'),(32,'Stick Em Up LVL2','Uploading at least 5 sticky torrents to the site.','sticky2.png'),(33,'Stick Em Up LVL3','Uploading at least 10 sticky torrents.','sticky3.png'),(34,'Stick EM Up LVL4','Uploading at least 25 sticky torrents.','sticky4.png'),(35,'Stick EM Up LVL5','Uploading at least 50 sticky torrents.','sticky5.png'),(36,'Gag Da B1tch','Getting gagged like he\'s Adams Man!','gagged.png'),(37,'Signature Setter','User has successfully set a signature on profile settings.','signature.png'),(38,'Corruption Counts','Transferred at least 1 byte of corrupt data incoming.','corrupt.png'),(40,'7 Day Seeder','Seeded a snatched torrent for a total of at least 7 days.','7dayseed.png'),(41,'14 Day Seeder','Seeded a snatched torrent for a total of at least 14 days.','14dayseed.png'),(42,'21 Day Seeder','Seeded a snatched torrent for a total of at least 21 days.','21dayseed.png'),(43,'28 Day Seeder','Seeded a snatched torrent for a total of at least 28 days.','28dayseed.png'),(44,'45 Day Seeder','Seeded a snatched torrent for a total of at least 45 days.','45dayseed.png'),(45,'60 Day Seeder','Seeded a snatched torrent for a total of at least 60 days.','60dayseed.png'),(46,'90 Day Seeder','Seeded a snatched torrent for a total of at least 90 days.','90dayseed.png'),(47,'120 Day Seeder','Seeded a snatched torrent for a total of at least 120 days.','120dayseed.png'),(48,'200 Day Seeder','Seeded a snatched torrent for a total of at least 200 days.','200dayseed.png'),(49,'1 Year Seeder','Seeded a snatched torrent for a total of at least 1 Year.','365dayseed.png'),(50,'Sheep Fondler','User has been caught touching the sheep at least 1 time.','sheepfondler.png'),(51,'Forum Topic Starter LVL1','Started at least 1 topic in the forums.','ftopic1.png'),(52,'Forum Topic Starter LVL2','Started at least 10 topics in the forums.','ftopic2.png'),(53,'Forum Topic Starter LVL3','Started at least 25 topics in the forums.','ftopic3.png'),(55,'Forum Topic Starter LVL4','Started at least 50 topics in the forums.','ftopic4.png'),(57,'Forum Topic Starter LVL5','Started at least 75 topics in the forums.','ftopic5.png'),(58,'Bonus Banker LVL1','Earned at least 1 bonus point.','bonus1.png'),(60,'Bonus Banker LVL2','Earned at least 100 bonus points.','bonus2.png'),(61,'Bonus Banker LVL3','Earned at least 500 bonus points.','bonus3.png'),(63,'Bonus Banker LVL4','Earned at least 1000 bonus points.','bonus4.png'),(65,'Bonus Banker LVL5','Earned at least 2000 bonus points.','bonus5.png'),(66,'Bonus Banker LVL6','Earned at least 5000 bonus points.','bonus6.png'),(68,'Bonus Banker LVL7','Earned at least 10000 bonus points.','bonus7.png'),(70,'Bonus Banker LVL8','Earned at least 30000 bonus points.','bonus9.png'),(71,'Bonus Banker LVL9','Earned at least 70000 bonus points.','bonus10.png'),(72,'Bonus Banker LVL10','Earned at least 100000 bonus points.','bonus8.png'),(73,'Bonus Banker LVL11','Earned at least 1000000 bonus points.','bonus11.png'),(74,'Christmas Achievement','User has found the Christmas Achievement in the advent calendar page.','christmas.png'),(75,'Advent Playa','Played the Advent Calendar all 25 days straight.','xmasdays.png'),(76,'Request Filler LVL1','Filled at least 1 request from the request page.','reqfiller1.png'),(77,'Request Filler LVL2','Filled at least 5 requests from the request page.','reqfiller2.png'),(78,'Request Filler LVL3','Filled at least 10 requests from the request page.','reqfiller3.png'),(79,'Request Filler LVL4','Filled at least 25 requests from the request page.','reqfiller4.png'),(80,'Request Filler LVL5','Filled at least 50 requests from the request page.','reqfiller5.png'),(81,'Adam Punker','Officially Punked Adam in the proper forum thread.','adampnkr.png'),(82,'Shout Spammer LVL1','Made at least 10 posts to the shoutbox today.','spam1.png'),(83,'Shout Spammer LVL2','Made at least 25 posts to the shoutbox today.','spam2.png'),(84,'Shout Spammer LVL3','Made at least 50 posts to the shoutbox today.','spam3.png'),(85,'Shout Spammer LVL4','Made at least 75 posts to the shoutbox today.','spam4.png'),(86,'Shout Spammer LVL5','Made at least 100 posts to the shoutbox today.','spam5.png');
/*!40000 ALTER TABLE `achievementist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(5) NOT NULL DEFAULT '0',
  `achievement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `achievementid` int(5) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievements`
--

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement_main`
--

DROP TABLE IF EXISTS `announcement_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_main` (
  `main_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `expires` int(11) NOT NULL DEFAULT '0',
  `sql_query` mediumtext COLLATE utf8mb4_unicode_ci,
  `subject` mediumtext COLLATE utf8mb4_unicode_ci,
  `body` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`main_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_main`
--

LOCK TABLES `announcement_main` WRITE;
/*!40000 ALTER TABLE `announcement_main` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement_process`
--

DROP TABLE IF EXISTS `announcement_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_process` (
  `process_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `main_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`process_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_process`
--

LOCK TABLES `announcement_process` WRITE;
/*!40000 ALTER TABLE `announcement_process` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  `extension` enum('zip','rar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zip',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `times_downloaded` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avps`
--

DROP TABLE IF EXISTS `avps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avps` (
  `arg` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value_s` mediumtext COLLATE utf8mb4_unicode_ci,
  `value_i` int(11) NOT NULL DEFAULT '0',
  `value_u` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`arg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avps`
--

LOCK TABLES `avps` WRITE;
/*!40000 ALTER TABLE `avps` DISABLE KEYS */;
INSERT INTO `avps` VALUES ('bestfilmofweek','0',1402495922,20),('inactivemail','1',1341778326,1),('last24','0',50,1303875421),('loadlimit','0.39-1404324894',0,0),('sitepot','0',0,1359295634);
/*!40000 ALTER TABLE `avps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bannedemails`
--

DROP TABLE IF EXISTS `bannedemails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bannedemails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL,
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bannedemails`
--

LOCK TABLES `bannedemails` WRITE;
/*!40000 ALTER TABLE `bannedemails` DISABLE KEYS */;
INSERT INTO `bannedemails` VALUES (1,1282299331,1,'Fake provider','*@emailias.com'),(2,1282299331,1,'Fake provider','*@e4ward.com'),(3,1282299331,1,'Fake provider','*@dumpmail.de'),(4,1282299331,1,'Fake provider','*@dontreg.com'),(5,1282299331,1,'Fake provider','*@disposeamail.com'),(6,1282299331,1,'Fake provider','*@antispam24.de'),(7,1282299331,1,'Fake provider','*@trash-mail.de'),(8,1282299331,1,'Fake provider','*@spambog.de'),(9,1282299331,1,'Fake provider','*@spambog.com'),(10,1282299331,1,'Fake provider','*@discardmail.com'),(11,1282299331,1,'Fake provider','*@discardmail.de'),(12,1282299331,1,'Fake provider','*@mailinator.com'),(13,1282299331,1,'Fake provider','*@wuzup.net'),(14,1282299331,1,'Fake provider','*@junkmail.com'),(15,1282299331,1,'Fake provider','*@clarkgriswald.net'),(16,1282299331,1,'Fake provider','*@2prong.com'),(17,1282299331,1,'Fake provider','*@jrwilcox.com'),(18,1282299331,1,'Fake provider','*@10minutemail.com'),(19,1282299331,1,'Fake provider','*@pookmail.com'),(20,1282299331,1,'Fake provider','*@golfilla.info'),(21,1282299331,1,'Fake provider','*@afrobacon.com'),(22,1282299331,1,'Fake provider','*@senseless-entertainment.com'),(23,1282299331,1,'Fake provider','*@put2.net'),(24,1282299331,1,'Fake provider','*@temporaryinbox.com'),(25,1282299331,1,'Fake provider','*@slaskpost.se'),(26,1282299331,1,'Fake provider','*@haltospam.com'),(27,1282299331,1,'Fake provider','*@h8s.org'),(28,1282299331,1,'Fake provider','*@ipoo.org'),(29,1282299331,1,'Fake provider','*@oopi.org'),(30,1282299331,1,'Fake provider','*@poofy.org'),(31,1282299331,1,'Fake provider','*@jetable.org'),(32,1282299331,1,'Fake provider','*@kasmail.com'),(33,1282299331,1,'Fake provider','*@mail-filter.com'),(34,1282299331,1,'Fake provider','*@maileater.com'),(35,1282299331,1,'Fake provider','*@mailexpire.com'),(36,1282299331,1,'Fake provider','*@mailnull.com'),(37,1282299331,1,'Fake provider','*@mailshell.com'),(38,1282299331,1,'Fake provider','*@mymailoasis.com'),(39,1282299331,1,'Fake provider','*@mytrashmail.com'),(40,1282299331,1,'Fake provider','*@mytrashmail.net'),(41,1282299331,1,'Fake provider','*@shortmail.net'),(42,1282299331,1,'Fake provider','*@sneakemail.com'),(43,1282299331,1,'Fake provider','*@sofort-mail.de'),(44,1282299331,1,'Fake provider','*@spamcon.org'),(45,1282299331,1,'Fake provider','*@spamday.com'),(46,1282299331,1,'fake provider','*@spamex.com'),(47,1282299307,1,'fake provider','*@spamgourmet.com'),(48,1282299289,1,'fake provider','*@spamhole.com'),(49,1282299331,1,'Fake provider','*@spammotel.com'),(50,1282299331,1,'Fake provider','*@tempemail.net'),(51,1282299331,1,'Fake provider','*@tempinbox.com'),(52,1282299331,1,'Fake provider','*@throwaway.de'),(53,1282299331,1,'Fake provider','*@woodyland.org'),(54,1282299331,1,'Fake provider','*@trbvm.com');
/*!40000 ALTER TABLE `bannedemails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL,
  `addedby` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first` bigint(11) DEFAULT '0',
  `last` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `first_last` (`first`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bans`
--

LOCK TABLES `bans` WRITE;
/*!40000 ALTER TABLE `bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack`
--

DROP TABLE IF EXISTS `blackjack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack`
--

LOCK TABLES `blackjack` WRITE;
/*!40000 ALTER TABLE `blackjack` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack1`
--

DROP TABLE IF EXISTS `blackjack1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack1` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack1`
--

LOCK TABLES `blackjack1` WRITE;
/*!40000 ALTER TABLE `blackjack1` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack2`
--

DROP TABLE IF EXISTS `blackjack2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack2` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack2`
--

LOCK TABLES `blackjack2` WRITE;
/*!40000 ALTER TABLE `blackjack2` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack3`
--

DROP TABLE IF EXISTS `blackjack3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack3` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack3`
--

LOCK TABLES `blackjack3` WRITE;
/*!40000 ALTER TABLE `blackjack3` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack4`
--

DROP TABLE IF EXISTS `blackjack4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack4` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack4`
--

LOCK TABLES `blackjack4` WRITE;
/*!40000 ALTER TABLE `blackjack4` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack4` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack5`
--

DROP TABLE IF EXISTS `blackjack5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack5` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack5`
--

LOCK TABLES `blackjack5` WRITE;
/*!40000 ALTER TABLE `blackjack5` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack5` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack6`
--

DROP TABLE IF EXISTS `blackjack6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack6` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack6`
--

LOCK TABLES `blackjack6` WRITE;
/*!40000 ALTER TABLE `blackjack6` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack6` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack7`
--

DROP TABLE IF EXISTS `blackjack7`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack7` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack7`
--

LOCK TABLES `blackjack7` WRITE;
/*!40000 ALTER TABLE `blackjack7` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack7` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack8`
--

DROP TABLE IF EXISTS `blackjack8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack8` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `status` enum('playing','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'playing',
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `dealer_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(11) DEFAULT '0',
  `gameover` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ddown` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`userid`),
  KEY `status_gameover_date` (`status`,`gameover`,`date`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `ddown` (`ddown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack8`
--

LOCK TABLES `blackjack8` WRITE;
/*!40000 ALTER TABLE `blackjack8` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack8` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blackjack_history`
--

DROP TABLE IF EXISTS `blackjack_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blackjack_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(11) DEFAULT '0',
  `game` int(11) NOT NULL DEFAULT '0',
  `player1_userid` int(11) NOT NULL DEFAULT '0',
  `player1_points` int(11) NOT NULL DEFAULT '0',
  `player1_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `player2_points` int(11) NOT NULL DEFAULT '0',
  `player2_userid` int(11) NOT NULL DEFAULT '0',
  `player2_cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game` (`game`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blackjack_history`
--

LOCK TABLES `blackjack_history` WRITE;
/*!40000 ALTER TABLE `blackjack_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `blackjack_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocks`
--

DROP TABLE IF EXISTS `blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `blockid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks`
--

LOCK TABLES `blocks` WRITE;
/*!40000 ALTER TABLE `blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonus`
--

DROP TABLE IF EXISTS `bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bonus` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `bonusname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` decimal(10,1) NOT NULL DEFAULT '0.0',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `art` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menge` bigint(20) unsigned NOT NULL DEFAULT '0',
  `pointspool` decimal(10,1) NOT NULL DEFAULT '1.0',
  `enabled` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes' COMMENT 'This will determined a switch if the bonus is enabled or not! enabled by default',
  `minpoints` decimal(10,1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonus`
--

LOCK TABLES `bonus` WRITE;
/*!40000 ALTER TABLE `bonus` DISABLE KEYS */;
INSERT INTO `bonus` VALUES (1,'1.0GB Uploaded',275.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',1073741824,1.0,'yes',275.0),(2,'2.5GB Uploaded',350.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',2684354560,1.0,'yes',350.0),(3,'5GB Uploaded',550.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',5368709120,1.0,'yes',550.0),(4,'3 Invites',650.0,'With enough bonus points acquired, you are able to exchange them for a few invites. The points are then removed from your Bonus Bank and the invitations are added to your invites amount.','invite',3,1.0,'yes',650.0),(5,'Custom Title!',50.0,'For only 50.0 Karma Bonus Points you can buy yourself a custom title. the only restrictions are no foul or offensive language or userclass can be entered. The points are then removed from your Bonus Bank and your special title is changed to the title of your choice','title',1,1.0,'yes',50.0),(6,'VIP Status',5000.0,'With enough bonus points acquired, you can buy yourself VIP status for one month. The points are then removed from your Bonus Bank and your status is changed.','class',1,1.0,'yes',5000.0),(7,'Give A Karma Gift',100.0,'Well perhaps you dont need the upload credit, but you know somebody that could use the Karma boost! You are now able to give your Karma credits as a gift! The points are then removed from your Bonus Bank and added to the account of a user of your choice!\r\n\r\nAnd they recieve a PM with all the info as well as who it came from...','gift_1',1073741824,1.0,'yes',100.0),(8,'Custom Smilies',300.0,'With enough bonus points acquired, you can buy yourself a set of custom smilies for one month! The points are then removed from your Bonus Bank and with a click of a link, your new smilies are available whenever you post or comment!','smile',1,1.0,'yes',300.0),(9,'Remove Warning',1000.0,'With enough bonus points acquired... So you have been naughty... tsk tsk :P Yep now for the Low Low price of only 1000 points you can have that warning taken away lol.!','warning',1,1.0,'yes',1000.0),(10,'Ratio Fix',500.0,'With enough bonus points acquired, you can bring the ratio of one torrent to a 1 to 1 ratio! The points are then removed from your Bonus Bank and your status is changed.','ratio',1,1.0,'yes',500.0),(11,'FreeLeech',30000.0,'The Ultimate exchange if you have over 30000 Points - Make the tracker freeleech for everyone for 3 days: Upload will count but no download.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!','freeleech',1,5000.0,'yes',1.0),(12,'Doubleupload',30000.0,'The ultimate exchange if you have over 30000 points - Make the tracker double upload for everyone for 3 days: Upload will count double.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!','doubleup',1,1000.0,'yes',1.0),(13,'Halfdownload',30000.0,'The ultimate exchange if you have over 30000 points - Make the tracker Half Download for everyone for 3 days: Download will count only half.\r\nIf you dont have enough points you can donate certain amount of your points until it accumulates. Everybodys karma counts!','halfdown',1,1000.0,'yes',1.0),(14,'1.0GB Download Removal',150.0,'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.','traffic2',1073741824,1.0,'yes',150.0),(15,'2.5GB Download Removal',300.0,'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.','traffic2',2684354560,1.0,'yes',300.0),(16,'5GB Download Removal',500.0,'With enough bonus points acquired, you are able to exchange them for a Download Credit Removal. The points are then removed from your Bonus Bank and the download credit is removed from your total downloaded amount.','traffic2',5368709120,1.0,'yes',500.0),(17,'Anonymous Profile',750.0,'With enough bonus points acquired, you are able to exchange them for Anonymous profile for 14 days. The points are then removed from your Bonus Bank and the Anonymous switch will show on your profile.','anonymous',1,1.0,'yes',750.0),(18,'Freeleech for 1 Year',80000.0,'With enough bonus points acquired, you are able to exchange them for Freelech for one year for yourself. The points are then removed from your Bonus Bank and the freeleech will be enabled on your account.','freeyear',1,1.0,'yes',80000.0),(19,'3 Freeleech Slots',1000.0,'With enough bonus points acquired, you are able to exchange them for some Freeleech Slots. The points are then removed from your Bonus Bank and the slots are added to your free slots amount.','freeslots',3,0.0,'yes',1000.0),(20,'200 Bonus Points - Invite trade-in',1.0,'If you have 1 invite and dont use them click the button to trade them in for 200 Bonus Points.','itrade',200,0.0,'yes',0.0),(21,'Freeslots - Invite trade-in',1.0,'If you have 1 invite and dont use them click the button to trade them in for 2 Free Slots.','itrade2',2,0.0,'yes',0.0),(22,'Pirate Rank for 2 weeks',50000.0,'With enough bonus points acquired, you are able to exchange them for Pirates status and Freeleech for 2 weeks. The points are then removed from your Bonus Bank and the Pirate icon will be displayed throughout, freeleech will then be enabled on your account.','pirate',1,1.0,'yes',50000.0),(23,'King Rank for 1 month',70000.0,'With enough bonus points acquired, you are able to exchange them for Kings status and Freeleech for 1 month. The points are then removed from your Bonus Bank and the King icon will be displayed throughout,  freeleech will then be enabled on your account.','king',1,1.0,'yes',70000.0),(24,'10GB Uploaded',1000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',10737418240,0.0,'yes',1000.0),(25,'25GB Uploaded',2000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',26843545600,0.0,'yes',2000.0),(26,'50GB Uploaded',4000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',53687091200,0.0,'yes',4000.0),(27,'100GB Uploaded',8000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',107374182400,0.0,'yes',8000.0),(28,'520GB Uploaded',40000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',558345748480,0.0,'yes',40000.0),(29,'1TB Uploaded',80000.0,'With enough bonus points acquired, you are able to exchange them for an Upload Credit. The points are then removed from your Bonus Bank and the credit is added to your total uploaded amount.','traffic',1099511627776,0.0,'yes',80000.0),(30,'Parked Profile',75000.0,'With enough bonus points acquired, you are able to unlock the parked option within your profile which will ensure your account will be safe. The points are then removed from your Bonus Bank and the parked switch will show on your profile.','parked',1,1.0,'yes',75000.0),(31,'Pirates bounty',50000.0,'With enough bonus points acquired, you are able to exchange them for Pirates bounty which will select random users and deduct random amount of reputation points from them. The points are removed from your Bonus Bank and the reputation points will be deducted from the selected users then credited to you.','bounty',1,1.0,'yes',50000.0),(32,'100 Reputation points',40000.0,'With enough bonus points acquired, you are able to exchange them for some reputation points. The points are then removed from your Bonus Bank and the rep is added to your total reputation amount.','reputation',100,0.0,'yes',40000.0),(33,'Userblocks',50000.0,'With enough bonus points acquired and a minimum of 50 reputation points, you are able to exchange them for userblocks access. The points are then removed from your Bonus Bank and the user blocks configuration link will appear on your menu.','userblocks',0,0.0,'yes',50000.0),(34,'Bump a Torrent!',5000.0,'With enough bonus points acquired, you can Bump a torrent back to page 1 of the torrents page, bringing it back to life! \r\nThe torrent will then appear on page 1 again! The points are then removed from your Bonus Bank and the torrent is Bumped!\r\n** note there is an option to either view Bumped torrents or not.','bump',1,0.0,'yes',5000.0),(35,'Immunity',150000.0,'With enough bonus points acquired, you are able to exchange them for immunity for one year. The points are then removed from your Bonus Bank and the immunity switch is enabled on your account.','immunity',1,0.0,'yes',150000.0),(36,'User Unlocks',500.0,'With enough bonus points acquired and a minimum of 50 reputation points, you are able to exchange them for bonus locked moods. The points are then removed from your Bonus Bank and the user unlocks configuration link will appear on your menu.','userunlock',1,0.0,'yes',500.0);
/*!40000 ALTER TABLE `bonus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonuslog`
--

DROP TABLE IF EXISTS `bonuslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bonuslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donation` decimal(10,1) NOT NULL,
  `type` varchar(44) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_at` int(11) NOT NULL,
  KEY `id` (`id`),
  KEY `added_at` (`added_at`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='log of contributors towards freeleech etc...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonuslog`
--

LOCK TABLES `bonuslog` WRITE;
/*!40000 ALTER TABLE `bonuslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `bonuslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `private` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bugs`
--

DROP TABLE IF EXISTS `bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bugs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sender` int(10) NOT NULL DEFAULT '0',
  `added` int(12) NOT NULL DEFAULT '0',
  `priority` enum('low','high','veryhigh') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `problem` mediumtext COLLATE utf8mb4_unicode_ci,
  `status` enum('fixed','ignored','na') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'na',
  `staff` int(10) NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bugs`
--

LOCK TABLES `bugs` WRITE;
/*!40000 ALTER TABLE `bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `points` int(11) NOT NULL DEFAULT '0',
  `pic` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards`
--

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` VALUES (1,2,'2p.bmp'),(2,3,'3p.bmp'),(3,4,'4p.bmp'),(4,5,'5p.bmp'),(5,6,'6p.bmp'),(6,7,'7p.bmp'),(7,8,'8p.bmp'),(8,9,'9p.bmp'),(9,10,'10p.bmp'),(10,10,'vp.bmp'),(11,10,'dp.bmp'),(12,10,'kp.bmp'),(13,1,'tp.bmp'),(14,2,'2b.bmp'),(15,3,'3b.bmp'),(16,4,'4b.bmp'),(17,5,'5b.bmp'),(18,6,'6b.bmp'),(19,7,'7b.bmp'),(20,8,'8b.bmp'),(21,9,'9b.bmp'),(22,10,'10b.bmp'),(23,10,'vb.bmp'),(24,10,'db.bmp'),(25,10,'kb.bmp'),(26,1,'tb.bmp'),(27,2,'2k.bmp'),(28,3,'3k.bmp'),(29,4,'4k.bmp'),(30,5,'5k.bmp'),(31,6,'6k.bmp'),(32,7,'7k.bmp'),(33,8,'8k.bmp'),(34,9,'9k.bmp'),(35,10,'10k.bmp'),(36,10,'vk.bmp'),(37,10,'dk.bmp'),(38,10,'kk.bmp'),(39,1,'tk.bmp'),(40,2,'2c.bmp'),(41,3,'3c.bmp'),(42,4,'4c.bmp'),(43,5,'5c.bmp'),(44,6,'6c.bmp'),(45,7,'7c.bmp'),(46,8,'8c.bmp'),(47,9,'9c.bmp'),(48,10,'10c.bmp'),(49,10,'vc.bmp'),(50,10,'dc.bmp'),(51,10,'kc.bmp'),(52,1,'tc.bmp');
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casino`
--

DROP TABLE IF EXISTS `casino`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `casino` (
  `userid` int(10) NOT NULL DEFAULT '0',
  `win` bigint(20) NOT NULL DEFAULT '0',
  `lost` bigint(20) NOT NULL DEFAULT '0',
  `trys` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `enableplay` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `deposit` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casino`
--

LOCK TABLES `casino` WRITE;
/*!40000 ALTER TABLE `casino` DISABLE KEYS */;
/*!40000 ALTER TABLE `casino` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casino_bets`
--

DROP TABLE IF EXISTS `casino_bets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `casino_bets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `proposed` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challenged` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` bigint(20) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `winner` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casino_bets`
--

LOCK TABLES `casino_bets` WRITE;
/*!40000 ALTER TABLE `casino_bets` DISABLE KEYS */;
/*!40000 ALTER TABLE `casino_bets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` mediumint(5) NOT NULL DEFAULT '-1',
  `tabletype` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Apps','cat_appz.png','No Description',13,3),(2,'Games','cat_games.png','No Description',-1,1),(3,'Movies','cat_dvd.png','No Description',-1,2),(4,'Music','cat_music.png','No Description',-1,4),(5,'Episodes','cat_tveps.png','No Description',3,2),(6,'XXX','cat_xxx.png','No Description',3,2),(7,'Games/PSP','cat_psp.png','No Description',2,1),(8,'Games/PS2','cat_ps2.png','No Description',2,1),(9,'Anime','cat_anime.png','No Description',3,2),(10,'Movies/XviD','cat_xvid.png','No Description',3,2),(11,'Movies/HDTV','cat_hdtv.png','No Description',3,2),(12,'Games/PC Rips','cat_pcrips.png','No Description',2,1),(13,'Apps','cat_misc.png','No Description',-1,3),(14,'Music','cat_music.png','No Description',4,4);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cheaters`
--

DROP TABLE IF EXISTS `cheaters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cheaters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL,
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `client` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beforeup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upthis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timediff` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cheaters`
--

LOCK TABLES `cheaters` WRITE;
/*!40000 ALTER TABLE `cheaters` DISABLE KEYS */;
/*!40000 ALTER TABLE `cheaters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_config`
--

DROP TABLE IF EXISTS `class_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_config` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` int(3) DEFAULT NULL,
  `classname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classcolor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classpic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_config`
--

LOCK TABLES `class_config` WRITE;
/*!40000 ALTER TABLE `class_config` DISABLE KEYS */;
INSERT INTO `class_config` VALUES (1,'UC_USER',0,'USER','8e35ef','user.gif'),(2,'UC_POWER_USER',1,'POWER USER','f9a200','power.gif'),(3,'UC_VIP',2,'VIP','009f00','vip.gif'),(4,'UC_UPLOADER',3,'UPLOADER','0000ff','uploader.gif'),(5,'UC_MODERATOR',4,'MODERATOR','fe2e2e','moderator.gif'),(6,'UC_ADMINISTRATOR',5,'ADMINISTRATOR','b000b0','administrator.gif'),(7,'UC_SYSOP',6,'SYS0P','0c27e4','sysop.gif'),(8,'UC_MIN',0,'','',''),(9,'UC_MAX',6,'','',''),(10,'UC_STAFF',4,'','','');
/*!40000 ALTER TABLE `class_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_promo`
--

DROP TABLE IF EXISTS `class_promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_promo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_ratio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `low_ratio` decimal(10,2) NOT NULL DEFAULT '0.00',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_promo`
--

LOCK TABLES `class_promo` WRITE;
/*!40000 ALTER TABLE `class_promo` DISABLE KEYS */;
INSERT INTO `class_promo` VALUES (6,'1',1.20,50,20,0.85);
/*!40000 ALTER TABLE `class_promo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cleanup`
--

DROP TABLE IF EXISTS `cleanup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cleanup` (
  `clean_id` int(10) NOT NULL AUTO_INCREMENT,
  `clean_title` char(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `clean_file` char(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `clean_time` int(11) NOT NULL DEFAULT '0',
  `clean_increment` int(11) NOT NULL DEFAULT '0',
  `clean_cron_key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `clean_log` tinyint(1) NOT NULL DEFAULT '0',
  `clean_desc` mediumtext COLLATE utf8mb4_unicode_ci,
  `clean_on` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`clean_id`),
  KEY `clean_time` (`clean_time`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cleanup`
--

LOCK TABLES `cleanup` WRITE;
/*!40000 ALTER TABLE `cleanup` DISABLE KEYS */;
INSERT INTO `cleanup` VALUES (4,'Lottery Autoclean','lotteryclean.php',1359812894,86400,'d6704d582b136ea1ed13635bb9059f57',1,'Lottery Autoclean - Lottery clean up here every X days',0),(5,'Optimze Db Auto','optimizedb.php',1405715247,172800,'d6704d582b136ea1ed13635bb9059f57',1,'Auto Optimize - Runs every 2 days',1),(6,'Auto Backup Db','backupdb.php',1405742261,86400,'d6704d582b136ea1ed13635bb9059f57',1,'Auto Backup - Runs every 1 day',1),(8,'Irc bonus','irc_update.php',1405717294,1800,'c06a074cd6403bcc1f292ce864c3cdd5',1,'Irc idle bonus update',1),(9,'Statistics','sitestats_update.php',1405722386,3600,'2a2afb82d82cc4ddcb6ff1753a40dfe9',1,'SIte statistics update',1),(10,'Karma Bonus','karma_update.php',1405717612,1800,'d0df8a38cfba26ece2c285189a656ad0',0,'Seedbonus award update',1),(11,'Forums','forum_update.php',1405721151,900,'c9c58a0d43b02cd5358115673bc04c9e',0,'Forum online and count update',1),(12,'Torrents','torrents_update.php',1394919050,900,'81875d0e7b63771ae2a59f2a48755da4',1,'Torrents update',0),(13,'Normalize','torrents_normalize.php',1394919677,900,'1274dd2d9ffd203e6d489db25d0f28fe',1,'File, comment, torrent update',0),(14,'Ips','ip_update.php',1405725772,86400,'0b4f34774259b5069d220c485aa10eba',1,'Ip clean',1),(15,'Signups','expired_signup_update.php',1405801239,259200,'bdde41096f769d1a01251813cc2c1353',1,'Expired signups update',1),(16,'Peers','peer_update.php',1394919107,900,'72181fc6214ddc556d71066df031d424',1,'Peers update',0),(17,'Visible','visible_update.php',1405719080,900,'77c523eab12be5d0342e4606188cd2ca',0,'Torrents visible update',1),(18,'Announcements','announcement_update.php',1405770206,86400,'b73c139b4defbc031e201b91fef29a4c',1,'Old announcement updates',1),(19,'Readposts','readpost_update.php',1405793882,86400,'3e0c8bc6b6e6cc61fdfe8b26f8268b77',1,'Old Readposts updates',1),(20,'Happyhour','happyhour_update.php',1396565647,43200,'a7c422bc9f17b3fba5dab2d0129acd32',1,'HappyHour Updates',0),(21,'Customsmilies','customsmilie_update.php',1405720147,86400,'9e8a41be2b0a56d83e0d0c0b00639f66',1,'Custom Smilie Update',1),(22,'Karma Vips','karmavip_update.php',1405794046,86400,'c444f13b95998c98a851714673ff6b84',1,'Karma VIp Updates',1),(23,'Anonymous Profile','anonymous_update.php',1405804463,86400,'25146aec76a7b163ac6955685ff667d9',1,'Anonymous Profile Updates',1),(24,'Delete Torrents','delete_torrents_update.php',1395005525,86400,'52f8e3c9fd438d4a86062f88f1146098',1,'Delete Old Torrents Update',0),(25,'Funds','funds_update.php',1405806713,86400,'5f50f43a9e640cd6203e1964c17361ba',1,'Funds And Donation Updates',1),(26,'Leechwarns','leechwarn_update.php',1405715844,86400,'0303a05302fadf30fc18f987d2a5b285',1,'Leechwarnings Update',1),(27,'Auto Invite','autoinvite_update.php',1405718135,86400,'48839ced75a612d41d9278718075dbb2',1,'Auto Invite Updates',1),(28,'Hit And Run','hitrun_update.php',1394921211,3600,'3ab445bbff84f87e8dc5a16489d7ca31',1,'Hit And Run Updates',0),(29,'Freeslots Update','freeslot_update.php',1395005532,86400,'63db6b0519eccbfe0b06d87b8f0bcaad',1,'Freeslots Stuffs Update',0),(30,'Backup Clean','backup_update.php',1405719881,86400,'2c0d1a9ffa04937255344b97e2c9706f',1,'Backups Clean Update',1),(31,'Inactive Clean','inactive_update.php',1405794146,86400,'a401de097e031315b751b992ee40d733',1,'Inactive Users Update',1),(32,'Shout Clean','shout_update.php',1405761109,172800,'13515c22103b5b916c3d86023220cd61',1,'Shoutbox Clean Update',1),(33,'Power User Clean','pu_update.php',1405721579,86400,'4751425b1c765360a5f8bab14c6b9a47',1,'Power User Clean Updates',1),(34,'Power User Demote Clean','pu_demote_update.php',1405722059,86400,'e9249b5f653f03ed425d68947155056b',1,'Power User Demote Clean Updates',1),(35,'Bugs Clean','bugs_update.php',1405774783,1209600,'1e9734cdf50408a7739b7b03272aeab3',1,'Bugs Update Clean',1),(36,'Sitepot Clean','sitepot_update.php',1396628641,86400,'29dae941216f1bdb81f69dce807b3501',1,'Sitepot Update Clean',0),(37,'Userhits Clean','userhits_update.php',1405794345,86400,'d0cec8e7adb50290db6cf911a5c74339',1,'Userhits Clean Updates',1),(38,'Process Kill','processkill_update.php',1405795280,86400,'b7c0f14c9482a14e9f5cb0d467dfd7c6',1,'Mysql Process KIll Updates',1),(39,'Cleanup Log','cleanlog_update.php',1405796149,86400,'7dc0b72fc8c12b264fad1613fbea2489',1,'Cleanup Log Updates',1),(40,'Pirate Cleanup','pirate_update.php',1405799774,86400,'e5f20d43425832e9397841be6bc92be2',1,'Pirate Stuffs Update',1),(41,'King Cleanup','king_update.php',1405800001,86400,'12b5c6c9f9919ca09816225c29fddaeb',1,'King Stuffs Update',1),(42,'Free User Cleanup','freeuser_update.php',1396565083,3900,'37f9de0443159bf284a1c7a703e96cf9',1,'Free User Stuffs Update',0),(43,'Download Possible Cleanup','downloadpos_update.php',1405804020,86400,'e20bcc6d07c6ec493e106adb8d2a8227',1,'Download Possible Stuffs Update',1),(44,'Upload Possible Cleanup','uploadpos_update.php',1405807913,86400,'fd1110b750af878faccaf672fe53876d',1,'Upload Possible Stuffs Update',1),(45,'Free Torrents Cleanup','freetorrents_update.php',1396566633,3600,'20390090ac784fee830d19bd708cfcad',1,'Free Torrents Stuffs Update',0),(46,'Chatpost Cleanup','chatpost_update.php',1405804100,86400,'bab6f1de36dc97dff02745051e076a39',1,'Chatpost Stuffs Update',1),(47,'Immunity Cleanup','immunity_update.php',1405715322,86400,'11bf6f41c659b9f49f6ccdfa616e9f82',1,'Immunity Stuffs Update',1),(48,'Warned Cleanup','warned_update.php',1405716859,86400,'6e558b89ac60454eaa3a45243347c977',1,'Warned Stuffs Update',1),(49,'Games Update','gameaccess_update.php',1405722654,86400,'33704fd97f8840ff08ef4e6ff236b3e4',1,'Games Stuffs Updates',1),(50,'Pm Update','sendpmpos_update.php',1405724044,86400,'32784b9c2891f022a91d5007f068f7d9',1,'Pm Stuffs Updates',1),(51,'Avatar Update','avatarpos_update.php',1405799877,86400,'f257794129ee772f5cfe00b33b363100',1,'Avatar Stuffs Updates',1),(52,'Birthday Pms','birthday_update.php',1405800457,86400,'1fd167bf236ea5e74e835224d1cc36e9',1,'Pm all members with birthdays.',1),(53,'Movie of the week','mow_update.php',1406138137,604800,'716274782f2f7229d960a6661fb06b60',1,'Updates movie of the week',1),(54,'Silver torrents','silvertorrents_update.php',1396563248,3600,'3e1aab005271870d69934ebe37e28819',1,'Clean expired silver',0),(55,'Failed Logins','failedlogin_update.php',1405718159,86400,'c90f0f030d7914db6ae1263de1730541',1,'Delete expired failed logins',1),(56,'Christmas Gift Rest','gift_update.php',1435256312,31556926,'4bdd6190a0ba3420d21b50b79945c06b',1,'Reset all users yearly xmas gift',1),(58,'Achievements Update','achievement_avatar_update.php',1405799732,86400,'0c5889bab74e7ff8f920ec524423f627',1,'Updates user avatar achievements',1),(59,'Achievements Update','achievement_bday_update.php',1405718380,86400,'2b95ff34a27d540f61ceca3ee1424216',1,'Updates user birthday achievements',1),(60,'Achievements Update','achievement_corrupt_update.php',1405724125,86400,'afefaecc0e31e412c28dbab154e43f9d',1,'Updates user corrupt achievements',1),(61,'Achievements Update','achievement_fpost_update.php',1405726185,86400,'f466ff2246e7e84bc60210aa947185da',1,'Updates user forum post achievements',1),(62,'Achievements Update','achievement_ftopics_update.php',1405727610,86400,'825f6cac5fa992f505ceea3992db5483',1,'Updates user forum topic achievements',1),(63,'Achievements Update','achievement_invite_update.php',1405733242,86400,'02e56c3aeba0b1e3e4bcca11699f23eb',1,'Updates user invite achievements',1),(64,'Achievements Update','achievement_karma_update.php',1405734309,86400,'3827839629ade62f03a9fccbacb8402a',1,'Updates user Karma achievements',1),(65,'Achievements Update','achievement_request_update.php',1405736075,86400,'48ec70ecc00c88b37977e2743d294888',1,'Updates user request achievements',1),(66,'Achievements Update','achievement_seedtime_update.php',1405736690,86400,'158fb134b7a1487bdda67d42544693fc',1,'Updates user seedtime achievements',1),(67,'Achievements Update','achievement_sheep_update.php',1405736867,86400,'97c3919a5947e00952bf82d1dc6f5c58',1,'Updates user sheep achievements',1),(68,'Achievements Update','achievement_shouts_update.php',1405738701,86400,'b07151b274bb6d568ab1bc3b3364cb6c',1,'Updates user shout achievements',1),(69,'Achievements Update','achievement_sig_update.php',1405740740,86400,'82c3ff41b8e45a96bcd1582345d6dca9',1,'Updates user signature achievements',1),(70,'Achievements Update','achievement_sreset_update.php',1405770945,86400,'b51582111414701c0bd512fd2b4f0507',1,'Updates user achievements - Reset shouts',1),(71,'Achievements Update','achievement_sticky_update.php',1405728108,86400,'00aaf60d3806924a42e95e64ee00c5fb',1,'Updates user sticky torrents achievements',1),(72,'Achievements Update','achievement_up_update.php',1405794142,86400,'b0feb2e2c22dbf9f1575c798a5d1560d',1,'Updates user uploader achievements',1),(73,'Referrer cleans','referrer_update.php',1398091653,86400,'36bc2469228c1e0c8269ee9d309be37f',1,'Referrer Autoclean - Removes expired referrer entrys',0),(74,'Snatch list admin','snatchclean_update.php',1396631629,86400,'cfb8afef5b7a1c41e047dc791b0f1de0',1,'Clean old dead data',0),(76,'Normalize XBT','torrents_normalize_xbt.php',1405720207,900,'bd4f4ae7d7499aefbce82971a3b1cbbd',1,'XBT normalize query updates',1),(77,'Delete torrents','delete_torrents_xbt_update.php',1405731392,86400,'2d47cfeddfd61ed4529e0d4a25ca0d12',1,'Delete torrent xbt update',1),(78,'XBT Torrents','torrents_update_xbt.php',1405721775,900,'79e243cf24e92a13441b381d033d03a9',1,'XBT Torrents update',1),(79,'XBT Peers','peer_update_xbt.php',1403459321,900,'3a0245bc43e2cad94ac7966bb3fe75f3',1,'XBT Peers update - Not required',0),(80,'XBT hit and run system','hitrun_xbt_update.php',1405444631,3600,'a6804b0f6d5ce68ac390d4d261a82d85',1,'XBT hit and run detection',0),(81,'Clean cheater data','cheatclean_update.php',1408382495,86400,'9b0112ad44b0135220ef539804447d49',1,'Clean abnormal upload speed entrys',1),(82,'Trivia Cleanup','trivia_update.php',1500681600,300,'936cde05931085f7f3a1454b183c4522',1,'Trivia Questions Cleanup',1),(83,'Trivia Bonus Points','trivia_points_update.php',1500681600,86400,'62c3968205f2c1080b2253f90aeb31ef',1,'Trivia Bonus Points',1);
/*!40000 ALTER TABLE `cleanup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cleanup_log`
--

DROP TABLE IF EXISTS `cleanup_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cleanup_log` (
  `clog_id` int(10) NOT NULL AUTO_INCREMENT,
  `clog_event` char(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `clog_time` int(11) NOT NULL DEFAULT '0',
  `clog_ip` char(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `clog_desc` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`clog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cleanup_log`
--

LOCK TABLES `cleanup_log` WRITE;
/*!40000 ALTER TABLE `cleanup_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `cleanup_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coins`
--

DROP TABLE IF EXISTS `coins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coins`
--

LOCK TABLES `coins` WRITE;
/*!40000 ALTER TABLE `coins` DISABLE KEYS */;
/*!40000 ALTER TABLE `coins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  `text` mediumtext COLLATE utf8mb4_unicode_ci,
  `ori_text` mediumtext COLLATE utf8mb4_unicode_ci,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` int(11) NOT NULL,
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `request` int(10) unsigned NOT NULL DEFAULT '0',
  `offer` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_likes` mediumtext COLLATE utf8mb4_unicode_ci,
  `checked_by` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_when` int(11) NOT NULL,
  `checked` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flagpic` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'Sweden','sweden.gif'),(2,'United States of America','usa.gif'),(3,'Russia','russia.gif'),(4,'Finland','finland.gif'),(5,'Canada','canada.gif'),(6,'France','france.gif'),(7,'Germany','germany.gif'),(8,'China','china.gif'),(9,'Italy','italy.gif'),(10,'Denmark','denmark.gif'),(11,'Norway','norway.gif'),(12,'United Kingdom','uk.gif'),(13,'Ireland','ireland.gif'),(14,'Poland','poland.gif'),(15,'Netherlands','netherlands.gif'),(16,'Belgium','belgium.gif'),(17,'Japan','japan.gif'),(18,'Brazil','brazil.gif'),(19,'Argentina','argentina.gif'),(20,'Australia','australia.gif'),(21,'New Zealand','newzealand.gif'),(22,'Spain','spain.gif'),(23,'Portugal','portugal.gif'),(24,'Mexico','mexico.gif'),(25,'Singapore','singapore.gif'),(26,'South Africa','southafrica.gif'),(27,'South Korea','southkorea.gif'),(28,'Jamaica','jamaica.gif'),(29,'Luxembourg','luxembourg.gif'),(30,'Hong Kong','hongkong.gif'),(31,'Belize','belize.gif'),(32,'Algeria','algeria.gif'),(33,'Angola','angola.gif'),(34,'Austria','austria.gif'),(35,'Yugoslavia','yugoslavia.gif'),(36,'Western Samoa','westernsamoa.gif'),(37,'Malaysia','malaysia.gif'),(38,'Dominican Republic','dominicanrep.gif'),(39,'Greece','greece.gif'),(40,'Guatemala','guatemala.gif'),(41,'Israel','israel.gif'),(42,'Pakistan','pakistan.gif'),(43,'Czech Republic','czechrep.gif'),(44,'Serbia','serbia.gif'),(45,'Seychelles','seychelles.gif'),(46,'Taiwan','taiwan.gif'),(47,'Puerto Rico','puertorico.gif'),(48,'Chile','chile.gif'),(49,'Cuba','cuba.gif'),(50,'Congo','congo.gif'),(51,'Afghanistan','afghanistan.gif'),(52,'Turkey','turkey.gif'),(53,'Uzbekistan','uzbekistan.gif'),(54,'Switzerland','switzerland.gif'),(55,'Kiribati','kiribati.gif'),(56,'Philippines','philippines.gif'),(57,'Burkina Faso','burkinafaso.gif'),(58,'Nigeria','nigeria.gif'),(59,'Iceland','iceland.gif'),(60,'Nauru','nauru.gif'),(61,'Slovenia','slovenia.gif'),(62,'Albania','albania.gif'),(63,'Turkmenistan','turkmenistan.gif'),(64,'Bosnia Herzegovina','bosniaherzegovina.gif'),(65,'Andorra','andorra.gif'),(66,'Lithuania','lithuania.gif'),(67,'India','india.gif'),(68,'Netherlands Antilles','nethantilles.gif'),(69,'Ukraine','ukraine.gif'),(70,'Venezuela','venezuela.gif'),(71,'Hungary','hungary.gif'),(72,'Romania','romania.gif'),(73,'Vanuatu','vanuatu.gif'),(74,'Vietnam','vietnam.gif'),(75,'Trinidad & Tobago','trinidadandtobago.gif'),(76,'Honduras','honduras.gif'),(77,'Kyrgyzstan','kyrgyzstan.gif'),(78,'Ecuador','ecuador.gif'),(79,'Bahamas','bahamas.gif'),(80,'Peru','peru.gif'),(81,'Cambodia','cambodia.gif'),(82,'Barbados','barbados.gif'),(83,'Bangladesh','bangladesh.gif'),(84,'Laos','laos.gif'),(85,'Uruguay','uruguay.gif'),(86,'Antigua Barbuda','antiguabarbuda.gif'),(87,'Paraguay','paraguay.gif'),(88,'Union of Soviet Socialist Republics','ussr.gif'),(89,'Thailand','thailand.gif'),(90,'Senegal','senegal.gif'),(91,'Togo','togo.gif'),(92,'North Korea','northkorea.gif'),(93,'Croatia','croatia.gif'),(94,'Estonia','estonia.gif'),(95,'Colombia','colombia.gif'),(96,'Lebanon','lebanon.gif'),(97,'Latvia','latvia.gif'),(98,'Costa Rica','costarica.gif'),(99,'Egypt','egypt.gif'),(100,'Bulgaria','bulgaria.gif'),(101,'Scotland','scotland.gif'),(102,'United Arab Emirates','uae.gif'),(103,'Slovakia','slovakia.gif');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dbbackup`
--

DROP TABLE IF EXISTS `dbbackup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbbackup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbbackup`
--

LOCK TABLES `dbbackup` WRITE;
/*!40000 ALTER TABLE `dbbackup` DISABLE KEYS */;
/*!40000 ALTER TABLE `dbbackup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deathrow`
--

DROP TABLE IF EXISTS `deathrow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deathrow` (
  `uid` int(10) NOT NULL,
  `username` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tid` int(10) NOT NULL,
  `torrent_name` char(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` tinyint(1) NOT NULL,
  `notify` tinyint(1) unsigned NOT NULL DEFAULT '1',
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deathrow`
--

LOCK TABLES `deathrow` WRITE;
/*!40000 ALTER TABLE `deathrow` DISABLE KEYS */;
/*!40000 ALTER TABLE `deathrow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `decks`
--

DROP TABLE IF EXISTS `decks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `decks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameid` int(11) unsigned NOT NULL,
  `cards` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `shuffled` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gameid` (`gameid`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `decks`
--

LOCK TABLES `decks` WRITE;
/*!40000 ALTER TABLE `decks` DISABLE KEYS */;
/*!40000 ALTER TABLE `decks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `overlayText` mediumtext COLLATE utf8mb4_unicode_ci,
  `displayDates` tinyint(1) NOT NULL,
  `freeleechEnabled` tinyint(1) NOT NULL,
  `duploadEnabled` tinyint(1) NOT NULL,
  `hdownEnabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `startTime` (`startTime`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,1371323531,1371582731,'HalfDownload [ON]',1,0,0,1);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failedlogins`
--

DROP TABLE IF EXISTS `failedlogins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failedlogins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added` int(11) NOT NULL,
  `banned` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `attempts` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failedlogins`
--

LOCK TABLES `failedlogins` WRITE;
/*!40000 ALTER TABLE `failedlogins` DISABLE KEYS */;
/*!40000 ALTER TABLE `failedlogins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`),
  KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_config`
--

DROP TABLE IF EXISTS `forum_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_config` (
  `id` smallint(1) NOT NULL DEFAULT '1',
  `delete_for_real` smallint(6) NOT NULL DEFAULT '0',
  `min_delete_view_class` smallint(2) unsigned NOT NULL DEFAULT '7',
  `readpost_expiry` smallint(3) NOT NULL DEFAULT '14',
  `min_upload_class` smallint(2) unsigned NOT NULL DEFAULT '2',
  `accepted_file_extension` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accepted_file_types` varchar(280) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_file_size` int(10) unsigned NOT NULL DEFAULT '2097152',
  `upload_folder` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`readpost_expiry`),
  KEY `delete_for_real` (`delete_for_real`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_config`
--

LOCK TABLES `forum_config` WRITE;
/*!40000 ALTER TABLE `forum_config` DISABLE KEYS */;
INSERT INTO `forum_config` VALUES (13,1,4,7,6,'a:3:{i:0;s:3:\"zip\";i:1;s:3:\"rar\";i:2;s:0:\"\";}','a:3:{i:0;s:15:\"application/zip\";i:1;s:15:\"application/rar\";i:2;s:0:\"\";}',2097152,'/var/www/uploads');
/*!40000 ALTER TABLE `forum_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_poll`
--

DROP TABLE IF EXISTS `forum_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_poll` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `question` varchar(280) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poll_answers` mediumtext COLLATE utf8mb4_unicode_ci,
  `number_of_options` smallint(2) unsigned NOT NULL DEFAULT '0',
  `poll_starts` int(11) NOT NULL DEFAULT '0',
  `poll_ends` int(11) NOT NULL DEFAULT '0',
  `change_vote` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `multi_options` smallint(2) unsigned NOT NULL DEFAULT '1',
  `poll_closed` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_poll`
--

LOCK TABLES `forum_poll` WRITE;
/*!40000 ALTER TABLE `forum_poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_poll_votes`
--

DROP TABLE IF EXISTS `forum_poll_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_poll_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `option` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_poll_votes`
--

LOCK TABLES `forum_poll_votes` WRITE;
/*!40000 ALTER TABLE `forum_poll_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_poll_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forums`
--

DROP TABLE IF EXISTS `forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forums` (
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_class_read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `min_class_write` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `post_count` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0',
  `min_class_create` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `parent_forum` tinyint(4) NOT NULL DEFAULT '0',
  `forum_id` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forums`
--

LOCK TABLES `forums` WRITE;
/*!40000 ALTER TABLE `forums` DISABLE KEYS */;
/*!40000 ALTER TABLE `forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `freeleech`
--

DROP TABLE IF EXISTS `freeleech`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freeleech` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `var` int(10) NOT NULL DEFAULT '0',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `freeleech`
--

LOCK TABLES `freeleech` WRITE;
/*!40000 ALTER TABLE `freeleech` DISABLE KEYS */;
INSERT INTO `freeleech` VALUES (1,'Contribute 1 to Site Countdown Pot',1,'Donate 1 coin and 1 minute will be removed from the Countdown.','contribute',60),(2,'Contribute 5 to Site Countdown Pot',5,'Donate 5 coins and 5 minutes will be removed from the Countdown.','contribute',300),(3,'Contribute 10 to Site Countdown Pot',10,'Donate 10 coins and 10 minutes will be removed from the Countdown.','contribute',600),(4,'Contribute 25 to Site Countdown Pot',25,'Donate 25 coins and 25 minutes will be removed from the Countdown.','contribute',1500),(5,'Contribute 50 to Site Countdown Pot',50,'Donate 50 coins and 50 minutes will be removed from the Countdown.','contribute',3000),(6,'Contribute 100 to Site Countdown Pot',100,'Donate 100 coins and 1 hour and 40 minutes will be removed from the Countdown.','contribute',6000),(7,'Contribute 500 to Site Countdown Pot',500,'Donate 500 coins and 8 hours and 20 minutes will be removed from the Countdown.','contribute',30000),(8,'Contribute 1000 to Site Countdown Pot',1000,'Donate 1000 coins and 16 hours and 40 minutes will be removed from the Countdown.','contribute',60000),(9,'Contribute to Site Countdown Pot',0,'Enter a custom amount to donate. ','contribut3',0),(10,'Freeleech',0,'Freeleech Sunday is enabled','countdown',1362355200),(11,'Sitewide Freeleech',0,'set by','manual',0),(12,'Sitewide Doubleseed',0,'set by ','manual',0),(13,'Sitewide Freeleech and Doubleseed',0,'set by','manual',0),(15,'Crazy Hour',1395007265,'Freeleech and Double Upload credit for 24 Hours','crazyhour',0);
/*!40000 ALTER TABLE `freeleech` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `freeslots`
--

DROP TABLE IF EXISTS `freeslots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freeslots` (
  `torrentid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `doubleup` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `free` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `addedup` int(11) NOT NULL DEFAULT '0',
  `addedfree` int(11) NOT NULL DEFAULT '0',
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `freeslots`
--

LOCK TABLES `freeslots` WRITE;
/*!40000 ALTER TABLE `freeslots` DISABLE KEYS */;
/*!40000 ALTER TABLE `freeslots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `friendid` int(10) unsigned NOT NULL DEFAULT '0',
  `confirmed` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friends`
--

LOCK TABLES `friends` WRITE;
/*!40000 ALTER TABLE `friends` DISABLE KEYS */;
/*!40000 ALTER TABLE `friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funds`
--

DROP TABLE IF EXISTS `funds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cash` decimal(8,2) NOT NULL DEFAULT '0.00',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funds`
--

LOCK TABLES `funds` WRITE;
/*!40000 ALTER TABLE `funds` DISABLE KEYS */;
/*!40000 ALTER TABLE `funds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `happyhour`
--

DROP TABLE IF EXISTS `happyhour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `happyhour` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `multiplier` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `happyhour`
--

LOCK TABLES `happyhour` WRITE;
/*!40000 ALTER TABLE `happyhour` DISABLE KEYS */;
/*!40000 ALTER TABLE `happyhour` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `happylog`
--

DROP TABLE IF EXISTS `happylog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `happylog` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `multi` float NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `happylog`
--

LOCK TABLES `happylog` WRITE;
/*!40000 ALTER TABLE `happylog` DISABLE KEYS */;
/*!40000 ALTER TABLE `happylog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hit_and_run_settings`
--

DROP TABLE IF EXISTS `hit_and_run_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hit_and_run_settings` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hit_and_run_settings`
--

LOCK TABLES `hit_and_run_settings` WRITE;
/*!40000 ALTER TABLE `hit_and_run_settings` DISABLE KEYS */;
INSERT INTO `hit_and_run_settings` VALUES ('firstclass','UC_POWER_USER'),('secondclass','UC_VIP'),('thirdclass','UC_MODERATOR'),('_3day_first','48'),('_14day_first','30'),('_14day_over_first','18'),('_3day_second','48'),('_14day_second','30'),('_14day_over_second','18'),('_3day_third','48'),('_14day_third','30'),('_14day_over_third','18'),('torrentage1','1'),('torrentage2','7'),('torrentage3','7'),('cainallowed','3'),('caindays','0.5'),('hnr_online','1');
/*!40000 ALTER TABLE `hit_and_run_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `infolog`
--

DROP TABLE IF EXISTS `infolog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `infolog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) DEFAULT '0',
  `txt` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `infolog`
--

LOCK TABLES `infolog` WRITE;
/*!40000 ALTER TABLE `infolog` DISABLE KEYS */;
/*!40000 ALTER TABLE `infolog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invite_codes`
--

DROP TABLE IF EXISTS `invite_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invite_added` int(10) NOT NULL,
  `status` enum('Pending','Confirmed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `email` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sender` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invite_codes`
--

LOCK TABLES `invite_codes` WRITE;
/*!40000 ALTER TABLE `invite_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `invite_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ips`
--

DROP TABLE IF EXISTS `ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userid` int(10) DEFAULT NULL,
  `type` enum('login','announce','browse','like') COLLATE utf8mb4_unicode_ci NOT NULL,
  `seedbox` tinyint(1) NOT NULL DEFAULT '0',
  `lastbrowse` int(11) NOT NULL DEFAULT '0',
  `lastlogin` int(11) NOT NULL DEFAULT '0',
  `lastannounce` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ips`
--

LOCK TABLES `ips` WRITE;
/*!40000 ALTER TABLE `ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `likes` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `userip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lottery_config`
--

DROP TABLE IF EXISTS `lottery_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lottery_config` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lottery_config`
--

LOCK TABLES `lottery_config` WRITE;
/*!40000 ALTER TABLE `lottery_config` DISABLE KEYS */;
INSERT INTO `lottery_config` VALUES ('ticket_amount','10000'),('ticket_amount_type','seedbonus'),('user_tickets','10'),('class_allowed','0|1|2|3|4|5|6'),('total_winners','5'),('prize_fund','10000000'),('start_date','1328458121'),('end_date','1328542721'),('use_prize_fund','1'),('enable','0'),('lottery_winners',''),('lottery_winners_amount','2000000'),('lottery_winners_time','1334782914');
/*!40000 ALTER TABLE `lottery_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manage_likes`
--

DROP TABLE IF EXISTS `manage_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manage_likes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `disabled_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manage_likes`
--

LOCK TABLES `manage_likes` WRITE;
/*!40000 ALTER TABLE `manage_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `manage_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  `subject` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msg` mediumtext COLLATE utf8mb4_unicode_ci,
  `unread` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `poster` bigint(20) unsigned NOT NULL DEFAULT '0',
  `location` smallint(6) NOT NULL DEFAULT '1',
  `saved` enum('no','yes') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `urgent` enum('no','yes') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `draft` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modscredits`
--

DROP TABLE IF EXISTS `modscredits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modscredits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('Addon','Forum','Message/Email','Display/Style','Staff/Tools','Browse/Torrent/Details','Misc') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Misc',
  `status` enum('Complete','In-Progress') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Complete',
  `u232lnk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modscredits`
--

LOCK TABLES `modscredits` WRITE;
/*!40000 ALTER TABLE `modscredits` DISABLE KEYS */;
INSERT INTO `modscredits` VALUES (1,'Ratio Free','Addon','Complete','https://forum.u-232.com/index.php/topic,1060.0.html','Mindless','V3 Ratio free modification; A true ratio free system =]');
/*!40000 ALTER TABLE `modscredits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moods`
--

DROP TABLE IF EXISTS `moods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonus` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moods`
--

LOCK TABLES `moods` WRITE;
/*!40000 ALTER TABLE `moods` DISABLE KEYS */;
INSERT INTO `moods` VALUES (1,'is feeling neutral','noexpression.gif',0),(2,'is feeling bad','wall.gif',0),(3,'is feeling good','grin.gif',0),(4,'is feeling tired','yawn.gif',0),(5,'is feeling angry','angry.gif',0),(6,'in wub','wub.gif',0),(7,'is feeling sad','wavecry.gif',0),(8,'is feeling silly','clown.gif',0),(9,'in love','love.gif',0),(10,'is a pirate','pirate.gif',0),(11,'is feeling like ranting','rant.gif',0),(12,'is feeling devilish','devil.gif',0),(13,'is feeling yucky','yucky.gif',0),(14,'yarrr matey','pirate2.gif',0),(15,'is feeling happy','smile1.gif',0),(16,'is feeling like a tease','tease.gif',0),(17,'is feeling awesome','w00t.gif',0),(18,'is feeling bananas','bananadance.gif',0),(19,'is drinking with friends','beer2.gif',0),(20,'is drinking','beer.gif',0),(21,'is feeling like an angel','angel.gif',0),(22,'is feeling bossy','cigar.gif',0),(23,'needs coffee','cuppa.gif',0),(24,'is feeling like crying','cry.gif',0),(25,'is dancing','mml.gif',0),(26,'is feeling crazy','crazy.gif',0),(27,'is drunk','drunk.gif',0),(28,'has gone fishing','fishing.gif',0),(29,'is having fun','fun.gif',0),(30,'is feeling like a winner','hooray.gif',0),(31,'is feeling innocent','innocent.gif',0),(32,'is laughing out loud','laugh.gif',0),(33,'is feeling like kissing','kissing2.gif',0),(34,'is feeling lazy','smoke2.gif',0),(35,'is feeling like a king','king.gif',0),(36,'is into the music','music.gif',0),(37,'is a ninja','ninja.gif',0),(38,'is feeling old','oldtimer.gif',0),(39,'is feeling like a pimp','pimp.gif',0),(40,'is feeling like shit','shit.gif',0),(41,'is feeling sly','sly.gif',0),(42,'is feeling smart','smart.gif',0),(43,'is stoned','smokin.gif',0),(44,'is feeling weird','weirdo.gif',0),(45,'is in shock','sheesh.gif',0),(46,'is bored','tumbleweed.gif',0),(47,'is taz!','taz.gif',1),(48,'is spidey','spidey.gif',0),(49,'is hitting the bong','bong.gif',1),(50,'is drinking cola','pepsi.gif',1),(51,'is bouncing','trampoline.gif',1),(52,'is feeling super','super.gif',1),(53,'is feeling lucky','clover.gif',1),(54,'is kenny','kenny.gif',0),(55,'is in bed','sleeping.gif',0),(56,'Is old','oldman.gif',0),(57,'is pissed drunk','drinks.gif',0),(58,'is telling a story','talk2.gif',0),(59,'is having a cig','cigar.gif',0),(60,'is eating cookies','cookies.gif',0),(61,'is feeling Good!','good.gif',0),(62,'is feeling artistic','graffiti.gif',0),(63,'is farting','fart3.gif',0),(64,'is hard at work','elektrik.gif',0),(65,'is grooving to the music','music.gif',0),(66,'is headbanging','punk.gif',0),(67,'is a slurpee ninja','ninja.gif',1),(100,'is dead','wink_skull.gif',0),(101,'is crabby','evilmad.gif',0),(102,'woof woof!','pish.gif',0),(103,'is feeling like an angel','angeldevil.gif',0),(104,'is headbanging','mini4.gif',0),(105,'Is banned','banned.gif',0),(106,'is teasing','blum.gif',0),(107,'is crazy','crazy.gif',0),(108,'is da bomb','bomb.gif',0),(121,'is smiling','smile2.gif',0),(122,'is cheerful','clapper1.gif',0),(123,'hitting the bhong','bhong.gif',0),(131,'is a wizard','wizard.gif',0),(132,'is a pissed off','soapbox1.gif',0),(133,'is wanted','wanted.gif',0),(202,'devil','devil.gif',0),(203,'is wacko','wacko.gif',0);
/*!40000 ALTER TABLE `moods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `body` mediumtext COLLATE utf8mb4_unicode_ci,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sticky` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notconnectablepmlog`
--

DROP TABLE IF EXISTS `notconnectablepmlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notconnectablepmlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notconnectablepmlog`
--

LOCK TABLES `notconnectablepmlog` WRITE;
/*!40000 ALTER TABLE `notconnectablepmlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `notconnectablepmlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `now_viewing`
--

DROP TABLE IF EXISTS `now_viewing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `now_viewing` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `now_viewing`
--

LOCK TABLES `now_viewing` WRITE;
/*!40000 ALTER TABLE `now_viewing` DISABLE KEYS */;
/*!40000 ALTER TABLE `now_viewing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_votes`
--

DROP TABLE IF EXISTS `offer_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vote` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `user_offer` (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_votes`
--

LOCK TABLES `offer_votes` WRITE;
/*!40000 ALTER TABLE `offer_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `offered_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_torrent_id` int(10) NOT NULL DEFAULT '0',
  `vote_yes_count` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_no_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `link` varchar(240) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('approved','pending','denied') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `id_added` (`id`),
  KEY `offered_by_name` (`offer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `over_forums`
--

DROP TABLE IF EXISTS `over_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `over_forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_class_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `over_forums`
--

LOCK TABLES `over_forums` WRITE;
/*!40000 ALTER TABLE `over_forums` DISABLE KEYS */;
/*!40000 ALTER TABLE `over_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paypal_config`
--

DROP TABLE IF EXISTS `paypal_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_config` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_config`
--

LOCK TABLES `paypal_config` WRITE;
/*!40000 ALTER TABLE `paypal_config` DISABLE KEYS */;
INSERT INTO `paypal_config` VALUES ('email',''),('gb','3'),('weeks','4'),('invites','1'),('enable','1'),('freeslots','5'),('freeleech','1'),('immunity','1'),('seedbonus','100'),('reputation','100'),('multiplier','5'),('currency','\'GBP\''),('staff','1'),('sandbox',''),('gb_donated_1','2'),('gb_donated_2','4'),('gb_donated_3','7'),('gb_donated_4','13'),('gb_donated_5','20'),('gb_donated_6','40'),('vip_dur_1','1'),('donor_dur_1','1'),('free_dur_1','1'),('up_amt_1','1'),('kp_amt_1','200'),('vip_dur_2','2'),('donor_dur_2','2'),('free_dur_2','2'),('up_amt_2','2'),('kp_amt_2','400'),('vip_dur_3','4'),('donor_dur_3','4'),('free_dur_3','4'),('up_amt_3','5'),('kp_amt_3','600'),('vip_dur_4','8'),('donor_dur_4','8'),('free_dur_4','9'),('up_amt_4','9'),('kp_amt_4','900'),('vip_dur_5','12'),('donor_dur_5','12'),('free_dur_5','12'),('up_amt_5','350'),('kp_amt_5','3000'),('vip_dur_6','24'),('donor_dur_6','24'),('free_dur_6','24'),('up_amt_6','450'),('kp_amt_6','4000'),('duntil_dur_1','1'),('imm_dur_1','1'),('duntil_dur_2','2'),('imm_dur_2','2'),('duntil_dur_3','4'),('imm_dur_3','4'),('duntil_dur_4','8'),('imm_dur_4','8'),('duntil_dur_5','12'),('imm_dur_5','12'),('duntil_dur_6','24'),('imm_dur_6','24'),('inv_amt_1','1'),('inv_amt_2','2'),('inv_amt_3','3'),('inv_amt_4','4'),('inv_amt_5','5'),('inv_amt_6','6');
/*!40000 ALTER TABLE `paypal_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peers`
--

DROP TABLE IF EXISTS `peers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peers` (
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
  `started` int(11) NOT NULL,
  `last_action` int(11) NOT NULL,
  `prev_action` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peers`
--

LOCK TABLES `peers` WRITE;
/*!40000 ALTER TABLE `peers` DISABLE KEYS */;
/*!40000 ALTER TABLE `peers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pmboxes`
--

DROP TABLE IF EXISTS `pmboxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pmboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `boxnumber` tinyint(4) NOT NULL DEFAULT '2',
  `name` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pmboxes`
--

LOCK TABLES `pmboxes` WRITE;
/*!40000 ALTER TABLE `pmboxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `pmboxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll`
--

DROP TABLE IF EXISTS `poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `question` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answers` mediumtext COLLATE utf8mb4_unicode_ci,
  `votes` int(5) NOT NULL DEFAULT '0',
  `multi` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll`
--

LOCK TABLES `poll` WRITE;
/*!40000 ALTER TABLE `poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll_voters`
--

DROP TABLE IF EXISTS `poll_voters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_voters` (
  `vid` int(10) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_date` int(10) NOT NULL DEFAULT '0',
  `poll_id` int(10) NOT NULL DEFAULT '0',
  `user_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`vid`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll_voters`
--

LOCK TABLES `poll_voters` WRITE;
/*!40000 ALTER TABLE `poll_voters` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll_voters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polls`
--

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls` (
  `pid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `start_date` int(10) DEFAULT NULL,
  `choices` longtext COLLATE utf8mb4_unicode_ci,
  `starter_id` mediumint(8) NOT NULL DEFAULT '0',
  `starter_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `votes` smallint(5) NOT NULL DEFAULT '0',
  `poll_question` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polls`
--

LOCK TABLES `polls` WRITE;
/*!40000 ALTER TABLE `polls` DISABLE KEYS */;
/*!40000 ALTER TABLE `polls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `body` mediumtext COLLATE utf8mb4_unicode_ci,
  `edited_by` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_date` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_title` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bbcode` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `post_history` mediumtext COLLATE utf8mb4_unicode_ci,
  `edit_reason` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('deleted','recycled','postlocked','ok') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ok',
  `staff_lock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topic_id`),
  KEY `userid` (`user_id`),
  KEY `body` (`post_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promo`
--

DROP TABLE IF EXISTS `promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added` int(10) NOT NULL DEFAULT '0',
  `days_valid` int(2) NOT NULL DEFAULT '0',
  `accounts_made` int(3) NOT NULL DEFAULT '0',
  `max_users` int(3) NOT NULL DEFAULT '0',
  `link` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` int(10) NOT NULL DEFAULT '0',
  `users` mediumtext COLLATE utf8mb4_unicode_ci,
  `bonus_upload` bigint(10) NOT NULL DEFAULT '0',
  `bonus_invites` int(2) NOT NULL DEFAULT '0',
  `bonus_karma` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo`
--

LOCK TABLES `promo` WRITE;
/*!40000 ALTER TABLE `promo` DISABLE KEYS */;
/*!40000 ALTER TABLE `promo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rating`
--

DROP TABLE IF EXISTS `rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rating` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `topic` int(10) NOT NULL DEFAULT '0',
  `torrent` int(10) NOT NULL DEFAULT '0',
  `rating` int(1) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rating`
--

LOCK TABLES `rating` WRITE;
/*!40000 ALTER TABLE `rating` DISABLE KEYS */;
/*!40000 ALTER TABLE `rating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `read_posts`
--

DROP TABLE IF EXISTS `read_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `read_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post_read` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `read_posts`
--

LOCK TABLES `read_posts` WRITE;
/*!40000 ALTER TABLE `read_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `read_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrers`
--

DROP TABLE IF EXISTS `referrers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrers` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `browser` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrers`
--

LOCK TABLES `referrers` WRITE;
/*!40000 ALTER TABLE `referrers` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reported_by` int(10) unsigned NOT NULL DEFAULT '0',
  `reporting_what` int(10) unsigned NOT NULL DEFAULT '0',
  `reporting_type` enum('User','Comment','Request_Comment','Offer_Comment','Request','Offer','Torrent','Hit_And_Run','Post') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Torrent',
  `reason` mediumtext COLLATE utf8mb4_unicode_ci,
  `who_delt_with_it` int(10) unsigned NOT NULL DEFAULT '0',
  `delt_with` tinyint(1) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `how_delt_with` mediumtext COLLATE utf8mb4_unicode_ci,
  `2nd_value` int(10) unsigned NOT NULL DEFAULT '0',
  `when_delt_with` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `delt_with` (`delt_with`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reputation`
--

DROP TABLE IF EXISTS `reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reputation` (
  `reputationid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reputation` int(10) NOT NULL DEFAULT '0',
  `whoadded` int(10) NOT NULL DEFAULT '0',
  `reason` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateadd` int(10) NOT NULL DEFAULT '0',
  `locale` enum('posts','comments','torrents','users') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posts',
  `postid` int(10) NOT NULL DEFAULT '0',
  `userid` mediumint(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reputationid`),
  KEY `userid` (`userid`),
  KEY `whoadded` (`whoadded`),
  KEY `multi` (`postid`),
  KEY `dateadd` (`dateadd`),
  KEY `locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reputation`
--

LOCK TABLES `reputation` WRITE;
/*!40000 ALTER TABLE `reputation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reputation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reputationlevel`
--

DROP TABLE IF EXISTS `reputationlevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reputationlevel` (
  `reputationlevelid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `minimumreputation` int(10) NOT NULL DEFAULT '0',
  `level` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`reputationlevelid`),
  KEY `reputationlevel` (`minimumreputation`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reputationlevel`
--

LOCK TABLES `reputationlevel` WRITE;
/*!40000 ALTER TABLE `reputationlevel` DISABLE KEYS */;
INSERT INTO `reputationlevel` VALUES (1,-999999,'is infamous around these parts'),(2,-50,'can only hope to improve'),(3,-10,'has a little shameless behaviour in the past'),(4,0,'is an unknown quantity at this point'),(5,15,'is on a distinguished road'),(6,50,'will become famous soon enough'),(7,250,'has a spectacular aura about'),(8,150,'is a jewel in the rough'),(9,350,'is just really nice'),(10,450,'is a glorious beacon of light'),(11,550,'is a name known to all'),(12,650,'is a splendid one to behold'),(13,1000,'has much to be proud of'),(14,1500,'has a brilliant future'),(15,2000,'has a reputation beyond repute');
/*!40000 ALTER TABLE `reputationlevel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_votes`
--

DROP TABLE IF EXISTS `request_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vote` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `user_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_votes`
--

LOCK TABLES `request_votes` WRITE;
/*!40000 ALTER TABLE `request_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `requested_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_by_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `filled_torrent_id` int(10) NOT NULL DEFAULT '0',
  `vote_yes_count` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_no_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `link` varchar(240) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_added` (`id`),
  KEY `requested_by_name` (`request_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `searchcloud`
--

DROP TABLE IF EXISTS `searchcloud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `searchcloud` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `searchedfor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `howmuch` int(10) NOT NULL,
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchedfor` (`searchedfor`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `searchcloud`
--

LOCK TABLES `searchcloud` WRITE;
/*!40000 ALTER TABLE `searchcloud` DISABLE KEYS */;
INSERT INTO `searchcloud` VALUES (1,'Testing',1,'');
/*!40000 ALTER TABLE `searchcloud` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shit_list`
--

DROP TABLE IF EXISTS `shit_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shit_list` (
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `suspect` int(10) unsigned NOT NULL DEFAULT '0',
  `shittyness` int(2) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `text` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shit_list`
--

LOCK TABLES `shit_list` WRITE;
/*!40000 ALTER TABLE `shit_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `shit_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shoutbox`
--

DROP TABLE IF EXISTS `shoutbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shoutbox` (
  `id` bigint(40) NOT NULL AUTO_INCREMENT,
  `userid` bigint(6) NOT NULL DEFAULT '0',
  `to_user` int(10) NOT NULL DEFAULT '0',
  `username` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `text` mediumtext COLLATE utf8mb4_unicode_ci,
  `text_parsed` mediumtext COLLATE utf8mb4_unicode_ci,
  `staff_shout` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `for` (`to_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shoutbox`
--

LOCK TABLES `shoutbox` WRITE;
/*!40000 ALTER TABLE `shoutbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `shoutbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_config`
--

DROP TABLE IF EXISTS `site_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_config` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_config`
--

LOCK TABLES `site_config` WRITE;
/*!40000 ALTER TABLE `site_config` DISABLE KEYS */;
INSERT INTO `site_config` VALUES ('site_online','1'),('autoshout_on','1'),('seedbonus_on','1'),('openreg','true'),('forums_online','0'),('maxusers','10000'),('invites','5000'),('openreg_invites','true'),('failedlogins','5'),('ratio_free','false'),('captcha_on','true'),('dupeip_check_on','true'),('totalneeded','60'),('bonus_per_duration','0.225'),('bonus_per_download','20'),('bonus_per_comment','3'),('bonus_per_upload','15'),('bonus_per_rating','5'),('bonus_per_topic','8'),('bonus_per_post','5'),('bonus_per_delete','15'),('bonus_per_thanks','5');
/*!40000 ALTER TABLE `site_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sitelog`
--

DROP TABLE IF EXISTS `sitelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` int(11) NOT NULL DEFAULT '0',
  `txt` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sitelog`
--

LOCK TABLES `sitelog` WRITE;
/*!40000 ALTER TABLE `sitelog` DISABLE KEYS */;
/*!40000 ALTER TABLE `sitelog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snatched`
--

DROP TABLE IF EXISTS `snatched`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snatched` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `connectable` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `agent` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peer_id` binary(20) NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `upspeed` bigint(20) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downspeed` bigint(20) NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `seedtime` int(11) unsigned NOT NULL DEFAULT '0',
  `leechtime` int(11) unsigned NOT NULL DEFAULT '0',
  `start_date` int(11) NOT NULL DEFAULT '0',
  `last_action` int(11) NOT NULL DEFAULT '0',
  `complete_date` int(11) NOT NULL DEFAULT '0',
  `timesann` int(10) unsigned NOT NULL DEFAULT '0',
  `hit_and_run` int(11) NOT NULL DEFAULT '0',
  `mark_of_cain` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `finished` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `tr_usr` (`torrentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snatched`
--

LOCK TABLES `snatched` WRITE;
/*!40000 ALTER TABLE `snatched` DISABLE KEYS */;
/*!40000 ALTER TABLE `snatched` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staffmessages`
--

DROP TABLE IF EXISTS `staffmessages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staffmessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) DEFAULT '0',
  `msg` mediumtext COLLATE utf8mb4_unicode_ci,
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answeredby` int(10) unsigned NOT NULL DEFAULT '0',
  `answered` int(1) NOT NULL DEFAULT '0',
  `answer` mediumtext COLLATE utf8mb4_unicode_ci,
  `new` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `answeredby` (`answeredby`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffmessages`
--

LOCK TABLES `staffmessages` WRITE;
/*!40000 ALTER TABLE `staffmessages` DISABLE KEYS */;
/*!40000 ALTER TABLE `staffmessages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staffmessages_answers`
--

DROP TABLE IF EXISTS `staffmessages_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staffmessages_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `answeredby` int(10) unsigned NOT NULL DEFAULT '0',
  `answer` mediumtext COLLATE utf8mb4_unicode_ci,
  `added` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffmessages_answers`
--

LOCK TABLES `staffmessages_answers` WRITE;
/*!40000 ALTER TABLE `staffmessages_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `staffmessages_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staffpanel`
--

DROP TABLE IF EXISTS `staffpanel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staffpanel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('user','settings','stats','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `av_class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `added_by` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`),
  KEY `av_class` (`av_class`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffpanel`
--

LOCK TABLES `staffpanel` WRITE;
/*!40000 ALTER TABLE `staffpanel` DISABLE KEYS */;
INSERT INTO `staffpanel` VALUES (1,'Flood Control','staffpanel.php?tool=floodlimit','Manage flood limits','settings',5,1,1277910147),(2,'Coders Log','staffpanel.php?tool=editlog','Coders site file edit log','other',6,1,1277909868),(3,'Bonus Manager','staffpanel.php?tool=bonusmanage','Site karma bonus manager','settings',5,1,1277910813),(4,'Non Connectables','staffpanel.php?tool=findnotconnectable','Find - Pm non-connectable users','user',4,1,1277911274),(5,'Staff Shout History','staffpanel.php?tool=staff_shistory','View staff shoutbox history','other',4,1,1328723553),(6,'Edit Events','staffpanel.php?tool=events','Edit - Add Freeleech/doubleseed/halfdownload events','settings',6,1,1277911847),(7,'Site Log','staffpanel.php?tool=log','View site log','other',4,1,1277912694),(8,'Poll Manager','staffpanel.php?tool=polls_manager','Add - Edit site polls','settings',5,1,1277912814),(9,'Ban Ips','staffpanel.php?tool=bans','Cached ip ban manager','user',5,1,1277912935),(10,'Add user','staffpanel.php?tool=adduser','Add new users from site','user',5,1,1277912999),(11,'Extra Stats','staffpanel.php?tool=stats_extra','View graphs of site stats','stats',4,1,1277913051),(12,'Template Manager','staffpanel.php?tool=themes','Manage themes','settings',6,1,1339372213),(13,'Tracker Stats','staffpanel.php?tool=stats','View uploader and category activity','stats',4,1,1277913435),(14,'Shoutbox History','staffpanel.php?tool=shistory','View shout history','other',4,1,1277913521),(15,'Backup Db','staffpanel.php?tool=backup','Manual Mysql Database Back Up','other',6,1,1277913720),(16,'Usersearch','staffpanel.php?tool=usersearch','Announcement system + Usersearch','user',4,1,1277913916),(17,'Mysql Stats','staffpanel.php?tool=mysql_stats','Mysql server stats','other',4,1,1277914654),(18,'Failed Logins','staffpanel.php?tool=failedlogins','Clear Failed Logins','user',4,1,1277914881),(19,'Uploader Applications','staffpanel.php?tool=uploadapps&action=app','Manage Uploader Applications','user',4,1,1325807155),(20,'Inactive Users','staffpanel.php?tool=inactive','Manage inactive users','user',5,1,1277915991),(21,'Reset Passwords','staffpanel.php?tool=reset','Reset lost passwords','user',6,1,1277916104),(22,'Forum Manager','staffpanel.php?tool=forum_manage','Forum admin and management','settings',6,1,1277916172),(23,'Overforum Manager','staffpanel.php?tool=over_forums','Over Forum admin and management','settings',6,1,1277916240),(24,'Edit Categories','staffpanel.php?tool=categories','Manage site categories','settings',6,1,1277916351),(25,'Reputation Admin','staffpanel.php?tool=reputation_ad','Reputation system admin','settings',6,1,1277916398),(26,'Reputation Settings','staffpanel.php?tool=reputation_settings','Manage reputation settings','settings',6,1,1277916443),(27,'News Admin','staffpanel.php?tool=news&mode=news','Add - Edit site news','settings',4,1,1277916501),(28,'Freeleech Manage','staffpanel.php?tool=freeleech','Manage site wide freeleech','settings',5,1,1277916603),(29,'Freeleech Users','staffpanel.php?tool=freeusers','View freeleech users','stats',4,1,1277916636),(30,'Site Donations','staffpanel.php?tool=donations','View all/current site donations','stats',6,1,1277916690),(31,'View Reports','staffpanel.php?tool=reports','Respond to site reports','other',4,1,1278323407),(32,'Delete','staffpanel.php?tool=delacct','Delete user accounts','user',4,1,1278456787),(33,'Username change','staffpanel.php?tool=namechanger','Change usernames here.','user',4,1,1278886954),(34,'Blacklist','staffpanel.php?tool=nameblacklist','Control username blacklist.','settings',5,1,1279054005),(35,'System Overview','staffpanel.php?tool=system_view','Monitor load averages and view phpinfo','other',6,1,1277910147),(36,'Snatched Overview','staffpanel.php?tool=snatched_torrents','View all snatched torrents','stats',4,1,1277910147),(37,'Banned emails.','staffpanel.php?tool=bannedemails','Manage banned emails.','settings',4,1,1333817312),(38,'Data Reset','staffpanel.php?tool=datareset','Reset download stats for nuked torrents','user',5,1,1277910147),(39,'Dupe Ip Check','staffpanel.php?tool=ipcheck','Check duplicate ips','stats',4,1,1277910147),(40,'Lottery','lottery.php','Configure lottery','settings',5,1,1282824272),(41,'Group Pm','staffpanel.php?tool=grouppm','Send grouped pms','user',4,1,1282838663),(42,'Client Ids','staffpanel.php?tool=allagents','View all client id','stats',4,1,1283592994),(43,'Forum Config','staffpanel.php?tool=forum_config','Configure forums','settings',6,1,1284303053),(44,'Sysop log','staffpanel.php?tool=sysoplog','View staff actions','other',6,1,1284686084),(45,'Server Load','staffpanel.php?tool=load','View current server load','other',4,1,1284900585),(46,'Promotions','promo.php','Add new signup promotions','settings',5,1,1286231384),(47,'Account Manage','staffpanel.php?tool=acpmanage','Account manager - Conifrm pending users','stats',5,1,1289950651),(48,'Block Manager','staffpanel.php?tool=block.settings','Manage Global site block settings','settings',4,1,1292185077),(49,'Advanced Mega Search','staffpanel.php?tool=mega_search','Search by ip, invite code, username','user',4,1,1292333576),(50,'Warnings','staffpanel.php?tool=warn&mode=warn','Warning Management','stats',4,1,1294788655),(51,'Leech Warnings','staffpanel.php?tool=leechwarn','Leech Warning Management','stats',4,1,1294794876),(52,'Hnr Warnings','staffpanel.php?tool=hnrwarn','Hit And Run Warning Management','stats',5,1,1294794904),(53,'Site Peers','staffpanel.php?tool=view_peers','Site Peers Overview','stats',4,1,1296099600),(54,'Top Uploaders','staffpanel.php?tool=uploader_info','View site top uploaders','stats',4,1,1297907345),(55,'Watched User','staffpanel.php?tool=watched_users','Manage all watched users here','user',4,1,1321020749),(56,'Paypal Settings','staffpanel.php?tool=paypal_settings','Adjust global paypal settings here','settings',6,1,1304288197),(57,'Update staff arrays - *Member must be offline*','staffpanel.php?tool=staff_config','Hit once to update allowed staff arrays after member promotion','settings',6,1,1330807776),(58,'Site Settings','staffpanel.php?tool=site_settings','Adjust site settings here','settings',6,1,1304422497),(59,'Hit and run manager','staffpanel.php?tool=hit_and_run_settings','Manage all hit and run settings here','settings',6,1,1373110790),(60,'Opcode Manage','staffpanel.php?tool=op','View Opcode manager','other',6,1,1305728681),(61,'Memcache Manage','staffpanel.php?tool=memcache','View memcache manager','other',6,1,1305728711),(62,'Edit Moods','staffpanel.php?tool=edit_moods','Edit site usermoods here','user',4,1,1308914441),(63,'Search Cloud Manage','staffpanel.php?tool=cloudview','Manage searchcloud entries','settings',4,1,1311359588),(64,'Mass Bonus Manager','staffpanel.php?tool=mass_bonus_for_members','MassUpload, MassSeedbonus, MassFreeslot, MassInvite','settings',6,1,1311882635),(65,'Hit And Runs','staffpanel.php?tool=hit_and_run','View All Hit And Runs','stats',4,1,1312682819),(66,'View Possible Cheats','staffpanel.php?tool=cheaters','View All Cheat Information','stats',4,1,1312682871),(67,'Cleanup Manager','staffpanel.php?tool=cleanup_manager','Clean up interval manager','settings',6,1,1315001255),(68,'Deathrow','staffpanel.php?tool=deathrow','Torrents on Deathrow','user',4,1,1394313792),(69,'Referrers','staffpanel.php?tool=referrers','View referals here','stats',4,1,1362000677),(70,'Class Configurations','staffpanel.php?tool=class_config','Configure site user groups','settings',6,1,1366566489),(71,'Class Promotions','staffpanel.php?tool=class_promo','Set Promotion Critera','settings',6,1,1396513263),(72,'Comment viewer','staffpanel.php?tool=comments','Comment overview page','user',4,1,1403735418),(73,'Moderated torrents','staffpanel.php?tool=modded_torrents','Manage moderated torrents here','other',4,1,1406722110);
/*!40000 ALTER TABLE `staffpanel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats`
--

LOCK TABLES `stats` WRITE;
/*!40000 ALTER TABLE `stats` DISABLE KEYS */;
INSERT INTO `stats` VALUES (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
/*!40000 ALTER TABLE `stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stylesheets`
--

DROP TABLE IF EXISTS `stylesheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stylesheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stylesheets`
--

LOCK TABLES `stylesheets` WRITE;
/*!40000 ALTER TABLE `stylesheets` DISABLE KEYS */;
INSERT INTO `stylesheets` VALUES (1,'1.css','V3 Default');
/*!40000 ALTER TABLE `stylesheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtitles`
--

DROP TABLE IF EXISTS `subtitles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subtitles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imdb` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` mediumtext COLLATE utf8mb4_unicode_ci,
  `fps` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poster` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cds` int(3) NOT NULL DEFAULT '0',
  `hits` int(10) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `owner` int(10) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtitles`
--

LOCK TABLES `subtitles` WRITE;
/*!40000 ALTER TABLE `subtitles` DISABLE KEYS */;
/*!40000 ALTER TABLE `subtitles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanks`
--

DROP TABLE IF EXISTS `thanks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thanks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrentid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanks`
--

LOCK TABLES `thanks` WRITE;
/*!40000 ALTER TABLE `thanks` DISABLE KEYS */;
/*!40000 ALTER TABLE `thanks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thankyou`
--

DROP TABLE IF EXISTS `thankyou`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thankyou` (
  `tid` bigint(10) NOT NULL AUTO_INCREMENT,
  `uid` bigint(10) NOT NULL DEFAULT '0',
  `torid` bigint(10) NOT NULL DEFAULT '0',
  `thank_date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thankyou`
--

LOCK TABLES `thankyou` WRITE;
/*!40000 ALTER TABLE `thankyou` DISABLE KEYS */;
/*!40000 ALTER TABLE `thankyou` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thumbsup`
--

DROP TABLE IF EXISTS `thumbsup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thumbsup` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('torrents','posts','comments','users') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'torrents',
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `commentid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thumbsup`
--

LOCK TABLES `thumbsup` WRITE;
/*!40000 ALTER TABLE `thumbsup` DISABLE KEYS */;
/*!40000 ALTER TABLE `thumbsup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `poll_id` int(10) unsigned NOT NULL DEFAULT '0',
  `num_ratings` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_desc` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_count` int(10) unsigned NOT NULL DEFAULT '0',
  `first_post` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('deleted','recycled','ok') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ok',
  `main_forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `userid` (`user_id`),
  KEY `subject` (`topic_name`),
  KEY `lastpost` (`last_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topics`
--

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `torrents`
--

DROP TABLE IF EXISTS `torrents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` binary(20) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `save_as` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_text` mediumtext COLLATE utf8mb4_unicode_ci,
  `descr` mediumtext COLLATE utf8mb4_unicode_ci,
  `ori_descr` mediumtext COLLATE utf8mb4_unicode_ci,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `type` enum('single','multi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `numfiles` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `times_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `seeders` int(10) unsigned NOT NULL DEFAULT '0',
  `last_action` int(11) NOT NULL DEFAULT '0',
  `visible` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `banned` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `num_ratings` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `nfo` mediumtext COLLATE utf8mb4_unicode_ci,
  `client_created_by` char(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `free` int(11) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','fly','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `url` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_by` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` int(10) NOT NULL DEFAULT '0',
  `allow_comments` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `poster` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuked` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `nukereason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_reseed` int(11) NOT NULL DEFAULT '0',
  `release_group` enum('scene','p2p','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `subs` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vip` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `newgenre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pretime` int(11) NOT NULL DEFAULT '0',
  `bump` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `request` int(10) unsigned NOT NULL DEFAULT '0',
  `offer` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thanks` int(10) NOT NULL DEFAULT '0',
  `description` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` mediumtext COLLATE utf8mb4_unicode_ci,
  `recommended` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `torrents`
--

LOCK TABLES `torrents` WRITE;
/*!40000 ALTER TABLE `torrents` DISABLE KEYS */;
/*!40000 ALTER TABLE `torrents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triviaq`
--

DROP TABLE IF EXISTS `triviaq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triviaq` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer3` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer4` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer5` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `canswer` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asked` tinyint(1) NOT NULL DEFAULT '0',
  `current` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`)
) ENGINE=InnoDB AUTO_INCREMENT=1693 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triviaq`
--

LOCK TABLES `triviaq` WRITE;
/*!40000 ALTER TABLE `triviaq` DISABLE KEYS */;
INSERT INTO `triviaq` VALUES (1,'What is in the Statue of Liberty&#039;s hand?','Torch','Basketball','Baseball','','','answer1',0,0),(2,'What planet is the brightest object in the sky, after the sun and moon?','Mars','Venus','Saturn','Jupiter','','answer2',0,0),(3,'Another Canadian breed, this short-coated dog comes in black, chocolate, and yellow. For many years he was the number one owned breed in North America.','Retriever, Golden','Retriever, Labrador','Retriever, Flat Coated','Retriever, Chesapeake Bay','','answer2',0,0),(4,'In which battle was the Executor, Darth Vader&#039;s personal Super Star Destroyer, involved?','Battle of Yavin (\"Episode IV\")','Battle of Endor (\"Episode VI\")','Battle of Coruscant (\"Episode III\")','Battle of Naboo (\"Episode I\")','','answer2',0,0),(5,'_____ has executed more prisoners in the past 25 years than any other state.','New York','California','North Dakota',' Texas','Tennessee','answer4',1,0),(6,'What U.S. state has the most unemployed dancers?','New York','Nevada','California','New Jersey','','answer2',0,0),(7,'How many notes are there in a musical chromatic scale?','7','12','6','','','answer2',0,0),(8,'What president who was in the White House from 1913-1921 had a cat named Puffins?','Calvin Coolidge ','John F. Kennedy ','Woodrow Wilson ','Abraham Lincoln ','','answer3',0,0),(9,'Suppose you were a Viking and were afraid of giants and thunder. Suppose that you would like to see a woman with hair made of gold; that you&#039;d like to have a hammer which, when thrown at a target, returned magically to you; or that you&#039;d like to ride a chariot drawn by goats. You would call to the &quot;slayer of giants&quot;. What god would answer your pleas?','Bragi','Thor','Tyr','Baldr','','answer2',0,0),(10,'If Johnny mows one lawn a day and gets paid 10 to mow one lawn how much money will he make in one week if he mows one lawn per day?,','140','10','70','','','answer3',0,0),(11,'What are chunks of rocks of varied size in space called?','asteroids','stars','comets','shooting stars','','answer1',0,0),(12,'Sudoku puzzles are normally created using the numbers 1-9. What other symbol(s) can be used to make a Sudoku-like puzzle?','Letters of the alphabet','Colors','Any of these can be used.','Chinese characters','','answer3',0,0),(13,'I played the top gun and I complete impossible missions. My first leading role was in &quot;Risky Business&quot; (1983) in which I danced in my underwear, socks and shirt. I also played Claus von Stauffenberg and Ron Kovic. Who do you think I am?','Anthony Hopkins','Will Smith','Tom Cruise','Morgan Freeman','','answer3',0,0),(14,'How many matches are in a standard pack?','50','100','20','','','answer3',0,0),(15,'At the worst depths of the Great Depression, _____ percent of the nation’s work force was unemployed.','5','10','15','25','50','answer4',0,0),(16,'What two U.S. states went to court in 1996 over ownership of historic Ellis Island?','New York and Connecticut','New York and New Jersey','New Yiork and Rhode Island','New York and Maryland','','answer2',0,0),(17,'Where did the 2008 Summer Olympics occur?','Tokyo, Japan','Paris, France','Beijing, China','','','answer3',0,0),(18,'Which character from Star Wars did Harrison Ford play?','Chewbacca','Han Solo','Luke Skywalker','','','answer2',0,0),(19,'The next big advance was to impose some kind of order on the particle zoo. Murray Gell-Man was equal to the task with his 1961 Eightfold Way. What shape did this &quot;Periodic Table of Particles&quot; take?','a rectangle','three pentagons',' two hexagons and a triangle','an octagon','','answer3',0,0),(20,'Isobars are lines on a weather map. What do they represent?','Equal temperature','Equal Precipitation','Equal Pressure','Equal elevation','','answer3',0,0),(21,'What do English speakers commonly call the Netherlands, but actually is the name of only part of the country?','Holland','Belgium','Germany','the Low Countries','','answer1',0,0),(22,'What optical aids was nearsighted model Grace Robin the first to show off in 1930?','Trifocals','Bifocals','Cat\'s Eye Glasses','Contact Lenses','','answer4',0,0),(23,'What British group were very successful in the 1980s, had three members with the surname Taylor (who weren&#039;t related) and were famous for their expensive music videos which often looked like travelogues?','Depeche Mode','Japan','Spandau Ballet','Duran Duran','','answer4',0,0),(24,'Firmly believing that liberating women would also liberate men, this journalist was a leader of the women&#039;s movement in the 70s. She popularised the phrase, &quot;A woman needs a man like a fish needs a bicycle.&quot;','Margaret Mitchell','Dorthy Parker','Hannah Arendt','Gloria Steinem','','answer4',0,0),(25,'Well, about this geology field trip, one student had been wanting really badly to go, but couldn&#039;t &quot;mafic.&quot; Why not?','Olivine got him','Because he felsic',' It didn\'t a pyroxene likely','He had plagioclased a paper','','answer2',0,0),(26,'Who had the British hit version of &quot;The Battle of New Orleans&quot;?','Lonnie Donegan','Joe Brown','Tommy Steele','Wee Willie Harris','','answer1',0,0),(27,'What is the national tree of the USA?','Redwood','Liquid Amber','Oak','','','answer3',0,0),(28,'How many growth rings does one tree make in a year?','I don\'t know','1','9','','','answer2',0,0),(29,'What type of whale can dive over 2,000 feet?','Blue','Killer','Sperm','Gray','','answer3',0,0),(30,'A rating of &#039;10&#039; is perfect. Who starred in the movie of that name?','Brigitte Bardot','Raquel Welch','Bo Derek','Marilyn Monroe','','answer3',0,0),(31,'Vostok, Antarctica holds record for the coldest temperature on Earth. How cold was it?','-64 degrees F','-97 degrees F','-127 degrees F','-156 degrees F','','answer3',0,0),(32,'Which city is farthest west - San Diego, Reno, or Los Angeles.','San Diego','Los Angeles','Los Vegas','Reno','','answer4',0,0),(33,'Which key is on the right of the letter A?','S','3','Enter','','','answer1',0,0),(34,'Where is the Everglades National Park?','Louisiana','Texas','Florida','Mexico','','answer3',0,0),(35,'In the name Thomas Jefferson, which letter is silent?','T ','H','J ','S','','answer2',0,0),(36,'You have a square and a circle and the perimeter of the square is equal to the circumference of the circle. Which of the two figures covers a bigger area?','The square','The circle','Both cover the same are ','',' ','answer2',0,0),(37,'What causes ocean currents to curve to the right in the northern hemisphere and to the left in the southern hemisphere and hurricanes to rotate counter-clockwise in the northern hemisphere and clockwise in the southern?','Gravity','Coriolis effect','Stubborness','Viscosity Shear ','The curve of the earth','answer2',0,0),(38,'What do you do when you mud up?','Take a mud bath.','Have the last drink before the campfire goes out.','Get everyone involved in a mud fight.','Put mud on beesting','','answer2',0,0),(39,'The amount of space a car takes up is called its:','mass','volume','weight','density','','answer2',0,0),(40,'According to a Nursery Rhyme, it&#039;s falling down all the time.','Tower Hill','Royal Oak','London Bridge','Swiss Cottage','','answer3',0,0),(41,'Oh dear, Johnny Green did a very cruel thing when he put a cat into a well, but, fortunately, a kind lad came to the rescue and pulled her out. Who was this helpful boy?','Tommy Stout','Timmy Trout','Billy Sprout','Mickey Grout','','answer1',0,0),(42,'Who said “Middle Age is when your age starts to show around your middle”?','George Burns','Jack Nicklaus','Ronald Reagan','Bob Hope','','answer4',0,0),(43,'&#039;Lost but never forgotten&#039;. Which famous aviator was lost on July 2, 1937?','Amelia Earhart','\'Wrong Way\' Corrigan','Charles Lindbergh','Alcock and Brown','','answer1',0,0),(44,'The artist is Tina Turner. The title is &#039;We Don&#039;t Need Another _____&#039;.','airline','school day','hero','homework assignment','','answer3',0,0),(46,'What &quot;tasty&quot; entertainer was born in 1890&#039;s New Orleans and was known for his blues and ragtime works?','Johnny the Cabbage James','Jelly Roll Morton','Lemon Larry Harrison','Craig Olive Sandhurst','','answer2',0,0),(47,'How many degrees does the Earth turn in one day?','90','180','270','360','','answer4',0,0),(48,'Which type of scientist studies and forecasts the weather?','Astronomer','Meteorologist','Biologist','','','answer2',0,0),(49,'What U.S. state do Knickerbockers knock around in?','Pennsylvania','Connecticut','New York','New Jersey','','answer3',0,0),(50,'What do you fear if you have Blennophobia? Something gross, I would say!! I guess if you&#039;ll probably never watch the movie &quot;Flubber&quot; ever again!','slime','insanity','worms','poison','','answer1',0,0),(51,'What is your mummy bag?','A sleeping bag tapered at the ends to reduce air space and to conserve heat.','A bag mummies were kept in.','A bag your mom used for camping laundry.','','','answer1',0,0),(52,'The game&#039;s over. You&#039;ve just &quot;put on a clinic&quot;. What have you done?','Got beaten badly','Beaten the opposing team badly','Had a close call','Lost a close game','','answer2',0,0),(53,'3 is what kind of a number?','Prime number',' Even number','is not a number','','','answer1',0,0),(54,'Kunta Kinte is a character from which novel written in 1976, later turned into a mini-series?','Malcolm X ','Attica ','Mississippi Burning ','Roots:The Saga Of An American Family ','','answer4',0,0),(55,'What is the greatest amount of children born to one woman?','20','42','69','','','answer3',0,0),(56,'Who won the Football World Cup in 1982?','Brazil','Argentina','Germany','Italy','','answer4',0,0),(57,'Let&#039;s move all the way to 1984. This singer/songwriter had a hit with the song &quot;Purple Rain&quot;. Who gave us this hit?','The Hollies','Prince','Tori Amos','The Waterboys','','answer2',0,0),(58,'An old potter was selling pots.  Once, a woman came and bought a pot for its sticker price.  She had to pay 1 plus half its price.  What was the price?','1','0.5','2','','','answer3',0,0),(59,'How long is the total shoreline of the USA?','22680m','22680cm','22680km','','','answer3',0,0),(60,'What breed was responsible for two-thirds of U.S. dog bite deaths in the 1980s?','Pit bull','German Shepard','Labrador Retriever','Bull Terrier','','answer1',1,0),(61,'When were the 29th Olympic Games held?','2007','2009','2008','','','answer3',0,0),(62,'In what Disney film can you hear the song &quot;Higitus Figitus&quot;?','Cinderella','The Little Mermaid','The Sword in the Stone','Summer Magic','','answer3',0,0),(63,'Who changes places with a pauper in The Prince and the Pauper?','Edward III','Edward VI','Charles II','George IV','','answer2',0,0),(64,'What kind of grooming requirements do Rat Terriers have?','Low maintenance- Light brushing here and there, bath every now and then.','They clean themselves.','Frequent haircuts and brushing everyday','No grooming required at all!','','answer1',0,0),(65,'What does a herpetologist study?','Sexual Deseases','Insects','Birds','Reptiles','','answer4',0,0),(66,'What was Marilyn Monroe’s name at the time of her birth?','Norma Jeane Mortensen','Marlene Dietrich','Jane Seymour','Jill Dando','','answer1',0,0),(67,'Where is Emperor Akbar’s tomb?','Delhi','Amarkot','Agra','Sikandra','','answer4',0,0),(68,'The numbers &quot;__&quot; can be found on the back of the US 5 dollar bill, in the bushes at the base of the, Lincoln Memorial.','172','5','100','','','answer1',0,0),(69,'Which story first brought fame to Mark Twain?','The Adventures of Huckleberry Finn','The Innocents Abroad','The Celebrated Jumping Frog of Calavaras County','David Copperfield','','answer3',0,0),(70,'Which of these magazines with two-letter names is similar to &quot;People&quot; magazine?','MS ','US ','CQ ','MRS ','All of these ','answer2',0,0),(71,'What U.S. state has the highest percentage of residents born in other countries?','Texas','New York','Florida','California','','answer4',1,1),(72,'Andrew Jackson was considered one of the leading candidates in the U.S. presidential election of 1824. What was Jackson&#039;s popular nickname?','The King ','Smokey ','Old Hickory ','The Tiger ','','answer3',0,0),(73,'How long did Leonardo da Vinci spend painting the Mona Lisa&#039;s lips?','8 months','12 years','10 weeks','2 years','','answer2',0,0),(74,'The ___ Arrows are the Royal Air Force&#039;s world famous aerobatic display team.','Red','Blue','Green','Polka','','answer1',0,0),(75,'This gun was invented in 1947 and is one of the most recognisable in the world normally with a curved magazine  More have been produced than all other assault rifles combined  Legendarily rugged, it lasts in active service between 20 and 40 years.','Uzi','The M1 Garand','AK 47','',' ','answer3',0,0),(76,'Which is the longest river in Canada?','St. Lawrence','MacKenzie','Nelson','Fraser','','answer2',0,0),(77,'What branch in biology includes the study of potential life beyond Earth?','Astrobiology','Anatomy','Taxonomy','','','answer1',0,0),(78,'The Godfather of Soul and &quot;the hardest working man in show business&quot; are just two of the titles this performer has earned since he began singing and dancing (some others are &quot;Soul Brother #1&quot; and &quot;the King of Funk&quot;). Who is he?','James Brown ','George Clinton ','Bootsie Collins ','Donnie Osmond ','','answer1',0,0),(79,'The story of Scotsman William Wallace who unites the Scots in a battle against England.','Braveheart','You Can\'t Take it with You','Wallace','The Story of the 13th Century','','answer1',0,0),(80,'When does the eye color set in an infant?','6-9 months','4-5 months','2-4 weeks','','','answer1',0,0),(81,'How many years are there in a decade?','10','100','5','','','answer1',0,0),(82,'Which is the capital of Norway?','Bergen','Oslo','Stavanger','Trondheim','','answer2',0,0),(83,'Which term is given to lizard-hipped dinosaurs?','Saurischians','Lizischians','Ornithiscians','mammiscians','','answer1',0,0),(84,'What South Asian city is the planet&#039;s biggest feature film producer?','Delhi','Dhaka','Bombay','Karachi','','answer3',0,0),(85,'What African river did Henry Stanley prove was not connected to the Nile?','Zambezi','Niger','Senegal','Congo','','answer4',0,0),(86,'Which two European countries lead the world in wine consumption per capita?','France and Spain','France and Italy','Italy and Spain','France and Switzerland','','answer2',0,0),(87,'What&#039;s the world&#039;s highest island mountain?','Gran Canaria','Micronesia','Mauna','Malaita','','answer3',0,0),(88,'Where did Tolstoy come from?','The USA','Russia','Turkey','','','answer2',0,0),(89,'Which of the following is actually the name of a musical instrument?','ron ron','john john','tom tom','bill bill','','answer3',0,0),(90,'Melissa is taller than Ashley.  Bill is taller than Ted and Melissa., Fred is shorter than Ashley but taller than Ted.  Who is the shortest?','Melissa ','Ashley ','Ted','','','answer3',0,0),(91,'Who directed ‘Halloween’(1978), ‘The Thing’(1982) and ‘Prince of Darkness’(1978)?','Steven Spielberg','Joe Dante','Wes Craven','John Carpenter','','answer4',0,0),(92,'What&#039;s the southernmost state capital among the 48 contiguous states?','Pheonix','Baten Rouge','Austin','Jacksonville','','answer3',0,0),(93,'When did Aristotle live?','384-322 BC','428-348 BC','4 BC – 65 AD','121-180 AD','','answer1',0,0),(94,'Increasing reliance on the use of _____ has raised questions about the future of telephone polling.','faxes','email','cell phones','instant messaging','blogs','answer3',0,0),(95,'This candy bar is unusual in that it is mostly purchased online. It consists of peanut butter and toasted coconut. What&#039;s this candy?','Zephyr','Blossom','Tastetations','Zagnut','','answer4',0,0),(96,'On June 17, 1932, the Congress of the United States passed the Lindbergh Law. To what did it pertain?','Kidnapping','Antisemitism','Trans-Atantic flight','Air mail','','answer1',0,0),(97,'Who was the original voice of Mickey Mouse?','Burl Ives','Walt Disney','Roy Disney','Dan Dailey','','answer2',0,0),(98,'Which of the following nations has neither separation of powers nor judicial review?','France','Mexico','Canada','Great Britain','Japan','answer4',0,0),(99,'Which of the following is the Illinois state fossil?','Arthropoda','Trilobite','Ammonoid','Tully Monster','','answer4',0,0),(100,'Which of the following is the longest river on Earth?','Mississippi','Nile','Amazon','Congo','','answer2',0,0),(101,'What character sung about by Simon and Garfunkel and featured in the movie &quot;The Graduate&quot; was told that &quot;Jesus loves you more than you will know&quot;?','Mr. Robinson','None of These','Miss Robinson','Mrs. Robinson','','answer4',0,0),(102,'Gas, solid, and liquid are known in chemistry as the:','Formula of precipitation','Three basic states',' Flow of all things','','','answer2',0,0),(103,'Where does sound travel the fastest?','Water','Air','Solid','','','answer3',0,0),(104,'The bow used to play a violin contains hairs from what animal?','Cat','Bear','Goat','Horse','','answer4',0,0),(105,'This vegetable is also known as the snow cabbage. What is this called?','Bon Choa','Blo Chue','Bran Chii','Bok Choy','','answer4',0,0),(106,'I was a famous actor (mostly in Westerns) known as &quot;The Duke&quot;.','John Wayne','John West','Johnny Cash','John of West Point','','answer1',0,0),(107,'What island boasts Mount Fuji?','Honshu','Hokkaido','Kyushu','Shikoku','','answer1',0,0),(108,'What do we call a scientist who studies fossils from dinosaurs?','Geologist','Archeologist','A dead Beat','Paleontologist','','answer4',0,0),(109,'In the movie “October Sky” only one teacher understands his special students could actually win what?','An Athletic Scholarship','The National Science Fair (and a college scholarship)','A Seat in the New York Philharmonic','A Lead Role in a Play on Broadway','','answer2',0,0),(110,'What country boasts the world&#039;s oldest active brewery, dating back to 1040 A.D,.?','Japan','China','Austria','Germany','','answer4',0,0),(111,'What film has a snake called Kaa in it?','101 Dalmatians','Beauty and the Beast','The Jungle Book','Toy Story','','answer3',0,0),(112,'Would a strong acid have a pH closer to:','1','5','9','13','','answer1',0,0),(113,'In oil painting, why shouldn&#039;t you dry your paintings in the dark?','You can\'t see when it\'s dry.','This may cause a thin film of oil to rise to the surface, yellowing it.','Paint needs sunlight to dry.','It will stay wet and eventually go mouldy. ','','answer2',0,0),(114,'Originally United Nations had five official languages. Which language was made the sixth official language of United Nations?','Arabic','English','French','Spanish','','answer1',0,0),(115,'A man recently discharged from the Army after a war somewhere in Asia is recruited to recover the Egg of the Phoenix. Which author wrote this?','Herbert','Heinlein','None of these authors','Aldiss','','answer2',0,0),(116,'Six is the number of one of God&#039;s created beings. Which one is represented by SIX?','Man','Lucifer','Angel','Animal','','answer1',0,0),(117,'How many squares on a chess board?','23','64','45','','','answer2',0,0),(118,'What color is a NYC taxi?','Red','Black','Yellow','','','answer3',0,0),(119,'What country calls its expressways autostrada?','Germany','Austria','Poland','Italy','','answer4',0,0),(120,'What former Playboy Bunny was a spokeswoman for a popular diet pill?','Dana Plato','Anna Nicole Smith','Vida Guerrera','','','answer2',0,0),(121,'Which gland in the human body is called the master gland?','Pancreas','Thyroid','Pituitary','Spleen','','answer3',0,0),(122,'Who is the Greek Goddess of Victory?','Aegle','Nike','Adidas','','','answer2',0,0),(123,'What is the longest word in the English language with all the letters in alphabetical order?','Almost','Egg','Aegilops','','','answer3',0,0),(124,'In boxing what do the letters K.O. stand for?','Kick out','Knock over','Knock out','',' ','answer3',0,0),(125,'Author Graham Greene wrote which of the following books?','Blackpool Rock ','Plymouth Rock ','Brighton Rock ','Windsor Rock ','','answer3',0,0),(126,'Who is the author of Pride and Prejudice?','Jane Austen','Agatha Christie','William Shakespeare','Bernard Shaw','','answer1',0,0),(127,'The U.S. Post Office handles  ___percent of the world&#039;s mail?','10','90','46','','','answer3',0,0),(128,'What type of animal, named Laika, was inside Sputnik 2 when launched into orbit in 1957?','Monkey','Dog','Cat','','','answer2',0,0),(129,'Menolly is abused both mentally and physically by her parents and other members of their small community, once her beloved mentor Petiron dies. Although she is incredibly good at something usually highly prized in her world, &quot;everyone&quot; knows that &quot;girls cannot be...&quot;; what can girls not be?','Astrogators','Administrators-in-Chief','Harpers','Spellsingers','','answer3',0,0),(130,'What U.S. senator resigned shortly after the Ethics Committee recommended he be expelled for sexual misconduct?','Byron Dorgan','John Ensign','Bob Packwood','Chris Dodd','','answer3',0,0),(131,'What Harry Callahan line did Ronald Reagan invoke to &quot;tax increasers&quot;?','Go ahead, make my day','You want me, sucker','Just give me a reason','You and what army','','answer1',0,0),(132,'What does our Earth revolve around?','The Moon ','Our Earth does not revolve around anything','The sun','','','answer3',0,0),(133,'In which city were the &quot;Chariots OF Fire&quot; Olympic Games?','Paris','Moscow','Los Angeles','Salt Lake City','','answer1',0,0),(134,'What 4,588-mile dune-laden expanse did Choi Jong-yul say he walked across &quot;because it was there&quot;?','The Sahara Desert','The Gobi Desert','The Mojavy Desert','The kalahari Desert','','answer1',0,0),(135,'What country&#039;s farthest southern and northern points are Land&#039;s End and John o&#039; Groats, respectively?','Ireland','France','Britian','Scotland','','answer3',0,0),(136,'What group released the album Sticky Fingers?',' Led Zepplin','Rolling Stones','Grand Funk','Jefferson Airplane','','answer2',0,0),(137,'What Mississippi town name provides the answer to the Arizona town of Why?','Why Not','Because','I Said So','Who Cares','','answer1',0,0),(138,'The word fossil comes from the Latin word “fodere”. What does it mean?','Buried in the Dirt','Anchient Feature','Old Relic','To Dig Up','','answer4',0,0),(139,'Which fictional character appears in &#039;Alice in Wonderland&#039;?','A dragonfly','Puff the magic dragon','the Cheshire cat','The dragon from \'Pete\'s Dragon\'','','answer3',0,0),(140,'You hear your stage manager talking about &quot;preset.&quot; What are they referring to?','Making sure that all of the audience members have seats','A small set that sits in front of the curtain','The process of checking that set pieces, props and costumes are in the right place before a show','A type of ironing starch to stiffen costumes','','answer3',0,0),(141,'How long is the wingspan of the largest bat, the flying foxes of Asia?','1 foot','3 feet','6 feet','8 feet','','answer3',0,0),(142,'To &#039;put the cat among the pigeons&#039; means to','start a new life','cause trouble by acting suddenly','endanger innocent people','','','answer2',0,0),(143,'Which outrageously funny history of England, published in 1930, describes itself as &quot;comprising all the parts you can remember, including 103 Good Things, 5 Bad Kings and 2 Genuine Dates&quot;?',' \'A History of the English-Speaking Peoples\'',' \'Horrible Histories\'',' \'1066 and All That\'','\'England\'s History in a Nutshell\'','','answer3',0,0),(144,'What was Harry Houdini’s role in the film The Grim Game?','John Dawson','Matthew Perry','Peter Pan','Harvey Hanford','','answer4',0,0),(145,'What is the sum of degrees of the interior angles of a rectangle?','1000','360','180','','','answer2',0,0),(146,'What is the name of the device used to measure time, where sand falls from the upper bulb to the lower?','hourglass','stopwatch','sundial','atomic clock','','answer1',0,0),(147,'The often-heard &#039;capeesh&#039; is a distortion of &#039;capisci&#039;, meaning:','you hungry?','ready to go?','you need to go potty?','you understand?','','answer4',0,0),(148,'Who is considered the father of geometry?','Pythagoras','Socrates','Alexander','Euclid','','answer4',0,0),(149,'What strife-torn African nation boasts a world high of 8.3 births per female?','Rwanda','Nigeria','Cameroon','Congo','','answer1',0,0),(150,'What family of tentacled horrors includes the world&#039;s most venomous animal?','Octopus','Squid','Anemone','Jellyfish','','answer4',0,0),(151,'What is in the centre of Canada’s flag?','Two swords','Sun','Cross','Maple leaf','','answer4',0,0),(152,'This is a game that is often played during indoor recess when the teacher wants to keep the class quiet but still entertained.','Red Rover, Red Rover','Dodge Ball','7-Up','Kickball','','answer3',0,0),(153,'What&#039;s a water moccasin often called, due to the white inside its mouth?','Whitemouth','White Death','Cottonmouth','Cottonjaw','','answer3',0,0),(154,'&quot;The boys are back, The boys are back, The boys are back...gonna do it again, Gonna wake up the neighborhood.&quot; are the lyrics to which song?','The Boys Are Back','We\'re All in This Together (Graduation Mix)','A Night to Remember','Walk Away','','answer1',0,0),(155,'What was the name of the fox who told Pinocchio that he should be an actor?','Gipetto','Lampwick','Honest John ','None of the above','','answer3',0,0),(156,'Who was the voice of Mr. Magoo?',' Jim Backus','Bob Denver','Phil Silvers','Tim Allen','','answer1',0,0),(157,'How many floors are there in the Empire State Building?','102','50','900','','','answer1',1,0),(158,'You have just finished writing the great American novel. What type of writing is it?','exposition','narrative','description','argumentation','','answer2',0,0),(159,'What is the national drink of Greece?','Red Wine ','Dry Martini ','White Wine ','Ouzo ','','answer4',0,0),(160,'What are painted bright yellow and left out for public use on the streets of Portland, Oregon?','Bicycles','Trash Containers','Travel Guides','Rain Coats','','answer1',0,0),(161,'In the film ‘Batman Forever’, who plays Batman?','Michael Keaton','Val Kilmer','Adam West','Adam Sandler','','answer2',0,0),(162,'Which is the first 24 hours news channel?','CNN','Disney','Star News','NBC','','answer1',0,0),(163,'What is longer a foot or a yard?, same','yard','same ','Football','','','answer1',0,0),(164,'Which movie title starring Sir Alec Guinness is derived from a 1842 Tennyson poem called &#039;Lady Clara Vere de Vere&#039;?','Kind Hearts and Coronets (1949) ','The Ladykillers (1955) ','The Lavender Hill Mob (1951) ','Great Expectations (1946) ','','answer1',0,0),(165,'Who is Norway’s most famous writer?','Edvard Munch','Edvard Greig','Henrik Ibsen','Christian Krogh','','answer3',0,0),(166,'This city was founded in 969 by the Fatimid caliphs of Egypt, as a royal residence, near their actual capital city, Fustat.','Cairo','Tripoli','Alexandria','Baghdad','','answer1',0,0),(167,'Which music group sang the song &quot;Baby Love?&quot;','The Supremes ','Abba ','The Carpenters','','','answer1',0,0),(168,'How many seeds are there in a cocoa pod?','25-40','40-60','60-100','too many to count','','answer1',0,0),(169,'When born, what size is the baby&#039;s head in relation to the rest of his/her body?','one half the size of the body ',' one quarter the size of the body','one eighteenth the size of the body','','','answer2',0,0),(170,'What jukebox musical that opened at the Minskoff Theater, features music by The Bee Gees?','Lennon','Mamma Mia!','We Will Rock You','Saturday Night Fever','','answer4',0,0),(171,'Which of the following was a dwelling place in ancient Ireland?','Fulacht fiadh','Crannog','Quern','Souterrain','','answer2',0,0),(172,'Three of the four listed movie characters share a common trait. They either have blades instead of hands, or hands with blades attached . Which movie character is the odd one out?','Freddy Krueger','Wolverine','Edward Scissorhands','Spider-man','','answer4',0,0),(173,'On a bright morning of 1877, the first Test match in cricket history was played. Here, a player playing for Australia scored the first-ever century of Test cricket. He went on to score 165* and this was the highest individual Test score from 1877-1882. Who was this batsman?','Bill Murdoch','Charles Bannerman','Mark Pettini','W. G. Grace','','answer2',0,0),(174,'Different volcanos produce lava of different viscosities. This is the main reason some volcanos erupt explosively, and some just spread runny lava over a large area. To give you an idea of how the difference in viscosities of different types of magma, consider the following experiment. You have two swimming pools, one filled with basaltic lava (think Hawaii), and one filled with rhyolitic lava (think Mount St. Helens). You drop a steel ball in the basaltic lava. It takes about eight hours to sink to the bottom. You drop a steel ball in the rhyolitic lava. About how long does it take to sink to the bottom this time?','24 hours','Eight months','One week','900 years','','answer4',0,0),(175,'In the 1958 movie ‘Cat on a Hot Tin Roof’, what is Elizabeth Taylor’s response to Paul Newman’s question, “What is the victory of a cat on a hot tin roof?”?','“I guess I don’t know, is it really a victory?”','Its her choice to be there”','“Just staying on it, I guess. Long as she can”','“She can jump off, anytime she wants”','','answer3',0,0),(176,'Which rays does the Earth release after turning the ray from the sun into heat energy?','Infrared rays','Ultraviolet rays ','Both','','','answer1',0,0),(177,'How many pairs of legs does the crab have?','5','2','1','','','answer1',0,0),(178,'What does NASA stand for?','National Astronomic and Space Administration','National Aeronautics and Space Administration','National Aeronautics and Space Association','','','answer2',0,0),(179,'F is for a town in Suffolk where the largest container port in the UK sits alongside Languard Fort. The actor Sir John Mills was born here and Wallis Simpson stayed in the town during the abdication crisis. Where are we?','Felixstowe','Fressingfield','Framlingham','Freston','','answer1',0,0),(180,'Why does a whip make a cracking sound?','It doesn\'t make a sound ','Because  its tip moves  faster  than the speed of sound','Because its tip hits something in the air','','','answer2',0,0),(181,'What&#039;s short for &quot;light amplification by stimulated emission of radiation&quot;?','Laser','Phaser','Taser','Razer','','answer1',0,0),(182,'Who used a flask with a curved neck to prove that spontaneous generation did not occur in a broth?','Spallanzani','Redi','Darwin','Pasteur','','answer4',0,0),(183,'George Clooney is the captain of a fishing boat which runs into some bad weather, while Diane Lane waits for the crew to return.','Deep Blue Sea','The Perfect Storm','Captains Courageous','Lost at Sea','','answer2',0,0),(184,'Who is the patron saint of music?','St. Agnes','St. Cecilia','St. Jerome','St. Augustine','','answer2',0,0),(185,'What&#039;s the acronym for the South Western Townships near Johannesburg?','SWT','SoWeTo','SWeT','SLUM','','answer2',0,0),(186,'What is the longest word that can be made usi?ng the letters only on one row of the keyboard?','Overrated','Typewriter','Unbelievable','','','answer2',0,0),(187,'What hide was first used to cover baseballs in 1975?','pig hide','cow hide','buffalo hide','horse hide','','answer2',0,0),(188,'Which of these words matches the definition: self government for Ireland but no control of foreign policy?','Unionism','Home Rule','Republicanism','Federalism','','answer2',0,0),(189,'What does &#039;E&#039; represent in E = MC2?','Energy',' Einstein','EBay','','','answer1',0,0),(190,'When truck drivers talk about green stamps what are they referring to?','Money','Traffic Lights ','Wheels','',' ','answer1',0,0),(191,'Which battle did not take place simultaneously with a lightsaber fight?','Battle of Naboo (Episode I)','Battle of Yavin (Episode IV)','Battle of Coruscant (Episode III)','Battle of Endor (Episode VI)','','answer2',0,0),(192,'What Nepalese city name means &quot;wooden temples&quot;?','Dharan','Katmandu','Janakpur','Ratnanagar','','answer2',0,0),(193,'Shakespeare&#039;s fair Juliet could not live without her Romeo so she committed suicide. How?','She drank poison.','She jumped from her balcony.','She slit her wrists.','She stabbed herself with Romeo\'s dagger.','','answer4',0,0),(194,'Which word connects a dog breed, a pugilist and an uprising?','Dalmatian','Boxer','Pointer','Terrier','','answer2',0,0),(195,'If you were looking at castles in Fussen, where would you be?','Bavaria, Germany','Birmingham, England','Perth, Australia','Reims, France','','answer1',0,0),(196,'In what Disney film can you hear Sean Connery sing?','Rob Roy, the Highland Rogue','Darby O\'Gill and the Little People','Kidnapped','The Sword and the Rose','','answer2',0,0),(197,'Which of the following items was not invented or first developed in China?','Silk','Hourglass','Moveable type','Chopsticks','','answer2',0,0),(198,'Pixar is a movie studio known for its computer graphic animated films. What is the first Pixar movie to include live action footage?','M-O','BURN-E','WALL-E','EVE','','answer3',0,0),(199,'The space rocket engine has to supply its own oxygen because:','They don\'t have to supply its own oxygen','There\'s no oxygen  in outer space for its fuel to burn','More oxygen makes the rocket go faster ','',' ','answer2',0,0),(200,'What body part does bulbar polio attack?','Lungs','Heart','Muscles','Kidney','','answer1',0,0),(201,'What name, also the name of a colour and of a verb meaning &quot;to abandon on a desert island&quot;, was given to runaway slaves who formed independent communities in the Americas in the 17th and 18th centuries?','Yellow','Taupe','Maroon','Ochre','','answer3',0,0),(202,'Which of the following celebrities named their baby Phinnaeus?','Angelina Jolie','Julia Roberts','Courtney Cox','Barbra Walters','','answer2',0,0),(203,'How many seconds are in an hour?','2000','193','3600','','','answer3',0,0),(204,'Cult film director Peter Jackson utilised which country&#039;s locations for filming &#039;Lord of the Rings : The Fellowship of the Ring&#039;?','Canada','New Zealand','Papua New Guinea','Easter Island','','answer2',0,0),(205,'What was Esther also known as?','Ruth','Hadassah','Rebecka','Madria','','answer2',0,0),(206,'What now extinct fowl was originally named &quot;disgusting bird&quot; by its Dutch discoverers?','Passenger Pigeon','Dodo','Northern Sea Puffin','Grey Bellied Albatross','','answer2',0,0),(207,'What is the Square of 13?','169','199','283','','','answer1',0,0),(208,'Which residing U.S. President watched along with the world, the selling of Babe Ruth to the Yankees after the 1919 season?','Woodrow Wilson','James Buchanan','James Monroe','Herbert Hoover','','answer1',0,0),(209,'What do cows drink?','Milk','Wine','Water','','','answer3',0,0),(210,'What Star sign are you if born November 11th?','Scorpio','Leo','Cancer','','','answer1',0,0),(211,'If the scale on a map is 1:10000 how long in metres will a 5cm road be in real life?','500m','10000m','5m','',' ','answer1',0,0),(212,'What was the first product of H.J. Heinz?','Ketchup','Horseradish','Barbecue sauce','Heinz 57','','answer2',0,0),(213,'Where did Harry Houdini die?','Belgrade','Detroit','Skopje','New York','','answer2',0,0),(214,'On a map, are the lines that run parallel to the equator called:','latitude lines','longitude lines','contour lines','railroad lines','','answer1',0,0),(215,'Sicily: The Airborne Forces had a terrible entry into Sicily. They were dropped all over the place! What was the cause of the poor drop?','No radar pathfinders','All of these','Gale force winds','Poorly trained airwing (52nd Troop Carrier)','','answer2',0,0),(216,'What British Commonwealth nation has the most people driving on the right side of the road?','Austrailia','India','Canada','South Africa','','answer3',0,0),(217,'In the Hawaiian language, what word can mean either &quot;hello&quot;, &quot;goodbye&quot;, or &quot;love&quot;?','wahine ','kane ','aloha ','lanai ','puki ','answer3',0,0),(218,'Everybody likes this thing because it is beautiful and precious. The reasons why women and men worship it tend to be different. It can help you to become important, attractive or rich. Vikings called it &quot;Freya&#039;s tears&quot;, &quot;Sif&#039;s hair&quot; or &quot;a serpent&#039;s lair&quot;. What is it that I wouldn&#039;t mind getting?','brilliant','sapphire','silver','gold','','answer4',0,0),(219,'Which of the following colours does not appear on the South African flag?','Purple','Red','Green','Yellow','','answer1',0,0),(220,'What&#039;s the small flap in the back of your throat called?','The Tonsils','The Uvula','The Tongue','The Esophagus','','answer2',0,0),(221,'What is it called when therapists use art to help clients deal with emotional issues?','Crazy Art','Art Therapy','Angel Painting','Self-Portrait','','answer2',0,0),(222,'What baking ingredient, sprayed at high pressure, did the U.S. Air Force replace its toxic paint stripper with?','Flour','Baking Powder','Baking Soda','Shortening','','answer3',0,0),(223,'American physicist Carl Anderson&#039;s 1932 discovered the first known example of antimatter. Examining photographs of a cloud chamber bombarded by cosmic rays, Anderson noticed the track of what antiparticle of an electron?','pi meson','muon','positron','electron neutrino','','answer3',0,0),(224,'The river that carries the highest quantity of water into the sea is the..?','Chang Jiang ','Amazon  River','Nile','','','answer2',0,0),(225,'When the French munitions ship Mont Blanc collided with the Belgian relief ship Imo on December 6, 1917, it was the largest man made explosion prior to the atomic bomb. Where did this take place?','Victoria, British Columbia','Charlottetown, Prince Edward Island','St. Johns, Newfoundland','Halifax, Nova Scotia ','','answer4',0,0),(226,'What U.S. state boasts a town called Captain Cook?','Alaska','Washington','California','Hawaii','','answer4',1,0),(227,'What milk product did the U.S  Agriculture Department propose as a substitute for meat in school lunches in 1996?,','Yogurt','Tofu','Eggs','','','answer1',0,0),(228,'Who painted The Last Supper?','Andrea Mantegna','Picasso','Da Vinci','',' ','answer3',0,0),(229,'Which book is about a little girl from Kansas?','Sisterhood of the Traveling Pants','The Wizard of Oz','Clifford, the Big Red Dog','The Outsiders','','answer2',0,0),(230,'What is the feminine of gander?','Glider','Goat','Goose','Grout','','answer3',0,0),(231,'Solid fragmented material transported and deposited that forms in layers of loose, unconsolidated material.','Lithification','Sediment','Magma','Metamorphic Rock','','answer2',0,0),(232,'His son is called A.J. and is also working in the music industry. This Jim sings legendary songs like: &#039;Time In A Bottle&#039; and &#039;Bad Bad Leroy Brown&#039;. What is his name?','Jim Troce','Jim Groce','Jim Croce','Jim Kloce','','answer3',0,0),(233,'What Australian city boasts the largest Greek population in the world outside of Greece?','Sidney','Cessnock','Victor Harbor','Melbourne','','answer4',1,0),(234,'Italian composer; violinist; musician','Otto Lilienthal','Samuel Cunard','Niccolo Paganini','Dylan Thomas','','answer3',0,0),(235,'In the video game &quot;Mass Effect 2&quot; - What is the name of the band that intrigues Morinth?','Elcor Forta','Afterlife','Hallex','Expel 10','','answer4',0,0),(236,'What year was the Playboy magazine first published?','1021','1953','1999','','','answer2',0,0),(237,'Speaking in tongues. &quot;But in the church I would rather ____________ than [speak] ten thousand words in a tongue.&quot; (1 Corinthians 14 v. 19) Which words have been omitted?','Get a lucky ducky badge ','Speak five intelligible words to instruct others ','Spend a night in Hades ','Speak a million words in a tongue ','','answer2',0,0),(238,'What city did Sigmund Freud call home?','Rome','Berlin','Vienna','Paris','','answer3',0,0),(239,'Which is the largest planet in our solar system?','Earth','Mars','Jupiter','Saturn','','answer3',0,0),(240,'Which piece in a single move moves one square straight and another square diagonally in chess?','Knight','King','Bishop','Rook','','answer1',0,0),(241,'Which poem by Edgar Allan Poe begins “Dim vales- and shadowy floods- And cloudy-looking woods,”?','Dreamland','Fairy Land','Eldorado','The Haunted Palace','','answer2',0,0),(242,'What is the hottest planet in our solar system?','Venus','Mars','Mercury','','','answer1',0,0),(243,'This shape has three sides and three angles.','Heart','Triangle','Square','','','answer2',0,0),(244,'In which year was Magna Carta signed?','1214','1066','1707','1215','','answer4',0,0),(245,'In politics, if you want anything said, ask a man. If you want anything done, ask a woman.','Cynthia Nelms ','Sydney J. Harris ','Edward Estlin Cummings ','Margaret Thatcher ','','answer4',0,0),(246,'Which country is the top car producing country in the world?','Italy','Japan','Germany','','','answer2',0,0),(247,'The average hen will lay _____ eggs a year','155','196','227','296','','answer3',0,0),(248,'Which capital city does the river Han flow through?','Tokyo','Beijing','Seoul','','','answer3',0,0),(249,'&quot;Nothing is particularly hard if you divide it into small jobs.&quot; (Henry Ford): What was the title given to an assistant to a thatcher?','yatman','yowler','yagger','yeagerman','','answer2',0,0),(250,'When did the Continental congress first issue currency to finance the Revolutionary War?','1778','1988','1723','','','answer1',0,0),(251,'Which of the following animals can&#039;t vomit?','Rats and horses','Dogs and horses','Rats and dogs','','','answer1',1,0),(252,'The largest hailstone on record was 17.5 inches in circumference. How much did it weigh?','2 pounds','4 pounds','6 pounds','8 pounds','','answer1',0,0),(253,'Which was invented first?','Telephone','Microwave Oven','Light Bulb ','Internal Combustion Engine','','answer4',0,0),(254,'What is Donald Duck&#039;s middle name?','Faunterloy ','Walter ','Hewey ','He didn\'t have one ','','answer1',0,0),(255,'Are molecules packed most tightly together in a:','solid','liquid','gas','plasma','','answer1',0,0),(256,'What bean provides the colored inks used in most U.S. daily newspapers?','Coffee Beans','Cocoa Beans','Soy Beans','Kidney Beans','','answer3',0,0),(257,'As mountains get older, mountains gradually change shape. How much does a mountain lose every 1000 years?','3.5 inches','1.5 feet','105 feet','1,005 feet','','answer1',0,0),(258,'A caldera is a large depression found in the center of what geographical feature?','volcano ','glacier ','waterfall ','dyke ','','answer1',0,0),(259,'Which Ocean is on west coast of Canada?','The Pacific Ocean','The Indian Ocean','The Arctic Ocean','','','answer1',0,0),(260,'Which currency is used in the European Union?','Euro','Yen','Pound sterling','','','answer1',0,0),(261,'Which is not one of the 5 sense&#039;s?','Hating','Seeing','Hearing','','','answer1',0,0),(262,'In what corner of Pennsylvania is Pittsburgh located?','southeast ','northwest ','southwest ','northeast ','midwest ','answer3',0,0),(263,'on the new hundred dollar bill the time on the clock tower of Independence hall is',', 1:20 ','4:10','7:30','','','answer2',0,0),(264,'What are the two ingredients of a screwdriver cocktail?','Vodka and apple juice','Gin and apple juice','Vodka and orange juice','','','answer3',0,0),(265,'Who invented popcorn?','French ','Chinese ','Native Americans','','','answer3',0,0),(266,'How many books are in the Harry Potter series?','5','2','7','','','answer3',0,0),(267,'Which one is part of the Christmas tradition?','Turkey','Pumpkin','Decorative Lights','','','answer3',0,0),(268,'Rene is taller than Casey., David is shorter than Casey and Rene., Aaron is shorter than David., Who is the second tallest?','Rene ','David','Casey','','','answer3',0,0),(269,'What blood type is the Universal Donor?','Type A','Type O','Type B','Type AB','','answer2',0,0),(270,'What country was Berlin part of when it passed one million in population?','Brunswick','Saxony','Bavaria','Prussia','','answer4',0,0),(271,'Lake Baikal in Siberia is the deepest lake in the world and has been declared a World Heritage Site. It is located in a rift valley. Rift valleys are formed due to plate tectonics.','TRUE','FALSE','','','','answer1',0,0),(272,'Written for younger readers, and first published in 1973, which post apocalyptic book tells the story of Ann Burden and Mr. Loomis?','Z for Zachariah','Children of the Dust','Shade\'s Children','The Crystal Drop','','answer1',0,0),(273,'Which one is not a vehicle?','Car','Airplane','Sofa','','','answer3',0,0),(274,'What&#039;s the least expensive and most popular fruit?','Orange','Apple','Banana','Grape','','answer3',0,0),(275,'What three planets in our solar system show signs of oxygen in their atmospheres?','Earth, Mars, Jupiter','Venus, Earth, Mars','Venus, Earth, Jupiter','Earth, Mars, Saturn','','answer2',0,0),(276,'Marty Wilde had a hit with which Phil Phillips song?','Sea of Heartaches','Sea of Trouble','Sea of Loneliness','Sea of Love','','answer4',0,0),(277,'I don&#039;t speak, I cannot hear but I always tell the truth.','Old granny ','The watch','The Mirror','',' ','answer3',0,0),(278,'What planet represents woman?','Earth','Mars','Venus','','','answer3',0,0),(279,'What was the stage name of Louis Burton Lindley, Jr., who had prominent roles in &quot;Dr. Strangelove&quot; (1964) and &quot;Blazing Saddles&quot; (1974)?','Gary Cooper','Slim Pickens','W. C. Fields','Marilyn Monroe','','answer2',0,0),(280,'What&#039;s the itchy skin condition tinea pedis better known as?','Hives','Psoriosus','Athletes Foot','Poison Ivy','','answer3',0,0),(281,'What arachnid attacks its prey with the stinger at the end of its tail?','The Wasp','The Honey Bee','The Black Widow Spider','The Scorpion','','answer4',0,0),(282,'What country would you find the city of Beijing?','Japan','India','China','','','answer3',0,0),(283,'What do you call the branch of science that deals with fungi?','gnoalactics','fungiology','mycology','truffleology','','answer3',0,0),(284,'What is the shortest word in the English language to contain A  , B  , C  , D  , E  , and F?','Fabedc','Feedback','Aecbdf','','','answer2',0,0),(285,'What country does the company that makes TLBB call home?','Greece','China','Germany','','','answer2',0,0),(286,'What is number of white pawns in chess?','4','2','6','8','','answer4',0,0),(287,'Arizona, Kentucky, Northwestern, and Northern Michigan, shared the nickname of?','Lightning','Jackalopes','Wildcats','Kangaroos','','answer3',0,0),(288,'What is the world&#039;s largest Lizard?','Komodo Drago','Bearded Dragon','Water Dragon','','','answer1',0,0),(289,'In the video game &quot;Mass Effect 2&quot; - Do you have to kill the Thresher Maw in Grunt&#039;s loyalty mission?','Yes','No','','','','answer2',0,0),(290,'What Spanish port city was founded by Carthaginian general Hamilcar Barca?','Malaga','Barcelona','Valencia','Hamilonia','','answer2',0,0),(291,'During each week of the PGA Tour season, a day or two before the actual tournament starts, many of the pros play a round with local amateurs, for a nice fee, of course. What is this round usually called?','the pro-am ','the pay-and-play ','the am-pro ','the medal round ','the first rounds ','answer1',0,0),(292,'When/where do we see TIE Bombers?','In the asteroid field (Episode V)','Battle of Coruscant (Episode III)','Battle of Yavin (Episode IV)','Battle of Naboo (Episode I)','','answer1',0,0),(293,'which animal most resembles man?','Elephant','Chimpanzee','Panda','','','answer2',0,0),(294,'Which mountain went from being 9677 feet in height to 8365 feet in height in 1980?','Mt. Ararat','Mt. Rushmore','Mt. Kilimanjaro','Mt. St. Helens','','answer4',0,0),(295,'In which board game would you find a top hat, a boot, a flat iron, and a car?','Chess','Monopoly ','Scrabble','','','answer2',0,0),(296,'How many eyelids do camels have to protect themselves from blowing sand?','3','2','1','','','answer1',0,0),(297,'The winner of the “Strong Man” award for insects goes to the Leaf Beetle. How much weight can it pull?','13 times its body weight','23 times its body weight','43 times its body weight','63 times its body weight','','answer3',0,0),(298,'Which super-hero was played by Michael Keaton, Val Kilmer & George Clooney?','Robin','Spiderman','Batman','Superman','','answer3',1,0),(299,'Charles Goodyear was famous for his vulcanization process that he discovered accidentally in 1839. He was surprised to find that when rubber was mixed with a special element in powder form, the rubber produced had better properties than the common rubber, namely more elastic and durable. What was this special element?','Phosphorous','Sulfer','Nitrogen','Chlorine','','answer2',0,0),(300,'Congress made Native Americans official citizens of the United States in','1789','1828','1865','1924','1963','answer4',0,0),(301,'Shaft was a 1971 motion picture that had a successful theme song. Who wrote and recorded it?','Isaac Hayes','Sam and Dave','Jimmy Ruffin','Stevie Wonder','','answer1',0,0),(302,'How many squares are on a chess board?','72','12','32','64','','answer4',0,0),(303,'Mark Twain is a river term. What does it mean?','Likely to rain','Waterfall ahead','Storm is brewing','Safe to navigate','','answer4',0,0),(304,'Paul is hoping to visit Philemon and requests that which of the following be prepared for him?','A room','A map','A set of accounts','An armed escort','','answer1',0,0),(305,'What astrological star sign covers July 24 - August 23?','Cancer','Virgo','Leo','','','answer3',0,0),(306,'You remember the main characters in the &#039;60&#039;s TV show, &#039;Bewitched,&#039; with Samantha Stephens, the modern witch who tries to fit in as a suburban housewife. What is the name of her husband, Darrin&#039;s, boss?','Frank Stephens ','Larry Tate ','Ted Baxter ','Roger Healey ','','answer2',0,0),(307,'He was know as &quot;the father of canning&quot;. To prove the abilities of his methodologies, what did he once can to preserve it?','A sheep','A giraffe','A horse','A monkey','','answer1',0,0),(308,'What is the name of the fun-loving teacher who takes her class on wacky field trips aboard a very unusual school bus?','Miss Wade','Miss. Frizzle','Miss Gerber','Miss Wisenewski','','answer2',0,0),(309,'The U.S. Bill of Rights was enacted in','1776','1781','1786','1791','','answer4',0,0),(310,'Under the U.S. Constitution, members of the U.S. House of Representatives have a _____ term, members of the U.S. Senate have a _____ term, and the president has a _____ term.','2 year; 6 year; 4 year','2 year; 4 year; 6 year','4 year; 4 year; 4 year','2 year; 8 year; 4 year','','answer1',0,0),(311,'What continent&#039;s macro zamia tree lives for 7,000 years?','Africa','Asia','Austrailia','South America','','answer3',0,0),(312,'Which location has the highest normal annual rainfall?','Yakutat, Alaska','Blue Canyon, California','Tallahassee, Florida','Seatle, Washington','','answer1',0,0),(313,'Who said that &quot;Necessity is the mother of invention&quot;?','Socrates','Descartes','Plato','',' ','answer3',0,0),(314,'Which mammal lives in the water?','Crocodile','Whale','Shark','','','answer2',0,0),(315,'What Berlin landmark had lost over 60 tons in shipments to the U.S. by 1990?','Olympiastadion','The Berlin Wall','Charlottenburg Palace','Victory Column','','answer2',0,0),(316,'What Scandinavian country are you in if you&#039;re vacationing in Hell?','Norway','Sweden','Finland','Denmark','','answer1',1,0),(317,'You are on a ship and over the side hangs a rope ladder with one foot rungs  The tide rises a one foot per hour  At the end of five hours, how much of the ladder will remain above the water assuming that 12 rungs were above the water when the tide began.','12','7','5','','','answer1',0,0),(318,'Does a balloon create a sonic boom when it pops?','No','Sometimes','Yes','','','answer3',0,0),(319,'It&#039;s plain to see that this is the most popular ice cream flavor. What is it?','Chocolate','Vanilla','Strawberry','Cookie dough','','answer2',0,0),(320,'What year did the National Lottery start in Britain?','2008','1994','1432','','','answer2',0,0),(321,'There was a fad called the Swiss Army Diet in the 1970s.','TRUE','FALSE','Maybe','I wish','','answer2',0,0),(322,'About how long does it take Pluto to orbit the sun:','52 years','108 years','248 years','516 years','','answer3',0,0),(323,'What percentage does water expand when frozen?','100%','1%','-11%','','','answer3',0,0),(324,'The Customs department of new Zeeland is believed to be the oldest government department. When was it established ?','August 1843',' January 1841','January 1840','August 1842','','answer3',0,0),(325,'In a standard game of Poker what card ranks between a Jack & a King?','10','12','Queen','','','answer3',0,0),(326,'I&#039;ll bet you didn&#039;t know this, but Shakespeare&#039;s Richard III was really a geologist! What did he *really* run around yelling in the last scene?','My kingdom be graben from me!','These fools cleavage me from life!','Limestone shall I under earth!','A horst, a horst, my kingdom for a horst!','','answer4',0,0),(327,'According to a 2007 report, __ out of 5 dollar bills show traces of cocaine.','4','2','1','','','answer1',0,0),(328,'When is the Feast Day of St. John the Baptist?','3/19/2011','6/24/2011','7/19/2011','8/15/2011','','answer2',0,0),(329,'In a typical deck of playing cards which suits are red?,',' Hearts and Diamonds','Hearts and Spades','Diamonds and Clubs','','','answer1',0,0),(330,'&quot;Breaking Dawn&quot;: What is Bella Cullen&#039;s &#039;gift&#039;?','Mind reading','Mental shield','Seeing the future','Controlling emotions','','answer2',1,0),(331,'What nation of over 7,000 islands has two-thirds of its population living on Luzon and Mindanao?','The Philippines','New Guinea','Timor-Leste','Indonesia','','answer1',0,0),(332,'What is the longest word in the English language with just one vowel?','Untouchable','Strengths','Bananas','','','answer2',0,0),(333,'Which philosopher was Aristotle’s master?','Socrates','Heraclitus','Diogenes','Plato','','answer4',0,0),(334,'1cm= ___mm','10','100','1000','',' ','answer1',0,0),(335,'When the recipe calls for zest of a lemon, it means to __','Cut the lemon in half and cook in the pot with the meat','Grate the lemon peel to release the zest and oils for an enhanced flavor','Place the seeds of a lemon in the stock for poaching','All of these','','answer2',0,0),(336,'In what position do adult horses normally sleep?','Sitting','Standing','They don\'t sleep','','','answer2',0,0),(337,'Which anniversary is celebrated after 25 years of marriage?','Silver','Gold','Diamond','','','answer1',0,0),(338,'In &quot;Sleepaway Camp&quot;, how did Angela&#039;s dad die?','Angela\'s dad was killed by a machete.','Angela\'s dad didn\'t die.','Angela\'s dad is killed in a hunting accident..','Angela\'s dad was killed in a boating accident.','','answer4',0,0),(339,'What is the number of states in USA?','24','50','60','49','','answer2',0,0),(340,'What city has been the center of the U.S. oil industry since 1901?','New Orleans','Dallas','Bakersfield','Houston','','answer4',0,0),(341,'What kind of party do people expecting a new baby throw?','Baby Blessings','Baby Boomers','Baby Showers','','','answer3',0,0),(342,'Who was president of the U.S. when Uncle Sam first got a beard?','Thomas Jefferson','Andrew Jackson','Abraham Lincoln','John Quincy Adams','','answer3',0,0),(343,'At -40 degrees Centigrade a person loses about 14.4 calories per hour by doing what?','Breathing','Running','Doing push-ups','','','answer1',0,0),(344,'More American workers (18%) call sick on Friday than any other day of the week. When&#039;s the lowest percent of absenteeism (11%)?','Tuesday','Monday ','Thursday ','',' ','answer1',0,0),(345,'Which was Patrick Swayze’s first notable film?','The Outsiders','Blind Date','Runaway Bride','First Blood','','answer1',0,0),(346,'A crude version of which of these devices was in use in China in the 2nd century A.D.?','Seismograph','Vacuum cleaner','Printer','Chromatograph','','answer1',0,0),(347,'Help me, help me, help me. I’ve just escaped from being murdered. He’s in the house. He’s murdered the Nanny! The words of a bloodied woman on November 7, 1974 in the Plumbers Arms in Belgravia, London, UK. Her husband, the suspected murderer she referr','Lord Lucan ','Lord Snooty ','Little Lord Fauntleroy ','Lord Haw-Haw ','','answer1',0,0),(348,'What is the Chemical Symbol for Iron?','Fe','Ir','Io','','','answer1',0,0),(349,'Which of the following animals did Diane Fossey spend her life protecting?','Bengal Tigers','Mountain Gorillas','Koalas','Komodo Dragons','','answer2',0,0),(350,'The pancreas is part of the digestive system. What other system is it part of?','the immunolymphatic system','the respiratory system','the skeletal system','the endocrine system','','answer4',0,0),(351,'During World War Two and the Korean War, a certain American GI was often the first combatant or occupier at every battle site. This (likely fictional) GI was always peering over a wall, with googly eyes and an elongated nose.','Kilroy','Lorenzo','Fitzgerald','Wuzherr','','answer1',0,0),(352,'The snowiest winter on record for Peoria, IL was in 1978 - 1979. How much snow did Peoria receive?','52 inches','65 inches','76 inches','110 inches','','answer1',0,0),(353,'This lead actor dances and sings about Jennifer Grey.','Keanu Reeves','Cory Hart','Anthony Michael Hall','Patrick Swayze','','answer4',0,0),(354,'If you are planning on acquiring a purebred dog, which of the following should you do?','Verify test results online ','Do research on what genetic conditions the breed may have ','All of these ','Ask to see genetic test results of parents and pups ','','answer3',0,0),(355,'A Hematologist is a doctor who specializes in the study of what?','Blood','Animals','Trees','','','answer1',0,0),(356,'The blanket of air that envelops the earth is called:','Lithosphere','Hydrosphere','Atmosphere','','','answer3',0,0),(357,'What animal does Pinocchio turn into?','Horse','Donkey','Frog','Dog','','answer2',0,0),(358,'Who won the Pulitzer Prize in 1969 for ‘The Armies of the Night’?','Truman Capote','Arthur Miller','William Faulkner','Norman Mailer','','answer4',0,0),(359,'In what year did the Norman Conquest take place?','1086','1065','1166','1066','','answer4',0,0),(360,'According to research at the Harvard School of Public Health Americans believe there&#039;s a 50% chance they&#039;ll be involved in a serious accident  In reality, do they have:','A 5% chance','10% chance','15% chance ','',' ','answer1',0,0),(361,'Who led the Million Man March on Washington?','Jesse Jackson','Martin Luther King Jr.','Louis Farrakhan','Malcom X','','answer3',0,0),(362,'Portugal lies east of which ocean?','Pacific','Indian','Arctic','Atlantic','','answer4',0,0),(363,'In Cockney Rhyming Slang, your &quot;boat&quot; refers to your what?','hair',' bottom','face','stomach','','answer3',0,0),(364,'If it is 2:00 pm in New York City, New York, what time is it in Los Angeles, California?','10:00:00','11:00:00','noon','13:00:00','','answer2',0,0),(365,'Keith Richards rocks on in which super group?','Aerosmith','Van Halen','Rolling Stones','The Who','','answer3',0,0),(366,'What large northern member of the weasel family has jaws strong enough to crush bones, is apparently capable of bringing down a deer or caribou, and can even make a cougar back away from its kill?','Mink ','Wolverine ','Skunk ','Marten ','','answer2',0,0),(367,'In chess when is the only time you may move two pieces during one turn?','Castling','Checkmate','Cheating','','','answer1',0,0),(368,'What M-word did Texas citizens choose as a town name that would attract folks?','Money','More','Myspace','magnet','','answer4',0,0),(369,'For his role in which film did Charlton Heston get Academy Award?','El Cid','Ben-Hur','The Ten Commandments','The Big Country','','answer2',0,0),(370,'Alex is taller than Tom and Cara. Angela is shorter than Alex, but taller than Tom.  Maddie is shorter than Tom, but taller than Cara.  Who is in the middle?','Alex','Tom','Cara','','','answer2',0,0),(371,'What is the better known singular name of a female entertainer born in Spain as María Rosario Pilar Martínez Molina Baeza with a trademark exclamation of &quot;Cuchi! Cuchi!&quot;?','Cher','Selena','Madonna','Charo','','answer4',0,0),(372,'If 3 cats eat 3 mice in 1.5 hour that means that 1 cat eats 1 mice for the same time. Therefore 10 cats will eat 10 mice in 1.5 hour. So 10 cats will eat 20 mice (twice as much) in 3 hours (twice as much).','2 hrs ','1.5 hrs ','3 hrs','',' ','answer3',0,0),(373,'This cigar smoking comedian was born in New York City in 1893. He was known to his family as Julius. He was quoted as saying &quot;Anyone who says he can see through women is missing a lot.&quot; You Bet Your Life! Who was he?','Jackie Gleason ','George Burns ','Groucho Marx ','Jack Benny ','','answer3',0,0),(374,'In which film does an African bushman travel &quot;to the end of the Earth&quot; to dispose of a Coca-Cola bottle?','Dingaka','Funny People','The Gods Must Be Crazy','Animals Are Beautiful People','','answer3',0,0),(375,'Where are 95% of the world&#039;s opals used in jewelry mined?','Argentina ','Nevada ','Australia ','Colombia ','','answer3',0,0),(376,'How were Aristotle’s students known?','Peripatetics','Scholastics','Stoics','Epicureans','','answer1',0,0),(377,'Darth Vader first appeared in which film?','The Sorcerer\'s Apprentice','Starship Troopers','Dogma','Star Wars','','answer4',0,0),(378,'Which candy&#039;s slogan is &quot;Taste the Rainbow&quot;?','Mentos','Starbursts','Skittles','Life Savers','','answer3',0,0),(379,'What is sodium chloride?','Pepper','Ginger','Salt','Onion','','answer3',0,0),(380,'In which country did the spirit Tequila originate?','Spain','Brazil','Mexico','','','answer3',0,0),(381,'What distant planet circles the sun every 84 years?','Jupiter','Neptune','Uranus','Saturn','','answer3',0,0),(382,'What South American country was home to the early human &#039;Patagonian giants&quot;?','Brazil','Chile','Argentina','Columbia','','answer3',0,0),(383,'What U.S. state gets raked by the most tornadoes annually?','Texas','Oklahoma','Kansas','Iowa','','answer1',0,0),(384,'Which one is an electrical appliance?','Lamp','Glasses','Pen','','','answer1',0,0),(385,'Which city in the world has the largest population (not measured by metropolitan area)?','Tokyo','New York City','Mexico City','','','answer2',0,0),(386,'In ‘The Magnificent Seven Ride!’ (1972), who took over the role made famous by Yul Brynner?','Lee Marvin','Charles Bronson','Lee Van Cleef','Steve McQueen','','answer3',0,0),(387,'“Friends” is set in New York, a city in the United States of America. Which of the following is a popular nickname for New York?','The Big Apple','The Big Grape','The Big Pear','The Big Pomegranate','','answer1',0,0),(388,'There are lots of ways to travel down Main Street U.S.A. in style. Which of these is NOT one of them?','Horse-drawn streetcar','Vintage auto','Fire engine','Trolley','','answer4',0,0),(389,'&quot;If one synchronised swimmer drowns, do all the rest have to drown too?&quot; Who said this?','Steve Martin','Jerry Springer','Jimmy Durante','Steven Wright','','answer4',0,0),(390,'How many feet are there in 1/2 mile ?','500 ft ','2000 ft ','2640 ft','','','answer3',0,0),(391,'Which disaster took place in Kobe Japan in 1995?','Nothing happened','Earthquake',' Hurricane','',' ','answer2',0,0),(392,'If your mouth was completely dry, which of the following statements is true?','You would no be able to distinguish the taste of anything','You wouldn\'t be able to swallow anything ','You would die','',' ','answer1',0,0),(393,'Who directed &quot;Close Encounters of the Third Kind&quot;?','Steven Spielberg','David Lean','George Lucas','Rob Reiner','','answer1',0,0),(394,'What bird can&#039;t fly but can swim?','Penguin','Duck','Chicken','','','answer1',0,0),(395,'What gas do plant absorb from the atmosphere?','NO3','CO2','O2','','','answer2',0,0),(396,'Which film is in Aramaic and Latin?','The Ten Commandments','Ben Hur','Apocalypto','The Passion of the Christ','','answer4',0,0),(397,'Who wrote “All modern American literature comes from one book by Mark Twain called Huckleberry Finn…”?','Aldous Huxley','Julian Huxley','P. G. Wodehouse','Ernest Hemingway','','answer4',0,0),(398,'Ants are believed to make up what percentage of the total weight of all the animals in the world?','0.05','0.1','0.15','0.2','','answer2',0,0),(399,'What is the fundamental belief in the existence of a &#039;God&#039; or &#039;gods&#039; or &#039;the divine&#039; called?','Atheism','Theism','Agnosticism','Logical Empiricism','','answer2',0,0),(400,'Which of these is the hottest?','The Sun','A campfire','Lava','','','answer1',0,0),(401,'Born on Dec. 24, 1971, he&#039;ll make you &#039;shake your bon-bon&#039; over to the end of the page to check your answers!','Ricky Martin','JC','Justin Timberlake','Bill Clinton','','answer1',0,0),(402,'What&#039;s the third-largest continent in square miles?','South America','Austrailia','Africa','North America','','answer4',0,0),(403,'How many bones make up normal human skull?','22','25','34','','','answer1',0,0),(404,'Which of these game show hosts was born in Canada?','all of them ','Monty Hall ','Alex Trebek ','Howie Mandel ','','answer1',0,0),(405,'What organization is Jennie Garth a part of, for animal rights?','TAPE','PATE','PETA','PEAT','','answer3',0,0),(406,'What Benjamin Holt invention was good news to farmers in 1900?','Combine','Hay Bailer','Tractor','Furrow','','answer3',0,0),(407,'Who said &quot;People who don&#039;t think probably don&#039;t have brains; rather, they have grey fluff that&#039;s blown into their heads by mistake&quot;?','Winnie the Pooh','Margaret Thatcher','Albert Einstein','Ozzy Osbourne','','answer1',0,0),(408,'Why is it important to portect eyes from ultraviolet radiation?','Ultraviolet radiation can lead to the development of cataract','It irritates the eyes',' It makes it hard to see when you go inside ','','','answer1',1,0),(409,'What body of water is approximately nine times saltier than ocean water?','The Great Salt Lake','The Sargasso Sea','The Dead Sea','The Baltic Sea','','answer3',0,0),(410,'How many furlongs is one mile?','Six','Eight','Four','Twelve','','answer2',0,0),(411,'Where did Cujo get bitten?','nose','neck','ear','paw','','answer1',0,0),(412,'What percent of the Earth&#039;s water is locked in ice caps and glaciers.','2','10','15','',' ','answer1',0,0),(413,'Which band included Phil Collins and Peter Gabriel?','Led Zepplin','Queen','Genesis','The Eagles','','answer3',0,0),(414,'What&#039;s the only Central American country without a coastline on the Caribbean?','Costa Rica','El Salvadore','Guatemala','Nicaragua','','answer2',0,0),(415,'Which of these is impossible for you to lick?','Your elbow','Your knee','Your finger','','','answer1',0,0),(416,'Which of the following is an example of a palindrome?','Building ','Cheese ','Facetious ','Rotor ','','answer4',0,0),(417,'Who was the candidate in the Presidential Election of 1992, who did not belong to Republican or Democratic Party?','Hugh Ross Perot','Jack Anderson','Patrick Buchanan','Walter Mondale','','answer1',0,0),(418,'We all know that &quot;Spam&quot; is now regarded as unwanted or junk e-mail, however spam was originally what type of food?','Chopped beef ','Chopped pork ','Chopped chicken ','Chopped lamb ','','answer2',0,0),(419,'What continent are you on if you&#039;re lost in the eastern tip of Egypt?','Europe','Africa','Austrailia','Asia','','answer2',0,0),(420,'An ostrich&#039;s eye and its brain which one is bigger?','Its brain','Same size ','Its eye','',' ','answer3',0,0),(421,'Jim went to the movies and paid 49.00 for one movie. How much money would Jim have to pay if he went to 7 movies?','313','343','35,643.00','',' ','answer2',0,0),(422,'What nation is prowled by 60 percent of the world&#039;s tigers?','Siberia','India','Thailand','China','','answer2',0,0),(423,'A fantastic feat was performed by a cow when she jumped over the moon - and without the aid of a space suit!  What did a little dog do when he witnessed this marvellous antic?','cried','barked','howled','laughed','','answer4',0,0),(424,'What religion has the most adherents?','Budism','Christianity','Islam','Jewism','','answer2',1,0),(425,'What Spanish islands are Gomera, Hierro and Lanzarote a part of?','The Canary Islands','The Mopion Islands','The Grenedines','Isla de Margarita','','answer1',0,0),(426,'1, 3, 5, 7, x:','9','11','13','','','answer1',0,0),(427,'After decades, this one-time assistant stepped into the leadership position and led the Israelites through additional hurdles. Who was this faithful servant of not only God, but also the Israelite nation?','Joshua','Ehud','Caleb','Obed','','answer1',0,0),(428,'Which was Elizabeth Taylor’s first film?','Lassie Come Home','Father of the Bride','There’s One Born Every Minute','Courage of Lassie','','answer3',0,0),(429,'Which is the official Ferrari racing color?','Black','Pink','Red','','','answer3',0,0),(430,'Keanu Reeves starred in the 1994 smash hit &quot;Speed&quot; with what actress?','Dennis Hopper','Carrie-Anne Moss','Sandra Bullock','Julia Roberts','','answer3',0,0),(431,'What is the nickname Harvey Dent got when he worked internal affairs in The Dark Knight?','Riddler','Two-Face','Joker','','','answer2',0,0),(432,'Beretta&#039;s cockatoo&#039;s name was?','Fred','Seinfefld','Birdie','Mac','','answer1',1,0),(433,'What is the largest city in Australia?','Queanbeyan','Sydney','Albury','','','answer2',0,0),(434,'Total volume of blood in a normal adult human being is','5-6 liters','3-4 liters','8-10 liters','10-12 liters','','answer1',0,0),(435,'Which country has the highest per capita consumption of cheese?','Greece','America','Japan','','','answer1',0,0),(436,'Follow my long axis, and I point the way the glacier moved.','Crevasse','Drumlin','Meander','Cirque','','answer2',0,0),(437,'What is a tarp for camping?','A type of fish you caught while camping.','Waterproof canvas or laminated material.','An instrument used round the campfire.','A type of camping stove','','answer2',0,0),(438,'What Scottish-born American tycoon made a fortune in the steel industry and has a dinosaur, among other things, named after him?','Henry Clay Frick','John Roll McLean','J.P. Morgan','Andrew Carnegie','','answer4',0,0),(439,'Traditional Italian pesto is made from basil, olive oil and which nut?','walnut ','pine nut ','almond','','','answer2',0,0),(440,'Where can you get a Burrito Supreme?','Del Taco ','Carl\'s Jr. ','Jack in the Box ','Taco Bell ','','answer4',0,0),(441,'Legal entity created by a government to exercise some of the powers of the government is the:','Private sector','Government-owned corporation','Public Sector Undertaking','','','answer2',0,0),(442,'What future Soviet dictator was training to be a priest when he got turned on to Marxism?','Groucho Marx','Jospeh Stalin','Lennin','Frederick Malchovich','','answer2',0,0),(443,'What body part becomes infected if you contract cholera?','The Intestine','The Kidney','The Gallbladder','The Stomach','','answer1',0,0),(444,'Black-eyed peas are not peas.  What are they?','Beans','Chick-peas','lentils','soybeans','','answer1',0,0),(445,'What was the only TV show of the 1970s to have its theme top Billboard&#039;s Hot 100?','Welcome Back Kotter','One Day at a Time','Sanford & Son','Brady Bunch','','answer1',0,0),(446,'Albert Einstein was a scientist famous for his work on physics. Where was he born?','Germany','United States','France','Poland','','answer1',0,0),(447,'This art term, describing an outlined image of a solid figure and having the appearance of a &#039;shadow&#039;, was named for a finance minister in the government of French King Louis XV. What is the name of this shadowy art form?','Blotter','Poster','Silhouette','Modern','','answer3',0,0),(448,'In which country was Pasternak&#039;s Dr. Zhivago first published?','Italy','Russia','Germany','Poland','','answer1',0,0),(449,'What will fall off of the Great Sphinx in 200 years due to pollution and erosion, according to scholar Chikaosa Tanimoto?','His nose','His tail','His head','His leg','','answer3',0,0),(450,'How many minutes in 2.5 hours?','300','150','120','','','answer2',0,0),(451,'The symbol of the God Zeus and the scar on Harry&#039;s forehead are both in what shape?','Star','Circle','Bird','Lightning bolt','','answer4',0,0),(452,'How many athletes in an Olympic relay race?','2','4','8','','','answer2',0,0),(453,'What angle is formed by the hands of a clock at 4 o&#039;clock?','120','180','182','','','answer1',0,0),(454,'What did Aristotle call metaphysics?','Cosmology','First philosophy','Psychology','Geology','','answer2',0,0),(455,'When magma cools and hardens, it forms:','igneous rock','sedimentary rock','metamorphic rock','gypsum','','answer1',0,0),(456,'According to a saying, what is it that speaks louder than words?','Movies','Actions','Pictures','','','answer2',0,0),(457,'Movies: An abused beagle runs away from home, where he meets a young boy who takes him in against his father&#039;s wishes. What is the name of the dog, which is also the title of this 1996 film?','Shiloh','Duke','Blackie','Marmalade','','answer1',0,0),(458,'Why are sunglasses important to wear?','So no one knows who you are','To protect the eyes from ultraviolet radiation','To be fashionable','','','answer2',0,0),(459,'Which insect is honored with a statue in Enterprise, Alabama?','Boll Weevil','Praying Mantis','Grasshopper','Butterfly','','answer1',0,0),(460,'What are there 88 of in the night sky, according to the international Astronomical Union?','Constellations','Nebula','Galaxies','Red Dwarf Stars','','answer1',0,0),(461,'The 12 Days of Christmas -- (Song) On the eleventh day of Christmas my true love sent to me eleven _________ __________.','Pumpkin pies','Pipers piping','Kids kissing','Silver statues','','answer2',0,0),(462,'Which female science fiction character describes herself thus? &quot;I am thirty-three years old. I have brown hair. I stand five seven without shoes&quot;.','Lessa','Offred','Anita Blake','Éowyn','','answer2',0,0),(463,'What baseball announcer said Pope Paul VI&#039;s death &quot;puts a damper on even a Yankees win&quot;?','Marv Albert','Joe Garagiola, Sr.','Phil Rizzuto','Howard Cosell','','answer3',0,0),(464,'Who has a friend named Baloo and a foe named Shere Khan?','Natty Bumppo','Pigling Bland','Uriah Heep','Mowgli','','answer4',0,0),(465,'Russian microbiologist; won the Nobel Prize in Medicine','Claude Debussy','Moshe Dayan','Ilya Ilyich Mechnikov','W. H. Auden','','answer3',0,0),(466,'Please name the singer who had a 1980 U.S. number one hit with these lyrics: &quot;Hot funk, cool punk, even if it&#039;s old junk It&#039;s still rock and roll to me...&quot;','Stevie Wonder ','Tom Petty ','Billy Preston ','Billy Joel ','','answer4',0,0),(467,'What&#039;s the purpose in why my cat &quot;kneads&quot;?','a sign of well-being or contentment','a sign of aggressiveness','a sign of feeling sad','a sign of fear','','answer1',0,0),(468,'What Canadian city&#039;s name means &quot;muddy water&quot;?','Toronto','Wetaskiwin','Winnipeg','Miramichi','','answer3',0,0),(469,'What state is San Francisco located in?','New York','California','Texas','','','answer2',0,0),(470,'What&#039;s the more common term for herpes zoster?','Venerial Desease','Shingles','Gout','Hives','','answer2',0,0),(471,'How much did it cost to buy the FIRST box of Crayola crayons when they were first sold in 1903?','Five Cents','Ten Cents','Fifteen Cents','A Quarter','','answer1',0,0),(472,'Which of these holidays is the equivalent of Halloween?','Yule','Imbolc','Samhain','Beltaine','','answer3',0,0),(473,'Which of these animals is not a fish?','Blenny','Bunting','Bream','Barracuda','','answer2',0,0),(474,'Who said, &quot;I was thrown out of college for cheating on the metaphysics exam. I looked into the soul of the boy next to me&quot;?','Steve Martin','Bill Murray','Woody Allen','Dean Martin','','answer3',0,0),(475,'Which animal is not halobios?','Whale','Crocodile','Shark','','','answer2',0,0),(476,'Which pupil of Aristotle became a conqueror?','Callisthenes','Theophrastus','Alexander','Julius Caesar','','answer3',0,0),(477,'What color is the cross on the flag of Finland?','Red','Green','Blue','','','answer3',0,0),(478,'In the Country and Western song &#039;I&#039;m So Lonesome I Could ________&#039;','Call','Fight','Drink','Cry','','answer4',0,0),(479,'What was an official language in 87 nations and territories, by 1994?','English','French','Spanish','German','','answer1',1,0),(480,'Which of these is the Latin version of the name for the goddess of the moon and hunt?','Diana','Demeter','Phaedra','Thetis','','answer1',0,0),(481,'Match the definition to one of these words: Hitler&#039;s invasion of the USSR (Russia).','Operation Barbarossa','Operation Sea Lion','Operation Market Garden','Operation Torch','','answer1',0,0),(482,'They call me San Andreas and monitor me around the clock. What am I?','A normal fault','A strike-slip fault','A reverse fault','A thrust fault','','answer2',0,0),(483,'Which city is also called Motor City?','San Francisco','Detroit','Santiago','','','answer2',0,0),(484,'What U.S. state boasts the towns of Gulf Stream, Lakebreeze and Frostproof?','Georgia','Mississippi','Florida','Alabama','','answer3',0,0),(485,'Can you fill in the blank? I walk this empty street on the __________ of ________ ________.','boulevard, broken dreams','avenue, shallow hearts','street, painful thoughts','rue, rueful hands','','answer1',0,0),(486,'During his entire life Vincent Van Gogh sold how many paintings?','211','39','1','',' ','answer3',0,0),(487,'What is the sacred river in India?','The Yellow River','The Gan','The Ganges','','','answer3',0,0),(488,'Which ocean is located on the west coast of the USA?','The Arctic Ocean','The Pacific Ocean','The Indian Ocean','','','answer2',0,0),(489,'What star sign are you if born August 26th?','Leo','Virgo','Taurus','','','answer2',0,0),(490,'With which 20th century art movement were Salvador Dali, René Magritte, and André Breton associated?','Surrealism','Dadaism','Post-Impressionism','Outsider','','answer1',0,0),(491,'Simon and Garfunkel ushered in 1966 with a chart topping song on January 1, 1966. Which song was their premier number one?','Eve of Destruction','The Sounds of Silence ','Turn! Turn! Turn!','Red Rubber Ball','','answer2',0,0),(492,'Ancient Northern men were passionate and didn&#039;t have to find a pretext to kiss a girl. Meanwhile, modern victims of civilization have to look forward to celebrating Christmas and use &quot;Baldur&#039;s bane&quot; to snog. What will help you kiss?','holly','amaryllis','ivy','mistletoe','','answer4',0,0),(493,'What is the name of 0 degrees latitude?','Equator','North Pole','South Pole','','','answer1',0,0),(494,'The island of Fiji can be found in which ocean?','Indian Ocean','Pacific Ocean','Atlantic Ocean','Arctic Ocean','','answer2',0,0),(495,'What state grew to become the second most populous in the U.S. , by 1994?','California','Florida','Arizona','Texas','','answer4',0,0),(496,'What planet represents woman ?','Mars','Venus','Earth','','','answer2',0,0),(497,'A baby is born with all the following reflexes, except...','The ability to swim','Understanding foreign language','Sucking ','Grasping a finger','','answer2',0,0),(498,'Which of these horror movies was the first to see a release date?','Halloween','Black Christmas','Friday the 13th','Carrie','','answer2',0,0),(499,'The American Bill of Rights was enacted in','1776','1781','1791','1789','','answer3',0,0),(500,'In which of these sports will you find a green?','Boxing','Golf','Darts','Rowing','','answer2',0,0),(501,'What U.K. principality has its capital in Cardiff?','Wales','Gwynedd','Marcher','Sealand','','answer1',0,0),(502,'Which word links the following: a shoe repairer, a fish, and a delicious baked dessert from America?','Smithy','Cobbler','Pavlova','Barramundi','','answer2',0,0),(503,'Olympus Mons is a volcano almost three times as tall as Mount Everest. Is it found on:','Earth','Venus','Mars','Titan','','answer3',0,0),(504,'How many years elapsed between DH Lawrence&#039;s writing of Lady Chatterley&#039;s Lover and the book&#039;s publication?','32','33','34','35','','answer1',0,0),(505,'Which planet is closest to the sun?','Venus','Mars','Mercury','Earth','','answer3',0,0),(506,'Who was told “Beware the Ides of March.”?','Julius Caesar','Augustus Caesar','Mark Antony','Cleopatra','','answer1',0,0),(507,'People on Long Island, New York, USA, build their communities on me. My origin is from the north and a colder climate.','Wave-built terrace','Anticline','Tuff','End moraine','','answer4',0,0),(508,'How many wives can a man legally have in the United States?','2','10','1','',' ','answer3',0,0),(509,'What is the diameter of a golf hole?','2 inches','4 1/4 inches','8 inches','','','answer2',0,0),(510,'Who said, &quot;Baseball is 90 percent mental. The other half is physical&quot;?','Tommy Lasorda','Yogi Berra','Babe Ruth','Ty Cobb','','answer2',0,0),(511,'Which of the following is called “a thunderstorm’s worst killer”?','Rain','Lightning','Tornadoes','Hailstones','','answer2',0,0),(512,'Which is the largest gland in the human body?','thyroid','liver','pancreas','none of these','','answer2',0,0),(513,'It occurs once in a minute twice in a week and once in a year','The letter \"e\"','Sunset','Tornado','',' ','answer1',0,0),(514,'When did Apollo arrive on the moon?','1969','Never','it was a hoax','',' ','answer1',0,0),(515,'Which 19th-century artist inspired the American Congress to create the National Park System?','Vincent van Gogh','Albert Bierstadt','Ansel Adams','Winslow Homer','','answer2',0,0),(516,'Where is Wall Street?','Paris','New York','San Francisco','','','answer2',0,0),(517,'In Germany she is Veronique, in The Netherlands she is Ravian, and in Denmark, Norway and Sweden she is Linda. This 11th century French peasant girl was transported through time, trained as a &quot;Spatio-Temporal Agent&quot; and assigned to be the partner of Valerian. Like J.M. Barrie&#039;s Wendy, her original name was made up for her character by the authors; who is she?','Laurana','Laureline','Calanthe','Clodine','','answer2',0,0),(518,'What capital has a name meaning &quot;city of Islam&quot;?','Amman','Islamabad','Ar Riyad','Manama','','answer2',0,0),(519,'In Aristocats, What kind of cat is Thomas O&#039;Malley?','Scatty Cat','Alley Cat','Posh Cat','House Cat','','answer2',0,0),(520,'What year did the Beverly Hills Diet become a bestseller in the United States?','1987','1983','1981','1985','','answer3',0,0),(521,'In what direction does the sun set?','South','West','East','','','answer2',0,0),(522,'Whom does Superman love?','Jane Grey','Shannon Kent','Lois Lane','Fiona Smith','','answer3',0,0),(523,'What peppery spice shares its name with the capital of French Guiana?','Cayenne','Habanero','Jalapeno','Tobasco','','answer1',0,0),(524,'As well as constructive and destructive plate margins, there are also less common types. One of these is a conservative plate margin, which can lead to earthquakes. What movement occurs with conservative plate margins?','Plates moving away from each other','Oceanic plates moving towards each other','No plate movement at all','Two plates moving past each other','','answer4',0,0),(525,'By the time you are 75 years old, how many years will you have spent sleeping:','17','23','37','42','','answer2',0,0),(526,'Which Western philosopher declared in a political tract from 1690 that all individuals have certain natural rights, including those of life, liberty, and property?','Thomas Hobbes','John Locke','Jean Jacques Rousseau','Thomas Jefferson','','answer2',0,0),(527,'What transport machine has forks gears and a chainwheel?','A Car','A boat','A Bicycle','',' ','answer3',0,0),(528,'When did Napoleon die?','In 2008','In 1999','In 1821','','','answer3',0,0),(529,'In the music group The Beatles which instrument did Paul play?','guitars','bass','drums','','','answer2',0,0),(530,'Where is the Golden Gate Bridge located?','NYC','New Jersey','San Francisco','','','answer3',0,0),(531,'How many digits in a Visa Card number?','15','16','12','','','answer2',0,0),(532,'Where is the best place to keep tomatoes?',' in the fridge ','at room temperature ','under your bed','','','answer2',0,0),(533,'Henri Victor Regnault was a French thermodynamicist who was noted for the discovery of PVC. The incident took place in 1835 when he accidentally left a flask of colorless solution under sunlight. He returned several hours later, only to find that there was white solid formed in the flask. The solid was none other than the ever-useful PVC. What does PVC stand for?','Polyvinyl chloride','Polyvanadium chloride','Polyvinyl chromate','Polyvanadium chromate','','answer1',0,0),(534,'Which northwestern state borders only two other states?','Oregon','Washington','Idaho','Montana','','answer2',0,0),(535,'Tom Jones had a 1966 hit with &quot;The Green Green Grass of Home&quot;. Which of the following had NOT recorded it before then?','Porter Waggoner','Merle Haggard','Jerry Lee Lewis','Bobby Bare','','answer2',0,0),(536,'A matron of honour is traditionally what?','The mother of the bride','A nurse','A married woman','A family member','','answer3',0,0),(537,'What&#039;s the third-largest continent in square miles?','Austrailia','Affrica','South America','North America','','answer4',0,0),(538,'What is the Square of 4?','19','16','18','','','answer2',0,0),(539,'Which person is on the US 50 dollar bill?','Ulysses S. Grant ','Theodore Rosevelt  ','John Muir','','','answer1',0,0),(540,'How many different species (kinds) of insects are there?','100000','250000','500000','1000000','','answer4',0,0),(541,'It had been 86 years since the Red Sox won the World Series. What team remains as having the longest span since a World Series title?','Chicago Cubs (National League)','Chicago White Sox (American League)','Cleveland Indians (American League)','San Francisco Giants (National League)','','answer1',0,0),(542,'Who said, in reply to a reporter`s sleeze allegations, “If I’d had as many affairs as you fellows claim, I`d be speaking to you from a jar in the Harvard Medical School.”?','Warren Beatty','Frank Sinatra','Bill Clinton','Errol Flynn','','answer2',0,0),(543,'Which following animal lives in the sea?','Whale  ','Kangaroo','Lion','','','answer1',0,0),(544,'What&#039;s the main mode of transport for the nomads who make up half of Somalia&#039;s population?','Camel','Horse','Jeep','Foot','','answer1',0,0),(545,'What is the name of the fruity toucan bird on &quot;Froot Loops&quot;?','Sam','Dom','Dan','Pam','','answer1',0,0),(546,'What do residents of Bunyol, Spain, throw at each other during the LA Tomatina Festival?','Cow intestines','Cactus Flowers','Water Soaked Foam Balls','Tomatoes','','answer4',0,0),(547,'With what kind of transport would you associate the names Heathrow, Gatwick and Stansted?','Driving','Rail','Flying','Sailing','','answer3',0,0),(548,'What regional accent did Americans deem sexiest, most liked and most recognizable?','Southern','Eastern','Midwestern','Southwestern','','answer1',0,0),(549,'What&#039;s the only U.S. state to share a border with one of Canada&#039;s Maritime Provinces?','Washington','Vermont','Maine','Alaska','','answer3',0,0),(550,'Who is credited with the discovery of penicillin?','Dr. Charles Drew','Dr. Edward Jenner','Sir Alexander Fleming','Dr. Joseph Penic','','answer3',0,0),(551,'What Alpine country&#039;s women got the right to vote in 1971?','Switzerland','Austria','Germany','Italy','','answer1',0,0),(552,'What is the feminine of peacock?','Peahen','Peanut','Pierce','Peak','','answer1',0,0),(553,'Which scientist would study rocks and minerals?','Meteorologist','Chemist','Botanist','Geologist','','answer4',0,0),(554,'What barnyard animal utterance is known in France as groin groin?','Moo-Moo','Oink-Oink.','Baa-Baa','Quack-Quack','','answer2',0,0),(555,'What would you be watching if you were in the stands at Wrigley Field?','A basketball game','A tennis match','A horse race','A baseball game','','answer4',0,0),(556,'Usually when people walk, do thier left arms swing with their right or left legs?','Left','Both','Right','',' ','answer3',0,0),(557,'What artist associated with the Moulin Rouge was portrayed on film in 1952 by Jose Ferrer and again nearly fifty years later by John Leguizamo?','Claude Monet','Henri de Toulouse-Lautrec','Jan Vermeer','Pablo Picasso','','answer2',0,0),(558,'How long is three Mile Island?','2.5 miles','3 miles','1 mile ','','','answer1',0,0),(559,'Which movie is NOT about a beauty contest?','Miss Congeniality','Miss Potter','Little Miss Sunshine','Miss All-American Beauty','','answer2',0,0),(560,'Which person is on the US 1 dollar bill?','George Washington','Obama','John F. Kennedy','',' ','answer1',0,0),(561,'This kind of number is divisible by only one and itself. The first few examples are 2, 3, 5, 7, and 11.','Rational','Prime','Deficient','Mersenne','','answer2',0,0),(562,'What country is bordered on the west by Germany and on the est by Ukraine and Belarus?','Poland','Denmark','Czech Republic','Slovakia','','answer1',0,0),(563,'Where is the secretariat of United Nations?','Geneva','London','New York','Paris','','answer3',0,0),(564,'What kind of dogs are white with black spots all over them?','Poodles','Bulldogs','Dalmatians','','','answer3',0,0),(565,'What is the feminine of horse?','Hearse','Hear','Heart  ','Mare','','answer4',0,0),(566,'Thomas Jefferson counted the founding of which university to be among his   greatest accomplishments?','the College of William and Mary','University of Virginia','Harvard','Yale','','answer2',0,0),(567,'Which state of matter has neither a definite shape nor a definite volume?','solid','liquid','gas','plasma','','answer3',0,0),(568,'How many eyes are there on a pack of 52 cards?','42','13','33','','','answer1',0,0),(569,'Hockey is, of course, Canada&#039;s national winter sport, but this C sport is hugely popular with Canadians of all ages and genders.','Crabbing','Curling','Coldwater rafting','Clipping coupons','','answer2',0,0),(570,'What does the term Prima Donna mean in Opera ?','The Leading Female Singer','Diva ','Superstar','','','answer1',0,0),(571,'What English-speaking Caribbean island has a Spanish name meaning &quot;Bearded&quot;?','Basseterra','Antigua','Barbados','Barbuda','','answer3',0,0),(572,'Who was the high priest who accused Jesus of blasphemy, after Jesus answered that he was, &quot;...the Christ, the Son of God.&quot;?','Caiaphas','Chuza','Cleophas','Coz','','answer1',0,0),(573,'What part of an elephant is estimated to have over 40,000 muscles?','Legs','Ears','Trunk','Torso','','answer3',0,0),(574,'Where was the main center of public life in Pompeii?','The Civic Forum','Vetti\'s House','The Odeon Gymnasium','The Villa of Mysteries','','answer1',0,0),(575,'What body of water lies to the west of Sierra Leone?','the Pacific Ocean','the Mediterranean Sea','the Indian Ocean','the Atlantic Ocean','','answer4',0,0),(576,'What is the name of both the river that runs through Galway city and the lake in County Galway?','Liffey','Clare','Corrib','Neagh','','answer3',0,0),(577,'In which country was greenpeace founded?','America','France','Canada','','','answer3',0,0),(578,'In the racing series known as NASCAR, what is a Polish victory lap?','Driving in the opposite direction','Walking the track after winning','Waving a Polish flag after winning','Buying a Kielbasa for everyone','','answer1',0,0),(579,'If you could pile up all the food you’ll eat in a lifetime, how many tons would it weigh:','10','100','1000','10000','','answer2',0,0),(580,'The largest butterfly, the Queen Alexandra of New Guinea, has a wingspan of how many inches?','6','11','16','21','','answer2',0,0),(581,'Harry Potter Spell: I want to unlock a door - what do I say?','Open Sesame','Alohomora','abrir','Aloha','','answer2',0,0),(582,'What organ of a buffalo did Plains Indians use to make yellow paint?','Kidney','Stomach','Gallbladder','Spleen','','answer3',0,0),(583,'What was the first commercially manufactured breakfast cereal?','Cheerios','Corn Flakes','Shredded Wheat','Grape Nuts','','answer3',0,0),(584,'As a river term how deep is Mark Twain?','One fathom (6 feet)','Two fathoms (12 feet)','Three fathoms (18 feet)','Four fathoms (24 feet)','','answer2',0,0),(585,'Which side of your body is your left hand on when you are looking in a mirror?','Left','Both','Right','',' ','answer3',0,0),(586,'This American singer and actress played Evita(Eva) Peron in the film &#039;Evita&#039; (1996) and during 1985-1991 she was married to Sean Penn:','Andrea Corr','Laura Pallas','Victoria Principal','Madonna','','answer4',0,0),(587,'What is the main ingredient of stargazey pie?','Pilchards ','Salmon ','Starfish ','Peanuts ','','answer1',0,0),(588,'During which era did dinosaurs rule the land?','Precambrian','Paleozoic','Mesozoic','Cenozoic','','answer3',0,0),(589,'Which is Norway’s highest peak?','Besshoi','Leirhoi','Galdho','Rondeslottet','','answer3',0,0),(590,'If you are increasing in latitude in the Northern Hemisphere, are you traveling:','North','South','East','West','','answer1',0,0),(591,'Which of the following animals can&#039;t fly?','Rat','Eagle','Swan','','','answer1',0,0),(592,'In ‘Snow White and the Seven Dwarves’ which of her dwarf friends wear glasses?','Dopey','Doc','Grumpy','Bashful','','answer2',0,0),(593,'The Latin word for a dragon, draco actually means:','Donosaur','Eel','Snake','',' ','answer3',0,0),(594,'What percentage of babies actually arrive on their due date?','80-81%','24-25%','3-4% ','','','answer3',0,0),(595,'Which State of USA was once part of Mexico?','Texas','Maryland','New York','Alaska','','answer1',0,0),(596,'What other invention of the late 1880s greatly enhanced bicycle safety?','Battery-powered lights','Glow-in-the-dark clothing','Hand brakes','Chain locks','','answer3',0,0),(597,'What is a diaper called in South Africa?','Nappy','Dispenser','Disposable','','','answer3',1,0),(598,'In which city is the United Nations headquarters?','New York','Tokyo','Paris','','','answer1',0,0),(599,'What best describes where the Blessed Virgin&#039;s great power of intercession with God originates?','from being a saint','from being Queen of Angels','from her assumption into Heaven','Her Immaculate Conception and from being mother of Jesus and from her holy life','','answer4',0,0),(600,'Whose book, ‘Profiles of Courage’(1956), won a Pulitzer Prize in 1957?','Earnest Hemingway','Douglas MacArthur','John F. Kennedy','Arthur Miller','','answer3',0,0),(601,'Light year is related to',' Energy','Distance ','Speed','',' ','answer2',0,0),(602,'What type of Music included composers such as Bach and Beethoven?','Hip-Hop','Pop','Classical','','','answer3',0,0),(603,'The first bird domesticated by humans was the…','Duck','Chickens','Goose','Swan','','answer3',0,0),(604,'What Italian city had the Roman name Mediolanum?','Milan','Rome','Firenze','Calabria','','answer1',0,0),(605,'Who&#039;s Murphy Brown&#039;s favorite soul singer?','Al Green','Aretha Franklin','Aaron Neville','Ray Charles','','answer2',0,0),(606,'Which book of the Bible tells the story of Adam and Eve?','Deuteronomy','Adam','Genesis','Eve','','answer3',0,0),(607,'What New England state would be home if you laid down roots in Bald Head?','Vermont','New Hampshire','Maine','Massechusettes','','answer3',0,0),(608,'(2003) Hugh Grant plays Britain&#039;s newest Prime Minister and Alan Rickman plays his brother-in-law, in this series of vignettes about the love lives of loosely inter-connected couples (or singles), all looking not to be lonely during the Christmas season.','Intolerable Cruelty','Love Actually','Two Weeks Notice','Down With Love','','answer2',0,0),(609,'The Romans used the abacus for counting purposes. What were the beads running along the wires of an abacus called?','Additae','Ferculi','Calculi','Computae','','answer3',0,0),(610,'What is the minimum distance between Earth and Mars?','25 million miles','35 million miles','50 million miles','60 million miles','','answer2',0,0),(611,'Which team won Super Bowl XX after making a Super Bowl video?','Pittsburgh Steelers','Miami Dolphins','Washington Redskins','Chicago Bears','','answer4',0,0),(612,'Why was Michael Jordan called &quot;Air Jordan&quot; or &quot;His Airness&quot;?','Because  he can jump very high/far','Because he shot many air-balls','Because he invented wings.','','','answer1',0,0),(613,'What creature goes through a period where it gains 10 pounds an hour?','Elephant','Bison','Blue Whale','Rhinocerus','','answer3',0,0),(614,'If two typists can type two pages in two minutes, how many typists will it take to type 18 pages in six minutes?','6','1700','34','',' ','answer1',0,0),(615,'“You have the right to remain silent...Anything you say can and will be used against you in a court of law...You have the right to an attorney.” This is called the _____ warning.','Miranda','Escobedo','Gideon','Mapp','Dickerson','answer1',0,0),(616,'which state is the largest producer of wines in the US?','New York ','Texas ','California','','','answer3',0,0),(617,'What mountains are home to the entertainment world&#039;s Borscht Belt?','The Urals','The Catskills','The Appalacian','The Rockies','','answer2',0,0),(618,'What culture is credited with producing the first ceramics?','The Egyptians','The Aztecs','The Chinese','The Japanese','','answer4',0,0),(619,'What percent of the people on Earth live north of the Equator?','0.2','0.4','0.6','0.8','','answer4',0,0),(620,'On what continent is the chimpanzee&#039;s natural habitat?','South America','Africa','Asia','','','answer2',0,0),(621,'Which part of your eye receives no blood at all:','Cornea','Iris','retina','optic nerve','','answer1',0,0),(622,'What U.S. age group more than doubled in size between 1960 and 1990?','under 25','25-55','55-75','Over 85','','answer4',0,0),(623,'Who can make more vocal sounds a cat or dog?','Dogs','Same','Cats','',' ','answer3',0,0),(624,'What happened to Alice after she drank from a bottle labeled &quot;Drink Me&quot;?','She threw up','She shrank','She grew two extra legs','','','answer2',0,0),(625,'Whom did Harry Houdini marry?','Elsie Wright','Wilhelmia Rahner','Frances Griffiths','Estelle Roberts','','answer2',0,0),(626,'What suntan lotion was developed by Dr. Ben Green in 1944 to protect pilots who bailed out over the Pacific?','Hawaiian Tropic','Coppertone','Sun Worshiper','Blue Lizard','','answer2',0,0),(627,'What country did Greek historian Herodotus dub &quot;the gift of the Nile&quot;?','Saudi Arabia','Jordan','Egypt','Syria','','answer3',0,0),(628,'Maria Montessori was the first woman in Italy to be awarded a degree in which subject?','History','Geography','Medicine','English','','answer3',0,0),(629,'Which of the following is musician Conway Twitty&#039;s real name?','Charlie Chaplin Jenkins ','Stan Laurel Jenkins ','Buster Keaton Jenkins ','Harold Lloyd Jenkins ','','answer4',0,0),(630,'Which is Michael Jackson&#039;s middle name?','Joseph',' Martin','Michael','','','answer1',0,0),(631,'Norway. Some of the world&#039;s longest and deepest fjords are found here. Which process led to their formation?','Volcanism','Sub-aerial processes','Plate tectonics','Glaciation','','answer4',0,0),(632,'What food is the leading source of salmonella poisoning?','Eggs','Spinach','Pork','Chicken','','answer4',0,0),(633,'Who is the oldest?','Madonna','Olsen Twins combined age','David Copperfield ','Daryl Hannah','','answer3',0,0),(634,'What long-beaked bird needs 1,600 blossoms to get its daily diet of nectar?','Finch','Whippoorwill','Sandpiper','Hummingbird','','answer4',0,0),(635,'Of the 26 orders of insects, how many have appeared on postage stamps?','14','18','22','24','','answer1',0,0),(636,'What was Michael Jackson&#039;s best selling album?','Dangerous','Thriller','History','','','answer2',0,0),(637,'The correct formula for calculating density is:','mass x volume','mass + volume','mass - volume','mass/volume','','answer4',0,0),(638,'Which is the southernmost US state?','Texas','Hawaii','California','','','answer2',0,0),(639,'What interstate highway connects Boston and Seattle?','I-94','I-90','I-80','Route 66','','answer2',0,0),(640,'This city was founded in 753 B.C by the twin brothers Romulus and Remus on the seven hills overlooking the Tiber River.','Rome','Marseille','Naples','Algiers','','answer1',0,0),(641,'What was the very first published novel by Agatha Christie, which also introduced the much beloved Belgian detective, Hercule Poirot?','Murder on the Orient Express',' The Mysterious Affair at Styles','The Murder of Roger Ackroyd','Murder at the Vicarage','','answer2',0,0),(642,'What Florida city&#039;s name translates to &quot;mouth of the rat&quot; because of it&#039;s toothy inlet?','Biscayne','Sarasota','Bradenton','Boca Raton','','answer4',0,0),(643,'What is the missing date in the following, very-famous rhyme?  &#039;Remember, remember the ___ of November:  Gunpowder, treason and plot.  I see no reason why gunpowder, treason should ever be forgot!&#039;','fifth','sixth','first','tenth','','answer1',0,0),(644,'Which team is incorrectly matched with their nickname?','Detroit Motorcycles (NHL) ','San Diego Padres (MLB) ','Toronto Raptors (NBA) ','Arizona Cardinals (NFL) ','','answer1',0,0),(645,'What is the Roman numeral for 50?','X','V ','L','','','answer3',0,0),(646,'What town name did residents of a Florida retirement community switch to because they found Sunset Depressing?\n','Sunshine','Sunrise','Sunnydale','Hope','','answer2',0,0),(647,'The strongest winds ever recorded were:','127 mph','318 mph','231 mph','254 mph','','answer3',1,0),(648,'In Federalist No. 10, James Madison warns against the dangers of','factions.','states’ rights.','judicial review.','an all-powerful president.','','answer1',0,0),(649,'Who is the director of the movie Star Wars?','YiMou Zhang','Gump','George Lucas','','','answer3',0,0),(650,'Which film released in the forties was NOT a Disney production?','Pinocchio','Fantasia','Bluebeard','Dumbo','','answer3',0,0),(651,'British-born novelist; former actress; sister of Joan','Emmeline Pankhurst','Anna Pavlova','Edith Cavell','Jackie Collins','','answer4',0,0),(652,'Can horses vomit?','Yes','It depends on its age','No','','','answer3',0,0),(653,'The mid-autumn festival is a famous Chinese traditional festival  Which food do Chinese eat that day?','Bread','Beef','Moon cake','','','answer3',0,0),(654,'Who wrote the trilogy covering people coping with the various seasons on their planet, where each season lasts for several hundred years?','Herbert','Heinlein','None of these authors','Aldiss','','answer4',0,0),(655,'When you divide by zero, the answer is  ___:','1','You can\'t divide by zero','0','','','answer2',0,0),(656,'Louisville and MLBs St. Louis&#039; names are the same. What is Louisville&#039;s name?','Cardinals','Quilters','Purple Aces','Roughriders','','answer1',0,0),(657,'What San Francisco fixture is a favorite jumping-off point for an average of 14 people a year?','The Golden Gate Bridge','Coit Tower on Telegraph Hill','Pier 39','Fisherman\'s Wharf ','','answer1',0,0),(658,'What people were the first to adopt a solar year, after noticing spring holidays were beginning to occur in winter?','Greeks','Romans','Jews','Egyptians','','answer4',0,0),(659,'When your uvula vibrates are you:','snoring','coughing','burping','talking','','answer1',0,0),(660,'Who painted the Mona Lisa ?','Leonardo Da Vinci','Rudy Gay ','James Worthy','','','answer1',0,0),(661,'In the video game &quot;Mass Effect 2&quot; - What physical appears increases intensity as you become more of a Renegade?','Red Eyes','Fangs','Devil Horns','No change','','answer1',0,0),(662,'What was Charlton Heston‘s role in the film “The Ten Commandments”?','Aaron','Moses','Ramses II','Joshua','','answer2',0,0),(663,'What&#039;s the common term for epinephrine?','Adrenaline','Lactic Acid','Morphene','Saline','','answer1',0,0),(664,'What author wrote &#039;Of Mice and Men&#039;?','Ken Kesey','Leo Tolstoy','John Steinbeck','J.D. Salinger','','answer3',0,0),(665,'This entertainer had a broadcast show for twenty years, painted clowns and other circus memories of his teenage years, wrote short stories and composed many pieces of background music. Who was he?','Richard \"Red\" Skelton','Julius \"Groucho\" Marx','Jackie \"The Great One\" Gleason','Steve \"Steverino\" Allen','','answer1',0,0),(666,'Who proposed the law of motion that for every action there is an equal and opposite reaction?','Albert Einstein','Tresani','Isaac Newton','Galileo','','answer3',0,0),(667,'In this 2002 Victorian-era comedy, based on an Oscar Wilde play, Colin Firth (as Jack Worthing) tries to win the hand of his love and learns &quot;The Importance of Being&quot; this. What word completes the title?','Frank','Earnest','Honest','Cordial','','answer2',0,0),(668,'Who was the main creator of Windows?','Steve Jobs','Bill Gates','Ryoji Chubachi','Hiroshi Yamauchi','','answer2',0,0),(669,'Eric Clapton released his first solo album in 1970 after leaving what band?','Milk and Honey','The Birds','Cream  ','Buffalo Springfield','','answer3',0,0),(670,'Who was the famed teacher of Alexander the Great?','Plato','Aristotle','Socrates','Euripides','','answer2',0,0),(671,'Who destroyed the droid control ship in &quot;Episode I - The Phantom Menace&quot;?','Han Solo','Luke Skywalker','Anakin Skywalker','Obi-Wan Kenobi','','answer3',0,0),(672,'Red blood corpuscles are formed in the','liver','bone marrow','kidneys','heart','','answer2',0,0),(673,'How many carats is pure gold?','24','12','18','','','answer1',0,0),(674,'What product do Girl Scouts sell to raise money?','Cookies','Cars','Popcorn','','','answer1',0,0),(675,'Which of these races is supported by the NAACP?','African-American','Hispanic','Asian','Caucasian','','answer1',0,0),(676,'What is the capital of Sudan?','Cairo ','Casablanca ','New Delhi ','Khartoum ','','answer4',0,0),(677,'What are the names of Toronto&#039;s four major sports teams?','Argonauts, Canadiens, Blue Jays, Raptors','Maple Leafs, Expos, Raptors, Tigercats','Maple Leafs, Raptors, Blue Jays, Argonauts','Mavericks, Maple Leafs, Mets, Argonauts','','answer3',0,0),(678,'Who said, &quot;Aren&#039;t we forgetting the true meaning of Christmas? You know, the birth of Santa?&quot;','Ebeneezer Scrooge','Donald Trump','Paris Hilton','Bart Simpson','','answer4',0,0),(679,'What activity was banned on Canadian domestic flights in 1988?','drinking any alcoholic beverage ','sleeping ','eating ','smoking ','','answer4',0,0),(680,'How many planets orbit our sun?','6','8','10','12','','answer2',0,0),(681,'What is the last letter of the Greek alphabet?','Sigma','Omega','Alpha','','','answer2',0,0),(682,'A group of geese on the ground is gaggle.  What is a group of geese in the air','Formation','Flock','Troop','Skein','','answer4',0,0),(683,'What is Houston more known for?','Dairy Farming','NASA - Aerospace','Winemaking','','','answer2',0,0),(684,'Dino Paul Crocetti and Joseph Levitch made us all laugh in dozens of movies including &quot;At War with the Army&quot; (1950), and &quot;The Caddy&quot; (1953). By what names were this duo better known?','Dean Martin & Jerry Lewis','Dino Kartsonakis & Kirk Douglas','Jim Croce & Joseph Levitt','Paul Hartman & Joe E. Brown','','answer1',0,0),(685,'In which two events did Mildred &quot;Babe&quot; Didrikson win gold at the 1932 L A Olympics?','10k run and pole vault','Long jump and Javelin','Hurdles and long jump','Hurdles and javelin','','answer4',0,0),(686,'In the Alex Haley book `Roots`, who were the parents of Chicken George?','Tom and Irene','Kizzy and Tom Kea','Kunta Kinte and Bell','Cynthia and Will Palmer','','answer2',0,0),(687,'This game has a timer that looks like a clapper board and word cards and is played like charades.','Guesstures','Operation','Simon Says','Don\'t Wake Daddy','','answer1',0,0),(688,'What is the length of a year of Venus?','365.2425 days','243.1087 days','224.701 days','164.321 days','','answer3',0,0),(689,'What is 10 Squared?','100','109','110','','','answer1',0,0),(690,'What is the largest gland in the human body?','Liver','Kidneys','Lungs','','','answer1',0,0),(691,'Lob, love and deuce are the terms of what sport?','swimming','football (soccer)','skiing','tennis','','answer4',0,0),(692,'How many books of the Bible are named for women?','4','8','3','2','','answer4',0,0),(693,'Paris is the star of a reality show that premiered in 2003. What is it?','Chantelle\'s Dream Dates','Princess NIkki','My Super Sweet 16th','Simple Life','','answer4',0,0),(694,'What are scotch eggs?','eggs laid by Scotch hens ','eggs covered in sausagemeat and breadcrumbs ','eggs fried with haggis','','','answer2',0,0),(695,'What is the capital of Lithuania?','Valmiera','Taurage','Vilnius','Riga','','answer3',0,0),(696,'What is the resulting flavour when chocolate is added to coffee?','cocoa','mocha','latte','','','answer2',0,0),(697,'What&#039;s the groundnut better known as?','Walnut','Peanut','Coconut','Chestnut','','answer2',0,0),(698,'Now that our meats are covered, let&#039;s move on to vegetables. There are usually four different vegetables to pick from or to mix and match. One is this tasty starch (carbohydrate). It can be candied, baked or even fried. What vegetable is it?','irish potato','zucchini squash','yellow squash','sweet potato','','answer4',0,0),(699,'Who is the host of Show Me the Money?','Regis Philbin ','Alex Trebek','Paul Reubens','William Shatner','','answer4',0,0),(700,'The dot on top of the letter &#039;i&#039; is called a (n)','Point','Dot','Tittle','','','answer3',0,0),(701,'Which king is the only king without a moustache in poker ?','King of spades ','King of hearts','King of diamonds','','','answer2',0,0),(702,'What do Americans call the Huang Ho, China&#039;s second-longest river?','The Yangtze River','The Red River','The Mekong River','The Yellow River','','answer4',0,0),(703,'Elephants only sleep for  _______hours each day.','Two','Ten','Twenty','','','answer1',0,0),(704,'What sea laps the shores of Kazakhstan and Uzbekistan?','The Mediteranean Sea','The Aral Sea','The Black Sea','The Dead Sea','','answer2',0,0),(705,'What was Friedrich Serturner the first to extract from opium and use as a pain reliever?','Morphine','Acetylsalicitic Acid','Codiene','Acetomenophen','','answer1',0,0),(706,'How many planets are closer to the sun than earth?','1','2','3','4','','answer2',0,0),(707,'Is a Spider an insect?','No','Yes','Depends on what kind of spider','','','answer1',0,0),(708,'What is the abbreviation for Major League Baseball?','ABP','MLB','USBA','','','answer2',0,0),(709,'Which branch of science deals with the study of motion, forces, & energy?','Physics','Chemistry','Astronomy','Geology','','answer1',0,0),(710,'This short story was written by American horror writer H.P Lovecraft in 1926. It was about a sane man going very insane while investigating an unknown underwater creature. The creature was worshipped as a god that slept in death only to awaken soon to devour all of humanity. What was the name of this book?','The Call of Cthulhu','The Dunwich Horror','The Lurking Fear','The Other Gods','','answer1',0,0),(711,'What happens when the ball goes off the side of the field in soccer?','A kick in by one team','A throw in by one team','A coin toss','The linesman kicks it back in','','answer2',0,0),(712,'To &#039;let the cat out of the bag&#039; means to','cause trouble','reveal the truth','endanger other people unnecessarily','','','answer2',0,0),(713,'What&#039;s the only U.S. state to border Maine?','Vermont','Connecticut','Massechusetts','New Hampshire','','answer4',0,0),(714,'What is the baptismal name of Pope John XXIII?','Albino Luciani','Angelo Roncalli','Aldo Moro','Sandro Pertini','','answer2',0,0),(715,'How old would someone be if he was a quarter of a century old?','100','25','50','','','answer2',0,0),(716,'Where is the country Chad located?','Africa','South America','North America','Asia','','answer1',0,0),(717,'Norwegian artist (1863-1944). He was a pioneer for a lot of other Norwegian, German and Czech artists. His best known painting is &#039;The Scream&#039; (1893). In Oslo there is a museum in tribute to him.','Johann Christian Dahl','Edvard Munch','Christian Krohg','Gustav Klimt','','answer2',0,0),(718,'Which charitable organization was founded by London minister, William Booth, for the destitute, the hungry, the homeless, and the poor?','The Samaritan Army','United Way','The Salvation Army','UNICEF','','answer3',0,0),(719,'How many seconds are there in a day?','94','853','84,000','86,400','','answer4',0,0),(720,'What is a single unit of quanta called?','Quark','Quantum','Quant','Quait','','answer2',0,0),(721,'Which Western philosopher declared in a political tract from 1690 that all individuals have certain natural rights, including those of life, liberty, and property?','Thomas Hobbes',' John Locke','Jean Jacques Rousseau','Thomas Jefferson','James Madison','answer2',0,0),(722,'What is the official state insect for Illinois?','Ladybug','Honney Bee','Firefly','Monarch butterfly','','answer4',0,0),(723,'What letter is on the right of a B on a keyboard?','C','Shift','N','','','answer3',0,0),(724,'The buildup of which common gas is affecting global warming?','CO2','O2','SO4','','','answer1',0,0),(725,'“Sue”, the most preserved T-Rex fossil ever found, was uncovered in 1990 and is on display at the Field Museum. Where was it found?','South Dakota','Wyoming','Illinois','Washington','','answer1',0,0),(726,'Which country has never invaded any country in her last 10000 years of history?','India','China','Egypt','',' ','answer1',0,0),(727,'The War of 1812, which lasted from 1812 to 1815, took a great number of casualties. Perhaps the most well-known among them is the death of Major General Sir Isaac Brock. During which battle of the War of 1812 did Brock lose his life?','Battle of Beaverdams, June 24, 1813 ','Battle of Queenston Heights, October 13, 1812 ','Battle of Lundy\'s Lane, July 25, 1814 ','Battle of Chippewa, July 5, 1814 ','','answer2',0,0),(728,'What weight is the lightest in Amateur Boxing?','Ultra Light Weight ','Light Flyweight','Light Weight','','','answer2',0,0),(729,'Mark Twain wrote about which of these characters?','Little Boy Blue','Tom Sawyer','Red Riding Hood','Dennis the Menace','','answer2',0,0),(730,'Who wrote the book Alice In Wonderland?','Lewis Carroll ','J.K. Rowling','C. L. Lewis','','','answer1',0,0),(731,'Which one has been the president of the USA?','Washington','Guileless Du ','Master Chow','','','answer1',0,0),(732,'Where is the seat of International Court of Justice?','Amsterdam','Rotterdam','The Hague','Brussels','','answer3',0,0),(733,'The blood of mammals is red, the blood of insects is yellow, and the blood of lobsters is ?','Yellow','Blue','Red','','','answer2',0,0),(734,'When did Harry Houdini die?','January 18, 1946','June 3, 1928','July 7, 1940','October 31, 1926','','answer4',0,0),(735,'Thomas Jefferson counted the founding of which university to be among his   greatest accomplishments?',' the College of William and Mary','University of Virginia','Harvard','Yale','Princeton','answer2',0,0),(736,'Science and Technology: The Beagle Crater can be found on which of the following celestial bodies?','The Moon','Mars','Mercury','Venus','','answer2',0,0),(737,'In 1993 students from Purdue University created crayons that were NOT made out of petroleum-based paraffin wax (the commonly used substance). What did they make their crayons from?','Soybean Oil','Fungi','Rhubarb Stalks','Pumpkin Seeds','','answer1',0,0),(738,'Which of the following people was not a famous scientist?','Isaac Newton','Albert Einstein','Lebron James','','','answer3',0,0),(739,'NASA launched a series of missions in the 1960s and early 70s which achieved the first fly-by and became the first artificial orbiting satellite of Mars. What were these missions called?','Mars','Milstar','Messenger','Mariner','','answer4',0,0),(740,'How much liquid does the average human bladder hold?','8 ounces','12 ounces','16 ounces','20 ounces','','answer3',0,0),(741,'What outfit, after investing 20 years and $20 million, stopped using psychics to gather info?','CIA','FBI','NCIS','CGIS','','answer1',0,0),(742,'The Baby Ruth candy bar was created in 1920 by Curtiss Candy Co. The candy bar is named after the baseball player Babe Ruth.','TRUE','FALSE','','','','answer2',0,0),(743,'The first Three Musketeers bar orignailly sold for how much?','3 cents','5 cents','10 cents','one cent','','answer2',0,0),(744,'You can survive about a month without food. How many days can you survive without water?','1 to 2 days ','14 to 30 days ','5 to 7 days','','','answer3',0,0),(745,'Each Summer Olympic Games (or each Winter Olympics) are held how many years apart?','4','5','10','','','answer1',0,0),(746,'What is the name given to a substance which speeds up a chemical reaction?','Catalyst','Protein','Fat','','','answer1',0,0),(747,'Who played the title role in the 1982 film adaptation of &#039;Annie&#039;?','Lana Turner','Carol Burnett','Doris Day','Aileen Quinn','','answer4',0,0),(748,'Which legendary bird rises from ashes to be born again?','Wyatt','Madi','Phoenix','','','answer3',0,0),(749,'Which Canadian-born artist was the biggest-selling singles artist tin the UK in 1991?','Celine Dion','Bryan Adams','Alannis Morrisette','KD Lang','','answer2',0,0),(750,'What was the approximate weight of the heaviest hailstones recorded in history?','.5 lbs','2.25 lbs','1.5 lbs','','','answer2',0,0),(751,'What country&#039;s auto identification letters are KWT?','Kazakhstan','Kenya','Kiribati','Kuwait','','answer4',0,0),(752,'What two trees did Ponce de Leon introduce to Florida in 1513?','Orange & Grapefruit','Grapefruit & Lemon','Mango & Orange','Orange & Lemon','','answer4',0,0),(753,'Catnip has relaxing properties for humans too! How is it most commonly consumed ?','pizza topping','soup','tea','ravioli filling','','answer3',0,0),(754,'What&#039;s the world&#039;s second largest archipelago?','The Philippines','Surinam','Indonesia','Japan','','answer1',0,0),(755,'What island was Abel Tasman the first European to land on, in 1642?','Tasmania','New Zeeland','Austrailia','Tiawan','','answer1',0,0),(756,'Which extends further North?','Japan','North Korea','Turkey','Afghanistan','','answer1',0,0),(757,'Which currency is used in the USA?','Dollar','Yen','Euro','','','answer1',0,0),(758,'The Wave is a rock formation found on the slopes of the Coyote Buttes, Arizona, U.S.A.. What rock is it made up of?','Limestone','Sandstone','Marble','Basalt','','answer2',0,0),(759,'How many equal angles are there in an Isosceles Triangle?','1','2','3','','','answer2',0,0),(760,'In Islamic law how many wives is a man allowed to have?','10','4','1','','','answer2',0,0),(761,'What is the best option to dealing with your trash while camping?','Seal it up in a bag for when you are able to properly dispose of it.','Feed it to the wild life.','Bury it in the woods or burn it in the campfire.','Place it for birds to use in nests','','answer1',0,0),(762,'Who was born on August 15, 1769?','Winston Churchill','Queen Victoria','Napoleon Bonaparte','George V','','answer3',0,0),(763,'What animal can live for a few days after their head has been cut off?','Chicken','Pig','Cockroaches','','','answer3',0,0),(764,'Where&#039;s a shrimp&#039;s heart?','In their head','In their tail','In their body','','','answer1',0,0),(765,'What city, founded in 1550 by Sweden&#039;s King Gustav Vasa, was first called Helsingfors?','Stockholm','Oslo','Helsinki','Bergen','','answer3',0,0),(766,'Italian painter; architect; rebuilt St. Peter&#039;s','Alberto Santos-Dumont','Raphael','Victorien Sardou','Franz Lehar','','answer2',0,0),(767,'Where does Bill Gates come from?','Scotland','The USA','Canada','','','answer2',0,0),(768,'What does the rose symbolize?','Humor','Love','Hate','','','answer2',0,0),(769,'Where in the world is the most active volcano on earth?','Korea','Greece','Hawaii','Peru','Kenya','answer3',0,0),(770,'His was a story of a man transported to the penal colonies of early Australia for a crime of which he was innocent. Written by Marcus Clarke, it described in vivid details the horrors of life at the time within that system, and the hero&#039;s undying love for a woman which was interwound throughout the story right up to its tragic conclusion.','Australia Felix','The Sentimental Bloke','Robbery Under Arms','For the Term of His Natural Life','','answer4',0,0),(771,'What Arab city has a name derived from a word meaning &quot;sanctuary&quot;?','Kuait','Amman','','Mecca','','answer4',0,0),(772,'The fastest running insect is a(n):','army ant','stag Beetle','blister Beetle','Cockroach','','answer4',0,0),(773,'Flemish is an official language of which country?','Belgium','Germany','Switzerland','Lichtenstein','','answer1',0,0),(774,'In which month did the attack on Pearl Harbor take place?','January','April','July','December','','answer4',0,0),(775,'Superman can not see through what?','Rocks','Clothes','Metals ','Lead','','answer4',0,0),(776,'Which are the official languages of Canada?','English and Spanish','Spanish and French','French and Russian','English and French','','answer4',1,0),(777,'What did Oscar Wilde define as a man who knows the price of everything and the value of nothing?','An estate agent (realtor) ','A lawyer ','A cynic ','A numbskull ','An idiot ','answer3',0,0),(778,'What are Albert Park in Melbourne, Spa-Francorchamps in Belgium and Suzuka in Japan ?','Theme parks','Championship golf courses','National parks','Motor Racing circuits','','answer4',0,0),(779,'What so-called &quot;war&quot; spawned the dueling slogans &quot;Better Dead Than RED&quot; and &quot;Better Red Than Dead&quot; in the 1950&#039;s?','The Cold War','The Korean War','World War 2','French & Indian War','','answer1',0,0),(780,'What is the name of the hottest desert in the world?','The Mohave Desert ','The Sahara Desert ','The Kalahari Desert','','','answer2',0,0),(781,'Charles Rolls of Rolls Royce fame died in what type of vehicle?','Boat','Car','Train','Airplane','','answer4',0,0),(782,'Destructive process by which rocks are changed on exposure to atmospheric agents at or near the Earth&#039;s surface with little or no transport of loosened or altered material.','Deposition','Weathering','Exhumation','Erosion','','answer2',0,0),(783,'Which veteran comedy actor played the pink-clad uncle in the 1995 movie ‘Arizona Dream’?','Walter Matthau','Sid Caesar','Jack Lemmon','Jerry Lewis','','answer4',0,0),(784,'This place is supposedly haunted. The residents of Hogsmeade can hear ghosts wailing in the night. There is a passage through the Whomping Willow from Hogwarts to here. What is the name of this place?','Haunted House','Honeydukes','The Shrieking Shack','Hog\'s Head','','answer3',0,0),(785,'What southwestern U.S. state has the highest percentage of non-English speakers?','Texas','Arizona','New mexico','Nevada','','answer3',0,0),(786,'What fish is called &quot;finnan haddie&quot; when smoked in Scotland?','Tuna','Cod','Swordfish','Haddock','','answer4',0,0),(787,'Which existing franchise used to share a team &#039;nickname&#039; with another CFL team for more than 50 years?','Montreal Alouettes','Saskatchewan Roughriders','Montreal Concorde','Edmonton Eskimos','','answer2',0,0),(788,'What is the capital of Burundi?','Rome','Cairo','Bujumbura','Jerusalem','','answer3',0,0),(789,'What Pacific atoll got its name from its location between the Americas and Asia?','Bikini','Pacifica','Midway','Middleton Reef','','answer3',0,0),(790,'This thing is absolutely essential if you want to live. You can make somebody&#039;s &quot;sweat of sword&quot; boil. If you are a nobleman, you can say that your &quot;wound-weeping&quot; is blue and cold. It is also possible to have a &quot;sweat of ravens&quot; brother. Tell me, please, what I am driving at?','blood','money','water','health','','answer1',0,0),(791,'What is the feminine of lamb?','Lumber ','Ewe','Limb','Lob','','answer2',0,0),(792,'What is the state capital of California, USA?','Cupertino','Sacramento','Los Angeles','','','answer2',0,0),(793,'In the video game &quot;Mass Effect 2&quot; - What was Tali&#039;s new name in &quot;Mass Effect 2&quot;? (When you FIRST meet her on Freedom&#039;s Progress)','Tali\'Zorah vas Normandy','Tali\'Zorah vas Neema','Tali\'Zorah nar Rayya','Tali\'Zorah vas Rayya','','answer2',0,0),(794,'What is the English name for &#039;agurk&#039;?','cucumber','lettuce','tomatoe','onion','','answer1',0,0),(795,'What 120,000-square-mile African desert is almost completely covered by woods and grass?','The Kalahari','The Sahara','The Nubian','The Nabib','','answer1',0,0),(796,'In Alice in Wonderland, what color were the cards painting the roses?','yellow','black','white','red ','','answer4',0,0),(797,'What Central American country has its capital in Tegucigalpa?','Nicaragua','Costa Rico','Honduras','Panama','','answer3',0,0),(798,'What do people call the number 1 followed by 12 zeros?','One million','One trillion','One billion','','','answer2',0,0),(799,'Which is the capital of Afghanistan?','Teheran','Baghdad','Kabul','Tashkent','','answer3',0,0),(800,'Theoretical physicists were examining antimatter and neutrinos at about the same time. The antiparticles of charged particles have the opposite charge, but the situation for neutral particles is not so clear! The neutron has a distinct antiparticle, but the photon is its own antiparticle. So debate raged over antineutrinos. Are neutrinos their own antiparticles?','YES','NO','','','','answer2',0,0),(801,'What colour is an octopus&#039; blood? (when it&#039;s oxygenated)','Mauve','Plaid','Blue','Striped','','answer3',1,0),(802,'Explain and show the correct way to wear a life jacket?','Watercraft','Photographer','Fishing','Skater','','answer1',0,0),(803,'A substance that allows heat or electricity to pass through is called:','Conductor','Acid','Alcohol','','','answer1',0,0),(804,'Who was the Prime Minister of Norway when it was under German occupation during World War II?','Kjell Magne Bondevik','Jens Stoltenberg','Olaf Haraldsson','Vidkun Quisling','','answer4',0,0),(805,'Let&#039;s see you batting this one over the boundary: In what sport would you see bails, googlies, chinamen, yorkers, and ducks?','Yachting ','Lacrosse ','Croquet ','Cricket ','','answer4',0,0),(806,'What were the names of the four Warner Brothers of Warner Brothers Studio fame?','Moe, Larry, Curly and Ed','Douglas, Joseph, Arnold and Robert','Manny, Moe, Jack and Harry','Sam, Albert Jack and Harry','','answer4',0,0),(807,'What country receives 26 percent of all Saudi Exports?','Brazil','Germany','China','The U.S.','','answer4',0,0),(808,'Which scandal hit sportsman was described by wife Monica in 1998 as &quot;kind of shy?&quot;','Coby Bryant','Magic Johnson','Mike Tyson','OJ Simpson','','answer3',0,0),(809,'What treaty for the protection of the ozone layer includes in its title the name of a Canadian city?','Montreal Protocol ','Ottawa Treaty ','Geneva Convention ','Treaty of Ghent ','','answer1',0,0),(810,'What action by the British in 1930 caused Mahatma Gandhi to lead thousands of his followers on a march to the sea?','They had threatened to deport several of his relatives','They had put a tax on salt','They had imposed a severe curfew throughout parts of India','They had refused to enter talks on independence','','answer2',0,0),(811,'Before becoming king, what was David&#039;s profession?','Shepherd','Fisherman','Tent maker','Carpenter','','answer1',0,0),(812,'What desert did David Livingstone have to cross to reach Lake Ngami?','The Kalahari','The Sahara','The Gobi','The Arabian','','answer1',0,0),(813,'When a smile is done as an involuntary expression of anxiety what is it called?','Grimace','Smirk','Snort ','',' ','answer1',0,0),(814,'Which of the following is the chemical symbol for lead?','Li','Le','Ld','Pb','','answer4',0,0),(815,'How  many U.S. states are named after a president?','one','two','three','four','','answer1',0,0),(816,'What New York City landmark is the largest movie theater in the U.S.?','Radio City Music Hall','The Apollo Theater','Palace Theater','Royale Theatre','','answer1',0,0),(817,'What&#039;s the most intelligent mammal after man?','The Chimpanzee','The Bottle-Nosed Dolphin','The Orca','The Golden Retriever','','answer1',0,0),(818,'What mountains do the Ganges, Brahmaputra and Indus rivers begin in?','The Andes','The Urals','The Rocky\'s','The Himalayas','','answer4',0,0),(819,'When did Mark Twain become a licensed river pilot?','1839','1844','1858','1870','','answer3',0,0),(820,'Are Europe and North America drifting closer together or farther apart?','closer','farther','','','','answer2',0,0),(821,'The formula for carbon dioxide is CO2. How many atoms of oxygen are in one molecule?','1','2','3','4','','answer2',0,0),(822,'Which spacecraft first visited Venus?','Pioneer 3','Mariner 2','Ulysses','Mariner 4','','answer2',0,0),(823,'What color are the 5 Olympic Rings?','Green Red Pink Blue White','Yellow Pink Red Blue Brown','Black Blue Red Green Yellow','','','answer3',0,0),(824,'In 1935, what was Bugs Bunny originally called?','Funny Bunny ','Doc','Wuzz Up','Happy Rabbit','','answer4',0,0),(825,'What was dedicated in 1982 when veteran Ian Scruggs said: &quot;Thank you America...for finally remembering us&quot;?','The WWII Memorial','The Korean War Memorial','The Vietnam Veterans Memorial','','','answer3',0,0),(826,'How many sonnets did William Shakespeare write?','167','154','108','','','answer2',0,0),(827,'What East African country&#039;s annual four percent population growth rate is the world&#039;s highest?','Tanzania','Kenya','Ruwanda','Burundi','','answer2',0,0),(828,'Who said “Good luck, Mr Gorsky”?','Neil Armstrong','John F.Kennedy','Karin Carpenter','Woody Allen','','answer1',0,0),(829,'What do the letters DNA stand for?','Deoxyribonucleic Acid','Dumb Narcotic Apricots','Do No Apathy','Do Not Oblige','','answer1',0,0),(830,'John has 16 nickels and 2 dimes.  Jenny has 3 quarters and 2 dimes.  Who has more money?','John','Jenny ','They have equal','','','answer1',0,0),(831,'What is the cube root of 8?','2','3','4','','','answer1',0,0),(832,'Alexandra Cymboliak Zuck was born April 23, 1942 in Bayonne, New Jersey USA. This actress had roles in &quot;A Summer Place&quot;, &quot;Imitation of Life&quot; and &quot;Tammy and the Doctor&quot; and married that guy who sang &quot;Mack the Knife&quot;. What was her name?','Connie Francis ','Dolores Hart ','Andrea Yeager ','Sandra Dee ','','answer4',0,0),(833,'Mary&#039;s whole world consists of her enclosed village, protected from the zombie hordes outside the fence by Guardians, and all under the control of the Sisterhood. There is a path leading to the village and away again but nobody seems to know where, if anywhere, it leads. Which book is this?','The Forest of Hands and Teeth','The Sunrise Lands','Lirael','Ashes, ashes','','answer1',0,0),(834,'About how long does it take sunlight to reach the Earth:','8 seconds','8 minutes','8 hours','8 days','','answer2',0,0),(835,'What is the number of squares on a chess board?','72','64','32','48','','answer2',0,0),(836,'Which of the Great Lkes does not lap Canadian shores?','Lake Superior','Lake Erie','Lake Michigan','Lake Huron','','answer3',1,0),(837,'The islands Hokkaido, Honshu, Shikoku and Kyushu are part of which country?','Philippines','S. Korea','Japan','Vietnam','','answer3',0,0),(838,'Under the U.S. Constitution, members of the U.S. House of Representatives have a _____ term, members of the U.S. Senate have a _____ term, and the president has a _____ term.','2 year; 6 year; 4 year','2 year; 4 year; 6 year','4 year; 4 year; 4 year','2 year; 8 year; 4 year','','answer1',0,0),(839,'What year did the Dow Jones Industrial Average break both the 4000 and 5000 marks?','1990','1995','1997','2000','','answer2',0,0),(840,'Which city do the Rockets call home?','Houston','Austin','Chicago','','','answer1',0,0),(841,'Who said “I guess it was because we were so completely unlike in every way, but, like bacon and eggs, we seemed to be about perfect together - but not so good apart”?\n','Oliver Hardy','Dean Martin','Richard Burton','Lou Costello','','answer2',0,0),(842,'How many took part in the Last Supper?','13','2','100','','','answer1',0,0),(843,'What is the all time best selling book in the world?','The Bible','Harry Potter','Twilight','','','answer1',0,0),(844,'What is the largest dinosaur known to man?','Tyrannosaurus','Brontosaurus','Meglodon','Amphicoelias','','answer4',0,0),(845,'Which is larger ?','Pacific Ocean','Atlantic Ocean','Eurasian Continent','North and South American continents','','answer1',0,0),(846,'The spell is used by Professor McGonagall to get the suits of armor to help in the Battle at Hogwarts, in the seventh book, &quot;The Deathly Hallows&quot;.','Come do my bidding','Locomotor Mortis','Piertotum Locomotor','Animate','','answer3',0,0),(847,'What country provides Cuba with most of its new cars and computers in exchange for sugar?','Korea','Japan','U.S.','Germany','','answer2',0,0),(848,'&quot;The Murders in the Rue Morgue&quot; first appeared in an 1841 magazine as a short story. Who wrote it?','Charles Dickens','Edgar Allan Poe','Wilkie Collins','Alexandre Dumas','','answer2',0,0),(849,'This organ is responsible for removing the waste chemicals and water from the blood?','Bladder','Kidney','Urethra','','','answer2',0,0),(850,'How many players can be on the field for each team in American Football?','Ten','twelve','Eleven','','','answer3',0,0),(851,'223*232*303*239*94*133*0*31+23*3*0*32*3+1=?','1','0','9.25E+14','','','answer1',0,0),(852,'What is the name of Pinocchio&#039;s creator?','Mostro','Geppetto','Stromboli','Jiminy','','answer2',0,0),(853,'How many reindeer were pulling the sleigh in &quot;Twas the Night before Christmas?&quot;','7','11','9','8','','answer4',0,0),(854,'What was the nationality of the jet shot down in Russian air space in 1983?','Korean','Chinese','American','English','','answer1',0,0),(855,'10% of all the people living in a certain town in Georgia have unlisted phone numbers.  If you selected 100 names at random from the town&#039;s phone book, on average, how many of these people would have unlisted phone numbers?','10','90','0','','','answer3',0,0),(856,'What Aussie cover girl put on 20 pounds to play a 19th-century artist&#039;s model in Sirens?','Tallulah Morton','Elle MacPherson','Miranda Kerr','Abbey Lee Kershaw ','','answer2',0,0),(857,'_____ is a written attack on a person’s reputation.','Libel','Slander','Obscenity','All of the answers are correct.','','answer1',0,0),(858,'&#039;Let Your Fingers do the Walking&#039;','Nike','Yellow Pages','Avis','Century 21','','answer2',0,0),(859,'What bulb has been dubbed &quot;the stinking rose&quot;?','Onion','Tulip','Garlic','','','answer3',0,0),(860,'Which of the five senses is sharpened by a radial keratotomy?','hearing','touch','taste','sight','smell','answer4',1,0),(861,'What is Rambo&#039;s first name?','James','John','Jason','','','answer2',0,0),(862,'Which city was known as New Amsterdam?','New Orleans','Los Angeles','Seattle','New York','','answer4',0,0),(863,'What New Orleans soup has a name derived from the Bantu word for okra?','Jambalaya','Gumbo','Creole','Roux','','answer2',0,0),(864,'What is the only rock that floats?','Granite','Basalt','Pumice','Obsidian','','answer3',0,0),(865,'Who was the first female protagonist in a video game?','Alis Landale','Chell','Samus Aran','Lara Croft','','answer4',0,0),(866,'What is the name of the main character from the music video of &quot;Shelter&quot; by Porter Robinson and A-1 Studios?','Ram','Rin','Rem','Ren','','answer3',0,0),(867,'In &quot;One Piece&quot;, what does &quot;the Pirate King&quot; mean to the captain of the Straw Hat Pirates?','Freedom','Adventure','Friendship','Promise','','answer2',0,0),(868,'Which of these blocks in &quot;Minecraft&quot; has the lowest blast resistance?','End Stone','Wood Planks','Water','Sand','','answer5',0,0),(869,'In the 1988 film &quot;Akira&quot;, Tetsuo ends up destroying Tokyo.','True','False','','','','answer2',0,0),(870,'In &quot;The Sims&quot; series, the most members in a household you can have is 8.','True','False','','','','answer2',0,0),(871,'The main character in the &quot;Half-Life&quot; franchise is named Morgan Freeman.','True','False','','','','answer3',0,0),(872,'In which 1973 film does Yul Brynner play a robotic cowboy who malfunctions and goes on a killing	spree?','Runaway','Westworld','Android','The Terminators','','answer3',0,0),(873,'What is the name of Team Fortress 2&#039;s Heavy Weapons Guy&#039;s minigun?','Betty','Diana','Sasha','Anna','','answer4',0,0),(874,'Which king was killed at the Battle of Bosworth Field in 1485? ','James I','Edward V','Henry VII','Richard III','','answer5',0,0),(875,'How many dice are used in the game of Yahtzee?','Five','Four','Six','Eight','','answer2',0,0),(876,'Seoul is the capital of North Korea.','False','True','','','','answer2',0,0),(877,'The most graphically violent game to precede the creation of the ESRB (Entertainment Software Rating Board) was...','Doom','Duke Nukem','Resident Evil','Mortal Kombat','','answer5',0,0),(878,'Which singer was featured in Jack &amp;Uuml; (Skrillex &amp; Diplo)&#039;s 2015 song &#039;Where Are &amp;Uuml; Now&#039;?','Justin Bieber','Ellie Goulding','Selena Gomez','The Weeknd','','answer2',0,0),(879,'Half-Life by Valve uses the GoldSrc game engine, which is a highly modified version of what engine?','Quake Engine','Source Engine','Doom Engine','id Engine','','answer2',0,0),(880,'In which 1955 film does Frank Sinatra play Nathan Detroit?','From Here to Eternity','High Society','Anchors Aweigh','Guys and Dolls','','answer5',0,0),(881,'Hippocampus is the Latin name for which marine creature?','Seahorse','Dolphin','Whale','Octopus','','answer2',0,0),(882,'Which country had an &quot;Orange Revolution&quot; between 2004 and 2005?','Lithuania','Belarus','Ukraine','Latvia','','answer4',0,0),(883,'All of the following are names of the Seven Warring States EXCEPT:','Zhai (翟)','Zhao (趙)','Qin (秦)','Qi (齊)','','answer2',0,0),(884,'Excluding their instructor, how many members of Class VII are there in the game &quot;Legend of Heroes: Trails of Cold Steel&quot;?','9','3','6','10','','answer2',0,0),(885,'Who was the star of the TV series &quot;24&quot;?','Kevin Bacon','Kiefer Sutherland','Rob Lowe','Hugh Laurie','','answer3',0,0),(886,'The United States was a member of the League of Nations.','False','True','','','','answer2',0,0),(887,'What is the name of the US Navy spy ship which was attacked and captured by North Korean forces in 1968?','USS North Carolina','USS Pueblo','USS Indianapolis','USS Constitution','','answer3',0,0),(888,'Who won the 2015 Formula 1 World Championship?','Sebastian Vettel','Jenson Button','Lewis Hamilton','Nico Rosberg','','answer4',0,0),(889,'RAM stands for Random Access Memory.','True','False','','','','answer2',0,0),(890,'What is the scientific name for the &quot;Polar Bear&quot;?','Polar Bear','Ursus Arctos','Ursus Maritimus','Ursus Spelaeus','','answer4',0,0),(891,'What is the romanized Chinese word for &quot;airplane&quot;?','Feiji','Huojian','Qiche','Zongxian','','answer2',0,0),(892,'Which team was the 2014-2015 NBA Champions?','Atlanta Hawks','Houston Rockets','Cleveland Cavaliers','Golden State Warriors','','answer5',0,0),(893,'In 2012, which movie won every category in the 32nd &quot;Golden Raspberry Awards&quot;?','The King\'s Speech','The Girl with the Dragon Tattoo','Jack and Jill','Thor','','answer4',0,0),(894,'Which game was the first time Mario was voiced by Charles Martinet?','Super Mario 64','Dr. Mario 64','Mario Tennis','Mario\'s Game Gallery','','',0,0),(895,'When was Minecraft first released to the public?','October 7th, 2011','November 18th, 2011','September 17th, 2009','May 17th, 2009','','answer5',0,0),(896,'In the episode of SpongeBob SquarePants, &quot;Survival of the Idiots&quot;, Spongebob called Patrick which nickname?','Pinhead','Starfish','Dirty Dan','Larry','','answer2',0,0),(897,'The stop motion comedy show &quot;Robot Chicken&quot; was created by which of the following?','Seth MacFarlane','Seth Green','Seth Rogen','Seth Rollins','','answer3',0,0),(898,'In the Homestuck Series, what is the alternate name for the Kingdom of Lights?','Prospit','No Name','Golden City','Yellow Moon','','answer2',0,0),(899,'What year did the Boxing Day earthquake &amp; tsunami occur in the Indian Ocean?','2002','2008','2004','2006','','answer4',0,0),(900,'Which band released songs suchs as &quot;Rio&quot;, &quot;Girls on Film&quot;, and &quot;The Reflex&quot;?','Depeche Mode','New Order','Duran Duran','The Cure','','answer4',0,0),(901,'In the Star Trek universe, what color is Vulcan blood?','Red','Purple','Green','Blue','','answer4',0,0),(902,'What song plays in the ending credits of the anime &quot;Ergo Proxy&quot;?','Mad World','Bittersweet Symphony','Paranoid Android','Sadistic Summer','','answer4',0,0),(903,'Who was the author of the 1954 novel, &quot;Lord of the Flies&quot;?','Hunter Fox','F. Scott Fitzgerald','William Golding','Stephen King','','answer4',0,0),(904,'Who played Sgt. Gordon Elias in &#039;Platoon&#039; (1986)?','Matt Damon','Charlie Sheen','Johnny Depp','Willem Dafoe','','answer5',0,0),(905,'In WarioWare: Smooth Moves, which one of these is NOT a Form?','The Mohawk','The Discard','The Elephant','The Hotshot','','answer5',0,0),(906,'Which of these are the name of a famous marker brand?','Dopix','Copic','Marx','Cofix','','answer3',0,0),(907,'Which of these game franchises were made by Namco?','Dragon Quest','Tekken','Street Fighter','Mortal Kombat','','answer3',0,0),(908,'What was Genghis Khan&#039;s real name?','Tem&amp;uuml;r','&amp;Ouml;gedei','Tem&amp;uuml;jin','M&amp;ouml;ngke','','answer4',0,0),(909,'Joko Widodo has appeared in the cover of a TIME magazine.','True','False','','','','answer2',0,0),(910,'What is the smallest country in South America by area?','Uruguay','Chile','Brazil','Suriname','','answer5',0,0),(911,'The General Motors EV1 was the first street-legal production electric vehicle.','False','True','','','','answer2',0,0),(912,'What is the Polish city known to Germans as Danzig?','Zakopane','Warsaw','Poznań','Gdańsk','','answer5',0,0),(913,'What year did the New Orleans Saints win the Super Bowl?','2011','2009','2010','2008','','answer3',0,0),(914,'How long did World War II last?','5 years','4 years','7 years','6 years','','answer5',0,0),(915,'This mobile OS held the largest market share in 2012.','iOS','BlackBerry','Android','Symbian','','answer2',0,0),(916,'What year was the RoboSapien toy robot released?','2004','2006','2000','2001','','answer2',0,0),(917,'Who is the creator of the manga series &quot;One Piece&quot;?','Masashi Kishimoto','Yoshihiro Togashi','Hayao Miyazaki','Eiichiro Oda','','answer5',0,0),(918,'What was the aim of the &quot;Umbrella Revolution&quot; in Hong Kong in 2014?','Genuine universal suffrage','Go back under British Rule','Lower taxes','Gaining Independence','','answer2',0,0),(919,'Which of the following is NOT a quote from the 1942 film Casablanca? ','&amp;ldquo;Of all the gin joints, in all the towns, in all the world, she walks into mine&amp;hellip;&amp;rdquo;','&quot;Here\'s lookin\' at you, kid.&quot;','&quot;Round up the usual suspects.&quot;','&quot;Frankly, my dear, I don\'t give a damn.&quot;','','',0,0),(920,'What French artist/band is known for playing on the midi instrument &quot;Launchpad&quot;?','Disclosure','Madeon','Daft Punk ','David Guetta','','answer3',0,0),(921,'How many Chaos Emeralds can you collect in the first Sonic The Hedgehog?','Six','Seven','Five','Eight','','answer2',0,0),(922,'In Dragon Ball Z, who was the first character to go Super Saiyan 2?','Goku','Trunks','Gohan','Vegeta','','answer4',0,0),(923,'Which franchise does the creature &quot;Slowpoke&quot; originate from?','Yugioh','Dragon Ball','Pokemon','Sonic The Hedgehog','','answer4',0,0),(924,'What is Doug Walker&#039;s YouTube name?','The Angry Video Game Nerd','The Cinema Snob','AngryJoeShow','The Nostalgia Critic','','answer5',0,0),(925,'San Marino is the only country completely surrounded by another country.','True','False','','','','answer3',0,0),(926,'Which gas forms about 78% of the Earth&amp;rsquo;s atmosphere?','Argon','Oxygen','Carbon Dioxide','Nitrogen','','answer5',0,0),(927,'What is the world&#039;s first video game console?','Nintendo Color TV Game','Atari 2600','Coleco Telstar','Magnavox Odyssey','','answer5',0,0),(928,'One of the deadliest pandemics, the &quot;Spanish Flu&quot;, killed off what percentage of the human world population at the time?','3 to 6 percent','1 to 3 percent','less than 1 percent','6 to 10 percent','','answer2',0,0),(929,'How many Chaos Emeralds are there in the &quot;Sonic the Hedgehog&quot; universe?','6','8','7','14','','answer4',0,0),(930,'What was Manfred von Richthofen&#039;s nickname?','The High Flying Ace','The Blue Serpent ','The Germany Gunner','The Red Baron','','answer5',0,0),(931,'Which artist released the 2012 single &quot;Harlem Shake&quot;, which was used in numerous YouTube videos in 2013?','NGHTMRE','RL Grime','Flosstradamus','Baauer','','answer5',0,0),(932,'In the 9th Pokemon movie, who is the Prince of the Sea?','Ash','Manaphy','Phantom','May','','answer3',0,0),(933,'Which German city is located on the River Isar?','Dortmund','Munich','Hamburg','Berlin','','answer3',0,0),(934,'What was the final score of the Germany vs. Brazil 2014 FIFA World Cup match?','16 - 0','7 - 1','3 - 4','0 - 1','','answer3',0,0),(935,'Which Mario spin-off game did Waluigi make his debut?','Mario Party 3','Mario Golf: Toadstool Tour','Mario Kart: Double Dash!!','Mario Tennis','','answer5',0,0),(936,'Which of the following countries does &quot;JoJo&#039;s Bizarre Adventure: Stardust Crusaders&quot; not take place in?','Pakistan','India','Philippines','Egypt','','answer4',0,0),(937,'Ewan McGregor did not know the name of the second prequel film of Star Wars during and after filming.','True','False','','','','answer2',0,0),(938,'Who is the main character with yellow hair in the anime Naruto?','Sasuke','Kakashi','Ten Ten','Naruto','','answer5',0,0),(939,'What does film maker Dan Bell typically focus his films on?','Historic Landmarks','Abandoned Buildings and Dead Malls','Documentaries ','Action Films','','answer3',0,0),(940,'Who is the leader of Team Valor in Pok&amp;eacute;mon Go?','Willow','Blanche','Spark','Candela','','answer5',0,0),(941,'What year was the United States Declaration of Independence signed?','1776','1775','1774','1777','','answer2',0,0),(942,'Which of the following superheros did Wonder Woman NOT have a love interest in?','Steve Trevor','Superman','Green Arrow','Batman','','answer4',0,0),(943,'In which game does a character say, &quot;Sometimes, I dream about cheese&quot;?','Dark Souls','Serious Sam: The Second Encounter','Team Fortress 2','Half Life 2','','answer5',0,0),(944,'What is generally considered to be William Shakespeare&#039;s birth date?','September 29th, 1699','December 1st, 1750','July 4th, 1409','April 23rd, 1564','','answer5',0,0),(945,'How many plays is Shakespeare generally considered to have written?','18','54','37','25','','answer4',0,0),(946,'The names of Tom Nook&#039;s cousins in the Animal Crossing franchise are named &quot;Timmy&quot; and &quot;Jimmy&quot;.','False','True','','','','answer2',0,0),(947,'The towns of Brugelette, Arlon and Ath are located in which country?','Belgium','France','Andorra','Luxembourg','','answer2',0,0),(948,'In TF2 Lore, what are the names of the Heavy&#039;s younger sisters?','Yana and Bronislava','Gaba and Anna','Anna and Bronislava','Yanna and Gaba','','answer2',0,0),(949,'What is the capital of the US State of New York?','New York','Albany','Rochester','Buffalo','','answer3',0,0),(950,'In the game &quot;Persona 4&quot;, what is the canonical name of the protagonist?','Tunki Sunada','Yu Narukami','Chino Mashido','Masaki Narinaka','','answer3',0,0),(951,'Which of the following Japanese islands is the biggest?','Kyushu','Honshu','Hokkaido','Shikoku','','answer3',0,0),(952,'Martin Luther King Jr. and Anne Frank were born the same year. ','True','False','','','','answer2',0,0),(953,'What is the same in Celsius and Fahrenheit?','32','-40','-42','-39','','answer3',0,0),(954,'What sport is being played in the Anime Eyeshield 21?','Football','Baseball','American Football','Basketball','','answer4',0,0),(955,'What is the capital of Belarus?','Kiev','Vilnius','Warsaw','Minsk','','answer5',0,0),(956,'The Konami Code is known as Up, Up, Down, Down, Left, Right, Right, Left, B, A, Start.','False','True','','','','answer2',0,0),(957,'What was the name of Marilyn Monroe&#039;s first husband?','Kirk Douglas','Joe Dimaggio','James Dougherty','Arthur Miller','','answer4',0,0),(958,'Which of the following has Jennifer Taylor NOT voiced?','Cortana','Sarah Kerrigan','Zoey','Princess Peach','','answer3',0,0),(959,'In the ADV (English) Dub of the anime &quot;Ghost Stories&quot;, which character is portrayed as a Pentacostal Christian?','Hajime Aoyama','Momoko Koigakubo','Mio Itai','Satsuki Miyanoshita','','answer3',0,0),(960,'What is the name of the 4-armed Chaos Witch from the 2016 video game &quot;Battleborn&quot;?','Oranda','Orendoo','Randy','Orendi','','answer5',0,0),(961,'Which of the following names is the &quot;Mega Man&quot; Franchise known as in Japan?','Paperman','Rockman','Mega Man','Scissorsman','','answer3',0,0),(962,'In &quot;The Binding of Isaac&quot;, what is the name of the final boss that you fight in The Void?','Hush','Mega Satan','Delirium','The Lamb','','answer4',0,0),(963,'In the TV Show &quot;Donkey Kong Country&quot;, which episode did the song &quot;Eddie, Let Me Go Back To My Home&quot; play in?','It\'s a Wonderful Life','Ape-Nesia','Message In A Bottle Show','To The Moon Baboon','','',0,0),(964,'In Heroes of the Storm, the Cursed Hollow map gimmick requires players to kill the undead to curse the enemy team.','False','True','','','','answer2',0,0),(965,'Toby Fox&#039;s &quot;Megalovania&quot; was first used where?','Homestuck: [S] Wake','Undertale','Mother: Cognitive Dissonance','Radiation\'s Earthbound Halloween Hack','','',0,0),(966,'What year was Min Yoongi from South Korea boy band &quot;BTS&quot; born in?','1992','1995','1993','1994','','answer4',0,0),(967,'Which game in the &quot;Monster Hunter&quot; series introduced the &quot;Insect Glaive&quot; weapon?','Monster Hunter 2','Monster Hunter Stories','Monster Hunter 4','Monster Hunter Freedom','','answer4',0,0),(968,'What programming language was used to create the game &quot;Minecraft&quot;?','Java','HTML 5','C++','Python','','answer2',0,0),(969,'Who had a US and UK number 1 hit in 1962 with the instrumental, &#039;Telstar&#039;?','The Spotnicks','The Ventures','The Tremeloes','The Tornados','','answer5',0,0),(970,'In the 1984 movie &quot;The Terminator&quot;, what model number is the Terminator portrayed by Arnold Schwarzenegger?','T-888','I-950','T-1000','T-800','','answer5',0,0),(971,'How many pieces are there on the board at the start of a game of chess?','16','32','20','36','','answer3',0,0),(972,'In which English county is Stonehenge?','Wiltshire','Somerset','Cumbria','Herefordshire','','answer2',0,0),(973,'Abel Magwitch is a character from which Charles Dickens novel?','Oliver Twist','The Pickwick Papers','Great Expectations','Nicholas Nickleby','','answer4',0,0),(974,'In which Shakespeare play does the character Marcellus say, &quot;Something is rotten in the state of Denmark&quot;?','Hamlet','Macbeth','Twelfth Night','King Lear','','answer2',0,0),(975,'Who made Garry&#039;s Mod?','Gabe Newell','Facepunch Studios','Garry Newman','Randy Newman','','answer4',0,0),(976,'Which language is NOT Indo-European?','Hungarian','Russian','Latvian','Greek','','answer2',0,0),(977,'What is the main ship used by Commander Shepard in the Mass Effect Franchise called?','Infinity','Normandy','Endeavour','Osiris','','answer3',0,0),(978,'Which of these is NOT a main playable character in &quot;Grand Theft Auto V&quot;?','Trevor','Lamar','Franklin','Michael','','answer3',0,0),(979,'An organic compound is considered an alcohol if it has what functional group?','Hydroxyl','Alkyl','Aldehyde','Carbonyl','','answer2',0,0),(980,'What is the name of the dog that played Toto in the 1939 film &quot;The Wizard of Oz&quot;?','Terry','Toto','Tommy','Teddy','','answer2',0,0),(981,'Who was the voice actor for Snake in Metal Gear Solid V: The Phantom Pain?','Kiefer Sutherland','Hideo Kojima','David Hayter','Norman Reedus','','answer2',0,0),(982,'What is the name of the assassin in the first &quot;Hellboy&quot; movie?','Ilsa Haupstein','Karl Ruprecht Kroenen','Grigori Efimovich Rasputin','Klaus Werner von Krupt','','answer3',0,0),(983,'What is the name of the first &quot;Star Wars&quot; film by release order?','The Phantom Menace','A New Hope','Revenge of the Sith','The Force Awakens','','answer3',0,0),(984,'When was the game &#039;Portal 2&#039; released?','2007','2011','2009','2014','','answer3',0,0),(985,'What year is on the flag of the US state Wisconsin?','1783','1901','1634','1848','','answer5',0,0),(986,'In Avatar: The Last Airbender, which element does Aang begin to learn after being defrosted?','Earth','Air','Water','Fire','','answer4',0,0),(987,'Who created the 2011 Video Game &quot;Minecraft&quot;?','Daniel Rosenfeld','Jens Bergensten','Carl Manneh','Markus Persson','','answer5',0,0),(988,'In Overwatch, Mercy&#039;s ultimate ability is..','Rocket Barrage','Earthshatter','Molten Core','Resurrect','','answer5',0,0),(989,'Myopia is the scientific term for which condition?','Double Vision','Clouded Vision','Farsightedness','Shortsightedness','','answer5',0,0),(990,'What was the title of Sakamoto Kyu&#039;s song &quot;Ue o Muite Arukou&quot; (I Look Up As I Walk) changed to in the United States?','Sukiyaki','Sushi','Takoyaki','Oden','','answer2',0,0),(991,'Rebecca Chambers does not appear in any Resident Evil except for the original Resident Evil and the Gamecube remake.','True','False','','','','answer3',0,0),(992,'Who invented the &quot;Flying Shuttle&quot; in 1738; one of the key developments in the industrialization of weaving?','John Deere','Richard Arkwright','James Hargreaves','John Kay','','answer5',0,0),(993,'In CSS, which of these values CANNOT be used with the &quot;position&quot; property?','static','center','relative','absolute','','answer3',0,0),(994,'What is the name of the three headed dog in Harry Potter and the Sorcerer&#039;s Stone?','Spike','Poofy','Spot','Fluffy','','answer5',0,0),(995,'Which of the following languages does NOT use gender as a part of its grammar?','German','Polish','Turkish','Danish','','answer4',0,0),(996,'Which figure from Greek mythology traveled to the underworld to return his wife Eurydice to the land of the living?','Hercules','Perseus','Orpheus','Daedalus','','answer4',0,0),(997,'Microphones can be used not only to pick up sound, but also to project sound similar to a speaker.','False','True','','','','answer3',0,0),(998,'In the game Call of Duty, what is the last level where you play as an American soldier?','Brecourt','Chateau','Ste. Mere-Eglise (Day)','Festung Recogne','','answer5',0,0),(999,'The Battle of Trafalgar took place on October 23rd, 1805','True','False','','','','answer3',0,0),(1000,'Volkswagen&#039;s legendary VR6 engine has cylinders positioned at what degree angle?','45 Degree','90 Degree','30 Degree','15 Degree','','answer5',0,0),(1001,'EDM label Monstercat signs tracks instead of artists.','False','True','','','','answer3',0,0),(1002,'Who designed the album cover for True Romance, an album by Estelle?','Matt Burnett','Ian Jones Quartey','Ben Leven','Rebecca Sugar','','answer5',0,0),(1003,'According to The Hitchhiker&#039;s Guide to the Galaxy book, the answer to life, the universe and everything else is...','42','Chocolate','Loving everyone around you','Death','','answer2',0,0),(1004,'In the Portal series of games, who was the founder of Aperture Science?','Cave Johnson','Wallace Breen','GLaDOs','Gordon Freeman','','answer2',0,0),(1005,'In the game &quot;Persona 4&quot;, what is the canonical name of the protagonist?','Masaki Narinaka','Yu Narukami','Tunki Sunada','Chino Mashido','','answer3',0,0),(1006,'How was Socrates executed?','Poison','Decapitation','Firing squad','Crucifixion ','','answer2',0,0),(1007,'What is the name of the main character in &quot;The Flash&quot; TV series?','Bruce Wayne','Barry Allen','Oliver Queen','Bart Allen','','answer3',0,0),(1008,'What does LASER stand for?','Light ampiflier by standby energy of radio','Light amplifiaction by stimulated eminission of radioation','Lite analysing by stereo ecorazer','Life antimatter by standing entry of range','','answer3',0,0),(1009,'What type of cheese, loved by Wallace and Gromit, had it&#039;s sale prices rise after their successful short films?','Edam','Wensleydale','Cheddar','Moon Cheese','','answer3',0,0),(1010,'Which of the following names is the &quot;Mega Man&quot; Franchise known as in Japan?','Mega Man','Rockman','Scissorsman','Paperman','','answer3',1,0),(1011,'In the &quot;Harry Potter&quot; novels, what must a Hogwarts student do to enter the Ravenclaw Common Room?','Rhythmically tap barrels with a wand','Speak a password','Answer a riddle','Knock in sequence','','answer4',0,0),(1012,'How many regular Sunken Sea Scrolls are there in &quot;Splatoon&quot;?','32','27','30','5','','answer3',0,0),(1013,'In the video game &quot;Transistor&quot;, &quot;Red&quot; is the name of the main character.','True','False','','','','answer2',0,0),(1014,'Kublai Khan is the grandchild of Genghis Khan?','False','True','','','','answer3',0,0),(1015,'Android versions are named in alphabetical order.','False','True','','','','answer3',0,0),(1016,'In Rugby League, performing a &quot;40-20&quot; is punished by a free kick for the opposing team.','True','False','','','','answer3',0,0),(1017,'Which species is a &quot;mountain chicken&quot;?','Chicken','Fly','Frog','Horse','','answer4',0,0),(1018,'During the Winter War, the amount of Soviet Union soliders that died or went missing was five times more than Finland&#039;s.','True','False','','','','answer2',0,0),(1019,'What&#039;s the name of the main protagonist in the &quot;Legend of Zelda&quot; franchise?','Link','Mario','Zelda','Pit','','answer2',0,0),(1020,'Peter Molyneux was the founder of Bullfrog Productions.','False','True','','','','answer3',0,0),(1021,'Which famous book is sub-titled &#039;The Modern Prometheus&#039;?','Dracula','The Legend of Sleepy Hollow','The Strange Case of Dr. Jekyll and Mr. Hyde ','Frankenstein','','answer5',0,0),(1022,'Which &#039;Family Guy&#039; character got his own spin-off show in 2009?','Cleveland Brown','Joe Swanson','The Greased-up Deaf Guy','Glenn Quagmire','','answer2',0,0),(1023,'The &quot;Gympie Stinger&quot; is the deadliest plant in the world.','False','True','','','','answer2',0,0),(1024,'What French sculptor designed the Statue of Liberty? ','Henri Matisse','Jean-L&amp;eacute;on G&amp;eacute;r&amp;ocirc;me','Fr&amp;eacute;d&amp;eacute;ric Auguste Bartholdi','Auguste Rodin','','answer4',0,0),(1025,'What color/colour is a polar bear&#039;s skin?','Pink','Green','White','Black','','answer5',0,0),(1026,'What does the &#039;S&#039; stand for in the abbreviation SIM, as in SIM card? ','Secure','Single','Solid','Subscriber','','answer5',0,0),(1027,'Which of these is NOT a main playable character in &quot;Grand Theft Auto V&quot;?','Michael','Lamar','Franklin','Trevor','','answer3',0,0),(1028,'The 2010 film &quot;The Social Network&quot; is a biographical drama film about MySpace founder Tom Anderson.','False','True','','','','answer2',0,0),(1029,'Norwegian producer Kygo released a remix of the song &quot;Sexual Healing&quot; by Marvin Gaye.','False','True','','','','answer3',0,0),(1030,'Sargon II, a king of the Neo-Assyrian Empire, was a direct descendant of Sargon of Akkad.','False','True','','','','answer2',0,0),(1031,'What is the most preferred image format used for logos in the Wikimedia database?','.gif','.jpeg','.png','.svg','','answer5',0,0),(1032,'In 2015, David Hasselhof released a single called...','True Survivor','True Fighter','Real Warrior','Real Kung-Fury','','answer2',0,0),(1033,'What was the name given to Android 4.3?','Nutella','Lollipop','Froyo','Jelly Bean','','answer5',0,0),(1034,'Which of these artists was NOT a member of the electronic music supergroup Swedish House Mafia, which split up in 2013?','Steve Angello','Alesso','Sebastian Ingrosso','Axwell','','answer3',0,0),(1035,'In the anime series &quot;Full Metal Alchemist&quot;, what do Alchemists consider the greatest taboo?','Human Transmutation ','Preforming Without A Permit','Using Alchemy For Crime ','Transmuting Lead Into Gold','','answer2',0,0),(1036,'Niko Bellic is the protagonist of Grand Theft Auto IV.','False','True','','','','answer3',0,0),(1037,'Which of these is NOT a song on The Beatles&#039; 1968 self titled album, also known as the White album?','Being For The Benefit Of Mr. Kite!','Everybody\'s Got Something to Hide Except Me and My Monkey','Why Don\'t We Do It in the Road?','The Continuing Story of Bungalow Bill','','answer2',0,0),(1038,'In the War of the Pacific (1879 - 1883), Bolivia lost its access to the Pacific Ocean after being defeated by which South American country?','Chile','Peru','Argentina','Brazil','','answer2',0,0),(1039,'What is the British term for a 64th note?','Semiquaver','Semihemidemisemiquaver','Demisemiquaver','Hemidemisemiquaver','','answer5',0,0),(1040,'Where are the Nazca Lines located?','Ecuador','Peru','Brazil','Colombia','','answer3',0,0),(1041,'What animal is featured on the cover of English electronic music group The Prodigy&#039;s album, &quot;The Fat of the Land&quot;?','Tiger','Fox','Crab','Elephant','','answer4',0,0),(1042,'Disney&#039;s Haunted Mansion is home to a trio of Hitchhiking Ghosts. Which of these is NOT one of them?','Harry','Phineas','Gus','Ezra','','answer2',0,0),(1043,'The color orange is named after the fruit.','True','False','','','','answer2',0,0),(1044,'Meryl Silverburgh, a video game character from the Metal Gear series, was originally a character in which game?','Gradius','Castlevania: Symphony of the Night','Contra','Policenauts','','answer5',0,0),(1045,'In the web-comic Homestuck, what is the name of the game the 4 kids play?','Sburb','Homesick','Husslie','Hiveswap','','answer2',0,0),(1046,'Which of these is the name of a song by Tears for Fears?','Shout','Yell','Shriek','Scream','','answer2',0,0),(1047,'According to Toby Fox, what was the method to creating the initial tune for Megalovania?','Listened to birds at the park','Using a Composer Software','Singing into a Microphone','Playing a Piano','','answer4',0,0),(1048,'What is the default alias that Princess Garnet goes by in Final Fantasy IX?','Dagger','Dirk','Quina','Garnet','','answer2',0,0),(1049,'Who was the author of the 1954 novel, &quot;Lord of the Flies&quot;?','Stephen King','Hunter Fox','F. Scott Fitzgerald','William Golding','','answer5',1,0),(1050,'In Splatoon, the Squid Sisters are named Tako and Yaki.','True','False','','','','answer3',0,0),(1051,'In the anime Assassination Classroom what is the class that Korosensei teaches?','Class 3-D','Class 3-E','Class 3-A','Class 3-B','','answer3',0,0),(1052,'Which computer language would you associate Django framework with?','Java','C#','C++','Python','','answer5',0,0),(1053,'If he was still alive, in what year would Elvis Presley celebrate his 100th birthday?','2045','2030','2040','2035','','answer5',0,0),(1054,'In the animated series RWBY, what is the name of the weapon used by Weiss Schnee?','Ember Celica','Gambol Shroud','Myrtenaster','Crescent Rose','','answer4',0,0),(1055,'In Halo 2, how many rounds does the M6C hold in a single magazine?','6','36','12','18','','answer4',0,0),(1056,'Who made the discovery of X-rays?','James Watt','Albert Einstein','Thomas Alva Edison','Wilhelm Conrad R&amp;ouml;ntgen','','answer5',0,0),(1057,'Kanye West&#039;s &quot;Gold Digger&quot; featured which Oscar-winning actor?','Dwayne Johnson','Alec Baldwin','Jamie Foxx',' Bruce Willis','','answer4',0,0),(1058,'In the 1999 movie Fight Club, which of these is not a rule of the &quot;fight club&quot;?','Only two guys to a fight','You do not talk about FIGHT CLUB','Always wear a shirt','Fights will go on as long as they have to','','answer4',0,0),(1059,'What do you declare in Rīchi Mahjong when you&#039;ve drawn your winning tile?','Tsumo','Kan','Ron','Rīchi','','answer2',0,0),(1060,'De Eemhof, Port Zelande and Het Heijderbos are holiday villas owned by what company?','Keycamp','Center Parcs','Villa Plus','Yelloh Village','','answer3',0,0),(1061,'What city did the monster attack in the film, &quot;Cloverfield&quot;?','Chicago, Illinois','Orlando, Florida','Las Vegas, Nevada','New York, New York','','answer5',0,0),(1062,'In the Mass Effect trilogy, who is the main protagonist?','Shepard','Garrus','Mordin','Thane','','answer2',0,0),(1063,'In &quot;Undertale&quot;, the main character of the game is Sans.','False','True','','','','answer2',0,0),(1064,'What nickname was given to Air Canada Flight 143 after it ran out of fuel and glided to safety in 1983?','Gimli Microlight','Gimli Glider','Gimli Superb','Gimli Chaser','','answer3',0,0),(1065,'What video game engine does the videogame Quake 2 run in?','Unreal Engine','iD Tech 2','iD Tech 3','iD Tech 1','','answer3',0,0),(1066,'The Killer Whale is considered a type of dolphin.','False','True','','','','answer3',0,0),(1067,'In which year did &quot;Caravan Palace&quot; release their first album?','2008','2004','2000','2015','','answer2',0,0),(1068,'Which popular First Person Shooter (FPS) franchise, got a Real Time Strategy (RTS) game developed based on its universe?','Halo','Call of Duty','Battlefield','Borderlands','','answer2',0,0),(1069,'What was the last Marx Brothers film to feature Zeppo?','Duck Soup','A Day at the Races','Monkey Business','A Night at the Opera','','answer2',0,0),(1070,'Which of these teams isn&#039;t a member of the NHL&#039;s &quot;Original Six&quot; era?','Boston Bruins','Toronto Maple Leafs','Philadelphia Flyers','New York Rangers','','answer4',0,0),(1071,'What was the name of the sea witch in the 1989 Disney film &quot;The Little Mermaid&quot;?','Lady Tremaine','Madam Mim','Ursula','Maleficent','','answer4',0,0),(1072,'Which German field marshal was known as the `Desert Fox`?','Wilhelm List','Ernst Busch','Wolfram Freiherr von Richthofen','Erwin Rommel','','answer5',0,0),(1073,'Who sang the theme song for the TV show &#039;Rawhide&#039;?','Guy Mitchell',' Tennessee Ernie Ford','Frankie Laine','Slim Whitman','','answer4',0,0),(1074,'Who was the only president to not be in office in Washington D.C?','Richard Nixon','Thomas Jefferson','George Washington','Abraham Lincoln','','answer4',0,0),(1075,'HTML is what type of language?','Programming Language','Macro Language','Markup Language','Scripting Language','','answer4',0,0),(1076,'Who is the only voice actor to have a speaking part in all of the Disney Pixar feature films? ','John Ratzenberger','Geoffrey Rush','Tom Hanks','Dave Foley','','answer2',0,0),(1077,'Which dictator killed the most people?','Joseph Stalin','Adolf Hitler','Kim Il Sung','Mao Zedong','','answer5',0,0),(1078,'Which of the following African countries was most successful in resisting colonization?','C&amp;ocirc;te d&amp;rsquo;Ivoire','Namibia','Ethiopia','Congo','','answer4',0,0),(1079,'In what year was the last natural case of smallpox documented?','1977','1980','1982','1990','','answer2',0,0),(1080,'The proof for the Chinese Remainder Theorem used in Number Theory was NOT developed by its first publisher, Sun Tzu.','False','True','','','','answer3',0,0),(1081,'List the following Iranic empires in chronological order:','Median, Achaemenid, Parthian, Sassanid','Median, Achaemenid, Sassanid, Parthian','Achaemenid, Median, Sassanid, Parthian','Achaemenid, Median, Parthian, Sassanid','','answer2',0,0),(1082,'Shrimp can swim backwards.','False','True','','','','answer3',0,0),(1083,'In the game &quot;Melty Blood Actress Again Current Code&quot;, you can enter Blood Heat mode in Half Moon style.','True','False','','','','answer3',0,0),(1084,'In &quot;Clash Royale&quot; what is Arena 4 called?','Royal Arena','P.E.K.K.A\'s Playhouse','Spell Valley','Barbarian Bowl','','',0,0),(1085,'Why was the character Trevor Philips discharged from the Air Force?','Injuries','Danger to Others','Mental Health Issues','Disease','','answer4',0,0),(1086,'What is the name of the assassin in the first &quot;Hellboy&quot; movie?','Karl Ruprecht Kroenen','Ilsa Haupstein','Grigori Efimovich Rasputin','Klaus Werner von Krupt','','answer2',0,0),(1087,'The first &quot;Metal Gear&quot; game was released for the PlayStation 1.','True','False','','','','answer3',0,0),(1088,'Which US state has the highest population?','Florida','California','Texas','New York','','answer3',0,0),(1089,'What is the name of the corgi in Cowboy Bebop?','Rocket','Joel','Einstein','Edward','','answer4',0,0),(1090,'Which driver has been the Formula 1 world champion for a record 7 times?','Jim Clark','Ayrton Senna','Michael Schumacher','Fernando Alonso','','answer4',0,0),(1091,'Anatomy considers the forms of macroscopic structures such as organs and organ systems.','True','False','','','','answer2',0,0),(1092,'Which of these is the name of a cut enemy from &quot;Half-Life 2&quot;?','Spike','Hydra','Cthulu','Tremor','','answer3',0,0),(1093,'There exists an island named &quot;Java&quot;.','True','False','','','','answer2',0,0),(1094,'Which car is the first mass-produced hybrid vehicle?','Chevrolet Volt','Toyota Prius','Peugeot 308 R HYbrid','Honda Fit','','answer3',0,0),(1095,'When was Elvis Presley born?','July 18, 1940','December 13, 1931','April 17, 1938','January 8, 1935','','answer5',0,0),(1096,'Which company has exclusive rights to air episodes of the &quot;The Grand Tour&quot;?','BBC','CCTV','Amazon','Netflix','','answer4',0,0),(1097,'Who played the sun baby in the original run of Teletubbies?','Sue Monroe','Lisa Brockwell','Jessica Smith','Pui Fan Lee','','answer4',0,0),(1098,'In Scandinavian languages, the letter &amp;Aring; means river.','False','True','','','','answer3',0,0),(1099,'Which board game was first released on February 6th, 1935?','Risk','Monopoly','Candy Land','Clue','','answer3',0,0),(1100,'The Windows 7 operating system has six main editions.','False','True','','','','answer3',0,0),(1101,'When was the Garfield comic first published?','1973','1982','1988','1978','','answer5',0,0),(1102,'Unlike on most salamanders, this part of a newt is flat?','Teeth','Head','Tail','Feet','','answer4',0,0),(1103,'What is the name of the family that the domestic cat is a member of?','Felinae','Cat','Felis','Felidae','','answer5',0,0),(1104,'What is the capital of British Columbia, Canada?','Kelowna','Hope','Victoria','Vancouver','','answer4',0,0),(1105,'Of the following space shooter games, which came out first?','Galaga','Space Invaders','Sinistar','Galaxian','','answer3',0,0),(1106,'In the movie Gremlins, after what time of day should you not feed Mogwai?','Morning','Evening','Afternoon','Midnight','','answer5',0,0),(1107,'What is the name of the song by Beyonc&amp;eacute; and Alejandro Fern&amp;aacute;ndez released in 2007?','La ultima vez','Amor Gitano','Hasta Dondes Estes','Rocket','','answer3',0,0),(1108,'Harry Potter was born on July 31st, 1980.','True','False','','','','answer2',0,0),(1109,'What is the name of New Zealand&#039;s indigenous people?','Samoans','Polynesians','Maori','Vikings','','answer4',0,0),(1110,'In the re-imagined science fiction show Battlestar Galactica, Cylons were created by man as cybernetic workers and soldiers.','False','True','','','','answer3',0,0),(1111,'&quot;Minecraft&quot; was released from beta in 2011 during a convention held in which city?','Las Vegas','Paris','London','Bellevue','','answer2',0,0),(1112,'All of these maps were in &quot;Tom Clancy&#039;s Rainbow Six Siege&quot; on its initial release: House, Clubhouse, Border, Consulate.','False','True','','','','answer2',0,0),(1113,'What household item make the characters of &quot;Steins; Gate&quot; travel through time?','Refrigerator','Televison','Microwave','Computer','','answer4',0,0),(1114,'In which year was Constantinople conquered by the Turks?','1454','1440','1453','1435','','answer4',0,0),(1115,'What is the name of the common, gun-toting enemies of the &quot;Oddworld&quot; video game series?','Glukkons','Slogs','Scrabs','Sligs','','answer5',0,0),(1116,'The French mathematician &amp;Eacute;variste Galois is primarily known for his work in which?','Abelian Integration','Galois\' Method for PDE\'s ','Galois Theory','Galois\' Continued Fractions','','answer4',0,0),(1117,'In the TV series &quot;Person of Interest&quot;, who plays the character &quot;Harold Finch&quot;?','Kevin Chapman','Michael Emerson','Taraji P. Henson','Jim Caviezel','','answer3',0,0),(1118,'Which of these bones is hardest to break?','Femur','Cranium','Tibia','Humerus','','answer2',0,0),(1119,'What is the derivative of Acceleration with respect to time?','Slide','Jerk','Bump','Shift','','answer3',0,0),(1120,'Hippopotomonstrosesquippedaliophobia is the irrational fear of long words.','True','False','','','','answer2',0,0),(1121,'The planet Mars has two moons orbiting it.','True','False','','','','answer2',0,0),(1122,'How many studio albums have the duo Daft Punk released?','1','2','5','4','','answer5',0,0),(1123,'Which famous spy novelist wrote the childrens&#039; story &quot;Chitty-Chitty-Bang-Bang&quot;?','Graham Greene','Joseph Conrad','Ian Fleming','John Buchan','','answer4',0,0),(1124,'Which of the following countries has officially banned the civilian use of dash cams in cars?','United States','Austria','South Korea','Czechia','','answer3',0,0),(1125,'Which of these aliases has NOT been used by electronic musician Aphex Twin?','GAK','Caustic Window','Burial','Bradley Strider','','answer4',0,0),(1126,'In &quot;Sonic the Hedgehog 2&quot; for the Sega Genesis, what do you input in the sound test screen to access the secret level select?','The Lead Programmer\'s birthday','The first release date of &quot;Sonic the Hedgehog 2&quot;','The first release date of &quot;Sonic the Hedgehog&quot;','The date Sonic Team was founded','','',0,0),(1127,'The collapse of the Soviet Union took place in which year?','1991','1992','1891','1990','','answer2',0,0),(1128,'What is the name the location-based augmented reality game developed by Niantic before Pok&amp;eacute;mon GO?','Aggress','Ingress','Regress','Digress','','answer3',0,0),(1129,'What is the name of the virus in &quot;Metal Gear Solid 1&quot;?','FOXALIVE','FOXDIE','FOXKILL','FOXENGINE','','answer3',0,0),(1130,'What was the first James Bond film?','Goldfinger','From Russia With Love','Dr. No','Thunderball','','answer4',0,0),(1131,'Who voices for Ruby in the animated series RWBY?','Hayden Panettiere','Jessica Nigri','Lindsay Jones','Tara Strong','','answer4',0,0),(1132,'When was &quot;Garry&#039;s Mod&quot; released?','December 24, 2004','November 13, 2004','December 13, 2004','November 12, 2004','','answer2',0,0),(1133,'What is the name of Sid&#039;s dog in &quot;Toy Story&quot;?','Buster','Mr. Jones','Scud','Whiskers','','answer4',0,0),(1134,'In the 1979 British film &quot;Quadrophenia&quot; what is the name of the main protagonist?','Pete Townshend','Franc Roddam','Archie Bunker','Jimmy Cooper','','answer5',0,0),(1135,'Johnny Cash did a cover of this song written by lead singer of Nine Inch Nails, Trent Reznor.','Hurt','Closer','A Warm Place','Big Man with a Gun','','answer2',0,0),(1136,'Who was the author of the 1954 novel, &quot;Lord of the Flies&quot;?','William Golding','Hunter Fox','Stephen King','F. Scott Fitzgerald','','answer2',0,0),(1137,'Who invented the &quot;Flying Shuttle&quot; in 1738; one of the key developments in the industrialization of weaving?','Richard Arkwright','John Deere','James Hargreaves','John Kay','','answer5',0,0),(1138,'Who wrote the lyrics for Leonard Bernstein&#039;s 1957 Brodway musical West Side Story?','Richard Rodgers','Oscar Hammerstein','Himself','Stephen Sondheim','','answer5',0,0),(1139,'Kanye West at 2009 VMA&#039;s interrupted which celebrity?','Beyonce','Taylor Swift','Beck','MF DOOM','','answer3',0,0),(1140,'What is the official language of Bhutan?','Ladakhi','Groma','Dzongkha','Karen','','answer4',0,0),(1141,'Which of the following plastic is commonly used for window frames, gutters and drain pipes?','Polyvinylchloride (PVC) ','Polyethylene (PE)','Polypropylene (PP)','Polystyrene (PS)','','answer2',0,0),(1142,'Which of these programming languages is a low-level language?','Python','Pascal','Assembly','C#','','answer4',0,0),(1143,'During the 2016 United States presidential election, the State of California possessed the most electoral votes, having 55.','True','False','','','','answer2',0,0),(1144,'Before voicing Pearl in Steven Universe, Deedee Magno Hall was part of which American band?','The Cheetah Girls','The Weather Girls','The Pussycat Dolls','The Party','','answer5',0,0),(1145,'What is the official German name of the Swiss Federal Railways?','Schweizerische Bundesbahnen','Bundesbahnen der Schweiz','Schweizerische Staatsbahnen','Schweizerische Nationalbahnen','','answer2',0,0),(1146,'What city is known as the Rose Capital of the World?','Tyler, Texas','San Diego, California','Miami, Florida','Anaheim, California','','answer2',0,0),(1147,'George W. Bush lost the popular vote in the 2004 United States presidential election.','False','True','','','','answer2',0,0),(1148,'The book &quot;The Silence of the Lambs&quot; gets its title from what?','The voice of innocent people being shut by the powerful','The relation it has with killing the innocents','The villain\'s favourite meal','The main character\'s trauma in childhood','','',0,0),(1149,'California is larger than Japan.','False','True','','','','answer3',0,0),(1150,'What nucleotide pairs with guanine?','Uracil','Thymine','Cytosine','Adenine','','answer4',0,0),(1151,'In Terraria, what does the Wall of Flesh not drop upon defeat?','Pwnhammer','Laser Rifle','Picksaw','Breaker Blade','','answer4',0,0),(1152,'According to Overwatch&#039;s lore, who was once a member of the Deadlock Gang?','Roadhog','McCree','Junkrat','Soldier 76','','answer3',0,0),(1153,'Like his character in &quot;Parks and Recreation&quot;, Aziz Ansari was born in South Carolina.','True','False','','','','answer2',0,0),(1154,'What is the smallest country in the world?','Malta','Monaco','Vatican City','Maldives','','answer4',0,0),(1155,'What is the highest mountain in the world?','Kangchenjunga','Annapurna','Mt. Everest','Mount Godwin Austen','','answer4',0,0),(1156,'Which of these games was NOT developed by Markus Persson?','Minecraft','Dwarf Fortress','0x10c','Wurm Online','','answer3',0,0),(1157,'What is the Klingon&#039;s afterlife called?','Sto-vo-kor','Karon\'gahk','New Jersey','Valhalla','','answer2',0,0),(1158,'In &quot;Toriko&quot;, which of the following foods is knowingly compatible with Toriko?','Poison Potato','Mors Oil','Parmesansho Fruit','Alpacookie','','answer2',0,0),(1159,'In which year did &quot;Caravan Palace&quot; release their first album?','2008','2000','2015','2004','','answer2',0,0),(1160,'When did the website &quot;Facebook&quot; launch?','2006','2004','2005','2003','','answer3',0,0),(1161,'In which city, is the Big Nickel located in Canada?','Victoria, British Columbia','Sudbury, Ontario','Halifax, Nova Scotia ','Calgary, Alberta','','answer3',0,0),(1162,'During the Mongolian invasions of Japan, what were the Mongol boats mostly stopped by?','Economic depression','Typhoons','Samurai','Tornados','','answer3',0,0),(1163,'Which Canadian reggae musician had a 1993 hit with the song &#039;Informer&#039;?','Sleet','Snow','Hail','Rain','','answer3',0,0),(1164,'Which Elton John hit starts with the line &#039;When are you gonna come down&#039;?','Crocodile Rock','Rocket Man','Bennie and the Jets','Goodbye Yellow Brick Road','','answer5',0,0),(1165,'What engine did the original &quot;Half-Life&quot; run on?','Unreal','Quake','GoldSrc','Source','','answer4',0,0),(1166,'An octopus can fit through any hole larger than its beak.','False','True','','','','answer3',0,0),(1167,'Which of these is NOT a terrorist faction in Counter-Strike (2000)?','Elite Crew','Midwest Militia','Guerrilla Warfare','Phoenix Connection','','answer3',0,0),(1168,'Which franchise does the creature &quot;Slowpoke&quot; originate from?','Sonic The Hedgehog','Pokemon','Yugioh','Dragon Ball','','answer3',0,0),(1169,'Who directed the movies &quot;Pulp Fiction&quot;, &quot;Reservoir Dogs&quot; and &quot;Django Unchained&quot;?','Steven Spielberg','James Cameron','Quentin Tarantino','Martin Scorcese','','answer4',0,0),(1170,'During WWII, in 1945, the United States dropped atomic bombs on the two Japanese cities of Hiroshima and what other city?','Nagasaki','Tokyo','Kagoshima','Kawasaki','','answer2',0,0),(1171,'According to a Beatles song, who kept her face in a jar by the door?','Loretta Martin','Eleanor Rigby','Molly Jones','Lady Madonna','','answer3',0,0),(1172,'What is the name of the robot in the 1951 science fiction film classic &#039;The Day the Earth Stood Still&#039;?','Box','Robby','Colossus','Gort','','answer5',0,0),(1173,'What word represents the letter &#039;T&#039; in the NATO phonetic alphabet?','Tango','Taxi','Target','Turkey','','answer2',0,0),(1174,'What nationality was the surrealist painter Salvador Dali?','Italian','Spanish','French','Portuguese','','answer3',0,0),(1175,'How many Star Spirits do you rescue in the Nintendo 64 video game &quot;Paper Mario&quot;?','7','5','10','12','','answer2',0,0),(1176,'Who wrote the musical composition, &quot;Rhapsody In Blue&quot;?','George Gershwin','Irving Berlin','Duke Ellington','Johnny Mandel','','answer2',0,0),(1177,'In which city did American rap producer DJ Khaled originate from?','Detroit','New York','Miami','Atlanta','','answer4',0,0),(1178,'Which of the following Pok&amp;eacute;mon games released first?','Pok&amp;eacute;mon Black','Pok&amp;eacute;mon Platinum','Pok&amp;eacute;mon FireRed','Pok&amp;eacute;mon Crystal','','answer5',0,0),(1179,'What alcoholic drink is mainly made from juniper berries?','Gin','Vodka','Tequila','Rum','','answer2',0,0),(1180,'The stop motion comedy show &quot;Robot Chicken&quot; was created by which of the following?','Seth Rollins','Seth Rogen','Seth Green','Seth MacFarlane','','answer4',0,0),(1181,'Which of the following is not a playable race in the MMORPG Guild Wars 2? ','Tengu','Sylvari','Charr','Asura ','','answer2',0,0),(1182,'Joseph Stalin had a criminal past doing what?','Pedophilia','Robbing trains','Identity Fraud','Tax evation','','answer3',0,0),(1183,'In the game Paper Mario for the Nintendo 64 the first partner you meet is a Goomba, what is its name?','Goomby','Goombario','Goombella','Goombarry','','answer3',0,0),(1184,'Who is the main character in the show &quot;Burn Notice&quot;?','Sam Axe','Fiona Glenanne','Madeline Westen','Michael Westen','','answer5',0,0),(1185,'What play is the quote &quot;Hell is other people&quot; from?','The Condemned of Altona','No Exit','The Devil and the Good Lord','The Flies','','answer3',0,0),(1186,'Which horror movie had a sequel in the form of a video game released in August 20, 2002?','The Evil Dead','Alien','The Thing','Saw','','answer4',0,0),(1187,'In the &quot;Toaru Majutsu no Index&quot; anime, Touma Kamijou is a level 0 esper that has the ability to do what?','Dispell any esper or magical powers','Teleport','Create electricity from his own body','Make telepathic communications','','answer2',0,0),(1188,'In what year was the first &quot;Mass Effect&quot; game released?','2007','2008','2009','2010','','answer2',0,0),(1189,'The name of the Metroid series comes from what?','A spaceship\'s name','The main character\'s name','An enemy in the game','The final boss\'s name','','answer4',0,0),(1190,'The first version of Blockland came out in which year?','2008','2007','2004','2006','','answer4',0,0),(1191,'In the game &quot;Terraria&quot;, which of these bosses are pre-hardmode bosses?','The Destroyer','Plantera','Skeletron Prime','Eye of Cthulhu','','answer5',0,0),(1192,'What was Radiohead&#039;s first album?','Kid A','The Bends','A Moon Shaped Pool','Pablo Honey','','answer5',0,0),(1193,'Who is the original author of the realtime physics engine called PhysX?','Ageia','Nvidia','AMD','NovodeX','','answer5',0,0),(1194,'&quot;All the Boys&quot; by Panic! At the Disco was released as a bonus track on what album?','Death Of A Bachelor','Too Weird To Live, Too Rare To Die!','Vices &amp; Virtues','A Fever You Can\'t Sweat Out','','answer3',0,0),(1195,'Which of these characters was almost added into Super Smash Bros. Melee, but not included as the game was too far in development?','R.O.B.','Solid Snake','Pit','Meta Knight','','answer3',0,0),(1196,'What team won the 2016 MLS Cup?','Seattle Sounders','Colorado Rapids','Toronto FC','Montreal Impact','','answer2',0,0),(1197,'What year did the James Cameron film &quot;Titanic&quot; come out in theaters?','1997','1996','1998','1999','','answer2',0,0),(1198,'Mortal Kombat was almost based on Jean-Claude Van Damme movie.','True','False','','','','answer2',0,0),(1199,'How many members are in the Japanese rock band SCANDAL?','5','4','18','2','','answer3',0,0),(1200,'Aperture Science CEO Cave Johnson is voiced by which American actor?','Nolan North','Christopher Lloyd','John Patrick Lowrie','J.K. Simmons','','answer5',0,0),(1201,'What is the name of the gang that Ponyboy is a part of in the book, The Outsiders?','The Mafia','The Outsiders','The Greasers','The Socs','','answer4',0,0),(1202,'What does the term &quot;isolation&quot; refer to in microbiology?','A lack of nutrition in microenviroments','The nitrogen level in soil','Testing effects of certain microorganisms in an isolated enviroments, such as caves','The separation of a strain from a natural, mixed population of living microbes','','answer5',0,0),(1203,'How many seasons did the Sci-Fi television show &quot;Stargate Atlantis&quot; have?','10','5','2','7','','answer3',0,0),(1204,'How many seasons did the Sci-Fi television show &quot;Stargate Universe&quot; have?','2','5','3','10','','answer2',0,0),(1205,'When was the Sega Genesis released in Japan?','August 14, 1989','September 1, 1986','November 30, 1990','October 29, 1988','','answer5',0,0),(1206,'Who of these people was the creator and director of the Katamari Damacy series?','Shinji Mikami','Keita Takahashi','Shu Takumi','Hideki Kamiya','','answer3',0,0),(1207,'In what year did Neil Armstrong and Buzz Aldrin land on the moon?','1973','1965','1969','1966','','answer4',0,0),(1208,'Which best selling toy of 1983 caused hysteria, resulting in riots breaking out in stores?','Care Bears','Cabbage Patch Kids','Rubik&amp;rsquo;s Cube','Transformers','','answer3',0,0),(1209,'Which of these games was the earliest known first-person shooter with a known time of publication?','Doom','Quake','Spasim','Wolfenstein','','answer4',0,0),(1210,'There are no roads in/out of Juneau, Alaska.','False','True','','','','answer3',0,0),(1211,'The theme for the popular science fiction series &quot;Doctor Who&quot; was composed by who?','Peter Howell','Delia Derbyshire','Ron Grainer','Murray Gold','','answer4',0,0),(1212,'In the original Star Trek TV series, what was Captain James T. Kirk&#039;s middle name?','Travis','Tiberius','Tyrone','Trevor','','answer3',0,0),(1213,'Who wrote the musical composition, &quot;Rhapsody In Blue&quot;?','Duke Ellington','Johnny Mandel','George Gershwin','Irving Berlin','','answer4',0,0),(1214,'Irish musician Hozier released a music track in 2013 titled, &quot;Take Me to ______&quot;','Temple','Mosque','Synagogue','Church','','answer5',0,0),(1215,'In the &quot;Harry Potter&quot; series, what is Headmaster Dumbledore&#039;s full name?','Albus Valencium Horatio Kul Dumbledore','Albus Percival Wulfric Brian Dumbledore','Albus Valum Jetta Mobius Dumbledore','Albus James Lunae Otto Dumbledore','','answer3',0,0),(1216,'If soccer is called football in England, what is American football called in England?','Touchdown','Handball','American football','Combball','','answer4',0,0),(1217,'What is the first Mersenne prime over 1000?','1069','2203','1009','1279','','answer5',0,0),(1218,'The series of the Intel HD graphics generation succeeding that of the 5000 and 6000 series (Broadwell) is called:','HD Graphics 7000','HD Graphics 600','HD Graphics 500','HD Graphics 700 ','','answer4',0,0),(1219,'In the Animal Crossing series, which flower is erroneously called the &quot;Jacob&#039;s Ladder&quot;?','Harebell','Yarrow','Lily of the Valley','Hydrangea','','answer4',0,0),(1220,'Who was the star of the TV series &quot;24&quot;?','Kiefer Sutherland','Hugh Laurie','Kevin Bacon','Rob Lowe','','answer2',0,0),(1221,'An eggplant is a vegetable.','False','True','','','','answer2',0,0),(1222,'Bulls are attracted to the color red.','False','True','','','','answer2',0,0),(1223,'How many legs do butterflies have?','4','0','2','6','','answer5',0,0),(1224,'Which of the following Arab countries does NOT have a flag containing only Pan-Arab colours?','Qatar','Jordan','United Arab Emirates','Kuwait','','answer2',0,0),(1225,'What year did radio icon Howard Stern start a job at radio station WNBC?','1995','1985','1982','1984','','answer4',0,0),(1226,'Which brass instrument has the lowest pitch in an orchestra?','Trumpet','Saxophone','Tuba','Trombone','','answer4',0,0),(1227,'Who painted &quot;Swans Reflecting Elephants&quot;, &quot;Sleep&quot;, and &quot;The Persistence of Memory&quot;?','Salvador Dali','Jackson Pollock','Edgar Degas','Vincent van Gogh','','answer2',0,0),(1228,'What is the romanized Japanese word for &quot;university&quot;?','Toshokan','Jimusho','Shokudou','Daigaku','','answer5',0,0),(1229,'AMD created the first consumer 64-bit processor.','False','True','','','','answer3',0,0),(1230,'In the 1979 British film &quot;Quadrophenia&quot; what is the name of the main protagonist?','Jimmy Cooper','Archie Bunker','Franc Roddam','Pete Townshend','','answer2',0,0),(1231,'During the Roman Triumvirate of 42 BCE, what region of the Roman Republic was given to Lepidus?','Italia','Gallia','Hispania ','Asia','','answer4',0,0),(1232,'TF2: What code does Soldier put into the door keypad in &quot;Meet the Spy&quot;?','1337','No code','1432','1111','','answer5',0,0),(1233,'In Jeff Wayne&#039;s Musical Version of War of the Worlds, the chances of anything coming from Mars are...','A trillion to one','A billion to one','A million to one','A hundred to one','','answer4',0,0),(1234,'In Game of Thrones, what is Littlefinger&#039;s real name?','Petyr Baelish','Podrick Payne','Lancel Lannister','Torrhen Karstark','','answer2',0,0),(1235,'The decimal number 31 in hexadecimal would be what?','2E','3D','1B','1F','','answer5',0,0),(1236,'Which of these is a stop codon in DNA?','GTA','ACT','TAA','ACA','','answer4',0,0),(1237,'The Hagia Sophia was commissioned by which emperor of the Byzantine Empire?','Arcadius','Justinian I','Constantine IV','Theodosius the Great','','answer3',0,0),(1238,'Which American author was also a budding travel writer and wrote of his adventures with his dog Charley?','Ernest Hemingway','F. Scott Fitzgerald','John Steinbeck','William Faulkner','','answer4',0,0),(1239,'In what year was Hearthstone released?','2014','2011','2013','2012','','answer2',0,0),(1240,'When did Norway become free from Sweden?','1834','1905','1925','1814','','answer3',0,0),(1241,'Formula E is an auto racing series that uses hybrid electric race cars.','False','True','','','','answer2',0,0),(1242,'Which class of animals are newts members of?','Mammals','Reptiles','Fish','Amphibian','','answer5',0,0),(1243,'What is the last song on the first Panic! At the Disco album?','Lying Is The Most Fun A Girl Can Have Without Taking Her Clothes Off','I Write Sins Not Tragedies','Build God, Then We\'ll Talk','Nails for Breakfast, Tacks for Snacks','','',0,0),(1244,'What year was Apple Inc. founded?','1978','1980','1976','1974','','answer4',0,0),(1245,'It&#039;s not possible to format a write-protected DVD-R Hard Disk.','False','True','','','','answer3',0,0),(1246,'What CS:GO case contains the Butterfly Knife?','Breakout Case','Esports 2014 Case','Shadow Case','Vanguard Case','','answer2',0,0),(1247,'In Yu-Gi-Oh, how does a player perform an Xyz Summon?','Add the Monsters\' Levels Together to Match the Xyz Monster','Activate a Spell and Send Monsters to the Graveyard','Overlay at least 2 Monsters of the Same Level','Banish A Number of Monsters From Your Hand And Deck','','answer4',0,0),(1248,'Which of the following countries was not an axis power during World War II?',' Soviet Union','Italy','Germany','Japan','','answer2',0,0),(1249,'Which famous singer was portrayed by actor Kevin Spacey in the 2004 biographical film &quot;Beyond the Sea&quot;?','Louis Armstrong','Bobby Darin','Frank Sinatra','Dean Martin','','answer3',0,0),(1250,'Valve&#039;s &quot;Portal&quot; and &quot;Half-Life&quot; franchises exist within the same in-game universe.','True','False','','','','answer2',0,0),(1251,'Which monster in &quot;Monster Hunter Tri&quot; was causing earthquakes in Moga Village?','Rathalos','Lagiacrus','Ceadeus','Alatreon','','answer4',0,0),(1252,'Which of these stages is not playable in &quot;Super Smash Bros. for Wii U&quot;?','Fountain of Dreams','Bridge of Eldin','75m','Miiverse','','answer2',0,0),(1253,'In the Doctor Who universe, how many times can a time lord normally regenerate?','13','11','12','15','','answer4',0,0),(1254,'What name did Tom Hanks give to his volleyball companion in the film `Cast Away`?','Jones','Friday','Billy','Wilson','','answer5',0,0),(1255,'Pianist Fr&amp;eacute;d&amp;eacute;ric Chopin was a composer of which musical era?','Renaissance','Baroque','Romantic','Classic','','answer4',0,0),(1256,'This Greek mythological figure is the god/goddess of battle strategy (among other things).','Ares','Artemis','Athena','Apollo','','answer4',0,0),(1257,'What is the main character of Metal Gear Solid 2?','Venom Snake','Solidus Snake','Big Boss','Raiden','','answer5',0,0),(1258,'Who was the British professional wrestler Shirley Crabtree better known as?','Masambula','Kendo Nagasaki','Big Daddy','Giant Haystacks','','answer4',0,0),(1259,'The Principality of Sealand is an unrecognized micronation off the coast of what country?','The United Kingdom','Japan','Argentina','Austrailia','','answer2',0,0),(1260,'Along with Gabe Newell, who co-founded Valve?','Robin Walker','Marc Laidlaw','Mike Harrington','Stephen Bahl','','answer4',0,0),(1261,'Which gas forms about 78% of the Earth&amp;rsquo;s atmosphere?','Nitrogen','Carbon Dioxide','Argon','Oxygen','','answer2',0,0),(1262,'The M41 Walker Bulldog remains in service in some countries to this day.','True','False','','','','answer2',0,0),(1263,'In the anime &quot;Mr. Osomatsu&quot;, how many brothers does Osomatsu-san have?','5','6','7','4','','answer2',0,0),(1264,'In the game &quot;Undertale&quot;, who was Mettaton&#039;s creator?','Asgore','Sans','Alphys','Undyne','','answer4',0,0),(1265,'Which computer hardware device provides an interface for all other connected devices to communicate?','Motherboard','Central Processing Unit','Random Access Memory','Hard Disk Drive','','answer2',0,0),(1266,'What is the world&#039;s oldest board game?','Go','Senet','Checkers','Chess','','answer3',0,0),(1267,'What does the Prt Sc button do?','Nothing','Closes all windows','Captures what\'s on the screen and copies it to your clipboard','Saves a .png file of what\'s on the screen in your screenshots folder in photos','','',0,0),(1268,'What year was Canada founded in?','1668','1867','1798','1859','','answer3',0,0),(1269,'The country song  &amp;ldquo;A Boy Named Sue&amp;rdquo; was written by Shel Silverstein.','True','False','','','','answer2',0,0),(1270,'Finish these lyrics from the 2016 song &quot;Panda&quot; by Desiigner: &quot;I got broads in _______&quot;.','Augusta','Savannah','Marietta','Atlanta','','answer5',0,0),(1271,'In the fighting game &quot;Skullgirls,&quot; which character utilizes a folding chair called the &quot;Hurting&quot; as a weapon?','Beowulf','Cerebella','Big Band','Squigly','','answer2',0,0),(1272,'After how many years would you celebrate your crystal anniversary?','20','10','25','15','','answer5',0,0),(1273,'When was &quot;The Gadget&quot;, the first nuclear device to be detonated, tested?','July 16, 1945','April 5, 1945','August 6, 1945','June 22, 1945','','answer2',0,0),(1274,'Which country is hosting the 2022 FIFA World Cup?','Qatar','Uganda','Bolivia','Vietnam','','answer2',0,0),(1275,'What is the name of the main character in the video game VA-11 HALL-A: Cyberpunk Bartender Action?','Dana','Anna','Jill','Alma','','answer4',0,0),(1276,'Disney&#039;s Haunted Mansion is home to a trio of Hitchhiking Ghosts. Which of these is NOT one of them?','Ezra','Phineas','Gus','Harry','','answer5',0,0),(1277,'How many countries border Kyrgyzstan?','4','1','3','6','','answer2',0,0),(1278,'In &quot;Overwatch,&quot; what is the hero McCree&#039;s full name?','Jamison &quot;Deadeye&quot; Fawkes','Jesse McCree','Jack &quot;McCree&quot; Morrison','Gabriel Reyes','','answer3',0,0),(1279,'Which of the following countries does &quot;JoJo&#039;s Bizarre Adventure: Stardust Crusaders&quot; not take place in?','Philippines','India','Egypt','Pakistan','','answer2',0,0),(1280,'Theodore Roosevelt Jr. was the only General involved in the initial assault on D-Day.','True','False','','','','answer2',0,0),(1281,'Which movie of film director Stanley Kubrick is known to be an adaptation of a Stephen King novel?','The Shining','Eyes Wide Shut',' Dr. Strangelove ','2001: A Space Odyssey','','answer2',0,0),(1282,'TF2: Sentry rocket damage falloff is calculated based on the distance between the sentry and the enemy, not the engineer and the enemy','False','True','','','','answer2',0,0),(1283,'In Dead Rising, Frank West&#039;s job is being a','Taxi Driver','Photojournalist','Janitor','Chef','','answer3',0,0),(1284,'You could walk from Norway to North Korea while only passing through Russia.','False','True','','','','answer3',0,0),(1285,'Which country does the YouTuber &quot;SinowBeats&quot; originate from?','Germany','England','Sweden','Scotland','','answer5',0,0),(1286,'Which celebrity announced his presidency in 2015?','Leonardo DiCaprio','Donald Trump','Miley Cyrus','Kanye West','','answer5',0,0),(1287,'What collaborative album was released by Kanye West and Jay-Z in 2011?','Watch the Throne','What a Time to be Alive','Unfinished Business','Distant Relatives','','answer2',0,0),(1288,'Joan Cusack starred in the 2009 disaster movie, &quot;2012&quot;.','True','False','','','','answer3',0,0),(1289,'In Riot Games &quot;League of Legends&quot; the name of Halloween event is called &quot;The Reckoning&quot;.','False','True','','','','answer2',0,0),(1290,'How many regular Sunken Sea Scrolls are there in &quot;Splatoon&quot;?','27','32','5','30','','answer2',0,0),(1291,'What was the name of one of the surviving palaces of Henry VIII located near Richmond, London?','Hampton Court','Buckingham Palace','Coughton Court','St James\'s Palace','','answer2',0,0),(1292,'Norway has a larger land area than Sweden.','True','False','','','','answer3',0,0),(1293,'Danganronpa Another Episode: Ultra Despair Girls is set after which Danganronpa game?','Danganronpa: Trigger Happy Havoc','Danganronpa 3: The End of Hope\'s Peak High School','Danganronpa V3: Killing Harmony','Danganronpa 2: Goodbye Despair','','answer2',0,0),(1294,'Which of Michael Jackson&#039;s albums sold the most copies?','Thriller','Dangerous','Bad','Off the Wall','','answer2',0,0),(1295,'Klingons respect their disabled comrades, and those who are old, injuried, and helpless.','True','False','','','','answer3',0,0),(1296,'Tokyo is the capital of Japan.','False','True','','','','answer3',0,0),(1297,'What was Nickelodeon&#039;s original name?','Pinwheel','Splat!','KidsTV','MTVKids','','answer2',0,0),(1298,'In which location does Dark Sun Gwyndolin reside in &quot;Dark Souls&quot;?','Blighttown','Firelink Shrine','Kiln of the first flame','Anor Londo','','answer5',0,0),(1299,'In the &quot;Jurassic Park&quot; universe, when did &quot;Jurassic Park: San Diego&quot; begin construction?','1985','1988','1986','1993','','answer2',0,0),(1300,'Which game did &quot;Sonic The Hedgehog&quot; make his first appearance in?','Super Mario 64','Rad Mobile','Mega Man','Sonic The Hedgehog','','answer3',0,0),(1301,'Who&#039;s the creator of Geometry Dash?','Andrew Spinks','Robert Topala','Scott Cawthon','Adam Engels','','answer3',0,0),(1302,'What is the capital of the American state of Arizona?','Phoenix','Tallahassee','Raleigh','Montgomery','','answer2',0,0),(1303,'How many objects are equivalent to one mole?','6.022 x 10^23','6.022 x 10^22','6.002 x 10^22','6.002 x 10^23','','answer2',0,0),(1304,'When was the Declaration of Independence approved by the Second Continental Congress?','June 4, 1776','July 2, 1776','July 4, 1776','May 4, 1776','','answer3',0,0),(1305,'What name represents the letter &quot;M&quot; in the NATO phonetic alphabet?','Mark','Matthew','Mike','Max','','answer4',0,0),(1306,'Which is the longest bone in the human body? ','Femur','Scapula','Ulna','Fibula','','answer2',0,0),(1307,'Ringo Starr of The Beatles mainly played what instrument?','Bass','Guitar','Piano','Drums','','answer5',0,0),(1308,'What is Hermione Granger&#039;s middle name?','Jo','Jane','Jean','Emma','','answer4',0,0),(1309,'The S in Harry S. Truman stands for &quot;Samuel&quot;.','False','True','','','','answer2',0,0),(1310,'What was the pen name of novelist, Mary Ann Evans?','George Saunders','George Orwell','George Eliot','George Bernard Shaw','','answer4',0,0),(1311,'The programming language &quot;Python&quot; is based off a modified version of &quot;JavaScript&quot;.','False','True','','','','answer2',0,0),(1312,'In the comic book &quot;Archie&quot;, Betty is friends with Veronica because she is rich.','True','False','','','','answer3',0,0),(1313,'All of the following programs are classified as raster graphics editors EXCEPT:','GIMP','Adobe Photoshop','Paint.NET','Inkscape','','answer5',0,0),(1314,'The original Roman alphabet lacked the following letters EXCEPT:','W','U','X','J','','answer4',0,0),(1315,'Pac-Man was invented by the designer Toru Iwatani while he was eating pizza.','True','False','','','','answer2',0,0),(1316,'What was the first interactive movie video game?','Cube Quest','M.A.C.H. 3','Astron Belt','Dragon\'s Lair','','answer4',0,0),(1317,'What country has a horizontal bicolor red and white flag?','Liechenstein','Malta','Bahrain','Monaco','','answer5',0,0),(1318,'In the 1994 movie &quot;Speed&quot;, what is the minimum speed the bus must go to prevent to bomb from exploding?','40 mph','70 mph','60 mph','50 mph','','answer5',0,0),(1319,'What is the stage name of English female rapper Mathangi Arulpragasam, who is known for the song &quot;Paper Planes&quot;?','C.I.A.','M.I.A.','A.I.A.','K.I.A.','','answer3',0,0),(1320,'What is the romanized Russian word for &quot;winter&quot;?','Osen\'','Zima','Vesna','Leto','','answer3',0,0),(1321,'Russia passed a law in 2013 which outlaws telling children that homosexuals exist.','False','True','','','','answer3',0,0),(1322,'Which Game Boy from the Game Boy series of portable video game consoles was released first?','Game Boy Advance SP','Game Boy Color','Game Boy Advance','Game Boy Micro','','answer3',0,0),(1323,'Winch of these names are not a character of JoJo&#039;s Bizarre Adventure?','JoJo Kikasu','George Joestar','Risotto Nero','Jean-Pierre Polnareff','','answer2',0,0),(1324,'The song &quot;Feel Good Inc.&quot; by British band Gorillaz features which hip hop group?','OutKast','Public Enemy','Cypress Hill','De La Soul','','answer5',0,0),(1325,'Who was the Prime Minister of Japan when Japan declared war on the US?','Fumimaro Konoe','Isoroku Yamamoto','Hideki Tojo','Michinomiya Hirohito','','answer4',0,0),(1326,'The asteroid belt is located between which two planets?','Jupiter and Saturn','Mercury and Venus','Mars and Jupiter','Earth and Mars','','answer4',0,0),(1327,'Which buzzword did Apple Inc. use to describe their removal of the headphone jack?','Bravery','Innovation','Courage','Revolution','','answer4',0,0),(1328,'In PROTOTYPE 2. who is referred to as &quot;Tango Primary&quot;?','Dana Mercer','Alex Mercer','James Heller','Any Goliaths roaming around','','answer4',0,0),(1329,'In Game of Thrones, what is Littlefinger&#039;s real name?','Petyr Baelish','Podrick Payne','Lancel Lannister','Torrhen Karstark','','answer2',0,0),(1330,'What is the name of the main character in &quot;Life is Strange&quot;?','Maxine Caulfield','Chloe Price','Stella Hill','Victoria Chase','','answer2',0,0),(1331,'In what year was the first &quot;Mass Effect&quot; game released?','2007','2010','2008','2009','','answer2',0,0),(1332,'Which male player won the gold medal of table tennis singles in 2016 Olympics Games?','Ma Long (China)','Vladimir Samsonov (Belarus)','Zhang Jike (China)','Jun Mizutani (Japan)','','answer2',0,0),(1333,'Which soccer team won the Copa Am&amp;eacute;rica Centenario 2016?','Brazil','Chile','Argentina','Colombia','','answer3',0,0),(1334,'71% of the Earth&#039;s surface is made up of','Continents','Forests','Water','Deserts','','answer4',0,0),(1335,'What is the name of the song by Beyonc&amp;eacute; and Alejandro Fern&amp;aacute;ndez released in 2007?','Amor Gitano','La ultima vez','Rocket','Hasta Dondes Estes','','answer2',0,0),(1336,'In 2014, over 6 million General Motors vehicles were recalled due to what critical flaw?','Faulty brake pads','Malfunctioning gas pedal','Faulty ignition switch','Breaking fuel lines','','answer4',0,0),(1337,'African-American performer Sammy Davis Jr. was known for losing which part of his body in a car accident?','Nose','Right Middle Finger','Right Ear','Left Eye','','answer5',0,0),(1338,'Who is the main character in most of the games of the YS series?','Adol Christin ','Estelle Bright','Roger Wilco','Character doesn\'t have a name','','answer2',0,0),(1339,'Which company developed the Hololens?','Tobii','HTC','Oculus','Microsoft','','answer5',0,0),(1340,'In &quot;JoJo&#039;s Bizarre Adventure&quot;, which of the following Stands does NOT have a time-based ability?','20th Century Boy','The World','Made in Heaven','Star Platinum','','answer2',0,0),(1341,'Which RAID array type is associated with data mirroring?','RAID 5','RAID 0','RAID 10','RAID 1','','answer5',0,0),(1342,'In &quot;Doctor Who&quot;, the Doctor gets his TARDIS by stealing it.','True','False','','','','answer2',0,0),(1343,'Who created the animated movie &quot;Spirited Away&quot; (2001)?','Isao Takahata','Hayao Miyazaki','Mamoru Hosoda','Hidetaka Miyazaki','','answer3',0,0),(1344,'Killing Floor started as a mod for which Unreal Engine 2 game?','Deus Ex: Invisible War','Unreal Tournament 3','Unreal Tournament 2004','Postal','','answer4',0,0),(1345,'What year did the television company BBC officially launch the channel BBC One?','1955','1936','1948','1932','','answer3',0,0),(1346,'When did the French Revolution begin?','1799','1823','1756','1789','','answer5',0,0),(1347,'In a standard set of playing cards, which is the only king without a moustache?','Spades','Clubs','Hearts','Diamonds','','answer4',0,0),(1348,'What is the name of Team Fortress 2&#039;s Heavy Weapons Guy&#039;s minigun?','Anna','Betty','Sasha','Diana','','answer4',0,0),(1349,'Who voiced the character Draco in the 1996 movie &#039;DragonHeart&#039;?','Pete Postlethwaite','Dennis Quaid','Brian Thompson','Sean Connery','','answer5',0,0),(1350,'Gumbo is a stew that originated in Louisiana.','False','True','','','','answer3',0,0),(1351,'What was the capital of South Vietnam before the Vietnam War?','Ho Chi Minh City','Hanoi','Saigon','Hue','','answer4',0,0),(1352,'If a &quot;360 no-scope&quot; is one full rotation before shooting, how many rotations would a &quot;1080 no-scope&quot; be?','2','4','5','3','','answer5',0,0),(1353,'What colour is the circle on the Japanese flag?','White','Yellow','Black','Red','','answer5',0,0),(1354,'Which of these features was added in the 1994 game &quot;Heretic&quot; that the original &quot;DOOM&quot; could not add due to limitations?','Highly-detailed textures','Increased room sizes','Looking up and down','Unlimited weapons','','answer4',0,0),(1355,'Complete the following analogy: Audi is to Volkswagen as Infiniti is to ?','Hyundai','Subaru','Nissan','Honda','','answer4',0,0),(1356,'Which infamous European traitor was known as &quot;the last person to enter Parliament with honest intentions&quot;?','Francis Tresham','Robert Catesby','Everard Digby','Guy Fawkes','','answer5',0,0),(1357,'The Ace Attorney trilogy was suppose to end with &quot;Phoenix Wright: Ace Attorney &amp;minus; Trials and Tribulations&quot; as its final game.','False','True','','','','answer3',0,0),(1358,'In which order do you need to hit some Deku Scrubs to open the first boss door in &quot;Ocarina of Time&quot;?','2, 1, 3','1, 2, 3','1, 3, 2','2, 3, 1','','answer5',0,0),(1359,'The binary number &quot;101001101&quot; is equivalent to the Decimal number &quot;334&quot;','False','True','','','','answer2',0,0),(1360,'The Rio 2016 Summer Olympics held it&#039;s closing ceremony on what date?','August 19','August 21','August 23','August 17','','answer3',0,0),(1361,'The 2014 movie &quot;The Raid 2: Berandal&quot; was mainly filmed in which Asian country?','Indonesia','Malaysia','Thailand','Brunei','','answer2',0,0),(1362,'In which year did the First World War begin?','1939','1917','1914','1930','','answer4',0,0),(1363,'What year was &quot;JoJo&#039;s Bizarre Adventure: Phantom Blood&quot; first released?','1995','1987','1983','2013','','answer3',0,0),(1364,'In Call of Duty: United Offensive, what two soldiers share a name of a video game character?','Sam &amp; Fisher','Nathan &amp; Drake','Gordon &amp; Freeman','Dig &amp; Dug','','',0,0),(1365,'The French word to travel is &quot;Travail&quot;','True','False','','','','answer3',0,0),(1366,'What year was Huun Huur Tu&#039;s album Altai Sayan Tandy-Uula released? ','2006','2010','2004','1993','','answer4',0,0),(1367,'What was another suggested name for, the 1985 film, Back to the Future?','Hill Valley Time Travelers','The Lucky Man','The Time Travelers','Spaceman From Pluto','','answer5',0,0),(1368,'The main playable character of the 2015 RPG &quot;Undertale&quot; is a monster.','False','True','','','','answer2',0,0),(1369,'Who created the pump &quot;F.L.U.D.D.&quot; Mario uses in Super Mario Sunshine?','Nirona','Elvin Gadd','Crygor','Robert Fludd','','answer3',0,0),(1370,'What was Genghis Khan&#039;s real name?','&amp;Ouml;gedei','Tem&amp;uuml;r','M&amp;ouml;ngke','Tem&amp;uuml;jin','','answer5',0,0),(1371,'Who patented a steam engine that produced continuous rotary motion?','Albert Einstein','Nikola Tesla','Alessandro Volta','James Watt','','answer5',0,0),(1372,'In Animal Crossing: New Leaf, which of these paintings from Redd&#039;s Art Gallery is always genuine?','Wistful Painting','Jolly Painting','Neutral Painting','Warm Painting','','answer5',0,0),(1373,'Romanian belongs to the Romance language family, shared with French, Spanish, Portuguese and Italian. ','False','True','','','','answer3',0,0),(1374,'What element on the periodic table has 92 electrons?','Uranium','Iron','Sulfur','Hydrogen','','answer2',0,0),(1375,'What was the first .hack game?','.hack//Liminality','.hack//Sign','.hack//Infection','.hack//Zero','','answer4',0,0),(1376,'In the game &quot;Persona 4&quot;, what is the canonical name of the protagonist?','Chino Mashido','Yu Narukami','Tunki Sunada','Masaki Narinaka','','answer3',0,0),(1377,'The song &quot;Caramelldansen&quot; is commonly mistaken as a Japanese song, what language is the song actually sung in?','Finnish','Swedish','Hungarian','Chinese','','answer3',0,0),(1378,'Which of the following characters from the game &quot;Overwatch&quot; was revealed to be homosexual in December of 2016?','Tracer','Sombra','Symmetra','Widowmaker','','answer2',0,0),(1379,'How many voice channels does the Nintendo Entertainment System support natively?','4','3','6','5','','answer5',0,0),(1380,'In &quot;Breaking Bad&quot;, Walter White is a high school teacher diagnosed with which form of cancer?','Testicular Cancer','Lung Cancer','Prostate Cancer','Brain Cancer','','answer3',0,0),(1381,'In Terraria, which of these items is NOT crafted at a Mythril Anvil?','Orichalcum Tools','Sky Fracture','Venom Staff','Ankh Charm','','answer5',0,0),(1382,'Which of the following originated as a manga?','Gurren Lagann','Akira','Cowboy Bebop','High School DxD','','answer3',0,0),(1383,'Which person from &quot;JoJo&#039;s Bizarre Adventure&quot; does NOT house a reference to a band, artist, or song earlier than 1980?','Johnny Joestar','Jolyne Cujoh','Giorno Giovanna','Josuke Higashikata','','answer4',0,0),(1384,'What is the unit of electrical capacitance?','Gauss','Farad','Watt','Henry','','answer3',0,0),(1385,'In &quot;Hunter x Hunter&quot;, what are members in Killua&#039;s family known for being?','Ninjas','Assassins','Bandits','Hunters','','answer3',0,0),(1386,'Stagecoach owned &quot;South West Trains&quot; before losing the rights to FirstGroup and MTR in March of 2017.','False','True','','','','answer3',0,0),(1387,'What is the punishment for playing Postal 2 in New Zealand?','Nothing','10 years in prison and a fine of $50,000','15 years in prison and a fine of $10,000','Fine of $5,000','','answer3',0,0),(1388,'What was the name of the police officer in the cartoon &quot;Top Cat&quot;?','Dibble','Murphy','Mahoney','Barbrady','','answer2',0,0),(1389,'Which of these TrackMania environments was NOT in the original game?','Desert','Bay','Snow','Rally','','answer3',0,0),(1390,'The Doppler effect applies to light.','False','True','','','','answer3',0,0),(1391,'In the  Rossini opera, what was the name of &#039;The Barber of Seville&#039;?','Figaro','Dave','Fernando','Angelo','','answer2',0,0),(1392,'Who recorded the1975 album &#039;Captain Fantastic and the Brown Dirt Cowboy&#039;?','Joe Cocker','Elton John','John Denver','Billy Joel','','answer3',0,0),(1393,'Which type of rock is created by intense heat AND pressure?','Diamond','Sedimentary','Metamorphic','Igneous','','answer4',0,0),(1394,'Who is the main character of the game Half-Life: Opposing Force?','Alyx Vance','Gordon Freeman','Adrian Shephard','Barney Calhoun','','answer4',0,0),(1395,'What is Doug Walker&#039;s YouTube name?','AngryJoeShow','The Nostalgia Critic','The Angry Video Game Nerd','The Cinema Snob','','answer3',0,0),(1396,'The city of Rockport is featured in which of the following video games?','Saints Row: The Third','Need for Speed: Most Wanted (2005)','Infamous 2','Burnout Revenge','','answer3',0,0),(1397,'Which of the following former Yugoslavian states is landlocked?','Croatia','Montenegro','Bosnia and Herzegovina','Serbia','','answer5',0,0),(1398,'In Forza Motorsport 6, which of these track-exclusive cars was NOT featured in the game, either originally with the game or added as DLC?','Ferrari FXX-K','McLaren P1 GTR','Lotus E23','Aston Martin Vulcan','','answer5',0,0),(1399,'111,111,111 x 111,111,111 = 12,345,678,987,654,321','True','False','','','','answer2',0,0),(1400,'Who was the King of Gods in Ancient Greek mythology?','Poseidon','Apollo','Hermes','Zeus','','answer5',0,0),(1401,'How many members are there in the idol group &quot;&amp;micro;&#039;s&quot;?','9','48','6','3','','answer2',0,0),(1402,'In &quot;Clash Royale&quot; what is Arena 4 called?','Barbarian Bowl','Royal Arena','Spell Valley','P.E.K.K.A\'s Playhouse','','',0,0),(1403,'The &quot;Tibia&quot; is found in which part of the body?','Arm','Hand','Leg','Head','','answer4',0,0),(1404,'Studio Ghibli is a Japanese animation studio responsible for the films &quot;Wolf Children&quot; and &quot;The Boy and the Beast&quot;.','True','False','','','','answer3',0,0),(1405,'The binary number &quot;101001101&quot; is equivalent to the Decimal number &quot;334&quot;','True','False','','','','answer3',0,0),(1406,'Alzheimer&#039;s disease primarily affects which part of the human body?','Brain','Heart','Lungs','Skin','','answer2',0,0),(1407,'What is the official language of Costa Rica?','Creole','Portuguese','Spanish','English','','answer4',0,0),(1408,'What is the chemical formula for ammonia?','CO2','NO3','CH4','NH3','','answer5',0,0),(1409,'&quot;Rollercoaster Tycoon&quot; was programmed mostly entirely in...','x86 Assembly','ALGOL','C++','C','','answer2',0,0),(1410,'One of the early prototypes of the Sega Dreamcast controller resembled which of the following?','Tablet','Television Remote','Hairdryer','Flip Phone','','answer3',0,0),(1411,'If you could fold a piece of paper in half 50 times, its&#039; thickness will be 3/4th the distance from the Earth to the Sun.','True','False','','','','answer2',0,0),(1412,'Former United States Presidents John Adams and Thomas Jefferson died within hours of each other.','True','False','','','','answer2',0,0),(1413,'Who is the protagonist in Dead Rising (2006)?','John North','Frank West','Jason Grey','Chuck Greene','','answer3',0,0),(1414,'Who tutored Alexander the Great?','Aristotle','Plato','King Philip','Socrates','','answer2',0,0),(1415,'Which of these African countries list &quot;Spanish&quot; as an official language?','Guinea','Equatorial Guinea','Angola','Cameroon','','answer3',0,0),(1416,'In vanilla Minecraft, you can make armor out of all BUT which of the following?','Leather','Diamonds','Iron','Emeralds','','answer5',0,0),(1417,'What does the yellow diamond on the NFPA 704 fire diamond represent?','Reactivity','Flammability','Health','Radioactivity','','answer2',0,0),(1418,'In PROTOTYPE 2. who is referred to as &quot;Tango Primary&quot;?','Any Goliaths roaming around','Alex Mercer','James Heller','Dana Mercer','','answer4',0,0),(1419,'&quot;HTML&quot; stands for Hypertext Markup Language.','False','True','','','','answer3',0,0),(1420,'In the year 1818, novelist Mary Shelly is credited with writing a fiction novel and creating this infamous character.','The Invisible Man','Frankenstein\'s monster','The Thing','Dracula','','',0,0),(1421,'What is the name of the peninsula containing Spain and Portugal?','Peloponnesian Peninsula','Scandinavian Peninsula','European Peninsula','Iberian Peninsula','','answer5',0,0),(1422,'The title of Adolf Hitler&#039;s autobiography &quot;Mein Kampf&quot; is what when translated to English?','My Sadness','My Hatred','My Desire','My Struggle','','answer5',0,0),(1423,'During the Spanish Civil War (1936), Francisco Franco fought for which political faction?','Nationalist Spain','Popular Front','Papal State','Republican Spain','','answer2',0,0),(1424,'What is the cartoon character, Andy Capp, known as in Germany?','Dick Tingeler','Willi Wakker','Rod Tapper','Helmut Schmacker','','answer3',0,0),(1425,'A minotaur is half human half what?','Horse','Eagle','Cow','Bull','','answer5',0,0),(1426,'What is the first primary weapon the player gets in PAYDAY: The Heist?','Reinbeck','Brenner 21','AMCAR-4','M308','','answer4',0,0),(1427,'Which of the following manga have the most tankouban volumes?','JoJo\'s Bizarre Adventure','Detective Conan','One Piece','Golgo 13','','answer5',0,0),(1428,'On what medium was &quot;Clannad&quot; first created?','Manga','Light novel','Anime','Visual novel','','answer5',0,0),(1429,'Cats have whiskers under their legs.','False','True','','','','answer3',0,0),(1430,'Zero factorial is equal to zero. ','True','False','','','','answer3',0,0),(1431,'Scatman John&#039;s real name was John Paul Larkin.','True','False','','','','answer2',0,0),(1432,'In the show &quot;Foster&#039;s Home For Imaginary Friends&quot;, which character had an obsession with basketball?','Coco','Wilt','Cheese','Mac','','answer3',0,0),(1433,'In which location does Dark Sun Gwyndolin reside in &quot;Dark Souls&quot;?','Anor Londo','Blighttown','Firelink Shrine','Kiln of the first flame','','answer2',0,0),(1434,'What does the term MIME stand for, in regards to computing?','Multipurpose Internet Mail Extensions','Mail Internet Mail Exchange','Multipurpose Interleave Mail Exchange','Mail Interleave Method Exchange','','answer2',0,0),(1435,'In the &quot;Jurassic Park&quot; universe, when did &quot;Jurassic Park: San Diego&quot; begin construction?','1986','1993','1988','1985','','answer5',0,0),(1436,'How many times did Martina Navratilova win the Wimbledon Singles Championship?','Seven','Ten','Nine','Eight','','answer4',0,0),(1437,'What was Marilyn Monroe`s character&#039;s first name in the film &quot;Some Like It Hot&quot;?','Sugar','Honey','Candy','Caramel','','answer2',0,0),(1438,'What was the name of the hero in the 80s animated video game &#039;Dragon&#039;s Lair&#039;?','Dirk the Daring','Sir Toby Belch','Guy of Gisbourne','Arthur','','answer2',0,0),(1439,'The Spitfire L.F. Mk IX had what engine?','Merlin 66','Merlin 50','Griffon 65','Merlin X','','answer2',0,0),(1440,'What is Pikachu&#039;s National Pok&amp;eacute;Dex Number?','#025','#001','#031','#109','','answer2',0,0),(1441,'What was the name of the cancelled sequel of Team Fortress?','Team Fortress 2: Return to Classic','Team Fortress 2: Operation Gear Grinder','Team Fortress 2: Desert Mercenaries','Team Fortress 2: Brotherhood of Arms','','answer5',0,0),(1442,'The surface area of Russia is slightly larger than that of the dwarf planet Pluto.','True','False','','','','answer2',0,0),(1443,'What position does Harry Potter play in Quidditch?','Seeker','Keeper','Beater','Chaser','','answer2',0,0),(1444,'The map featured in Arma 3 named &quot;Altis&quot; is based off of what Greek island?','Naxos','Ithaca','Lemnos','Anafi','','answer4',0,0),(1445,'Which Queen song was covered by Brittany Murphy in the 2006 film &quot;Happy Feet&quot;?','Bohemian Rhapsody','Flash','Under Pressure','Somebody to Love','','answer5',0,0),(1446,'How many classes are there in Team Fortress 2?','9','7','8','10','','answer2',0,0),(1447,'&quot;Windows NT&quot; is a monolithic kernel.','False','True','','','','answer2',0,0),(1448,'What is the primary addictive substance found in tobacco?','Nicotine','Glaucine','Cathinone','Ephedrine','','answer2',0,0),(1449,'What is the capital of the US state Nevada?','Carson City','Las Vegas','Reno','Henderson','','answer2',0,0),(1450,'Which of these artists did NOT remix the song &quot;Faded&quot; by Alan Walker?','Skrillex','Dash Berlin','Slushii','Ti&amp;euml;sto','','answer2',0,0),(1451,'In the 1994 movie &quot;Speed&quot;, what is the minimum speed the bus must go to prevent to bomb from exploding?','50 mph','40 mph','70 mph','60 mph','','answer2',0,0),(1452,'In &quot;Sonic Adventure&quot;, you are able to transform into Super Sonic at will after completing the main story.','False','True','','','','answer2',0,0),(1453,'Which musician has collaborated with American producer Porter Robinson and released the 2016 song &quot;Shelter&quot;?','Madeon','deadmau5','Zedd','Mat Zo','','answer2',0,0),(1454,'Who plays Alice in the Resident Evil movies?','Milla Johnson','Kim Demp','Madison Derpe','Milla Jovovich','','answer5',0,0),(1455,'When was Adolf Hitler appointed as Chancellor of Germany?','October 6, 1939','September 1, 1939','January 30, 1933','February 27, 1933','','answer4',0,0),(1456,'How many cores does the Intel i7-6950X have?','8','4','10','12','','answer4',0,0),(1457,'The PlayStation was originally a joint project between Sega and Sony that was a Sega Genesis with a disc drive.','False','True','','','','answer2',0,0),(1458,'In what Homestuck Update was [S] Game Over released?','October 25th, 2014','August 28th, 2003','April 13th, 2009','April 8th, 2012','','answer2',0,0),(1459,'Which of these is NOT a faction included in the game Counter-Strike: Global Offensive?','BOPE','Elite Crew','Phoenix Connexion','GSG-9','','answer2',0,0),(1460,'In &quot;Fallout 4&quot; which faction is not present in the game?','The Enclave','The Minutemen','The Institute','The Brotherhood of Steel','','answer2',0,0),(1461,'The main playable character of the 2015 RPG &quot;Undertale&quot; is a monster.','True','False','','','','answer3',0,0),(1462,'Sting, the lead vocalist of The Police, primarily plays what instrument?','Drums','Keyboards','Guitar','Bass Guitar','','answer5',0,0),(1463,'What is the most populous Muslim-majority nation in 2010?','Iran','Indonesia','Sudan','Saudi Arabia','','answer3',0,0),(1464,'Which one of these is not a typical European sword design?','Falchion','Scimitar','Flamberge','Ulfberht','','answer3',0,0),(1465,'In the game Dead by Daylight, the killer Michael Myers is refered to as &quot;The Shape&quot;.','False','True','','','','answer3',0,0),(1466,'In the Panic! At the Disco&#039;s song &quot;Nothern Downpour&quot;, which lyric follows &#039;I know the world&#039;s a broken bone&#039;.','&quot;So start a fire in their cold stone&quot;','&quot;So melt your headaches call it home&quot;','&quot;So let them know they\'re on their own&quot;','&quot;So sing your song until you\'re home&quot;','','',0,0),(1467,'What album did Bon Iver release in 2016?','22, A Million','For Emma, Forever Ago','Bon Iver, Bon Iver','Blood Bank EP','','answer2',0,0),(1468,'What is the oldest team in the NFL?','Chicago Bears','Green Bay Packers','New York Giants','Arizona Cardinals','','answer5',0,0),(1469,'In Undertale, how much do Spider Donuts cost in Hotland?','12G','9999G','7G','40G','','answer3',0,0),(1470,'What was the unofficial name for Germany between 1919 and 1933?','Federal Republic of Germany','Oesterreich ','Weimar Republic','German Democratic Republic','','answer4',0,0),(1471,'Who created the Cartoon Network series &quot;Regular Show&quot;?','Ben Bocquelet','Rebecca Sugar','J. G. Quintel','Pendleton Ward','','answer4',0,0),(1472,'Who created &quot;RWBY&quot;?','Miles Luna','Kerry Shawcross','Monty Oum','Shane Newville','','answer4',0,0),(1473,'Ikki Kurogane is known by what nickname at the beginning of &quot;Chivalry of a Failed Knight&quot;?','Worst One','Blazer','Another One','Princess','','answer2',0,0),(1474,'What does the term &quot;isolation&quot; refer to in microbiology?','Testing effects of certain microorganisms in an isolated enviroments, such as caves','The separation of a strain from a natural, mixed population of living microbes','A lack of nutrition in microenviroments','The nitrogen level in soil','','answer3',0,0),(1475,'Klingons express emotion in art through opera and poetry.','True','False','','','','answer2',0,0),(1476,'Which of these characters from &quot;SpongeBob SquarePants&quot; is not a squid?','Gary','Squidward','Squidette','Orvillie','','answer2',0,0),(1477,'In Topology, the complement of an open set is a closed set.','True','False','','','','answer2',0,0),(1478,'What year did the Chevrolet LUV mini truck make its debut?','1975','1972','1973','1982','','answer3',0,0),(1479,'The ghosts in &quot;Pac-Man&quot; and &quot;Ms. Pac-Man&quot; have completely different behavior.','False','True','','','','answer3',0,0),(1480,'Which of the following authors was not born in England? ','Graham Greene','H G Wells','Arthur Conan Doyle','Arthur C Clarke','','answer4',0,0),(1481,'Which one of these was not a beach landing site in the Invasion of Normandy?','Juno','Gold','Sword','Silver','','answer5',0,0),(1482,'Who wrote the novel &quot;Moby-Dick&quot;?','Herman Melville','J. R. R. Tolkien','William Golding','William Shakespeare','','answer2',0,0),(1483,'What was the title of ABBA`s first UK hit single?','Fernando','Waterloo','Dancing Queen','Mamma Mia','','answer3',0,0),(1484,'French is an official language in Canada.','False','True','','','','answer3',0,0),(1485,'In the Morse code, which letter is indicated by 3 dots? ','C','S','O','A','','answer3',0,0),(1486,'What did Alfred Hitchcock use as blood in the film &quot;Psycho&quot;? ','Ketchup','Maple syrup','Chocolate syrup','Red food coloring','','answer4',0,0),(1487,'What is &quot;dabbing&quot;?','A language','A dance','A medical procedure','A sport','','answer3',0,0),(1488,'Scottish producer Calvin Harris is from the town of Dumfries, Scotland.','False','True','','','','answer3',0,0),(1489,'When did Jamaica recieve its independence from England? ','1963','1962','1492','1987','','answer3',0,0),(1490,'What is the 15th letter of the Greek alphabet?','Omicron (&amp;Omicron;)','Nu (&amp;Nu;)','Pi (&amp;Pi;)','Sigma (&amp;Sigma;)','','answer2',0,0),(1491,'In &quot;Clash Royale&quot; what is Arena 4 called?','Spell Valley','P.E.K.K.A\'s Playhouse','Royal Arena','Barbarian Bowl','','',0,0),(1492,'Which of these countries remained neutral during World War II?','United Kingdom','France','Italy','Switzerland','','answer5',0,0),(1493,'In the anime series &quot;Full Metal Alchemist&quot;, what do Alchemists consider the greatest taboo?','Preforming Without A Permit','Human Transmutation ','Using Alchemy For Crime ','Transmuting Lead Into Gold','','answer3',0,0),(1494,'Which song by Swedish electronic musician Avicii samples the song &quot;Something&#039;s Got A Hold On Me&quot; by Etta James?','Seek Bromance','Fade Into Darkness','Levels','Silhouettes','','answer4',0,0),(1495,'Which of these &quot;Worms&quot; games featured 3D gameplay?','Worms 4: Mayhem','Worms Reloaded','Worms: Open Warfare 2','Worms W.M.D','','answer2',0,0),(1496,'Which modern day country is the region that was known as Phrygia in ancient times?','Turkey','Syria','Greece','Egypt','','answer2',0,0),(1497,'What Cartoon Network show aired its first episode on November 4th, 2013?','The Amazing World of Gumball','Steven Universe','Adventure Time','Regular Show','','answer3',0,0),(1498,'The 1939 movie &quot;The Wizard of Oz&quot; contained a horse that changed color, what material did they use to achieve this effect?','Paint','Dye','Gelatin','CGI Effect','','answer4',0,0),(1499,'Which car company is the only Japanese company which won the 24 Hours of Le Mans?','Nissan','Toyota','Mazda','Subaru','','answer4',0,0),(1500,'Who was the main antagonist of Max Payne 3 (2012)?','Milo Rego','Armando Becker','&amp;Aacute;lvaro Neves','Victor Branco','','answer5',0,0),(1501,'Who wrote the young adult novel &quot;The Fault in Our Stars&quot;?','Suzanne Collins','Stephenie Meyer','Stephen Chbosky','John Green','','answer5',0,0),(1502,'What name does the little headcrab in &quot;Half Life 2&quot; have?','Lamarr','Jerry','Drett','Jumperr','','answer2',0,0),(1503,'On Twitter, what is the character limit for a Tweet?','100','160','140','120','','answer4',0,0),(1504,'The starting pistol of the Terrorist team in a competitive match of Counter Strike: Global Offensive is what?','Dual Berretas','Tec-9','Desert Eagle','Glock-18','','answer5',0,0),(1505,'How many total monsters appear in Monster Hunter Generations?','105','100','98','73','','answer2',0,0),(1506,'In Monster Hunter Generations, which of these hunter arts are exclusive to the Longsword?','Unhinged Spirit','Shoryugeki','Provoke','Demon Riot','','answer2',0,0),(1507,'&quot;Cube&quot;, &quot;Cube 2: Hypercube&quot; and &quot;Cube Zero&quot; were directed by the same person.','True','False','','','','answer3',0,0),(1508,'What was Genghis Khan&#039;s real name?','Tem&amp;uuml;r','M&amp;ouml;ngke','&amp;Ouml;gedei','Tem&amp;uuml;jin','','answer5',0,0),(1509,'How many points did LeBron James score in his first NBA game?','19','69','41','25','','answer5',0,0),(1510,'Which Disney character sings the song &quot;A Dream is a Wish Your Heart Makes&quot;?','Snow White','Cinderella','Belle','Pocahontas','','answer3',0,0),(1511,'What is the name of rocky region that spans most of eastern Canada?','Appalachian Mountains','Himalayas','Rocky Mountains','Canadian Shield','','answer5',0,0),(1512,'What video game genre were the original Warcraft games?','RTS (Real Time Strategy)','MMO (Massively Multiplayer Online)','RPG (Role Playing Game)','TBS (Turn Based Strategy)','','answer2',0,0),(1513,'In the 2012 animated film &quot;Wolf Children&quot;, what are the names of the wolf children?','Hana &amp; Yuki','Ame &amp; Yuki','Chuck &amp; Anna','Ame &amp; Hana','','',0,0),(1514,'What was the original name of  &quot;Minecraft&quot;?','Minecraft: Order of the Stone','Dig And Build','Cave Game','Infiniminer','','answer4',0,0),(1515,'&quot;The Singing Cowboy&quot; Gene Autry is credited with the first recording for all but which classic Christmas jingle?','Rudolph the Red-Nosed Reindeer','White Christmas','Frosty the Snowman','Here Comes Santa Claus','','answer3',0,0),(1516,'Which of the following Copy Abilities was only featured in &quot;Kirby &amp; The Amazing Mirror&quot;?','Mini','Smash','Missile','Magic','','answer2',0,0),(1517,'Klingons respect William Shakespeare, they even suspect him having a Klingon lineage.','False','True','','','','answer3',0,0),(1518,'In &quot;Super Mario World&quot;, the rhino mini-boss, Reznor, is named after the lead singer of the band &quot;Nine Inch Nails&quot;.','True','False','','','','answer2',0,0),(1519,'Where does water from Poland Spring water bottles come from?','Maine, United States','Masovia, Poland','Hesse, Germany','Bavaria, Poland','','answer2',0,0),(1520,'In the webcomic &quot;Ava&#039;s Demon&quot;, what sin is &quot;Nevy Nervine&quot; based off of? ','Wrath ','Sloth','Lust','Envy ','','answer5',0,0),(1521,'When was the first Call of Duty title released?','July 18, 2004','October 29, 2003','November 14, 2002','December 1, 2003','','answer3',0,0),(1522,'Amazon acquired Twitch in August 2014 for $970 million dollars.','True','False','','','','answer2',0,0),(1523,'Which company did Valve cooperate with in the creation of the Vive?','HTC','Oculus','Razer','Google','','answer2',0,0),(1524,'What is the largest animal currently on Earth?','Orca','Colossal Squid','Blue Whale','Giraffe','','answer4',0,0),(1525,'Who played Deputy Marshal Samuel Gerard in the 1993 film &quot;The Fugitive&quot;?','Harrison Ford','Harvey Keitel','Tommy Lee Jones','Martin Landau','','answer4',0,0),(1526,'What is the largest city and commercial capital of Sri Lanka?','Moratuwa','Negombo','Colombo','Kandy','','answer4',0,0),(1527,'In the TV show &#039;M*A*S*H&#039;, what was the nickname of Corporal Walter O&#039;Reilly?','Radar','Trapper','Hot Lips','Hawkeye','','answer2',0,0),(1528,'What is the name of your team in Star Wars: Republic Commando?','Bravo Six','Vader\'s Fist','Delta Squad','The Commandos','','answer4',0,0),(1529,'Who played Agent Fox Mulder in the TV sci-fi drana &#039;The X-Files&#039;?','David Duchovny','Mitch Pileggi','Gillian Anderson','Robert Patrick','','answer2',0,0),(1530,'Which English guitarist has the nickname &quot;Slowhand&quot;?','Mark Knopfler','Jeff Beck','Jimmy Page','Eric Clapton','','answer5',0,0),(1531,'Soulja Boy&#039;s &#039;Crank That&#039; won a Grammy for Best Rap Song in 2007.','True','False','','','','answer3',0,0),(1532,'The computer OEM manufacturer Clevo, known for its Sager notebook line, is based in which country?','Taiwan','United States','China (People\'s Republic of)','Germany','','answer2',0,0),(1533,'Which Native American tribe/nation requires at least one half blood quantum (equivalent to one parent) to be eligible for membership?','Yomba Shoshone Tribe','Pawnee Nation of Oklahoma','Kiowa Tribe of Oklahoma','Standing Rock Sioux Tribe','','answer2',0,0),(1534,'The longest place named in the United States is Lake Chargoggagoggmanchauggagoggchaubunagungamaugg, located near Webster, MA.','False','True','','','','answer3',0,0),(1535,'Cucumbers are usually more than 90% water.','False','True','','','','answer3',0,0),(1536,'In the anime, &quot;Full Metal Panel&quot;, who is Kaname&#039;s best friend?','Kyoko Tokiwa','Ren Mikihara','Teletha &quot;Tessa&quot; Testarossa','Melissa Mao','','answer2',0,0),(1537,'The Swedish word &quot;Grunka&quot; means what in English?','Pineapple','People','Thing','Place','','answer4',0,0),(1538,'In Left 4 Dead, what is the name of the Special Infected that is unplayable in Versus mode?','The Witch','The Tank','The Smoker','The Spitter','','answer2',0,0),(1539,'In Portal 2, how did CEO of Aperture Science, Cave Johnson, presumably die?','Slipped in the shower','Asbestos Poisoning','Moon Rock Poisoning','Accidentally sending a portal to the Moon','','answer4',0,0),(1540,'What device allows Tracer to manipulate her own time in the game &quot;Overwatch&quot;?','TMD (Time Manipulation Device)','B.L.I.N.K','Chronal Accelerator','Spacial Displacement Manipulator','','answer4',0,0),(1541,'Which coding language was the #1 programming language in terms of usage on GitHub in 2015?','C#','JavaScript','PHP','Python','','answer3',0,0),(1542,'What was George Bizet&#039;s last opera?','Gris&amp;eacute;lidis','Les p&amp;ecirc;cheurs de perles','Carmen','Don Rodrigue','','answer4',0,0),(1543,'How many zombies need to be killed to get the &quot;Zombie Genocider&quot; achievement in Dead Rising (2006)?','53,593','53,595','53,594','53,596','','answer4',0,0),(1544,'In 1720, England was in massive debt, and became in involved in the South Sea Bubble. Who was the main mastermind behind it?','John Churchill','Daniel Defoe','Robert Harley','John Blunt','','answer5',0,0),(1545,'What happened to Half-Life 2 prior to its release, which resulted in Valve starting over the development of the game?','Way too many bugs to be fixed','They weren\'t satisfied with the result','The story was not good enough','The source code got leaked','','answer5',0,0),(1546,'What was the religion of famous singer &quot;Freddie Mercury&quot;?','Zoroastrianism','Paganism','Ashurism','Judaism','','answer2',0,0),(1547,'Which of these is a type of stretch/deep tendon reflex?','Scratch reflex','Gag reflex','Pupillary light reflex','Ankle jerk reflex','','answer5',0,0),(1548,'What video game sparked controversy because of its hidden &quot;Hot Coffee&quot; minigame?','Grand Theft Auto: San Andreas','Hitman: Blood Money','Cooking Mama','Grand Theft Auto: Vice City','','answer2',0,0),(1549,'St. Louis is the capital of the US State Missouri.','False','True','','','','answer2',0,0),(1550,'In Greek Mythology, who killed Achilles?','Helen','Paris','Hector','Pericles','','answer3',0,0),(1551,'Which Pokemon generation did the fan-named &quot;Masuda Method&quot; first appear in? ','X/Y','Black/White','Ruby/Sapphire','Diamond/Pearl','','answer5',0,0),(1552,'What was the name of the the first episode of Doctor Who to air in 1963?','The Aztecs','An Unearthly Child','The Daleks','The Edge of Destruction','','answer3',0,0),(1553,'Who voices the main character Blu in the 2011 animated film &quot;Rio&quot;?','Michael Cera','Jonah Hill','Jesse Eisenberg','Zach Galifianakis','','answer4',0,0),(1554,'Which of these Pok&amp;eacute;mon cannot learn Surf?','Arbok','Nidoking','Linoone','Tauros','','answer2',0,0),(1555,'&quot;Drink the Sea&quot; is an album by which electronic music artist?','Avicii','Flux Pavilion','The Glitch Mob','XXYYXX','','answer4',0,0),(1556,'According to the United States Constitution, how old must a person be to be elected President of the United States?','35','45','30','40','','answer2',0,0),(1557,'According to Greek Mythology, Atlas was an Olympian God.','True','False','','','','answer3',0,0),(1558,'What is the name of the formerly rich fishing grounds off the island of Newfoundland, Canada?','Great Barrier Reef','Grand Banks','Hudson Bay','Mariana Trench','','answer3',0,0),(1559,'What do the letters of the fast food chain KFC stand for?','Kentucky Fresh Cheese','Kentucky Fried Chicken','Kibbled Freaky Cow','Kiwi Food Cut','','answer3',0,0),(1560,'During development of &quot;Super Mario World&quot;, Yoshi&#039;s hard saddle was originally which of these?','A shell','A spike','A poffin','A slide of Gelatin','','answer2',0,0),(1561,'According to the Egyptian Myth of Osiris, who murdered Osiris?','Set','Ra','Anhur','Horus','','answer2',0,0),(1562,'In 1939, Britain and France declared war on Germany after it invaded which country?','Poland','Czechoslovakia','Hungary','Austria','','answer2',0,0),(1563,'British actor David Morrissey stars as which role in &quot;The Walking Dead&quot;?','The Governor','Rick Grimes','Negan','Daryl Dixon','','answer2',0,0),(1564,'Which Audi does not use Haldex based all wheel drive system?','Audi TT','Audi A8','Audi S3','Audi A3','','answer3',0,0),(1565,'In which location does Dark Sun Gwyndolin reside in &quot;Dark Souls&quot;?','Firelink Shrine','Anor Londo','Kiln of the first flame','Blighttown','','answer3',0,0),(1566,'AMC&#039;s &#039;The Walking Dead&#039;, Rick, Carl, Daryl, Morgan, Carol and Maggie were introduced to us in Season 1.','True','False','','','','answer3',0,0),(1567,'What European country is not a part of the EU?','Norway','Ireland','Lithuania','Czechia','','answer2',0,0),(1568,'What does a milliner make and sell?','Shoes','Hats','Belts','Shirts','','answer3',0,0),(1569,'How many people can you recruit in the game Suikoden in a single playthrough?','96','107','108','93','','answer3',0,0),(1570,'Who played Stan&#039;s dog in the South Park episode &quot;Big Gay Al&#039;s Big Gay Boat Ride&quot;?','George Clooney','Matt Stone','Robert Smith','Jay Leno','','answer2',0,0),(1571,'What was the name of the WWF professional wrestling tag team made up of the wrestlers Ax and Smash?','Demolition','The Dream Team','The British Bulldogs','The Bushwhackers','','answer2',0,0),(1572,'The moons, Miranda, Ariel, Umbriel, Titania and Oberon orbit which planet?','Jupiter','Venus','Uranus','Neptune','','answer4',0,0),(1573,'Arcade Fire&#039;s &#039;The Suburbs&#039; won the Album of the Year award in the 2011 Grammys.','True','False','','','','answer2',0,0),(1574,'A doctor with a PhD is a doctor of what?','Psychology','Phrenology','Philosophy','Physical Therapy','','answer4',0,0),(1575,'The Republic of Malta is the smallest microstate worldwide.','True','False','','','','answer3',0,0),(1576,'A stimpmeter measures the speed of a ball over what surface?',' Football Pitch','Pinball Table','Cricket Outfield','Golf Putting Green','','answer5',0,0),(1577,'By what name was the author Eric Blair better known?','Ernest Hemingway','Aldous Huxley','George Orwell','Ray Bradbury','','answer4',0,0),(1578,'Which of these levels does NOT appear in the console/PC versions of the game &quot;Sonic Generations&quot;?','Planet Wisp','City Escape','Mushroom Hill','Sky Sanctuary','','answer4',0,0),(1579,'Which of the following Assyrian kings did NOT rule during the Neo-Assyrian Empire?','Esharhaddon','Shamshi-Adad III','Ashur-nasir-pal II','Shalmaneser V','','answer3',0,0),(1580,'Which of these songs by Skrillex features Fatman Scoop as a side artist?','Recess','All is Fair in Love and Brostep','Rock N Roll (Will Take You to the Mountain)','Scary Monsters and Nice Sprites','','answer2',0,0),(1581,'In Portal 2, the iconic character GLaDOS is turned into:','An apple','A potato','A tomato','A lemon','','answer3',0,0),(1582,'How many members are there in the idol group &quot;&amp;micro;&#039;s&quot;?','3','48','9','6','','answer4',0,0),(1583,'What type of animal was Harambe, who was shot after a child fell into it&#039;s enclosure at the Cincinnati Zoo?','Tiger','Panda','Crocodile','Gorilla','','answer5',0,0),(1584,'What is the name of the final boss in Turok: Dinosaur Hunter?','Lord Tyrannus','Oblivion','The Campaigner','The Primagen','','answer4',0,0),(1585,'When was the United States National Security Agency established?',' July 1, 1973','July 26, 1908','November 4, 1952',' November 25, 2002','','answer4',0,0),(1586,'Who is the musical director for the award winning musical &quot;Hamilton&quot;?','Renee Elise-Goldberry','Lin-Manuel Miranda','Leslie Odom Jr.','Alex Lacamoire','','answer5',0,0),(1587,'Tennis was once known as Racquetball.','True','False','','','','answer3',0,0),(1588,'The 1952 musical composition 4&#039;33&quot;, composed by prolific American composer John Cage, is mainly comprised of what sound?','Farts','Silence','People talking','Cricket chirps','','answer3',0,0),(1589,'In &quot;Team Fortress 2&quot;, the &quot;Bill&#039;s Hat&quot; is a reference to the game &quot;Dota 2&quot;.','False','True','','','','answer2',0,0),(1590,'Which one of these rulers did not belong to the Habsburg dynasty?','Charles V','Francis Joseph','Philip II','Philip V','','answer5',0,0),(1591,'Pink Floyd made this song for their previous lead singer Syd Barrett.','Welcome to the Machine','Wish You Were Here','Have A Cigar','Shine On You Crazy Diamond','','answer5',0,0),(1592,'The average lifespan of a wildcat is only around 5-6 years. ','False','True','','','','answer2',0,0),(1593,'What is the name of the default theme that is installed with Windows XP?','Bliss','Neptune','Luna','Whistler','','answer4',0,0),(1594,'Which of these is NOT a possible drink to be made in the game &quot;VA-11 HALL-A: Cyberpunk Bartender Action&quot;?','Sour Appletini','Piano Man','Fringe Weaver','Bad Touch','','answer2',0,0),(1595,'Which one of these paintings is not by Caspar David Friedrich?','The Monk by the Sea','Wanderer above the Sea of Fog','The Black Sea','The Sea of Ice','','answer4',0,0),(1596,'Human cells typically have how many copies of each gene?','4','2','1','3','','answer3',0,0),(1597,'On the show &quot;Rick and Morty&quot;, in episode &quot;Total Rickall&quot;, who was a parasite?','Pencilvester','Summer Smith','Beth Smith','Mr. Poopy Butthole','','answer2',0,0),(1598,'When was Chapter 1 of the Source Engine mod &quot;Underhell&quot; released?','October 2nd, 2012','September 12th, 2013','March 3rd, 2011','September 1st, 2013','','answer5',0,0),(1599,'In Chemistry, how many isomers does Butanol (C4H9OH) have?','4','3','6','5','','answer2',0,0),(1600,'In programming, the ternary operator is mostly defined with what symbol(s)?','?','if then','?:','??','','answer4',0,0),(1601,'When you cry in space, your tears stick to your face.','False','True','','','','answer3',0,0),(1602,'In Monster Hunter Generations, which of the following monsters are a part of the &quot;Fated Four&quot;?','Malfestio','Arzuros','Gore Magala','Astalos','','answer5',0,0),(1603,'Who voices the infamous Citadel Station A.I known as S.H.O.D.A.N, in the System Shock games?','Jenn Taylor',' Jennifer Hale','Lori Alan','Terri Brosius','','answer5',0,0),(1604,'In the periodic table, Potassium&#039;s symbol is the letter K.','True','False','','','','answer2',0,0),(1605,'What is the capital of Seychelles?','Victoria','Tripoli','Luanda','N\'Djamena','','answer2',0,0),(1606,'Which historical conflict killed the most people?','World War II','Three Kingdoms War','Taiping Rebellion','Mongol conquests','','answer2',0,0),(1607,'Who stars in Brutal Legend?','Ozzy Osbourne','Kanye West','Lemmy','Jack Black','','answer5',0,0),(1608,'Which of the following is not a character in the Street Fighter series?','Laura Matsuda','Ibuki','Mai Shiranui','Sakura Kasugano','','answer4',0,0),(1609,'In &quot;Hunter x Hunter&quot;, which of the following is NOT a type of Nen aura?','Emission','Transmutation','Specialization','Restoration','','answer5',0,0),(1610,'Who wrote the &quot;A Song of Ice And Fire&quot; fantasy novel series?','George Eliot','George Lucas','George R. R. Martin','George Orwell','','answer4',0,0),(1611,'According to the BBPA, what is the most common pub name in the UK?','White Hart','Royal Oak','Red Lion','King\'s Head','','answer4',0,0),(1612,'What&#039;s the name of Batman&#039;s  parents?','Todd &amp; Mira','Jason &amp; Sarah','Thomas &amp; Martha','Joey &amp; Jackie','','',0,0),(1613,'What was the code name for the &quot;Nintendo Gamecube&quot;?','Nitro','Atlantis','Dolphin','Revolution','','answer4',0,0),(1614,'What was William Frederick Cody better known as?','Billy the Kid','Pawnee Bill','Buffalo Bill','Wild Bill Hickok','','answer4',0,0),(1615,'In the Batman comics, by what other name is the villain Dr. Jonathan Crane known?','Calendar Man','Clayface','Bane','Scarecrow','','answer5',0,0),(1616,'What is the first book of the Old Testament?','Leviticus','Exodus','Numbers','Genesis','','answer5',0,0),(1617,'Which of these island countries is located in the Caribbean?','Fiji','Maldives','Barbados','Seychelles','','answer4',0,0),(1618,'Butters Stotch, Pip Pirrup, and Wendy Testaburger are all characters in which long running animated TV series?','Family Guy','The Simpsons','Bob\'s Burgers','South Park','','answer5',0,0),(1619,'When did Spanish Peninsular War start?','1809','1810','1808','1806','','answer4',0,0),(1620,'Who directed the 1973 film &quot;American Graffiti&quot;?','Francis Ford Coppola','Ron Howard','Steven Spielberg','George Lucas','','answer5',0,0),(1621,'This movie contains the quote, &quot;What we&#039;ve got here is a failure to communicate.&quot;','Cool Hand Luke','In the Heat of the Night','The Graduate','Bonnie and Clyde','','answer2',0,0),(1622,'In the &quot;The Hobbit&quot;, who kills Smaug?','Gandalf the Grey','Bard','Frodo','Bilbo Baggins','','answer3',0,0),(1623,'What is the name of one of the Neo-Aramaic languages spoken by the Jewish population from Northwestern Iraq?','Lishan Didan','Lishana Deni','Hulaul&amp;aacute;','Chaldean Neo-Aramaic','','answer3',0,0),(1624,'Which of the following Inuit languages was the FIRST to use a unique writing system not based on the Latin alphabet?','Inupiat','Inuinnaqtun','Greenlandic','Inuktitut','','answer5',0,0),(1625,'What is a &quot;dakimakura&quot;?','A yoga posture','A body pillow','A word used to describe two people who truly love each other','A Chinese meal, essentially composed of fish','','answer3',0,0),(1626,'If you planted the seeds of Quercus robur what would grow?','Grains','Trees','Flowers','Vegtables','','answer3',0,0),(1627,'The word &quot;news&quot; originates from the first letters of the 4 main directions on a compass (North, East, West, South).','False','True','','','','answer2',0,0),(1628,'In what year was &quot;Metal Gear Solid&quot; released in North America?','1998','2001','1987','2004','','answer2',0,0),(1629,'Metal Gear Solid V: The Phantom Pain runs on the Fox Engine.','True','False','','','','answer2',0,0),(1630,'What is the last name of Edward and Alphonse in the Fullmetal Alchemist series.','Eliek','Elwood','Elric','Ellis','','answer4',0,0),(1631,'Which part of the body does glaucoma affect?','Eyes','Throat','Stomach','Blood','','answer2',0,0),(1632,'Denmark has a monarch.','True','False','','','','answer2',0,0),(1633,'What&#039;s the Team Fortress 2 Scout&#039;s city of origin?','Sydney','New York','Boston','Detroit','','answer4',0,0),(1634,'In World of Warcraft lore, who organized the creation of the Paladins?','Uther the Lightbringer','Sargeras, The Daemon Lord','Alonsus Faol','Alexandros Mograine','','answer4',0,0),(1635,'In &quot;Overwatch,&quot; an allied McCree will say &quot;Step right up&quot; upon using his ultimate ability Deadeye.','True','False','','','','answer2',0,0),(1636,'Shaquille O&#039;Neal appeared in the 1997 film &quot;Space Jam&quot;.','False','True','','','','answer2',0,0),(1637,'Sitting for more than three hours a day can cut two years off a person&#039;s life expectancy.','False','True','','','','answer3',0,0),(1638,'Who is the main protagonist in, the 1985 film, Back to the Future?','Emmett &quot;Doc&quot; Brown','George McFly','Marty McFly','Biff Tannen','','answer4',0,0),(1639,'Which Beatles album does NOT feature any of the band members on it&#039;s cover?','Magical Mystery Tour','Abbey Road','Rubber Soul','The Beatles (White Album)','','answer5',0,0),(1640,'What is the name of French electronic music producer Madeon&#039;s 2015 debut studio album?','The City','Icarus','Adventure','Pop Culture','','answer4',0,0),(1641,'Who is the leader of Team Instinct in Pok&amp;eacute;mon Go?','Spark','Candela','Willow','Blanche','','answer2',0,0),(1642,'Dee from &quot;It&#039;s Always Sunny in Philadelphia&quot; has dated all of the following guys EXCEPT','Ben the Soldier','Colin the Thief','Kevin Gallagher aka Lil\' Kevin','Matthew &quot;Rickety Cricket&quot; Mara','','',0,0),(1643,'The capital of the US State Ohio is the city of Chillicothe.','False','True','','','','answer2',0,0),(1644,'The Platypus is a mammal.','False','True','','','','answer3',0,0),(1645,'&quot;Cube&quot;, &quot;Cube 2: Hypercube&quot; and &quot;Cube Zero&quot; were directed by the same person.','True','False','','','','answer3',0,0),(1646,'In Scandinavian languages, the letter &amp;Aring; means river.','True','False','','','','answer2',0,0),(1647,'In the game &quot;Terraria&quot;, which of these bosses are pre-hardmode bosses?','Skeletron Prime','Eye of Cthulhu','Plantera','The Destroyer','','answer3',0,0),(1648,'Which of the following is not a faction in Tom Clancy&#039;s The Division?','Last Man Batallion','Cleaners','CDC','Rikers','','answer4',0,0),(1649,'What is the name of the largest planet in Kerbal Space Program?','Jool','Minmus','Eeloo','Kerbol','','answer2',0,0),(1650,'Nickelodeon rejected the pilot to Adventure Time.','True','False','','','','answer2',0,0),(1651,'Which of these is not a key value of Agile software development?','Comprehensive documentation','Individuals and interactions','Customer collaboration','Responding to change','','answer2',0,0),(1652,'Kiznaiver is an adaptation of a manga.','True','False','','','','answer3',0,0),(1653,'Which person from &quot;JoJo&#039;s Bizarre Adventure&quot; does NOT house a reference to a band, artist, or song earlier than 1980?','Giorno Giovanna','Johnny Joestar','Josuke Higashikata','Jolyne Cujoh','','answer2',0,0),(1654,'Who wrote the &quot;A Song of Ice And Fire&quot; fantasy novel series?','George Lucas','George Orwell','George R. R. Martin','George Eliot','','answer4',0,0),(1655,'Which author and poet famously wrote the line, &quot;The female of the species is more deadly than the male&quot;?','Rudyard Kipling','William Wordsworth','William Shakespeare','Edgar Allan Poe','','answer2',0,0),(1656,'Who was the British Prime Minister at the outbreak of the Second World War?','Clement Attlee','Neville Chamberlain','Stanley Baldwin','Winston Churchill','','answer3',0,0),(1657,'Which element has the chemical symbol &#039;Fe&#039;?','Iron','Tin','Silver','Gold','','answer2',0,0),(1658,'Which internet company began life as an online bookstore called &#039;Cadabra&#039;?','Shopify','Amazon','Overstock','eBay','','answer3',0,0),(1659,'What is the capital of Peru?','Lima','Santiago','Buenos Aires','Montevideo','','answer2',0,0),(1660,'The film &quot;2001: A Space Odyssey&quot; was released on December 31st, 2000.','False','True','','','','answer2',0,0),(1661,'What are the four corner states of the US?','Oregon, Idaho, Nevada, Utah','Kansas, Oklahoma, Arkansas, Louisiana','South Dakota, Minnesota, Nebraska, Iowa','Utah, Colorado, Arizona, New Mexico','','answer5',0,0),(1662,'All the following metal elements are liquids at or near room temperature EXCEPT:','Beryllium','Caesium','Gallium','Mercury','','answer2',0,0),(1663,'Which of the following Ivy League universities has its official motto in Hebrew as well as in Latin?','Columbia University','Princeton University','Harvard University','Yale University','','answer5',0,0),(1664,'Which of these Bojack Horseman characters is a human?','Tom Jumbo-Grumbo','Todd Chavez','Princess Carolyn','Lennie Turtletaub','','answer3',0,0),(1665,'What ingredient is NOT used to craft a cake in Minecraft?','Egg','Bread','Wheat','Milk','','answer3',0,0),(1666,'Which film star has his statue in Leicester Square?','Rowan Atkinson ','Charlie Chaplin','Paul Newman','Alfred Hitchcock','','answer3',0,0),(1667,'What is the code name for the mobile operating system Android 7.0?','Ice Cream Sandwich','Marshmallow','Nougat','Jelly Bean','','answer4',0,0),(1668,'What is the title of song on the main menu in Halo?','Halo','Shadows','Suite Autumn','Opening Suite','','answer2',0,0),(1669,'What does the acronym CDN stand for in terms of networking?','Content Distribution Network','Computational Data Network','Compressed Data Network','Content Delivery Network','','answer5',0,0),(1670,'Which of these games includes the phrase &quot;Do not pass Go, do not collect $200&quot;?','Monopoly','Pay Day','Cluedo','Coppit','','answer2',0,0),(1671,'What year did the game &quot;Overwatch&quot; enter closed beta?','2016','2015','2013','2011','','answer3',0,0),(1672,'What was Rage Against the Machine&#039;s debut album?','Rage Against the Machine','Evil Empire','Bombtrack','The Battle Of Los Angeles','','answer2',0,0),(1673,'The 2005 video game &quot;Call of Duty 2: Big Red One&quot; is not available on PC.','False','True','','','','answer3',0,0),(1674,'The television show Doctor Who first aired in 1963.','True','False','','','','answer2',0,0),(1675,'In Alfred Hitchcock&#039;s film &#039;Psycho&#039; it is said he used chocolate syrup to simulate the blood in the famous shower scene from ','True','False','','','','answer2',0,0),(1676,'Dee from &quot;It&#039;s Always Sunny in Philadelphia&quot; has dated all of the following guys EXCEPT','Colin the Thief','Ben the Soldier','Matthew &quot;Rickety Cricket&quot; Mara','Kevin Gallagher aka Lil\' Kevin','','',0,0),(1677,'In &quot;Hexadecimal&quot;, what color would be displayed from the color code? &quot;#00FF00&quot;?','Yellow','Blue','Green','Red','','answer4',0,0),(1678,'Adolf Hitler was born in Australia. ','True','False','','','','answer3',0,0),(1679,'In the National Pokedex what number is Porygon-Z?','376','474','432','589','','answer3',0,0),(1680,'What was the world&#039;s first handheld game device?','Mattel Auto Race','Game &amp; Watch','Microvision','Game Boy','','answer2',0,0),(1681,'What is the maximum HP in Terraria?','500','400','1000','100','','answer2',0,0),(1682,'The book &quot;The Little Prince&quot; was written by...','Jane Austen','Miguel de Cervantes Saavedra','Antoine de Saint-Exup&amp;eacute;ry','F. Scott Fitzgerald','','answer4',0,0),(1683,'How many regular Sunken Sea Scrolls are there in &quot;Splatoon&quot;?','30','27','32','5','','answer3',0,0),(1684,'Which of these Disney shows is classified as an anime?','Cory in the House','The Emperor\'s New School','Stitch!','Hannah Montana','','answer4',0,0),(1685,'How many sides does a heptagon have?','8','6','5','7','','answer5',0,0),(1686,'Which of the following Copy Abilities was only featured in &quot;Kirby &amp; The Amazing Mirror&quot;?','Missile','Magic','Smash','Mini','','answer5',0,0),(1687,'In &quot;One Piece&quot;, which one of the following is NOT an Ancient Weapon?','Jupiter','Uranus','Poseidon','Pluton','','answer2',0,0),(1688,'What was the first Android version specifically optimized for tablets?','Froyo','Marshmellow','Eclair','Honeycomb','','answer5',0,0),(1689,'In &quot;Super Mario World&quot;, the rhino mini-boss, Reznor, is named after the lead singer of the band &quot;Nine Inch Nails&quot;.','False','True','','','','answer3',0,0),(1690,'In the &quot;Sailor Moon&quot; series, what is Sailor Jupiter&#039;s civilian name?','Usagi Tsukino','Makoto Kino','Rei Hino','Minako Aino','','answer3',0,0),(1691,'Which game in the &quot;Monster Hunter&quot; series introduced the &quot;Insect Glaive&quot; weapon?','Monster Hunter 4','Monster Hunter Freedom','Monster Hunter Stories','Monster Hunter 2','','answer2',0,0),(1692,'Which species is a &quot;mountain chicken&quot;?','Frog','Horse','Chicken','Fly','','answer2',0,0);
/*!40000 ALTER TABLE `triviaq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triviasettings`
--

DROP TABLE IF EXISTS `triviasettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triviasettings` (
  `gamenum` int(16) NOT NULL AUTO_INCREMENT,
  `started` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `finished` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gameon` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`gamenum`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triviasettings`
--

LOCK TABLES `triviasettings` WRITE;
/*!40000 ALTER TABLE `triviasettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `triviasettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triviausers`
--

DROP TABLE IF EXISTS `triviausers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triviausers` (
  `userid` int(11) NOT NULL,
  `qid` int(11) NOT NULL,
  `correct` tinyint(1) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triviausers`
--

LOCK TABLES `triviausers` WRITE;
/*!40000 ALTER TABLE `triviausers` DISABLE KEYS */;
/*!40000 ALTER TABLE `triviausers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uploadapp`
--

DROP TABLE IF EXISTS `uploadapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uploadapp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `applied` int(11) NOT NULL DEFAULT '0',
  `speed` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer` longtext COLLATE utf8mb4_unicode_ci,
  `reason` longtext COLLATE utf8mb4_unicode_ci,
  `sites` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `sitenames` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scene` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `creating` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `seeding` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `connectable` enum('yes','no','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `status` enum('accepted','rejected','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `moderator` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users` (`userid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploadapp`
--

LOCK TABLES `uploadapp` WRITE;
/*!40000 ALTER TABLE `uploadapp` DISABLE KEYS */;
/*!40000 ALTER TABLE `uploadapp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_blocks`
--

DROP TABLE IF EXISTS `user_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_blocks` (
  `userid` int(10) unsigned NOT NULL,
  `index_page` int(10) unsigned NOT NULL DEFAULT '585727',
  `global_stdhead` int(10) unsigned NOT NULL DEFAULT '1023',
  `userdetails_page` bigint(20) unsigned NOT NULL DEFAULT '4294967295',
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_blocks`
--

LOCK TABLES `user_blocks` WRITE;
/*!40000 ALTER TABLE `user_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usercomments`
--

DROP TABLE IF EXISTS `usercomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usercomments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  `text` mediumtext COLLATE utf8mb4_unicode_ci,
  `ori_text` mediumtext COLLATE utf8mb4_unicode_ci,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usercomments`
--

LOCK TABLES `usercomments` WRITE;
/*!40000 ALTER TABLE `usercomments` DISABLE KEYS */;
/*!40000 ALTER TABLE `usercomments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userhits`
--

DROP TABLE IF EXISTS `userhits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userhits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `hitid` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `added` (`added`),
  KEY `hitid` (`hitid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userhits`
--

LOCK TABLES `userhits` WRITE;
/*!40000 ALTER TABLE `userhits` DISABLE KEYS */;
/*!40000 ALTER TABLE `userhits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passhash` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `torrent_pass` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `added` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_access` int(11) NOT NULL DEFAULT '0',
  `curr_ann_last_check` int(10) unsigned NOT NULL DEFAULT '0',
  `curr_ann_id` int(10) unsigned NOT NULL DEFAULT '0',
  `editsecret` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `privacy` enum('strong','normal','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `stylesheet` int(10) NOT NULL DEFAULT '1',
  `info` mediumtext COLLATE utf8mb4_unicode_ci,
  `acceptpms` enum('yes','friends','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `override_class` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `language` int(11) NOT NULL DEFAULT '1',
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `av_w` smallint(3) unsigned NOT NULL DEFAULT '0',
  `av_h` smallint(3) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` int(10) unsigned NOT NULL DEFAULT '0',
  `notifs` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modcomment` mediumtext COLLATE utf8mb4_unicode_ci,
  `enabled` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `donor` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `warned` int(11) NOT NULL DEFAULT '0',
  `torrentsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `deletepms` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `savepms` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `reputation` int(10) NOT NULL DEFAULT '10',
  `time_offset` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dst_in_use` tinyint(1) NOT NULL DEFAULT '0',
  `auto_correct_dst` tinyint(1) NOT NULL DEFAULT '1',
  `show_shout` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `shoutboxbg` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `chatpost` int(11) NOT NULL DEFAULT '1',
  `smile_until` int(10) NOT NULL DEFAULT '0',
  `seedbonus` decimal(10,1) NOT NULL DEFAULT '200.0',
  `bonuscomment` mediumtext COLLATE utf8mb4_unicode_ci,
  `vip_added` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `vip_until` int(10) NOT NULL DEFAULT '0',
  `freeslots` int(11) unsigned NOT NULL DEFAULT '5',
  `free_switch` int(11) unsigned NOT NULL DEFAULT '0',
  `invites` int(10) unsigned NOT NULL DEFAULT '1',
  `invitedby` int(10) unsigned NOT NULL DEFAULT '0',
  `invite_rights` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `anonymous` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `uploadpos` int(11) NOT NULL DEFAULT '1',
  `forumpost` int(11) NOT NULL DEFAULT '1',
  `downloadpos` int(11) NOT NULL DEFAULT '1',
  `immunity` int(11) NOT NULL DEFAULT '0',
  `leechwarn` int(11) NOT NULL DEFAULT '0',
  `disable_reason` mediumtext COLLATE utf8mb4_unicode_ci,
  `clear_new_tag_manually` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `last_browse` int(11) NOT NULL DEFAULT '0',
  `sig_w` smallint(3) unsigned NOT NULL DEFAULT '0',
  `sig_h` smallint(3) unsigned NOT NULL DEFAULT '0',
  `signatures` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `signature` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forum_access` int(11) NOT NULL DEFAULT '0',
  `forum_sort` enum('ASC','DESC') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DESC',
  `highspeed` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `hnrwarn` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `hit_and_run_total` int(9) DEFAULT '0',
  `donoruntil` int(11) unsigned NOT NULL DEFAULT '0',
  `donated` int(3) NOT NULL DEFAULT '0',
  `total_donated` decimal(8,2) NOT NULL DEFAULT '0.00',
  `vipclass_before` int(10) NOT NULL DEFAULT '0',
  `parked` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `passhint` int(10) unsigned NOT NULL,
  `hintanswer` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatarpos` int(11) NOT NULL DEFAULT '1',
  `support` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `supportfor` mediumtext COLLATE utf8mb4_unicode_ci,
  `language_new` int(11) NOT NULL DEFAULT '1',
  `sendpmpos` int(11) NOT NULL DEFAULT '1',
  `invitedate` int(11) NOT NULL DEFAULT '0',
  `invitees` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invite_on` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `subscription_pm` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `gender` enum('Male','Female','NA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NA',
  `anonymous_until` int(10) NOT NULL DEFAULT '0',
  `viewscloud` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `tenpercent` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `avatars` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `offavatar` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `pirate` int(11) unsigned NOT NULL DEFAULT '0',
  `king` int(11) unsigned NOT NULL DEFAULT '0',
  `hidecur` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ssluse` int(1) NOT NULL DEFAULT '1',
  `signature_post` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `forum_post` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `avatar_rights` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `offensive_avatar` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `view_offensive_avatar` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `paranoia` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `google_talk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aim` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yahoo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icq` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_email` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `parked_until` int(10) NOT NULL DEFAULT '0',
  `gotgift` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `hash1` varchar(96) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspended` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `bjwins` int(10) NOT NULL DEFAULT '0',
  `bjlosses` int(10) NOT NULL DEFAULT '0',
  `warn_reason` mediumtext COLLATE utf8mb4_unicode_ci,
  `onirc` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `irctotal` bigint(20) unsigned NOT NULL DEFAULT '0',
  `birthday` date DEFAULT '0000-00-00',
  `got_blocks` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `last_access_numb` bigint(30) NOT NULL DEFAULT '0',
  `onlinetime` bigint(30) NOT NULL DEFAULT '0',
  `pm_on_delete` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `commentpm` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `split` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `browser` mediumtext COLLATE utf8mb4_unicode_ci,
  `hits` int(10) NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `categorie_icon` int(10) DEFAULT '1',
  `perms` int(11) NOT NULL DEFAULT '0',
  `mood` int(10) NOT NULL DEFAULT '1',
  `got_moods` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `pms_per_page` tinyint(3) unsigned DEFAULT '20',
  `show_pm_avatar` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `watched_user` int(11) NOT NULL DEFAULT '0',
  `watched_user_reason` mediumtext COLLATE utf8mb4_unicode_ci,
  `staff_notes` mediumtext COLLATE utf8mb4_unicode_ci,
  `game_access` int(11) NOT NULL DEFAULT '1',
  `where_is` mediumtext COLLATE utf8mb4_unicode_ci,
  `show_staffshout` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `request_uri` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browse_icons` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usersachiev`
--

DROP TABLE IF EXISTS `usersachiev`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usersachiev` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `totalshoutlvl` tinyint(2) NOT NULL DEFAULT '0',
  `username` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usersachiev`
--

LOCK TABLES `usersachiev` WRITE;
/*!40000 ALTER TABLE `usersachiev` DISABLE KEYS */;
/*!40000 ALTER TABLE `usersachiev` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ustatus`
--

DROP TABLE IF EXISTS `ustatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ustatus` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `last_status` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_update` int(11) NOT NULL DEFAULT '0',
  `archive` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ustatus`
--

LOCK TABLES `ustatus` WRITE;
/*!40000 ALTER TABLE `ustatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `ustatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wiki`
--

DROP TABLE IF EXISTS `wiki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wiki` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `userid` int(10) unsigned DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `lastedit` int(10) unsigned DEFAULT '0',
  `lastedituser` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wiki`
--

LOCK TABLES `wiki` WRITE;
/*!40000 ALTER TABLE `wiki` DISABLE KEYS */;
INSERT INTO `wiki` VALUES (1,'index','[align=center][size=6]Welcome to the [b]Wiki[/b][/size][/align]',0,1228076412,1281610709,1);
/*!40000 ALTER TABLE `wiki` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_announce_log`
--

DROP TABLE IF EXISTS `xbt_announce_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_announce_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(10) unsigned NOT NULL,
  `port` int(11) NOT NULL DEFAULT '0',
  `event` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob NOT NULL,
  `peer_id` blob NOT NULL,
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `left0` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `useragent` varchar(51) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_announce_log`
--

LOCK TABLES `xbt_announce_log` WRITE;
/*!40000 ALTER TABLE `xbt_announce_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_announce_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_client_whitelist`
--

DROP TABLE IF EXISTS `xbt_client_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_client_whitelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vstring` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_id` (`peer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_client_whitelist`
--

LOCK TABLES `xbt_client_whitelist` WRITE;
/*!40000 ALTER TABLE `xbt_client_whitelist` DISABLE KEYS */;
INSERT INTO `xbt_client_whitelist` VALUES (1,'-','all');
/*!40000 ALTER TABLE `xbt_client_whitelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_config`
--

DROP TABLE IF EXISTS `xbt_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_config` (
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_config`
--

LOCK TABLES `xbt_config` WRITE;
/*!40000 ALTER TABLE `xbt_config` DISABLE KEYS */;
INSERT INTO `xbt_config` VALUES ('torrent_pass_private_key','MG58LNj5LHHz49A9PKhAkxIH8Aa');
/*!40000 ALTER TABLE `xbt_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_deny_from_hosts`
--

DROP TABLE IF EXISTS `xbt_deny_from_hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_deny_from_hosts` (
  `begin` int(11) NOT NULL DEFAULT '0',
  `end` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_deny_from_hosts`
--

LOCK TABLES `xbt_deny_from_hosts` WRITE;
/*!40000 ALTER TABLE `xbt_deny_from_hosts` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_deny_from_hosts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_files`
--

DROP TABLE IF EXISTS `xbt_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_files` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_files`
--

LOCK TABLES `xbt_files` WRITE;
/*!40000 ALTER TABLE `xbt_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_files_users`
--

DROP TABLE IF EXISTS `xbt_files_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_files_users` (
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
  `peer_id` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completedtime` int(11) unsigned NOT NULL DEFAULT '0',
  `ipa` int(11) unsigned NOT NULL DEFAULT '0',
  `connectable` tinyint(4) NOT NULL DEFAULT '1',
  `mark_of_cain` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `hit_and_run` int(11) NOT NULL DEFAULT '0',
  `started` int(11) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `fid` (`fid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_files_users`
--

LOCK TABLES `xbt_files_users` WRITE;
/*!40000 ALTER TABLE `xbt_files_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_files_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_scrape_log`
--

DROP TABLE IF EXISTS `xbt_scrape_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_scrape_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_scrape_log`
--

LOCK TABLES `xbt_scrape_log` WRITE;
/*!40000 ALTER TABLE `xbt_scrape_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_scrape_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xbt_users`
--

DROP TABLE IF EXISTS `xbt_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xbt_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `wait_time` int(11) NOT NULL DEFAULT '0',
  `peers_limit` int(11) NOT NULL DEFAULT '0',
  `torrents_limit` int(11) NOT NULL DEFAULT '0',
  `torrent_pass` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `torrent_pass_version` int(11) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xbt_users`
--

LOCK TABLES `xbt_users` WRITE;
/*!40000 ALTER TABLE `xbt_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `xbt_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50112 SET @disable_bulk_load = IF (@is_rocksdb_supported, 'SET SESSION rocksdb_bulk_load = @old_rocksdb_bulk_load', 'SET @dummy_rocksdb_bulk_load = 0') */;
/*!50112 PREPARE s FROM @disable_bulk_load */;
/*!50112 EXECUTE s */;
/*!50112 DEALLOCATE PREPARE s */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-07-22 13:23:07
