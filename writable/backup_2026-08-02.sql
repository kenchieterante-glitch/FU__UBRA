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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action` varchar(150) DEFAULT NULL,
  `logged_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aircon_units`
--

DROP TABLE IF EXISTS `aircon_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aircon_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(120) NOT NULL,
  `unit_name` varchar(120) NOT NULL,
  `last_cleaning` date DEFAULT NULL,
  `next_schedule` date DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT 'Operational',
  `assigned_tech` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aircon_units`
--

LOCK TABLES `aircon_units` WRITE;
/*!40000 ALTER TABLE `aircon_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `aircon_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrow_records`
--

DROP TABLE IF EXISTS `borrow_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrow_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) DEFAULT NULL,
  `borrower` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `borrowed_date` date DEFAULT NULL,
  `expected_return` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Borrowed',
  `created_at` datetime DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `disposal_status` enum('None','For Disposal','Disposed') DEFAULT 'None',
  `disposal_date` datetime DEFAULT NULL,
  `disposal_authorized_by` int(11) DEFAULT NULL,
  `disposal_signature` text DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tool_id` (`tool_id`),
  CONSTRAINT `borrow_records_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow_records`
--

LOCK TABLES `borrow_records` WRITE;
/*!40000 ALTER TABLE `borrow_records` DISABLE KEYS */;
INSERT INTO `borrow_records` VALUES (1,2,'Pedro Penduko','IT Support','2026-07-29','2026-08-05','Borrowed','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(2,3,'Mina Santos','Housekeeping','2026-07-20','2026-07-27','Returned','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(3,4,'sherina Banosong','Administration','2026-06-15','2026-06-22','Returned','2026-07-29 00:29:06',1,'2026-07-01 09:00:00','For Disposal',NULL,NULL,NULL,'2026-07-29 00:29:06'),(4,5,'Dr. Helen Peralta','College of IT','2026-07-28','2026-08-04','Borrowed','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(5,6,'Armand Perez','Facilities','2026-05-10','2026-05-17','Returned','2026-07-29 00:29:06',1,'2026-07-15 14:00:00','Disposed','2026-07-15 00:00:00',NULL,NULL,'2026-07-29 00:29:06'),(6,7,'Sonia G. Ramirez','Facilities','2026-07-29','2026-08-02','Borrowed','2026-07-29 00:29:06',0,NULL,'For Disposal',NULL,NULL,NULL,'2026-07-28 19:26:59'),(7,2,'Rico Dela Cruz','Facilities','2026-07-25','2026-08-01','Borrowed','2026-07-29 00:42:00',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:42:00'),(8,3,'Timothy Eraham','Housekeeping','2026-04-10','2026-04-17','Returned','2026-07-29 00:42:00',1,'2026-06-01 10:00:00','None',NULL,NULL,NULL,'2026-07-29 00:42:00'),(9,7,'Juan dela beto','Facilities','2026-03-01','2026-03-08','Returned','2026-07-29 00:42:00',1,'2026-06-20 09:00:00','Disposed','2026-06-20 00:00:00',NULL,NULL,'2026-07-29 00:42:00'),(10,55,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:34:15',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 19:34:15'),(11,55,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:38:32',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 20:10:55');
/*!40000 ALTER TABLE `borrow_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumable_inventory`
--

DROP TABLE IF EXISTS `consumable_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consumable_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category` enum('Cleaning Agent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Agent',
  `unit` varchar(40) NOT NULL DEFAULT 'Pieces',
  `current_stock` decimal(8,2) NOT NULL DEFAULT 0.00,
  `reorder_threshold` decimal(8,2) NOT NULL DEFAULT 5.00,
  `last_refill` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumable_inventory`
--

LOCK TABLES `consumable_inventory` WRITE;
/*!40000 ALTER TABLE `consumable_inventory` DISABLE KEYS */;
INSERT INTO `consumable_inventory` VALUES (1,'Floor Cleaner (Pine)','Cleaning Agent','Liters',18.00,5.00,'2025-07-10','2026-07-19 05:37:22',NULL),(2,'Toilet Bowl Cleaner','Cleaning Agent','Bottles',7.00,3.00,'2025-07-08','2026-07-19 05:37:22',NULL),(3,'Trash Liners (Large)','Disposable','Rolls',4.00,5.00,'2025-07-05','2026-07-19 05:37:22',NULL),(4,'Mop Heads','Tools','Pieces',6.00,3.00,'2025-07-01','2026-07-19 05:37:22',NULL),(5,'Disinfectant Spray','Cleaning Agent','Bottles',2.00,4.00,'2025-06-28','2026-07-19 05:37:22',NULL),(6,'Tissue Paper (Rolls)','Disposable','Rolls',30.00,10.00,'2025-07-12','2026-07-19 05:37:22',NULL),(7,'Liquid Hand Soap','Cleaning Agent','Liters',3.00,4.00,'2025-07-09','2026-07-19 05:37:22',NULL),(8,'Brooms','Tools','Pieces',12.00,4.00,'2025-06-15','2026-07-19 05:37:22',NULL);
/*!40000 ALTER TABLE `consumable_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Facilities','2026-07-18 20:20:56'),(2,'Logistics','2026-07-18 20:20:56'),(3,'Security','2026-07-18 20:20:56'),(4,'Housekeeping','2026-07-18 20:20:56'),(5,'College of IT','2026-07-18 20:20:56'),(6,'Administration','2026-07-18 20:20:56'),(7,'Athletics','2026-07-18 20:20:56'),(8,'Finance','2026-07-18 20:20:56');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disposal_logs`
--

DROP TABLE IF EXISTS `disposal_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disposal_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_type` varchar(20) NOT NULL,
  `record_id` int(11) NOT NULL,
  `authorized_by_id` int(11) DEFAULT NULL,
  `signature` longtext DEFAULT NULL,
  `disposal_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `authorized_by_id` (`authorized_by_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disposal_logs`
--

LOCK TABLES `disposal_logs` WRITE;
/*!40000 ALTER TABLE `disposal_logs` DISABLE KEYS */;
INSERT INTO `disposal_logs` VALUES (1,'borrow',5,8,NULL,'2026-07-15','Industrial Vacuum beyond repair; unit decommissioned.','2026-07-29 00:29:06'),(2,'borrow',9,8,NULL,'2026-06-20','Cordless drill set damaged; unit disposed per inspection.','2026-07-29 00:42:00');
/*!40000 ALTER TABLE `disposal_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fire_extinguishers`
--

DROP TABLE IF EXISTS `fire_extinguishers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fire_extinguishers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` varchar(30) NOT NULL COMMENT 'e.g. FE-ADM-01',
  `type` enum('CO2','Dry Chemical','Wet Chemical','Foam') NOT NULL DEFAULT 'CO2',
  `location` varchar(120) NOT NULL COMMENT 'Building/Area name',
  `weight_kg` decimal(5,1) NOT NULL DEFAULT 6.0,
  `last_inspection` date DEFAULT NULL,
  `next_due` date DEFAULT NULL,
  `status` enum('New','Refillable','Defective','Missing') NOT NULL DEFAULT 'New',
  `year_acquired` year(4) DEFAULT NULL,
  `inspector` varchar(100) DEFAULT NULL,
  `assigned_guard` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_id` (`unit_id`),
  KEY `idx_location` (`location`),
  KEY `idx_status` (`status`),
  KEY `idx_next_due` (`next_due`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fire_extinguishers`
--

LOCK TABLES `fire_extinguishers` WRITE;
/*!40000 ALTER TABLE `fire_extinguishers` DISABLE KEYS */;
INSERT INTO `fire_extinguishers` VALUES (1,'FE-ADM-01','CO2','Admin Building',10.0,'2026-06-14','2027-01-10','New',2024,'Cruz, M.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(2,'FE-ADM-02','Dry Chemical','Admin Building',6.0,'2026-03-01','2026-08-16','Refillable',2021,'Cruz, M.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(3,'FE-ADM-03','CO2','Admin Building',10.0,'2025-11-11','2026-07-09','Defective',2018,'Cruz, M.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(4,'FE-LIB-01','Dry Chemical','Library',10.0,'2026-06-14','2027-01-10','New',2023,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(5,'FE-LIB-02','CO2','Library',6.0,'2026-06-14','2027-01-10','New',2024,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(6,'FE-LIB-03','Foam','Library',9.0,'2026-03-01','2026-08-16','Refillable',2020,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(7,'FE-SCI-01','CO2','Science Building',10.0,'2026-06-14','2027-01-10','New',2023,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(8,'FE-SCI-02','Dry Chemical','Science Building',6.0,'2025-11-11','2026-07-09','Defective',2017,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(9,'FE-SCI-03','CO2','Science Building',10.0,'2026-03-01','2026-08-16','Refillable',2022,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(10,'FE-GYM-01','Dry Chemical','Gymnasium',9.0,'2026-06-14','2027-01-10','New',2024,'Santos, R.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(11,'FE-GYM-02','CO2','Gymnasium',10.0,'2026-06-14','2027-01-10','New',2024,'Santos, R.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(12,'FE-CAN-01','Wet Chemical','Canteen',6.0,'2026-03-01','2026-08-16','Refillable',2021,'Gomez, T.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(13,'FE-CAN-02','Wet Chemical','Canteen',6.0,'2026-03-01','2026-08-16','Refillable',2021,'Gomez, T.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(14,'FE-ENG-01','CO2','Engineering',10.0,'2026-06-14','2027-01-10','New',2023,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(15,'FE-ENG-02','Dry Chemical','Engineering',6.0,'2025-11-11','2026-07-09','Defective',2016,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(16,'FE-ENG-03','CO2','Engineering',9.0,'2026-03-01','2026-08-16','Refillable',2022,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(17,'FE-CCS-01','CO2','CCS Building',10.0,'2026-06-14','2027-01-10','New',2024,'Aquino, D.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(18,'FE-CCS-02','Dry Chemical','CCS Building',10.0,'2026-06-14','2027-01-10','New',2024,'Aquino, D.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(19,'FE-CLI-01','CO2','Clinic',6.0,'2026-06-14','2027-01-10','New',2023,'Torres, L.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(20,'FE-CLI-02','Wet Chemical','Clinic',6.0,'2026-03-01','2026-08-16','Refillable',2020,'Torres, L.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(21,'FE-GRD-01','Dry Chemical','Guard House',6.0,'2026-06-14','2027-01-10','New',2023,'Mendoza, P.','Guard Santos',NULL,'2026-07-19 05:37:22','2026-07-29 02:41:32'),(22,'FE-E8BFCC','Dry Chemical','Admin Building',10.0,'2026-07-01','2027-01-01','New',2026,NULL,NULL,NULL,'2026-08-02 03:36:52',NULL),(23,'FE-TESTQR01','CO2','Library',5.0,'2026-07-01','2027-01-01','New',2026,NULL,NULL,NULL,'2026-08-02 04:10:55',NULL);
/*!40000 ALTER TABLE `fire_extinguishers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_logs`
--

DROP TABLE IF EXISTS `gps_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gps_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `device_id` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `signal_strength` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Offline',
  `logged_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_logs`
--

LOCK TABLES `gps_logs` WRITE;
/*!40000 ALTER TABLE `gps_logs` DISABLE KEYS */;
INSERT INTO `gps_logs` VALUES (1,2,'FU-GPS-802',9.3103500,123.3080000,'Strong (98%)','Online','2026-07-31 23:20:24'),(2,3,'FU-GPS-431',9.3050000,123.3010000,'Strong (91%)','Online','2026-07-31 23:20:24'),(3,5,'FU-GPS-204',9.3120000,123.3150000,'Medium (74%)','Online','2026-07-31 23:20:24');
/*!40000 ALTER TABLE `gps_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `janitorial`
--

DROP TABLE IF EXISTS `janitorial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `janitorial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_name` varchar(100) DEFAULT NULL,
  `assigned_area` varchar(255) NOT NULL,
  `task` text NOT NULL,
  `schedule_date` date NOT NULL,
  `assigned_personnel_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_personnel_id` (`assigned_personnel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial`
--

LOCK TABLES `janitorial` WRITE;
/*!40000 ALTER TABLE `janitorial` DISABLE KEYS */;
/*!40000 ALTER TABLE `janitorial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `janitorial_assignments`
--

DROP TABLE IF EXISTS `janitorial_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `janitorial_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_name` varchar(120) NOT NULL,
  `assigned_zone` varchar(120) NOT NULL,
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL,
  `date_assigned` date NOT NULL,
  `status` enum('Active','Off Duty','On Leave') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_assigned`),
  KEY `idx_zone` (`assigned_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_assignments`
--

LOCK TABLES `janitorial_assignments` WRITE;
/*!40000 ALTER TABLE `janitorial_assignments` DISABLE KEYS */;
INSERT INTO `janitorial_assignments` VALUES (1,'Bautista, M.','Admin Building','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(2,'Dizon, L.','Library','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(3,'Fernandez, G.','Science Building','06:00:00','14:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(4,'Hernandez, K.','Gymnasium','05:00:00','13:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(5,'Ignacio, P.','Canteen','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(6,'Javier, C.','Engineering','08:00:00','16:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(7,'Lacson, A.','CCS Building','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(8,'Mendez, R.','Clinic','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22');
/*!40000 ALTER TABLE `janitorial_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `janitorial_tasks`
--

DROP TABLE IF EXISTS `janitorial_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `janitorial_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `task_name` varchar(200) NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_assignment` (`assignment_id`),
  CONSTRAINT `fk_jan_task` FOREIGN KEY (`assignment_id`) REFERENCES `janitorial_assignments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_tasks`
--

LOCK TABLES `janitorial_tasks` WRITE;
/*!40000 ALTER TABLE `janitorial_tasks` DISABLE KEYS */;
INSERT INTO `janitorial_tasks` VALUES (1,1,'Sweep & mop corridors',1,'2025-07-18 07:30:00','2026-07-19 05:37:22'),(2,1,'Clean restrooms — Floor 1',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(3,1,'Empty trash bins',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(4,1,'Wipe window sills',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(5,1,'Mop main lobby',1,'2025-07-18 09:30:00','2026-07-19 05:37:22'),(6,1,'Replenish soap & tissue',1,'2025-07-18 10:00:00','2026-07-19 05:37:22'),(7,1,'Clean comfort rooms — Floor 2',0,NULL,'2026-07-19 05:37:22'),(8,1,'General sanitizing',0,NULL,'2026-07-19 05:37:22'),(9,2,'Dust bookshelves',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(10,2,'Vacuum reading area',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(11,2,'Mop entrance',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(12,2,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(13,2,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(14,2,'Wipe computer tables',0,NULL,'2026-07-19 05:37:22'),(15,3,'Sweep lab corridors',1,'2025-07-18 06:30:00','2026-07-19 05:37:22'),(16,3,'Mop stairs',0,NULL,'2026-07-19 05:37:22'),(17,3,'Empty lab trash',0,NULL,'2026-07-19 05:37:22'),(18,3,'Sanitize lab benches',0,NULL,'2026-07-19 05:37:22'),(19,3,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(20,4,'Sweep gym floor',1,'2025-07-18 05:30:00','2026-07-19 05:37:22'),(21,4,'Mop court',1,'2025-07-18 06:00:00','2026-07-19 05:37:22'),(22,4,'Clean locker rooms',1,'2025-07-18 06:45:00','2026-07-19 05:37:22'),(23,4,'Empty trash bins',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(24,5,'Wipe dining tables',1,'2025-07-18 07:00:00','2026-07-19 05:37:22'),(25,5,'Sweep floor',1,'2025-07-18 07:20:00','2026-07-19 05:37:22'),(26,5,'Mop canteen floor',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(27,5,'Clean restrooms',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(28,5,'Empty grease traps',0,NULL,'2026-07-19 05:37:22'),(29,5,'Sanitize counter tops',0,NULL,'2026-07-19 05:37:22'),(30,5,'Replace trash liners',0,NULL,'2026-07-19 05:37:22'),(31,6,'Sweep corridors',1,'2025-07-18 08:10:00','2026-07-19 05:37:22'),(32,6,'Mop workshop floor',0,NULL,'2026-07-19 05:37:22'),(33,6,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(34,6,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(35,6,'Wipe notice boards',0,NULL,'2026-07-19 05:37:22'),(36,6,'Sanitize door handles',0,NULL,'2026-07-19 05:37:22'),(37,7,'Sweep corridors',1,'2025-07-18 07:10:00','2026-07-19 05:37:22'),(38,7,'Mop server room hallway',1,'2025-07-18 07:35:00','2026-07-19 05:37:22'),(39,7,'Clean restrooms',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(40,7,'Wipe workstations',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(41,7,'Empty trash',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(42,8,'Sanitize consultation room',1,'2025-07-18 07:05:00','2026-07-19 05:37:22'),(43,8,'Mop clinic floor',1,'2025-07-18 07:30:00','2026-07-19 05:37:22'),(44,8,'Clean restroom',1,'2025-07-18 07:55:00','2026-07-19 05:37:22'),(45,8,'Replace biohazard bags',1,'2025-07-18 08:20:00','2026-07-19 05:37:22');
/*!40000 ALTER TABLE `janitorial_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `key_borrow_logs`
--

DROP TABLE IF EXISTS `key_borrow_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `key_borrow_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_number` varchar(20) NOT NULL,
  `borrower_id` varchar(50) NOT NULL COMMENT 'Scanned ID number',
  `trip_ticket_id` int(11) DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `department` varchar(100) NOT NULL,
  `key_item` varchar(150) NOT NULL COMMENT 'What key/item was borrowed',
  `scan_in` datetime NOT NULL COMMENT 'Time borrowed',
  `scan_out` datetime DEFAULT NULL COMMENT 'Time returned',
  `status` enum('Active','Returned') NOT NULL DEFAULT 'Active',
  `guard_on_duty` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `log_number` (`log_number`),
  KEY `idx_status` (`status`),
  KEY `idx_scan_in` (`scan_in`),
  KEY `fk_keylog_trip` (`trip_ticket_id`),
  CONSTRAINT `fk_keylog_trip` FOREIGN KEY (`trip_ticket_id`) REFERENCES `travel_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `key_borrow_logs`
--

LOCK TABLES `key_borrow_logs` WRITE;
/*!40000 ALTER TABLE `key_borrow_logs` DISABLE KEYS */;
INSERT INTO `key_borrow_logs` VALUES (1,'KL-001','EMP-2021-042',NULL,'Dela Cruz, J.','Library','Library Storeroom Key','2026-07-29 07:30:00','2026-07-29 12:00:00','Returned','Santos, J.','2026-07-19 05:37:22'),(2,'KL-002','EMP-2022-118',NULL,'Magsaysay, R.','CCS','Server Room Key','2026-07-29 08:15:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),(3,'KL-003','FAC-2020-007',NULL,'Torres, F.','Science','Lab Cabinet Keys','2026-07-29 09:00:00','2026-07-29 10:30:00','Returned','Dela Cruz, P.','2026-07-19 05:37:22'),(4,'KL-004','EMP-2019-055',NULL,'Reyes, A.','Admin','Admin Filing Room Key','2026-07-29 10:45:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),(5,'KL-005','EMP-2020-034',NULL,'Juan dela Cruz','Logistics','Library storeroom key','2026-08-01 19:34:59','2026-08-01 19:35:00','Returned','Test Guard','2026-08-02 03:34:59');
/*!40000 ALTER TABLE `key_borrow_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `recipient` varchar(120) NOT NULL DEFAULT 'Operations Team',
  `priority` enum('CRITICAL','MODERATE','ROUTINE') NOT NULL DEFAULT 'ROUTINE',
  `status` varchar(40) NOT NULL DEFAULT 'Unread',
  `channel` enum('system','email','sms') NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'Vehicle Inspection','Routine vehicle health compliance check is scheduled tomorrow. System: FU-UBRA Fleet Telematics Service Engine','Operations Team','CRITICAL','Verified','system',1,'2026-07-28 19:18:13','2026-07-29 02:56:09'),(2,'Travel Reminder','Driver Mark has an assigned dispatch schedule tomorrow. Destination: Dumaguete City Top Nail Territory.','Driver Mark','MODERATE','Notified','email',1,'2026-07-28 19:18:17','2026-07-28 02:56:09'),(3,'Air-Con Cleaning','Air Conditioner Building A: A preventive system maintenance starts in 2 days. Facilities Dept.','Facilities Dept.','ROUTINE','Assigned','system',1,'2026-07-27 02:56:09','2026-07-27 02:56:09'),(4,'Janitorial Assignment','Weekly deep disinfection assignment schedule for Team B begins tomorrow. Team B Duty.','Team B','ROUTINE','Assigned','system',1,'2026-07-26 02:56:09','2026-07-26 02:56:09'),(5,'Inventory Low Stock','Critical spare parts and engine filters are low on spare parts inventory. Notify: Open Calendar Events.','Office Supplies','CRITICAL','Ordered','system',1,'2026-07-28 19:18:20','2026-07-29 02:56:09'),(6,'Vehicle Expiry','Registration for Utility Truck-04 has been safely completed earlier this week. Reference: FU Comms House Print Document.','Fleet Admin','ROUTINE','Reviewed','email',1,'2026-07-24 02:56:09','2026-07-24 02:56:09');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `assigned_task` varchar(150) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_id` (`emp_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
INSERT INTO `personnel` VALUES (3,'EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',3,'Safety & Security Chief','GPS Route Validation','Active','2026-07-18 20:20:56'),(4,'EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',7,'Carpenter','Science Lab Cleaning','Active','2026-07-18 20:20:56'),(5,'EMP-2022-071','Pedro Penduko','pedro.penduko@foundation.edu.ph',5,'IT Support','Network Deployment','Active','2026-07-18 20:20:56'),(6,'EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',2,'Driver','Van-01 Dispatch','Active','2026-07-18 20:20:56'),(7,'EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',2,'Senior Driver','Bus-02 Assignment','Active','2026-07-18 20:20:56'),(8,'EMP-2018-009','Dr. Helen Peralta','helen.peralta@foundation.edu.ph',6,'Department Head','Trip Approvals','Active','2026-07-18 20:20:56'),(9,'EMP-2023-301','sherina Banosong','sherina.banosong@foundation.edu.ph',2,'Staff','Front Office Records Filing','Active','2026-07-19 02:54:16'),(14,'EMP-2024-302','Timothy Eraham','timothy.eraham@foundation.edu.ph',5,'Driver','Van-02 Dispatch','Active','2026-07-26 00:20:05'),(15,'EMP-2024-303','TimothyLincon','timothy.lincon@foundation.edu.ph',1,'Janitor','Library Restroom Cleaning','Active','2026-07-26 12:13:38'),(16,'EMP-2023-304','Juan dela Cruz','juan.delacruz@foundation.edu.ph',8,'Maintenance','HVAC Unit Inspection - Bldg 14','Active','2026-07-27 22:20:10'),(17,'EMP-2023-305','Juan dela beto','juan.delabeto@foundation.edu.ph',1,'Janitor','Gymnasium Floor Maintenance','Active','2026-07-27 22:22:00'),(18,'EMP-2018-306','Lapu-lapu','lapu.lapu@foundation.edu.ph',5,'Janitor','Admin Lobby Floor Care','Active','2026-07-28 14:03:20'),(19,'EMP-2023-307','Juan Cruz','juancruz@example.com',1,'Driver',NULL,'On Leave','2026-07-28 18:33:24'),(20,'EMP-2023-308','Mina Santos','minasantos@example.com',1,'Janitor',NULL,'On Leave','2026-07-28 18:33:24'),(21,'EMP-2023-309','Rico Dela Cruz','ricodelacruz@example.com',1,'Carpenter',NULL,'On Leave','2026-07-28 18:33:24'),(22,'EMP-2023-310','Armand Perez','armandperez@example.com',1,'Maintenance',NULL,'On Leave','2026-07-28 18:33:24'),(23,'EMP-2026-835','Ricardo Reyes','ricardo.reyes@foundation.edu.ph',4,'Janitor','Unassigned','On Leave','2026-07-31 20:16:53'),(24,'EMP-2026-449','Goyo Pascual','goyo.pascual@foundation.edu.ph',4,'Janitor','Unassigned','On Leave','2026-07-31 20:16:53'),(25,'EMP-2026-440','Cardo Manalo','cardo.manalo@foundation.edu.ph',1,'Carpenter','Cabinet Fabrication','Active','2026-07-31 20:16:53'),(26,'EMP-2026-765','Rodrigo Torres','rodrigo.torres@foundation.edu.ph',8,'Accounting Staff','Payroll Processing','Active','2026-07-31 20:16:53'),(27,'EMP-2026-834','Josefa Mendoza','josefa.mendoza@foundation.edu.ph',1,'Maintenance Technician','Electrical Repair','Active','2026-07-31 20:16:53'),(28,'EMP-2026-100','Fernando Ocampo','fernando.ocampo@foundation.edu.ph',1,'Construction Foreman','Renovation Project Lead','Active','2026-07-31 20:16:53'),(29,'EMP-2026-786','Cardo Domingo','cardo.domingo@foundation.edu.ph',6,'Administrator','Department Coordination','Active','2026-07-31 20:16:53'),(30,'EMP-2026-654','Teresa Domingo','teresa.domingo@foundation.edu.ph',1,'Maintenance Technician','Plumbing Repair','Active','2026-07-31 20:16:53'),(31,'EMP-2026-683','Emilio Reyes','emilio.reyes@foundation.edu.ph',2,'Driver','Unassigned','Inactive','2026-07-31 20:16:53'),(32,'EMP-2026-631','Consolacion Garcia','consolacion.garcia@foundation.edu.ph',8,'Accounting Staff','Payroll Processing','Active','2026-07-31 20:16:53'),(33,'EMP-2026-243','Antonio Reyes','antonio.reyes@foundation.edu.ph',2,'Senior Driver','Long Haul Route','Active','2026-07-31 20:16:53'),(34,'EMP-2026-283','Danilo Mendoza','danilo.mendoza@foundation.edu.ph',1,'Lead Carpenter','Custom Furniture Build','Active','2026-07-31 20:16:53'),(35,'EMP-2026-134','Rizal Bautista','rizal.bautista@foundation.edu.ph',2,'Driver','Utility Truck Duty','Active','2026-07-31 20:16:53'),(36,'EMP-2026-201','Remedios Ocampo','remedios.ocampo@foundation.edu.ph',1,'Physical Plant Supr.','Preventive Maintenance','Active','2026-07-31 20:16:53'),(37,'EMP-2026-887','Ricardo Mendoza','ricardo.mendoza@foundation.edu.ph',1,'Lead Carpenter','Unassigned','On Leave','2026-07-31 20:16:53'),(38,'EMP-2026-305','Fernando Salazar','fernando.salazar@foundation.edu.ph',1,'Carpenter','Unassigned','On Leave','2026-07-31 20:16:53'),(39,'EMP-2026-709','Andres Navarro','andres.navarro@foundation.edu.ph',8,'Accounting Staff','Unassigned','Inactive','2026-07-31 20:16:53'),(40,'EMP-2026-953','Diego Castillo','diego.castillo@foundation.edu.ph',6,'Administrator','Department Coordination','Active','2026-07-31 20:16:53'),(41,'EMP-2026-172','Isabel Aquino','isabel.aquino@foundation.edu.ph',1,'Lead Carpenter','Custom Furniture Build','Active','2026-07-31 20:16:53'),(42,'EMP-2026-738','Fernando Navarro','fernando.navarro@foundation.edu.ph',3,'Security Officer','Visitor Screening','Active','2026-07-31 20:16:53'),(43,'EMP-2026-970','Gabriela Pascual','gabriela.pascual@foundation.edu.ph',6,'Administrator','Department Coordination','Active','2026-07-31 20:16:53'),(44,'EMP-2026-949','Emilio Rivera','emilio.rivera@foundation.edu.ph',4,'Janitor','Unassigned','Inactive','2026-07-31 20:16:53'),(45,'EMP-2026-492','Maria Mendoza','maria.mendoza@foundation.edu.ph',1,'Lead Carpenter','Unassigned','On Leave','2026-07-31 20:16:53'),(46,'EMP-2026-603','Rizal Garcia','rizal.garcia@foundation.edu.ph',6,'Administrator','Department Coordination','Active','2026-07-31 20:16:53'),(47,'EMP-2026-626','Cardo Garcia','cardo.garcia@foundation.edu.ph',1,'Maintenance Technician','Plumbing Repair','Active','2026-07-31 20:16:53'),(48,'EMP-2026-329','Diego Fernandez','diego.fernandez@foundation.edu.ph',1,'Maintenance Technician','Unassigned','On Leave','2026-07-31 20:16:53'),(49,'EMP-2026-617','Josefa Villanueva','josefa.villanueva@foundation.edu.ph',1,'Construction Worker','Scaffolding Setup','Active','2026-07-31 20:16:53'),(50,'EMP-2026-628','Goyo Castillo','goyo.castillo@foundation.edu.ph',6,'Office Staff','Unassigned','On Leave','2026-07-31 20:16:53'),(51,'EMP-2026-735','Antonio Mendoza','antonio.mendoza@foundation.edu.ph',2,'Senior Driver','Long Haul Route','Active','2026-07-31 20:16:53'),(52,'EMP-2026-815','Josefa Garcia','josefa.garcia@foundation.edu.ph',3,'Security Officer','Visitor Screening','Active','2026-07-31 20:16:53'),(53,'EMP-2026-844','Goyo Ramos','goyo.ramos@foundation.edu.ph',1,'Construction Foreman','Unassigned','Inactive','2026-07-31 20:16:53'),(54,'EMP-2026-737','Juan Santos','juan.santos@foundation.edu.ph',4,'Cleaning Operative','CCS Building Cleaning','Active','2026-07-31 20:16:53'),(55,'EMP-2026-272','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Construction Foreman','Unassigned','On Leave','2026-07-31 20:16:53'),(56,'EMP-2026-378','Fernando Cruz','fernando.cruz@foundation.edu.ph',3,'Guard','Guard House Duty','Active','2026-07-31 20:16:53'),(57,'EMP-2026-271','Pedro Reyes','pedro.reyes@foundation.edu.ph',5,'IT Support','Helpdesk Support','Active','2026-07-31 20:16:53'),(58,'EMP-2026-782','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Lead Carpenter','Custom Furniture Build','Active','2026-07-31 20:16:53'),(59,'EMP-2026-792','Jose Torres','jose.torres@foundation.edu.ph',1,'Construction Foreman','Renovation Project Lead','Active','2026-07-31 20:16:53'),(60,'EMP-2026-711','Cardo Navarro','cardo.navarro@foundation.edu.ph',3,'Guard','Unassigned','Inactive','2026-07-31 20:16:53'),(61,'EMP-2026-498','Fernando Reyes','fernando.reyes@foundation.edu.ph',1,'Maintenance Technician','AC Maintenance A','Active','2026-07-31 20:16:53'),(62,'EMP-2026-487','Manuel Domingo','manuel.domingo@foundation.edu.ph',6,'Office Staff','Front Desk Duty','Active','2026-07-31 20:16:53'),(63,'EMP-2026-392','Josefa Ramos','josefa.ramos@foundation.edu.ph',6,'Office Staff','Unassigned','On Leave','2026-07-31 20:16:53'),(64,'EMP-2026-511','Corazon Castillo','corazon.castillo@foundation.edu.ph',1,'Physical Plant Supr.','Unassigned','Inactive','2026-07-31 20:16:53'),(65,'EMP-2026-385','Andres Garcia','andres.garcia@foundation.edu.ph',3,'Security Officer','Night Shift Patrol','Active','2026-07-31 20:16:53'),(66,'EMP-2026-429','Diego Manalo','diego.manalo@foundation.edu.ph',3,'Security Officer','Night Shift Patrol','Active','2026-07-31 20:16:53'),(67,'EMP-2026-646','Consolacion Reyes','consolacion.reyes@foundation.edu.ph',6,'Office Staff','Front Desk Duty','Active','2026-07-31 20:16:53'),(68,'EMP-2026-525','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',1,'Maintenance Technician','AC Maintenance A','Active','2026-07-31 20:16:53'),(69,'EMP-2026-292','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',3,'Security Officer','Visitor Screening','Active','2026-07-31 20:16:53'),(70,'EMP-2026-877','Danilo Villanueva','danilo.villanueva@foundation.edu.ph',4,'Janitor','Unassigned','Inactive','2026-07-31 20:16:53'),(71,'EMP-2026-157','Josefa Manalo','josefa.manalo@foundation.edu.ph',2,'Senior Driver','Unassigned','Inactive','2026-07-31 20:16:53'),(72,'EMP-2026-743','Isabel Castillo','isabel.castillo@foundation.edu.ph',3,'Guard','CCTV Monitoring','Active','2026-07-31 20:16:53');
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `predictions`
--

DROP TABLE IF EXISTS `predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) DEFAULT NULL,
  `insight_text` varchar(255) DEFAULT NULL,
  `suggestion_text` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predictions`
--

LOCK TABLES `predictions` WRITE;
/*!40000 ALTER TABLE `predictions` DISABLE KEYS */;
INSERT INTO `predictions` VALUES (1,'Dashboard','Three trips are scheduled this week.','Schedule Maintenance','2026-07-18 20:20:56'),(2,'Dashboard','Vehicle Van-01 inspection is due tomorrow.','Generate Report','2026-07-18 20:20:56'),(3,'Dashboard','Inventory of cleaning chemicals is running low.','Notify Personnel','2026-07-18 20:20:56');
/*!40000 ALTER TABLE `predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(255) NOT NULL,
  `generated_by_id` int(11) DEFAULT NULL,
  `type_module` varchar(100) NOT NULL,
  `status` enum('Draft','Pending','Completed') NOT NULL DEFAULT 'Draft',
  `file_path` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `generated_by_id` (`generated_by_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'Facilities Management Report - Last 30 Days',NULL,'','Draft',NULL,0,NULL,'2026-07-25 08:22:54','2026-07-28 16:42:49','2026-07-25 16:22:54'),(2,'Monthly Tools Utilization Report',8,'Asset Inventory','Completed',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(3,'Vehicle Fleet Maintenance Summary',5,'Vehicle Fleet','Completed',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(4,'Q2 Travel Operations Report',9,'Travel Operations','Pending',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(5,'Janitorial Performance FY2025',8,'Janitorial Performance','Completed',NULL,1,'2026-07-10 00:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(6,'Fire Extinguisher Compliance Audit',3,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),(7,'Campus Cleaning Performance - June',8,'Janitorial Performance','Completed',NULL,1,'2026-07-05 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:49','2026-07-28 16:42:00'),(8,'Safety Drill Readiness Report',3,'Maintenance Compliance','Draft',NULL,0,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_records`
--

DROP TABLE IF EXISTS `return_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) DEFAULT NULL,
  `tool_id` int(11) DEFAULT NULL,
  `returned_by` varchar(100) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `tool_id` (`tool_id`),
  CONSTRAINT `return_records_ibfk_1` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_records` (`id`),
  CONSTRAINT `return_records_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_records`
--

LOCK TABLES `return_records` WRITE;
/*!40000 ALTER TABLE `return_records` DISABLE KEYS */;
INSERT INTO `return_records` VALUES (1,10,55,'John Doe','2026-08-01','Good',NULL,'2026-08-02 03:34:15'),(2,11,55,'John Doe','2026-08-01','Good',NULL,'2026-08-02 04:10:55');
/*!40000 ALTER TABLE `return_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safety_reports`
--

DROP TABLE IF EXISTS `safety_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safety_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date NOT NULL,
  `building` varchar(120) DEFAULT NULL,
  `generated_by` varchar(100) NOT NULL,
  `total_units` int(11) DEFAULT 0,
  `overdue` int(11) DEFAULT 0,
  `due_soon` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safety_reports`
--

LOCK TABLES `safety_reports` WRITE;
/*!40000 ALTER TABLE `safety_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `safety_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safety_work_orders`
--

DROP TABLE IF EXISTS `safety_work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safety_work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wo_number` varchar(20) NOT NULL,
  `issue` text NOT NULL,
  `location` varchar(120) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `assigned_to` varchar(100) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `stage` enum('Issue Logged','In Progress','Pending Parts','Completed/Verified') NOT NULL DEFAULT 'Issue Logged',
  `date_logged` date NOT NULL,
  `date_closed` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `wo_number` (`wo_number`),
  KEY `idx_stage` (`stage`),
  KEY `idx_location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safety_work_orders`
--

LOCK TABLES `safety_work_orders` WRITE;
/*!40000 ALTER TABLE `safety_work_orders` DISABLE KEYS */;
INSERT INTO `safety_work_orders` VALUES (1,'WO-001','FE-ADM-03 defective — pressure gauge broken','Admin Building','Cruz, M.','Tech Valdez','High','In Progress','2025-07-10',NULL,NULL,'2026-07-19 05:37:22',NULL),(2,'WO-002','FE-ENG-02 past expiry — needs replacement','Engineering','Flores, C.','Tech Ramos','High','Issue Logged','2025-07-12',NULL,NULL,'2026-07-19 05:37:22',NULL),(3,'WO-003','Missing FE slot — Admin lobby unprotected','Admin Building','Guard Santos','Purchasing Dept','Critical','Pending Parts','2025-07-14',NULL,NULL,'2026-07-19 05:37:22',NULL),(4,'WO-004','FE-SCI-02 not returning pressure after refill','Science Building','Lim, B.','Tech Valdez','Medium','Completed/Verified','2025-07-15',NULL,NULL,'2026-07-19 05:37:22',NULL);
/*!40000 ALTER TABLE `safety_work_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'system_name','FU-UBRA Operational Portal','2026-07-22 05:49:14'),(2,'university','Foundation University','2026-07-22 05:49:14'),(3,'api_key','','2026-07-22 05:49:14'),(5,'smtp_host','smtp.gmail.com','2026-07-19 00:06:28'),(6,'smtp_port','587','2026-07-19 00:06:28'),(7,'smtp_user','','2026-07-19 00:06:28'),(8,'smtp_from','','2026-07-19 00:06:28'),(9,'smtp_name','FU-UBRA System','2026-07-19 00:06:28'),(10,'smtp_pass','','2026-07-19 00:06:28'),(11,'notif_maintenance','1','2026-07-19 00:06:28'),(12,'notif_vehicle','1','2026-07-19 00:06:28'),(13,'notif_janitorial','1','2026-07-19 00:06:28'),(14,'notif_asset','1','2026-07-19 00:06:28'),(15,'notif_travel','1','2026-07-19 00:06:28'),(16,'reminder_days','5','2026-07-19 00:06:28');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tools`
--

DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(150) NOT NULL,
  `asset_code` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `custodian` varchar(100) DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT 'Excellent',
  `availability` varchar(50) DEFAULT 'Available',
  `created_at` datetime DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools`
--

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES (2,'MacBook Pro 16','AST-92041','IT Equipment','Deans Office, CCS','Maria Clara Santos','Excellent','Borrowed','2026-07-18 20:20:56',0,NULL,NULL),(3,'Floors Buffer Matt','AST-03481','Janitorial','Janitor Depot B','Sonia G. Ramirez','Excellent','Available','2026-07-18 20:20:56',0,NULL,NULL),(4,'Sony Alpha A7 III','AST-00612','Media Studio','Media Center','Col. Arthur Miller','Poor','Available','2026-07-18 20:20:56',0,NULL,NULL),(5,'Epson Projector X50','AST-77120','IT Equipment','AVR Room 2','Pedro Penduko','Good','Available','2026-07-18 20:20:56',0,NULL,NULL),(6,'Industrial Vacuum','AST-55019','Janitorial','Housekeeping Store','Sonia G. Ramirez','Good','Borrowed','2026-07-18 20:20:56',0,NULL,NULL),(7,'Cordless Drill Set','AST-30188','Tools','Maintenance Shop','Engr. James Diaz','Excellent','Available','2026-07-18 20:20:56',0,NULL,NULL),(9,'Circular Saw','AST-22502','Tools Equipment','Maintenance Shop',NULL,'Fair','Available','2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(10,'Safety Helmet','AST-91612','Accessories','Admin Building Storage','Pedro Penduko','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-04-29 13:25:41'),(11,'HP LaserJet Printer','AST-18847','Electronic Devices','CCS Building Rm 204',NULL,'Good','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-05-17 13:25:41'),(12,'Hammer Drill','AST-59224','Tools Equipment','Media Center','Rodrigo S. Cruz','Excellent','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-07-03 13:25:41'),(13,'HDMI Cable 10m','AST-88557','Accessories','Admin Building Storage',NULL,'Poor','Disposal','2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(14,'Whiteboard Markers Set','AST-82485','Accessories','Science Building Lab','Sonia G. Ramirez','Fair','Available','2026-07-31 19:26:13',0,NULL,'2026-05-07 13:25:41'),(15,'Hammer Drill','AST-27776','Tools Equipment','Library Storage','Sonia G. Ramirez','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-28 13:25:41'),(16,'Hand Truck Dolly','AST-63457','Tools Equipment','Housekeeping Store','Dr. Helen Peralta','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-04-04 13:25:41'),(17,'Tablet iPad 10th Gen','AST-41450','Electronic Devices','AVR Room 2','Maria Clara Santos','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-05-29 13:25:41'),(18,'Ladder 8ft','AST-38363','Tools Equipment','Science Building Lab','Maria Clara Santos','Fair','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-07-23 13:25:41'),(19,'Extension Reel','AST-85475','Accessories','IT Server Room',NULL,'Good','Available','2026-07-31 19:26:13',0,NULL,'2026-05-15 13:25:41'),(20,'Welding Rods Box','AST-53033','Consumable','Deans Office','Juan dela Cruz','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-06-13 13:25:41'),(21,'Zip Ties Pack','AST-85804','Consumable','Engineering Workshop','Col. Arthur Miller','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-07-03 13:25:41'),(22,'Ladder 8ft','AST-73437','Tools Equipment','Media Center','Sonia G. Ramirez','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-06-02 13:25:41'),(23,'Canon DSLR Camera','AST-17694','Electronic Devices','Housekeeping Store',NULL,'Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-05-11 13:25:41'),(24,'Table Saw','AST-61814','Tools Equipment','Gymnasium Storage','Pedro Penduko','Fair','Maintenance','2026-07-31 19:26:13',0,NULL,'2026-04-15 13:25:41'),(25,'Bluetooth Speaker','AST-57379','Electronic Devices','Housekeeping Store',NULL,'Good','Available','2026-07-31 19:26:13',0,NULL,'2026-07-30 13:25:41'),(26,'Ladder 8ft','AST-14888','Tools Equipment','Main Utility Bldg','Sonia G. Ramirez','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-07-17 13:25:41'),(27,'Zip Ties Pack','AST-95359','Consumable','Housekeeping Store','Juan dela Cruz','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-07-09 13:25:41'),(28,'Electrical Tape Roll','AST-46018','Consumable','Deans Office','Engr. James Diaz','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-30 13:25:41'),(29,'Hammer Drill','AST-30960','Tools Equipment','Athletics Storage','Juan dela Cruz','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-07-07 13:25:41'),(30,'Camera Tripod','AST-83606','Accessories','Main Utility Bldg','Engr. James Diaz','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-07-24 13:25:41'),(31,'Pipe Wrench','AST-89976','Tools Equipment','Deans Office','Dr. Helen Peralta','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-07-04 13:25:41'),(32,'Tool Cabinet','AST-10921','Tools Equipment','Main Utility Bldg','Rodrigo S. Cruz','Poor','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-04-20 13:25:41'),(33,'Laptop Charger Adapter','AST-92882','Accessories','Library Storage','Maria Clara Santos','Fair','Available','2026-07-31 19:26:13',0,NULL,'2026-05-26 13:25:41'),(34,'Laptop Charger Adapter','AST-66319','Accessories','Housekeeping Store','Rodrigo S. Cruz','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-06-26 13:25:41'),(35,'Hammer Drill','AST-17624','Tools Equipment','Athletics Storage',NULL,'Poor','Maintenance','2026-07-31 19:26:13',0,NULL,'2026-07-29 13:25:41'),(36,'HP LaserJet Printer','AST-21694','Electronic Devices','Main Utility Bldg','Pedro Penduko','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-05-16 13:25:41'),(37,'Disinfectant Spray','AST-91615','Consumable','Engineering Workshop','Dr. Helen Peralta','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-11 13:25:41'),(38,'Welding Rods Box','AST-84482','Consumable','Athletics Storage','Juan dela Cruz','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-06-30 13:25:41'),(39,'HDMI Cable 10m','AST-74161','Accessories','Maintenance Shop','Pedro Penduko','Poor','Disposal','2026-07-31 19:26:13',0,NULL,'2026-04-16 13:25:41'),(40,'HDMI Cable 10m','AST-54117','Accessories','Maintenance Shop','Maria Clara Santos','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-05-10 13:25:41'),(41,'Canon DSLR Camera','AST-67613','Electronic Devices','Admin Building Storage','Rodrigo S. Cruz','Good','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-06-24 13:25:41'),(42,'CCTV Camera Kit','AST-19879','Electronic Devices','IT Server Room','Engr. James Diaz','Poor','Available','2026-07-31 19:26:13',0,NULL,'2026-07-20 13:25:41'),(43,'Air Compressor','AST-56187','Tools Equipment','Media Center','Dr. Helen Peralta','Excellent','Available','2026-07-31 19:26:13',0,NULL,'2026-04-06 13:25:41'),(44,'Camera Tripod','AST-36890','Accessories','CCS Building Rm 204','Juan dela Cruz','Poor','Disposal','2026-07-31 19:26:13',0,NULL,'2026-07-10 13:25:41'),(45,'Cordless Drill Set','AST-55542','Tools Equipment','Admin Building Storage','Dr. Helen Peralta','Poor','Available','2026-07-31 19:26:13',0,NULL,'2026-05-12 13:25:41'),(46,'Ladder 8ft','AST-42846','Tools Equipment','Admin Building Storage','Juan dela Cruz','Fair','Available','2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(47,'Laptop Bag','AST-23150','Accessories','Housekeeping Store','Juan dela Cruz','Fair','Available','2026-07-31 19:26:13',0,NULL,'2026-06-14 13:25:41'),(48,'Laptop Charger Adapter','AST-84251','Accessories','Deans Office','Maria Clara Santos','Poor','Available','2026-07-31 19:26:13',0,NULL,'2026-04-06 13:25:41'),(49,'Wireless Microphone Set','AST-80958','Electronic Devices','Admin Building Storage','Maria Clara Santos','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-06-05 13:25:41'),(50,'Cleaning Alcohol 1L','AST-48435','Consumable','AVR Room 2',NULL,'Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-15 13:25:41'),(51,'Extension Cord 20m','AST-73374','Accessories','Engineering Workshop','Juan dela Cruz','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-16 13:25:41'),(52,'Electrical Tape Roll','AST-18067','Consumable','Deans Office','Rodrigo S. Cruz','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-07-14 13:25:41'),(53,'Pipe Wrench','AST-55114','Tools Equipment','Gymnasium Storage','Engr. James Diaz','Poor','Available','2026-07-31 19:26:13',0,NULL,'2026-07-23 13:25:41'),(54,'Table Saw','AST-80661','Tools Equipment','Housekeeping Store','Engr. James Diaz','Good','Available','2026-07-31 19:26:13',0,NULL,'2026-04-18 13:25:41'),(55,'Printer Ink Cartridge','AST-31961','Consumable','CCS Building Rm 204',NULL,'Good','Available','2026-07-31 19:26:13',0,NULL,'2026-08-01 20:10:55'),(56,'Socket Wrench Set','AST-49752','Tools Equipment','Library Storage',NULL,'Poor','Disposal','2026-07-31 19:26:13',0,NULL,'2026-06-22 13:25:41'),(57,'Batteries AA Pack','AST-28474','Consumable','Housekeeping Store','Col. Arthur Miller','Poor','Maintenance','2026-07-31 19:26:13',0,NULL,'2026-05-17 13:25:41'),(58,'Printer Ink Cartridge','AST-29735','Consumable','Science Building Lab','Maria Clara Santos','Excellent','Borrowed','2026-07-31 19:26:13',0,NULL,'2026-04-25 13:25:41');
/*!40000 ALTER TABLE `tools` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_requests`
--

LOCK TABLES `travel_requests` WRITE;
/*!40000 ALTER TABLE `travel_requests` DISABLE KEYS */;
INSERT INTO `travel_requests` VALUES (1,'TR-20260729-0001',8,'Cebu City Hall','Official meeting with city officials','2026-07-29','08:00:00','17:00:00',1,6,2,'Approved',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(2,'TR-20260728-0001',9,'Mandaue Campus','Inventory transfer and outreach','2026-07-28','09:30:00','15:30:00',2,7,3,'Completed',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(3,'TR-20260730-0001',5,'DepEd Regional Office','Submission of accreditation documents','2026-07-30','07:00:00','12:00:00',5,7,2,'Completed','2026-08-01 19:36:32','2026-08-01 19:36:32','EMP-2023-210',0,'For Disposal',NULL,NULL,NULL,NULL,'2026-07-28 16:29:06','2026-08-01 11:36:32','2026-07-28 12:26:12'),(4,'TR-20260601-0001',8,'Provincial Office','Inspection of maintenance requests','2026-06-01','07:15:00','13:00:00',1,14,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-07-01 02:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(5,'TR-20260715-0001',9,'Lapu-Lapu Warehouse','Equipment pickup for Facilities','2026-07-15','10:00:00','16:00:00',4,19,3,'Cancelled',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(6,'TR-20260726-0001',6,'Bacolod Provincial Capitol','Coordination meeting','2026-07-26','08:30:00','14:00:00',3,7,2,'Completed',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),(7,'TR-20260410-0001',8,'Silliman University','Interagency site visit','2026-04-10','07:00:00','18:00:00',1,6,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-06-01 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),(8,'TR-20260320-0001',9,'Dumaguete Airport','Guest pickup','2026-03-20','06:00:00','09:00:00',2,19,2,'Rejected',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00');
/*!40000 ALTER TABLE `travel_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `department_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `emp_id` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin',
  `department` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'John Doe','user@example.com','12345','12345','$2y$10$1GurcyUteQJBUJ4A3W7yNuDpr.oLHPp1fEf7kUmt5graLsPaUvvoG','staff',NULL,NULL,'2026-07-26 11:56:12'),(2,'Kenchie Terante','admin@fu.edu.ph','admin',NULL,'$2b$12$c8vA1tfnL.JQGWsHuahepukT6/UGD41npxdErvEsNFlsGr7d4Rxwq','Administrator','Operations Office',NULL,'2026-07-18 22:18:46'),(3,'Janitorial Staff','janitorial@fuubra.local','janitorial','10001','$2y$10$cuPkb/3WE.cLxmjHb9a6BeFYvyULuPzinBL66wYgWHhGgMzjWqZpe','Janitorial','Housekeeping',NULL,'2026-08-02 04:02:37'),(4,'Maintenance Staff','maintenance@fuubra.local','maintenance','10002','$2y$10$M0HDGngyE9z7G29NvC/RXOJuV5Mpi0DYXAUIIktCgnrUG2yJceQ3a','Maintenance','Facilities',NULL,'2026-08-02 04:02:37'),(5,'Tools and Equipment Staff','tools@fuubra.local','tools','10003','$2y$10$ahDQ08Uy1V.CPnB8cRkY4uWCr5d3GJYfDHsiVdOouU42NBpcKuiXa','Tools','Logistics',NULL,'2026-08-02 04:02:37'),(6,'Inspection Staff','inspection@fuubra.local','inspection','10004','$2y$10$drMP7HFTFHlkyJpVavI0d.2pEfuLSQvDzS2PB1urNkDg04xW7ze1e','Inspection','Operations',NULL,'2026-08-02 04:02:37');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_name` varchar(255) NOT NULL,
  `plate_no` varchar(50) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `gps_status` enum('Online','Offline') NOT NULL DEFAULT 'Offline',
  `inspection_status` enum('Completed','Due Soon','Expired') NOT NULL DEFAULT 'Due Soon',
  `availability` enum('Available','In Use','Maintenance','Reserved','Inactive') NOT NULL DEFAULT 'Available',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_no` (`plate_no`),
  KEY `driver_id` (`driver_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_vehicle_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vehicle_driver` FOREIGN KEY (`driver_id`) REFERENCES `personnel` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (2,'Click25','466','Motorcycle',5,5,'Online','Due Soon','In Use',0,NULL,'2026-07-26 21:52:08','2026-07-28 12:26:12','2026-07-27 05:52:09'),(3,'Mitsubishi','90HJI87','4 Wheels',14,5,'Offline','Completed','Available',0,NULL,'2026-07-27 06:23:47','2026-07-27 06:23:47','2026-07-27 14:23:47'),(5,'Yamaha','4567HUJI','Automatic Car ',14,7,'Online','Completed','In Use',0,NULL,'2026-07-29 17:07:42','2026-07-29 17:07:42','2026-07-30 01:07:42');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'fu_ubra'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-02  5:06:09
