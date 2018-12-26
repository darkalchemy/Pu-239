-- MySQL dump 10.13  Distrib 5.7.24-27, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: master
-- ------------------------------------------------------
-- Server version	5.7.24-27

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
/*!50717 SELECT COUNT(*) INTO @rocksdb_has_p_s_session_variables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'performance_schema' AND TABLE_NAME = 'session_variables' */;
/*!50717 SET @rocksdb_get_is_supported = IF (@rocksdb_has_p_s_session_variables, 'SELECT COUNT(*) INTO @rocksdb_is_supported FROM performance_schema.session_variables WHERE VARIABLE_NAME=\'rocksdb_bulk_load\'', 'SELECT 0') */;
/*!50717 PREPARE s FROM @rocksdb_get_is_supported */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;
/*!50717 SET @rocksdb_enable_bulk_load = IF (@rocksdb_is_supported, 'SET SESSION rocksdb_bulk_load = 1', 'SET @rocksdb_dummy_bulk_load = 0') */;
/*!50717 PREPARE s FROM @rocksdb_enable_bulk_load */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;

--
-- Dumping data for table `imdb_info`
--

LOCK TABLES `imdb_info` WRITE;
/*!40000 ALTER TABLE `imdb_info` DISABLE KEYS */;
INSERT  IGNORE INTO `imdb_info` VALUES ('1650060','A troubled Iraq War veteran struggling to reintegrate into society sets out on a cross-country journey with the hope of reuniting with his young son.',0,0),('1987680','A comedic look at the relationship between a wealthy man with quadriplegia and an unemployed man with a criminal record who\'s hired to help him.',0,0),('3737840','Edward Forester\'s career and personal life have hit rock bottom-but not his spirit. At 64, the handsome bachelor moves in to a tiny apartment and must start over again.',0,0),('4154916','A scientist becomes obsessed with bringing back his family members who died in a traffic accident.',0,0),('4426464','Swifty the Arctic fox works in the mail room of the Arctic Blast Delivery Service but dreams of one day becoming a Top Dog (the Arctic\'s star husky couriers). To prove himself worthy of the...',0,0),('5586052','While Joseph Goebbels infamously declared Berlin \"free of Jews\" in 1943, 1,700 managed to survive in the Nazi capital through the end of the WWII. The Invisibles traces the stories of four young people who learned to hide in plain sight.',0,0),('5715828','A young writer tries to obtain romance letters a poet sent to his mistress.',0,0),('5886046','Six strangers find themselves in circumstances beyond their control, and must use their wits to survive.',0,0),('6022946','A young couple\'s decision to get engaged threatens to break them apart.',0,0),('6101820','Seven friends gather for dinner and decide to play a game in which all incoming messages and calls will be on display for the entire group, leading to a series of revelations that gradually unravels their \'normal\' lives.',0,0),('6476140','The mysterious past of a fishing boat captain comes back to haunt him, when his ex-wife tracks him down with a desperate plea for help, ensnaring his life in a new reality that may not be all that it seems.',0,0),('6811018','A band of kids embark on an epic quest to thwart a medieval menace.',0,0),('6823368','Security guard David Dunn uses his supernatural abilities to track Kevin Wendell Crumb, a disturbed man who has twenty-four personalities.',0,0),('7616798','A dog travels 400 miles in search of her owner.',0,0),('7875464','Chela and Chiquita are both descended from wealthy families in Asunción and have been together for over 30 years. But recently, their financial situation has worsened and they begin selling...',0,0),('9109306','After the death of his father, Aaron returns home to help his grief-stricken mother and to confront his past. Going through his dad\'s belongings, he comes across a mysterious item that is more than it seems.',0,0);
/*!40000 ALTER TABLE `imdb_info` ENABLE KEYS */;
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

-- Dump completed on 2018-12-26 12:04:49
