-- MySQL dump 10.13  Distrib 5.5.34, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ebt_dev
-- ------------------------------------------------------
-- Server version	5.5.34-0ubuntu0.12.04.1

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `associate`
--

LOCK TABLES `associate` WRITE;
/*!40000 ALTER TABLE `associate` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_day_meta`
--

LOCK TABLES `schedule_day_meta` WRITE;
/*!40000 ALTER TABLE `schedule_day_meta` DISABLE KEYS */;
INSERT INTO `schedule_day_meta` VALUES (43,301,'2014-01-19','{\"sequence\":[\"000FOO\",\"001FOO\",\"002FOO\",\"003FOO\",\"004FOO\"]}');
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
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_inout`
--

LOCK TABLES `scheduled_inout` WRITE;
/*!40000 ALTER TABLE `scheduled_inout` DISABLE KEYS */;
INSERT INTO `scheduled_inout` VALUES (154,'000FOO',301,'2014-01-19 07:30:00','2014-01-19 12:30:00'),(159,'000FOO',301,'2014-01-20 09:00:00','2014-01-20 12:00:00'),(164,'000FOO',301,'2014-01-21 08:00:00','2014-01-21 13:00:00'),(169,'000FOO',301,'2014-01-22 07:30:00','2014-01-22 15:00:00'),(174,'000FOO',301,'2014-01-23 08:30:00','2014-01-23 13:00:00'),(179,'000FOO',301,'2014-01-24 08:30:00','2014-01-24 12:30:00'),(184,'000FOO',301,'2014-01-25 07:30:00','2014-01-25 12:30:00'),(155,'001FOO',301,'2014-01-19 09:30:00','2014-01-19 14:30:00'),(160,'001FOO',301,'2014-01-20 09:00:00','2014-01-20 15:30:00'),(165,'001FOO',301,'2014-01-21 11:30:00','2014-01-21 16:30:00'),(170,'001FOO',301,'2014-01-22 10:30:00','2014-01-22 17:30:00'),(175,'001FOO',301,'2014-01-23 12:00:00','2014-01-23 17:00:00'),(180,'001FOO',301,'2014-01-24 10:30:00','2014-01-24 15:00:00'),(185,'001FOO',301,'2014-01-25 11:00:00','2014-01-25 15:30:00'),(156,'002FOO',301,'2014-01-19 11:30:00','2014-01-19 16:00:00'),(161,'002FOO',301,'2014-01-20 07:00:00','2014-01-20 12:30:00'),(166,'002FOO',301,'2014-01-21 09:30:00','2014-01-21 14:30:00'),(171,'002FOO',301,'2014-01-22 08:30:00','2014-01-22 14:30:00'),(176,'002FOO',301,'2014-01-23 08:00:00','2014-01-23 14:30:00'),(181,'002FOO',301,'2014-01-24 08:30:00','2014-01-24 13:00:00'),(186,'002FOO',301,'2014-01-25 12:00:00','2014-01-25 15:30:00'),(157,'003FOO',301,'2014-01-19 12:30:00','2014-01-19 17:30:00'),(162,'003FOO',301,'2014-01-20 11:00:00','2014-01-20 17:00:00'),(167,'003FOO',301,'2014-01-21 13:30:00','2014-01-21 19:00:00'),(172,'003FOO',301,'2014-01-22 10:00:00','2014-01-22 17:30:00'),(177,'003FOO',301,'2014-01-23 10:00:00','2014-01-23 18:00:00'),(182,'003FOO',301,'2014-01-24 11:00:00','2014-01-24 16:00:00'),(187,'003FOO',301,'2014-01-25 07:00:00','2014-01-25 11:00:00'),(158,'004FOO',301,'2014-01-19 13:30:00','2014-01-19 17:30:00'),(163,'004FOO',301,'2014-01-20 13:30:00','2014-01-20 20:00:00'),(168,'004FOO',301,'2014-01-21 09:00:00','2014-01-21 16:30:00'),(173,'004FOO',301,'2014-01-22 12:30:00','2014-01-22 18:00:00'),(178,'004FOO',301,'2014-01-23 15:00:00','2014-01-23 20:30:00'),(183,'004FOO',301,'2014-01-24 14:30:00','2014-01-24 17:30:00'),(188,'004FOO',301,'2014-01-25 08:30:00','2014-01-25 18:00:00');
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

-- Dump completed on 2014-01-22 15:13:47
