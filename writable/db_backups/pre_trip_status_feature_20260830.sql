-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: fu_ubra
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `travel_requests`
--

DROP TABLE IF EXISTS `travel_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `travel_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_id` varchar(50) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `travel_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `return_time` time NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `assigned_driver_id` int(11) DEFAULT NULL,
  `assigned_vehicle_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `scanned_id` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `disposal_status` enum('None','For Disposal','Disposed') NOT NULL DEFAULT 'None',
  `disposal_date` datetime DEFAULT NULL,
  `disposal_authorized_by` int(11) DEFAULT NULL,
  `disposal_signature` text DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_id` (`trip_id`),
  KEY `requester_id` (`requester_id`),
  KEY `department_id` (`department_id`),
  KEY `assigned_driver_id` (`assigned_driver_id`),
  KEY `assigned_vehicle_id` (`assigned_vehicle_id`),
  CONSTRAINT `fk_travel_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_travel_driver` FOREIGN KEY (`assigned_driver_id`) REFERENCES `personnel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_travel_requester` FOREIGN KEY (`requester_id`) REFERENCES `personnel` (`id`),
  CONSTRAINT `fk_travel_vehicle` FOREIGN KEY (`assigned_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_requests`
--

LOCK TABLES `travel_requests` WRITE;
/*!40000 ALTER TABLE `travel_requests` DISABLE KEYS */;
INSERT INTO `travel_requests` VALUES (1,'TR-20260729-0001',8,'Cebu City Hall','Official meeting with city officials','2026-07-29','08:00:00','17:00:00',1,6,2,'Completed','2026-08-04 06:40:27','2026-08-04 06:40:29',NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:49','2026-07-28 16:29:06','2026-08-04 05:21:49','2026-08-03 22:40:29'),(2,'TR-20260728-0001',9,'Mandaue Campus','Inventory transfer and outreach','2026-07-28','09:30:00','15:30:00',2,7,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:45','2026-07-28 16:29:06','2026-08-04 05:21:45','2026-07-28 16:29:06'),(3,'TR-20260730-0001',5,'DepEd Regional Office','Submission of accreditation documents','2026-07-30','07:00:00','12:00:00',5,7,2,'Completed','2026-08-01 19:36:32','2026-08-01 19:36:32','EMP-2023-210',1,'For Disposal',NULL,NULL,NULL,'2026-08-04 05:21:40','2026-07-28 16:29:06','2026-08-04 05:21:40','2026-07-28 12:26:12'),(4,'TR-20260601-0001',8,'Provincial Office','Inspection of maintenance requests','2026-06-01','07:15:00','13:00:00',1,14,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-07-01 02:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(5,'TR-20260715-0001',9,'Lapu-Lapu Warehouse','Equipment pickup for Facilities','2026-07-15','10:00:00','16:00:00',4,19,3,'Cancelled',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:36','2026-07-28 16:29:06','2026-08-04 05:21:36','2026-07-28 16:29:06'),(6,'TR-20260726-0001',6,'Bacolod Provincial Capitol','Coordination meeting','2026-07-26','08:30:00','14:00:00',3,7,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:31','2026-07-28 16:42:00','2026-08-04 05:21:31','2026-07-28 16:42:00'),(7,'TR-20260410-0001',8,'Silliman University','Interagency site visit','2026-04-10','07:00:00','18:00:00',1,6,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-06-01 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),(8,'TR-20260320-0001',9,'Dumaguete Airport','Guest pickup','2026-03-20','06:00:00','09:00:00',2,19,2,'Rejected',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:20','2026-07-28 16:42:00','2026-08-04 05:21:20','2026-07-28 16:42:00'),(13,'TR-20260804-0001',5,'Tanjay asaggra','Regional outreach visit','2026-08-05','07:00:00','18:00:00',5,51,2,'Approved','2026-08-04 09:34:59',NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:08:40','2026-08-04 01:23:05','2026-08-19 16:03:31','2026-08-04 01:34:59'),(16,'TR-20260804-0002',14,'Bais City','Site visit and coordination','2026-08-05','07:30:00','05:00:00',5,31,7,'Pending',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-08-04 05:21:04','2026-08-19 16:03:31','2026-08-04 05:21:04');
/*!40000 ALTER TABLE `travel_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-30  0:43:40
