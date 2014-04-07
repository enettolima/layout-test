-- MySQL dump 10.13  Distrib 5.6.15, for osx10.9 (x86_64)
--
-- Host: 10.11.12.13    Database: dbname
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
-- Table structure for table `assigned_roles`
--

DROP TABLE IF EXISTS `assigned_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assigned_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_roles_user_id_foreign` (`user_id`),
  KEY `assigned_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `assigned_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `assigned_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assigned_roles`
--

LOCK TABLES `assigned_roles` WRITE;
/*!40000 ALTER TABLE `assigned_roles` DISABLE KEYS */;
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (4,1,13);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (7,6,8);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (9,6,12);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (10,6,6);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (11,1,14);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (12,1,15);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (15,1,16);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (16,1,5);
INSERT INTO `assigned_roles` (`id`, `user_id`, `role_id`) VALUES (17,1,6);
/*!40000 ALTER TABLE `assigned_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2014_03_06_212125_entrust_setup_tables',1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2014_03_06_214619_update_users_table',2);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2014_03_18_222757_add_default_store_to_users_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` (`id`, `permission_id`, `role_id`) VALUES (1,2,6);
INSERT INTO `permission_role` (`id`, `permission_id`, `role_id`) VALUES (2,3,5);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `display_name`, `created_at`, `updated_at`) VALUES (2,'scheduler_manage','Manage Scheduler','2014-03-07 05:14:40','2014-03-07 05:14:40');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `created_at`, `updated_at`) VALUES (3,'scheduler_view','View Scheduler Data','2014-03-20 00:00:14','2014-03-20 00:00:14');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (5,'Associate','2014-03-07 04:47:39','2014-03-07 04:47:39');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (6,'Store Manager','2014-03-07 04:48:08','2014-03-07 04:48:08');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (8,'Store100','2014-03-07 05:35:48','2014-03-07 05:35:48');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (11,'Store200','2014-03-15 00:36:22','2014-03-15 00:36:22');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (12,'Store300','2014-03-15 00:36:39','2014-03-15 00:36:39');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (13,'Developer','2014-03-20 01:55:45','2014-03-20 01:55:45');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (14,'Administrator','2014-03-24 23:05:41','2014-03-24 23:05:41');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (15,'Store301','2014-03-27 03:26:28','2014-03-27 03:26:28');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES (16,'Store376','2014-03-28 19:02:58','2014-03-28 19:02:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_day_meta`
--

LOCK TABLES `schedule_day_meta` WRITE;
/*!40000 ALTER TABLE `schedule_day_meta` DISABLE KEYS */;
INSERT INTO `schedule_day_meta` (`id`, `store_id`, `date`, `data`) VALUES (2,301,'2014-04-06','{\"sequence\":[\"397CL\"]}');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_inout`
--

LOCK TABLES `scheduled_inout` WRITE;
/*!40000 ALTER TABLE `scheduled_inout` DISABLE KEYS */;
INSERT INTO `scheduled_inout` (`id`, `associate_id`, `store_id`, `date_in`, `date_out`) VALUES (13,'397CL',301,'2014-04-06 08:30:00','2014-04-06 12:00:00');
/*!40000 ALTER TABLE `scheduled_inout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(60) DEFAULT NULL,
  `fname` varchar(20) DEFAULT NULL,
  `lname` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(64) NOT NULL,
  `defaultStore` varchar(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `password`, `fname`, `lname`, `updated_at`, `created_at`, `email`, `username`, `defaultStore`) VALUES (1,'$2y$10$AItaHvZ8ZcdvPwShWgN6AulmLUgq.pfwvwWq8K54zya0gruHWz1wu','Chad','Davis','2014-04-02 01:44:40','2014-03-05 00:10:45','chad@earthboundtrading.com','cdavis','301');
INSERT INTO `users` (`id`, `password`, `fname`, `lname`, `updated_at`, `created_at`, `email`, `username`, `defaultStore`) VALUES (6,'$2y$10$xNAF50.TxikMAdTbjWdZ7eX7DuDyCwDSxRn9A5Klmeh6Jp2B94wsm','Chuy','Zero','2014-03-22 03:17:27','2014-03-07 04:03:55','chad.davis@gmail.com','chuyzero','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'dbname'
--
/*!50003 DROP PROCEDURE IF EXISTS `p2` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`dbuser`@`%` PROCEDURE `p2`(s VARCHAR(3), d DATE)
BEGIN
	SELECT 
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 00:00:00') and date_out >= CONCAT(d, ' 00:30:00')) as '00:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 00:30:00') and date_out >= CONCAT(d, ' 01:00:00')) as '00:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 01:00:00') and date_out >= CONCAT(d, ' 01:30:00')) as '01:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 01:30:00') and date_out >= CONCAT(d, ' 02:00:00')) as '01:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 02:00:00') and date_out >= CONCAT(d, ' 02:30:00')) as '02:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 02:30:00') and date_out >= CONCAT(d, ' 03:00:00')) as '02:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 03:00:00') and date_out >= CONCAT(d, ' 03:30:00')) as '03:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 03:30:00') and date_out >= CONCAT(d, ' 04:00:00')) as '03:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 04:00:00') and date_out >= CONCAT(d, ' 04:30:00')) as '04:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 04:30:00') and date_out >= CONCAT(d, ' 05:00:00')) as '04:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 05:00:00') and date_out >= CONCAT(d, ' 05:30:00')) as '05:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 05:30:00') and date_out >= CONCAT(d, ' 06:00:00')) as '05:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 06:00:00') and date_out >= CONCAT(d, ' 06:30:00')) as '06:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 06:30:00') and date_out >= CONCAT(d, ' 07:00:00')) as '06:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 07:00:00') and date_out >= CONCAT(d, ' 07:30:00')) as '07:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 07:30:00') and date_out >= CONCAT(d, ' 08:00:00')) as '07:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 08:00:00') and date_out >= CONCAT(d, ' 08:30:00')) as '08:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 08:30:00') and date_out >= CONCAT(d, ' 09:00:00')) as '08:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 09:00:00') and date_out >= CONCAT(d, ' 09:30:00')) as '09:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 09:30:00') and date_out >= CONCAT(d, ' 10:00:00')) as '09:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 10:00:00') and date_out >= CONCAT(d, ' 10:30:00')) as '10:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 10:30:00') and date_out >= CONCAT(d, ' 11:00:00')) as '10:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 11:00:00') and date_out >= CONCAT(d, ' 11:30:00')) as '11:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 11:30:00') and date_out >= CONCAT(d, ' 12:00:00')) as '11:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 12:00:00') and date_out >= CONCAT(d, ' 12:30:00')) as '12:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 12:30:00') and date_out >= CONCAT(d, ' 13:00:00')) as '12:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 13:00:00') and date_out >= CONCAT(d, ' 13:30:00')) as '13:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 13:30:00') and date_out >= CONCAT(d, ' 14:00:00')) as '13:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 14:00:00') and date_out >= CONCAT(d, ' 14:30:00')) as '14:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 14:30:00') and date_out >= CONCAT(d, ' 15:00:00')) as '14:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 15:00:00') and date_out >= CONCAT(d, ' 15:30:00')) as '15:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 15:30:00') and date_out >= CONCAT(d, ' 16:00:00')) as '15:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 16:00:00') and date_out >= CONCAT(d, ' 16:30:00')) as '16:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 16:30:00') and date_out >= CONCAT(d, ' 17:00:00')) as '16:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 17:00:00') and date_out >= CONCAT(d, ' 17:30:00')) as '17:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 17:30:00') and date_out >= CONCAT(d, ' 18:00:00')) as '17:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 18:00:00') and date_out >= CONCAT(d, ' 18:30:00')) as '18:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 18:30:00') and date_out >= CONCAT(d, ' 19:00:00')) as '18:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 19:00:00') and date_out >= CONCAT(d, ' 19:30:00')) as '19:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 19:30:00') and date_out >= CONCAT(d, ' 20:00:00')) as '19:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 20:00:00') and date_out >= CONCAT(d, ' 20:30:00')) as '20:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 20:30:00') and date_out >= CONCAT(d, ' 21:00:00')) as '20:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 21:00:00') and date_out >= CONCAT(d, ' 21:30:00')) as '21:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 21:30:00') and date_out >= CONCAT(d, ' 22:00:00')) as '21:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 22:00:00') and date_out >= CONCAT(d, ' 22:30:00')) as '22:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 22:30:00') and date_out >= CONCAT(d, ' 23:00:00')) as '22:30',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 23:00:00') and date_out >= CONCAT(d, ' 23:30:00')) as '23:00',
		( select count(*) from scheduled_inout where store_id = s and date_in <= CONCAT(d, ' 23:30:00') and date_out >= CONCAT(d, ' 23:59:59')) as '23:30'
	;
	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-07 17:54:00
