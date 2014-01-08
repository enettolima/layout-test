-- MySQL dump 10.13  Distrib 5.5.29, for osx10.8 (i386)
--
-- Host: localhost    Database: ebt_dev
-- ------------------------------------------------------
-- Server version	5.5.29

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

--
-- Table structure for table `associate`
--

DROP TABLE IF EXISTS `associate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `associate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `empcode` varchar(5) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `associate`
--

LOCK TABLES `associate` WRITE;
/*!40000 ALTER TABLE `associate` DISABLE KEYS */;
INSERT INTO `associate` (`id`, `empcode`, `fname`, `lname`) VALUES (1,'301CD','Chad','Davis');
INSERT INTO `associate` (`id`, `empcode`, `fname`, `lname`) VALUES (2,'301MF','Marcelo','Fleitas');
/*!40000 ALTER TABLE `associate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_day_meta`
--

DROP TABLE IF EXISTS `schedule_day_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_day_meta` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storeId` (`store_id`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_day_meta`
--

LOCK TABLES `schedule_day_meta` WRITE;
/*!40000 ALTER TABLE `schedule_day_meta` DISABLE KEYS */;
INSERT INTO `schedule_day_meta` (`id`, `store_id`, `date`, `data`) VALUES (1,301,'2013-11-05','{\"sequence\":[\"003FOO\",\"023BAR\",\"004FOO\",\"019BAR\",\"j\",\"002FOO\"]}');
/*!40000 ALTER TABLE `schedule_day_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduled_inout`
--

DROP TABLE IF EXISTS `scheduled_inout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduled_inout` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `associate_id` varchar(6) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_id` (`associate_id`,`store_id`,`date_in`,`date_out`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_inout`
--

LOCK TABLES `scheduled_inout` WRITE;
/*!40000 ALTER TABLE `scheduled_inout` DISABLE KEYS */;
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (12,'002FOO',301,'2013-11-05 08:30:00','2013-11-05 12:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (1,'003FOO',301,'2013-11-05 07:00:00','2013-11-05 08:30:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (2,'003FOO',301,'2013-11-05 12:00:00','2013-11-05 18:30:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (7,'004FOO',301,'2013-11-05 12:00:00','2013-11-05 14:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (8,'019BAR',301,'2013-11-05 07:00:00','2013-11-05 08:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (9,'019BAR',301,'2013-11-05 09:00:00','2013-11-05 11:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (10,'019BAR',301,'2013-11-05 12:00:00','2013-11-05 14:30:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (11,'019BAR',301,'2013-11-05 15:30:00','2013-11-05 18:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (3,'023BAR',301,'2013-11-05 08:00:00','2013-11-05 09:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (4,'023BAR',301,'2013-11-05 10:00:00','2013-11-05 11:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (5,'023BAR',301,'2013-11-05 12:00:00','2013-11-05 13:00:00');
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (6,'023BAR',301,'2013-11-05 13:30:00','2013-11-05 15:00:00');
/*!40000 ALTER TABLE `scheduled_inout` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-12-19 16:32:52
