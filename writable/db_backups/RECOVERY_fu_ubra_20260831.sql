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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,2,'Personnel Monitoring','Assigned John Doe to Job Order JO-2026-002','2026-08-29 22:51:45');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aircon_checklist_items`
--

DROP TABLE IF EXISTS `aircon_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aircon_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aircon_unit_id` int(11) NOT NULL,
  `task_name` varchar(150) NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aircon_unit_id` (`aircon_unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aircon_checklist_items`
--

LOCK TABLES `aircon_checklist_items` WRITE;
/*!40000 ALTER TABLE `aircon_checklist_items` DISABLE KEYS */;
INSERT INTO `aircon_checklist_items` VALUES (15,3,'Clean or replace air filter',1,'2026-07-04 02:00:00'),(16,3,'Check refrigerant level',1,'2026-07-04 05:00:00'),(17,3,'Inspect drainage line',1,'2026-07-04 01:00:00'),(18,3,'Test thermostat / airflow',1,'2026-07-04 04:00:00'),(19,3,'Clean condenser coils',1,'2026-07-04 05:00:00'),(20,3,'Check electrical connections',1,'2026-07-04 01:00:00'),(21,3,'Record unit temperature output',0,NULL),(22,4,'Clean or replace air filter',1,'2026-07-29 02:00:00'),(23,4,'Check refrigerant level',1,'2026-07-29 04:00:00'),(24,4,'Inspect drainage line',1,'2026-07-29 05:00:00'),(25,4,'Test thermostat / airflow',1,'2026-07-29 05:00:00'),(26,4,'Clean condenser coils',1,'2026-07-29 01:00:00'),(27,4,'Check electrical connections',1,'2026-07-29 01:00:00'),(28,4,'Record unit temperature output',0,NULL),(29,5,'Clean or replace air filter',1,'2026-07-13 01:00:00'),(30,5,'Check refrigerant level',1,'2026-07-13 03:00:00'),(31,5,'Inspect drainage line',1,'2026-07-13 05:00:00'),(32,5,'Test thermostat / airflow',0,NULL),(33,5,'Clean condenser coils',0,NULL),(34,5,'Check electrical connections',0,NULL),(35,5,'Record unit temperature output',0,NULL),(36,6,'Clean or replace air filter',1,'2026-07-13 01:00:00'),(37,6,'Check refrigerant level',1,'2026-07-13 03:00:00'),(38,6,'Inspect drainage line',1,'2026-07-13 05:00:00'),(39,6,'Test thermostat / airflow',1,'2026-07-13 05:00:00'),(40,6,'Clean condenser coils',1,'2026-07-13 05:00:00'),(41,6,'Check electrical connections',1,'2026-07-13 04:00:00'),(42,6,'Record unit temperature output',0,NULL),(43,7,'Clean or replace air filter',1,'2026-07-17 04:00:00'),(44,7,'Check refrigerant level',0,NULL),(45,7,'Inspect drainage line',0,NULL),(46,7,'Test thermostat / airflow',0,NULL),(47,7,'Clean condenser coils',0,NULL),(48,7,'Check electrical connections',0,NULL),(49,7,'Record unit temperature output',0,NULL),(50,8,'Clean or replace air filter',1,'2026-06-08 04:00:00'),(51,8,'Check refrigerant level',1,'2026-06-08 03:00:00'),(52,8,'Inspect drainage line',1,'2026-06-08 03:00:00'),(53,8,'Test thermostat / airflow',1,'2026-06-08 01:00:00'),(54,8,'Clean condenser coils',1,'2026-06-08 04:00:00'),(55,8,'Check electrical connections',1,'2026-06-08 03:00:00'),(56,8,'Record unit temperature output',0,NULL),(57,9,'Clean or replace air filter',1,'2026-06-21 03:00:00'),(58,9,'Check refrigerant level',1,'2026-06-21 03:00:00'),(59,9,'Inspect drainage line',1,'2026-06-21 04:00:00'),(60,9,'Test thermostat / airflow',1,'2026-06-21 02:00:00'),(61,9,'Clean condenser coils',1,'2026-06-21 04:00:00'),(62,9,'Check electrical connections',1,'2026-06-21 02:00:00'),(63,9,'Record unit temperature output',0,NULL),(64,10,'Clean or replace air filter',1,'2026-06-15 01:00:00'),(65,10,'Check refrigerant level',1,'2026-06-15 05:00:00'),(66,10,'Inspect drainage line',1,'2026-06-15 02:00:00'),(67,10,'Test thermostat / airflow',0,NULL),(68,10,'Clean condenser coils',0,NULL),(69,10,'Check electrical connections',0,NULL),(70,10,'Record unit temperature output',0,NULL);
/*!40000 ALTER TABLE `aircon_checklist_items` ENABLE KEYS */;
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
  `floor` varchar(30) NOT NULL DEFAULT 'Ground Floor',
  `unit_name` varchar(120) NOT NULL,
  `last_cleaning` date DEFAULT NULL,
  `next_schedule` date DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT 'Operational',
  `assigned_tech` varchar(100) DEFAULT NULL,
  `installed_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aircon_units`
--

LOCK TABLES `aircon_units` WRITE;
/*!40000 ALTER TABLE `aircon_units` DISABLE KEYS */;
INSERT INTO `aircon_units` VALUES (3,'College of Education Building','Ground Floor','AC-EDU-G1','2026-07-04','2026-10-02','Operational','Cardo Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(4,'College of Education Building','2nd Floor','AC-EDU-2F','2026-07-29','2026-10-27','Operational','Cardo Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(5,'College of Business Economics and Accountancy','Ground Floor','AC-BEA-G1','2026-07-13','2026-10-11','Needs Cleaning','Fernando Reyes',NULL,'2026-08-04 00:19:27',NULL),(6,'College of Art & Sciences Building','2nd Floor','AC-ART-2F','2026-07-13','2026-10-11','Operational','Remedios Mendoza',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(7,'College of Art & Sciences Building','3rd Floor','AC-ART-3F','2026-07-17','2026-10-15','Not Working','Remedios Mendoza',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(8,'University Library','Ground Floor','AC-LIB-G1','2026-06-08','2026-09-06','Operational','Josefa Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(9,'Administration Building','Ground Floor','AC-ADM-G1','2026-06-21','2026-09-19','Operational','Cardo Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(10,'Administration Building','2nd Floor','AC-ADM-2F','2026-06-15','2026-09-13','Needs Cleaning','Fernando Reyes',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow_records`
--

LOCK TABLES `borrow_records` WRITE;
/*!40000 ALTER TABLE `borrow_records` DISABLE KEYS */;
INSERT INTO `borrow_records` VALUES (1,2,'Pedro Penduko','IT Support','2026-07-29','2026-08-05','Borrowed','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(2,3,'Mina Santos','Housekeeping','2026-07-20','2026-07-27','Returned','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(3,4,'sherina Banosong','Administration','2026-06-15','2026-06-22','Returned','2026-07-29 00:29:06',1,'2026-07-01 09:00:00','For Disposal',NULL,NULL,NULL,'2026-07-29 00:29:06'),(4,5,'Dr. Helen Peralta','College of IT','2026-07-28','2026-08-04','Borrowed','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),(5,6,'Armand Perez','Facilities','2026-05-10','2026-05-17','Returned','2026-07-29 00:29:06',1,'2026-07-15 14:00:00','Disposed','2026-07-15 00:00:00',NULL,NULL,'2026-07-29 00:29:06'),(6,7,'Sonia G. Ramirez','Facilities','2026-07-29','2026-08-02','Borrowed','2026-07-29 00:29:06',0,NULL,'For Disposal',NULL,NULL,NULL,'2026-07-28 19:26:59'),(7,2,'Rico Dela Cruz','Facilities','2026-07-25','2026-08-01','Borrowed','2026-07-29 00:42:00',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:42:00'),(8,3,'Timothy Eraham','Housekeeping','2026-04-10','2026-04-17','Returned','2026-07-29 00:42:00',1,'2026-06-01 10:00:00','None',NULL,NULL,NULL,'2026-07-29 00:42:00'),(9,7,'Juan dela beto','Facilities','2026-03-01','2026-03-08','Returned','2026-07-29 00:42:00',1,'2026-06-20 09:00:00','Disposed','2026-06-20 00:00:00',NULL,NULL,'2026-07-29 00:42:00'),(10,55,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:34:15',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 19:34:15'),(11,55,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:38:32',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 20:10:55'),(12,55,'Sherina Banosong','Logistics','2026-08-02','2026-08-09','Returned','2026-08-02 09:42:30',0,NULL,'None',NULL,NULL,NULL,'2026-08-02 09:42:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumable_inventory`
--

LOCK TABLES `consumable_inventory` WRITE;
/*!40000 ALTER TABLE `consumable_inventory` DISABLE KEYS */;
INSERT INTO `consumable_inventory` VALUES (1,'Floor Cleaner (Pine)','Cleaning Agent','Liters',83.00,5.00,'2026-08-30','2026-07-19 05:37:22','2026-08-30 03:29:17'),(2,'Toilet Bowl Cleaner','Cleaning Agent','Bottles',22.00,3.00,'2026-08-04','2026-07-19 05:37:22','2026-08-04 13:48:40'),(3,'Trash Liners (Large)','Disposable','Rolls',4.00,5.00,'2025-07-05','2026-07-19 05:37:22',NULL),(4,'Mop Heads','Tools','Pieces',6.00,3.00,'2025-07-01','2026-07-19 05:37:22',NULL),(5,'Disinfectant Spray','Cleaning Agent','Bottles',2.00,4.00,'2025-06-28','2026-07-19 05:37:22',NULL),(6,'Tissue Paper (Rolls)','Disposable','Rolls',30.00,10.00,'2025-07-12','2026-07-19 05:37:22',NULL),(7,'Liquid Hand Soap','Cleaning Agent','Liters',3.00,4.00,'2025-07-09','2026-07-19 05:37:22',NULL),(8,'Brooms','Tools','Pieces',12.00,4.00,'2025-06-15','2026-07-19 05:37:22',NULL),(9,'Glass Cleaner (Window Spray)','Cleaning Agent','Bottles',0.00,4.00,'2025-06-20','2026-08-03 20:43:20',NULL),(10,'Trash Bag','Disposable','Rolls',10.00,10.00,'2026-08-29','2026-08-29 23:18:48',NULL);
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
-- Table structure for table `document_requirement_types`
--

DROP TABLE IF EXISTS `document_requirement_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_requirement_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_requirement_types`
--

LOCK TABLES `document_requirement_types` WRITE;
/*!40000 ALTER TABLE `document_requirement_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_requirement_types` ENABLE KEYS */;
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
  `floor` varchar(30) NOT NULL DEFAULT 'Ground Floor',
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fire_extinguishers`
--

LOCK TABLES `fire_extinguishers` WRITE;
/*!40000 ALTER TABLE `fire_extinguishers` DISABLE KEYS */;
INSERT INTO `fire_extinguishers` VALUES (28,'FE-7FC697','CO2','College of Education Building','Ground Floor',10.0,'2026-08-03','2026-10-10','New',2026,NULL,NULL,NULL,'2026-08-02 23:05:23','2026-08-04 05:07:03'),(29,'FE-A3A248','CO2','College of Art & Sciences Building','Ground Floor',20.0,'2026-07-15','2027-01-01','New',2026,NULL,NULL,NULL,'2026-08-02 23:12:49','2026-08-04 05:07:03'),(30,'FE-6E7EF8','Dry Chemical','Executive House','Ground Floor',2.0,'2026-08-31','2027-08-04','New',2026,NULL,NULL,NULL,'2026-08-02 23:24:17','2026-08-04 05:07:03'),(31,'FE-14C453','CO2','Animation Lab / ROTC Office','Ground Floor',3.0,'2026-11-30','2027-08-04','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-02 23:26:39','2026-08-04 05:07:03'),(32,'FE-C5AED1','CO2','College of Business Economics and Accountancy','Ground Floor',5.0,'2026-10-12','2027-08-04','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-02 23:28:26','2026-08-02 23:30:48'),(33,'FE-E368E2','CO2','College of Law Building','Ground Floor',3.0,'2026-09-25','2027-08-06','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-02 23:49:02','2026-08-04 05:07:03'),(34,'FE-B76F14','CO2','College of Education Building','2nd Floor',10.0,'2026-06-13','2027-06-13','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 00:19:26','2026-08-04 05:07:03'),(35,'FE-EEB8BB','Dry Chemical','College of Education Building','3rd Floor',5.0,'2026-04-05','2027-04-05','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 00:19:26','2026-08-04 05:07:03'),(36,'FE-7E1DDD','CO2','College of Business Economics and Accountancy','2nd Floor',10.0,'2026-07-05','2027-07-05','Refillable',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 00:19:26',NULL),(37,'FE-A9AE7D','Foam','College of Art & Sciences Building','2nd Floor',6.0,'2026-01-30','2027-01-30','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 00:19:26','2026-08-04 05:07:03'),(38,'FE-B8C6CB','CO2','College of Art & Sciences Building','3rd Floor',10.0,'2026-05-13','2027-05-13','New',2026,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),(39,'FE-84A046','Dry Chemical','University Cafeteria, Bookstore, Sewing','Ground Floor',5.0,'2026-02-12','2027-02-12','New',2026,'Cardo Garcia',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(40,'FE-74B590','CO2','University Library','Ground Floor',10.0,'2026-02-23','2027-02-23','New',2025,NULL,NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(41,'FE-B2D5A3','CO2','University Library','2nd Floor',6.0,'2026-03-10','2027-03-10','Refillable',2023,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(42,'FE-C0E3F2','Wet Chemical','Guest House','Ground Floor',3.0,'2026-02-14','2027-02-14','New',2026,'Cardo Garcia',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(43,'FE-1D9DB5','Wet Chemical','HRM Kitchen','Ground Floor',6.0,'2026-02-21','2027-02-21','New',2025,'Sherina Banosong',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(44,'FE-9F925F','CO2','LG Sinco Computer Center Building','Ground Floor',10.0,'2026-01-29','2027-01-29','New',2026,NULL,NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(45,'FE-6D0A41','CO2','LG Sinco Computer Center Building','2nd Floor',10.0,'2026-03-12','2027-03-12','New',2026,NULL,NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(46,'FE-EAD4E5','Dry Chemical','Sofia Soller Sinco Hall','Ground Floor',6.0,'2026-06-17','2027-06-17','New',2024,'Cardo Garcia',NULL,NULL,'2026-08-04 03:43:30',NULL),(47,'FE-79594D','Dry Chemical','Art & Science Laboratories / Audio Visual Rooms','Ground Floor',5.0,'2026-02-27','2027-02-27','Defective',2021,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(48,'FE-85BE4D','CO2','College of Nursing','Ground Floor',10.0,'2026-02-26','2027-02-26','New',2026,'Fernando Reyes',NULL,NULL,'2026-08-04 03:43:30',NULL),(49,'FE-B4766A','Foam','College of Nursing','2nd Floor',6.0,'2026-03-02','2027-03-02','New',2025,'Sherina Banosong',NULL,NULL,'2026-08-04 03:43:30',NULL),(50,'FE-BE8D98','CO2','Administration Building','Ground Floor',10.0,'2026-07-09','2027-07-09','New',2026,'Cardo Garcia',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(51,'FE-4FBD01','Dry Chemical','Administration Building','2nd Floor',5.0,'2026-06-24','2027-06-24','Refillable',2022,'Sherina Banosong',NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(52,'FE-0C362A','Dry Chemical','Registrar\'s Office','Ground Floor',3.0,'2025-12-13','2026-12-13','New',2025,NULL,NULL,NULL,'2026-08-04 03:43:30','2026-08-04 05:07:03'),(53,'FE-2F7B1B','CO2','Old College of Industrial Engineering and Technology','Ground Floor',10.0,'2026-04-14','2026-07-21','Missing',2020,'Fernando Reyes',NULL,NULL,'2026-08-04 03:43:30',NULL),(54,'FE-B1541D','CO2','Parade Ground','Ground Floor',6.0,'2025-08-15','2026-08-15','New',2025,'Sherina Banosong',NULL,NULL,'2026-08-04 04:47:13',NULL),(55,'FE-FC7DFC','Dry Chemical','Guest House','Ground Floor',5.0,'2025-08-08','2026-08-08','New',2024,'Sherina Banosong',NULL,NULL,'2026-08-04 04:47:13',NULL),(56,'FE-E0C2BE','CO2','Executive House','Ground Floor',10.0,'2025-09-17','2026-09-17','Refillable',2023,'Sherina Banosong',NULL,NULL,'2026-08-04 04:47:13',NULL),(57,'FE-D6F5E7','CO2','College of Agriculture and SIE','Ground Floor',10.0,'2026-07-24','2027-07-24','New',2026,'Sherina Banosong',NULL,NULL,'2026-08-04 05:09:24',NULL),(58,'FE-DF7293','Dry Chemical','College of Agriculture and SIE','2nd Floor',5.0,'2026-01-18','2027-01-18','New',2025,'Sherina Banosong',NULL,NULL,'2026-08-04 05:09:24',NULL),(59,'FE-05FF77','CO2','College of Agriculture and SIE','3rd Floor',10.0,'2025-12-19','2026-12-19','Refillable',2022,NULL,NULL,NULL,'2026-08-04 05:09:24',NULL),(60,'FE-33C25D','Dry Chemical','Museo de Vicente','Ground Floor',3.0,'2026-03-12','2027-03-12','New',2025,'Sherina Banosong',NULL,NULL,'2026-08-04 05:09:24',NULL),(61,'FE-01F04A','Wet Chemical','Bunk House','Ground Floor',3.0,'2026-05-04','2027-05-04','New',2026,NULL,NULL,NULL,'2026-08-04 05:09:24',NULL),(62,'FE-072B05','CO2','Electric Pump House','Ground Floor',6.0,'2026-07-10','2027-07-10','New',2024,'Maisie Therese Tigmo',NULL,NULL,'2026-08-04 05:09:24',NULL),(63,'FE-82161C','Dry Chemical','Business and Finance Office','Ground Floor',5.0,'2026-02-25','2027-02-25','New',2026,NULL,NULL,NULL,'2026-08-04 05:09:24',NULL);
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
  `priority` enum('Routine','Urgent') NOT NULL DEFAULT 'Routine',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_assigned`),
  KEY `idx_zone` (`assigned_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_assignments`
--

LOCK TABLES `janitorial_assignments` WRITE;
/*!40000 ALTER TABLE `janitorial_assignments` DISABLE KEYS */;
INSERT INTO `janitorial_assignments` VALUES (1,'Bautista, M.','Admin Building','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(2,'Dizon, L.','Library','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(3,'Fernandez, G.','Science Building','06:00:00','14:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(4,'Hernandez, K.','Gymnasium','05:00:00','13:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(5,'Ignacio, P.','Canteen','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(6,'Javier, C.','Engineering','08:00:00','16:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(7,'Lacson, A.','CCS Building','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(8,'Mendez, R.','Clinic','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),(10,'Janitorial Staff','CCS Building','08:00:00','17:00:00','2026-08-30','Active','Routine','2026-08-03 15:42:45'),(11,'Janitorial Staff','Admin Building','08:00:00','17:00:00','2026-08-06','Active','Routine','2026-08-04 07:43:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_tasks`
--

LOCK TABLES `janitorial_tasks` WRITE;
/*!40000 ALTER TABLE `janitorial_tasks` DISABLE KEYS */;
INSERT INTO `janitorial_tasks` VALUES (1,1,'Sweep & mop corridors',1,'2025-07-18 07:30:00','2026-07-19 05:37:22'),(2,1,'Clean restrooms — Floor 1',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(3,1,'Empty trash bins',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(4,1,'Wipe window sills',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(5,1,'Mop main lobby',1,'2025-07-18 09:30:00','2026-07-19 05:37:22'),(6,1,'Replenish soap & tissue',1,'2025-07-18 10:00:00','2026-07-19 05:37:22'),(7,1,'Clean comfort rooms — Floor 2',0,NULL,'2026-07-19 05:37:22'),(8,1,'General sanitizing',0,NULL,'2026-07-19 05:37:22'),(9,2,'Dust bookshelves',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(10,2,'Vacuum reading area',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(11,2,'Mop entrance',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(12,2,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(13,2,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(14,2,'Wipe computer tables',0,NULL,'2026-07-19 05:37:22'),(15,3,'Sweep lab corridors',1,'2025-07-18 06:30:00','2026-07-19 05:37:22'),(16,3,'Mop stairs',0,NULL,'2026-07-19 05:37:22'),(17,3,'Empty lab trash',0,NULL,'2026-07-19 05:37:22'),(18,3,'Sanitize lab benches',0,NULL,'2026-07-19 05:37:22'),(19,3,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(20,4,'Sweep gym floor',1,'2025-07-18 05:30:00','2026-07-19 05:37:22'),(21,4,'Mop court',1,'2025-07-18 06:00:00','2026-07-19 05:37:22'),(22,4,'Clean locker rooms',1,'2025-07-18 06:45:00','2026-07-19 05:37:22'),(23,4,'Empty trash bins',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(24,5,'Wipe dining tables',1,'2025-07-18 07:00:00','2026-07-19 05:37:22'),(25,5,'Sweep floor',1,'2025-07-18 07:20:00','2026-07-19 05:37:22'),(26,5,'Mop canteen floor',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(27,5,'Clean restrooms',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(28,5,'Empty grease traps',0,NULL,'2026-07-19 05:37:22'),(29,5,'Sanitize counter tops',0,NULL,'2026-07-19 05:37:22'),(30,5,'Replace trash liners',0,NULL,'2026-07-19 05:37:22'),(31,6,'Sweep corridors',1,'2025-07-18 08:10:00','2026-07-19 05:37:22'),(32,6,'Mop workshop floor',0,NULL,'2026-07-19 05:37:22'),(33,6,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(34,6,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(35,6,'Wipe notice boards',0,NULL,'2026-07-19 05:37:22'),(36,6,'Sanitize door handles',0,NULL,'2026-07-19 05:37:22'),(37,7,'Sweep corridors',1,'2025-07-18 07:10:00','2026-07-19 05:37:22'),(38,7,'Mop server room hallway',1,'2025-07-18 07:35:00','2026-07-19 05:37:22'),(39,7,'Clean restrooms',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(40,7,'Wipe workstations',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(41,7,'Empty trash',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(42,8,'Sanitize consultation room',1,'2025-07-18 07:05:00','2026-07-19 05:37:22'),(43,8,'Mop clinic floor',1,'2025-07-18 07:30:00','2026-07-19 05:37:22'),(44,8,'Clean restroom',1,'2025-07-18 07:55:00','2026-07-19 05:37:22'),(45,8,'Replace biohazard bags',1,'2025-07-18 08:20:00','2026-07-19 05:37:22'),(47,10,'Scheduled Cleaning: CCS Building',0,NULL,'2026-08-03 15:42:45'),(48,11,'Scheduled Cleaning: Admin Building',0,NULL,'2026-08-04 07:43:56');
/*!40000 ALTER TABLE `janitorial_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_orders`
--

DROP TABLE IF EXISTS `job_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_order_number` varchar(40) NOT NULL,
  `job_order_title` varchar(150) NOT NULL,
  `project_name` varchar(150) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `assignment_location` varchar(150) DEFAULT NULL,
  `personnel_required` int(11) NOT NULL DEFAULT 1,
  `supervisor` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_order_number` (`job_order_number`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_orders`
--

LOCK TABLES `job_orders` WRITE;
/*!40000 ALTER TABLE `job_orders` DISABLE KEYS */;
INSERT INTO `job_orders` VALUES (1,'JO-2026-001','Campus Landscaping Improvement','Grounds Beautification Phase 2','Construction Worker',1,'Main Campus Grounds',4,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE','Ongoing landscaping and grounds improvement across the main campus.',NULL,0,'2026-08-29 22:31:31',NULL),(2,'JO-2026-002','College of Nursing Building Renovation','CoN Facility Upgrade','Carpenter',1,'College of Nursing',6,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE','Interior renovation works for the College of Nursing building.',NULL,0,'2026-08-29 22:31:31',NULL),(3,'JO-2026-003','University Library Electrical Rewiring','Library Systems Upgrade','Maintenance Technician',1,'University Library',3,'Col. Arthur Miller','2026-03-01','2026-07-31','ACTIVE','Full electrical rewiring of the University Library building.',NULL,0,'2026-08-29 22:31:31',NULL),(4,'JO-2026-004','Perimeter Fence Painting Project','Campus Exterior Maintenance','Carpenter',1,'Campus Perimeter',2,'Dr. Helen Peralta','2026-01-10','2026-03-10','COMPLETED','Repainting of the campus perimeter fence and gates.',NULL,0,'2026-08-29 22:31:31',NULL),(5,'JO-2026-005','New Gymnasium Construction Support','Gymnasium Expansion','Construction Foreman',1,'Gymnasium Site',5,'Remedios Ocampo','2026-10-01','2027-04-01','PENDING','Support labor for the new gymnasium construction project.',NULL,0,'2026-08-29 22:31:31',NULL);
/*!40000 ALTER TABLE `job_orders` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (15,'2024-01-01-000001','App\\Database\\Migrations\\CreateUsersTable','default','App',1787709579,1),(16,'2024-01-01-000002','App\\Database\\Migrations\\CreateDepartmentsTable','default','App',1787709579,1),(17,'2024-01-01-000003','App\\Database\\Migrations\\CreatePersonnelTable','default','App',1787709579,1),(18,'2024-01-01-000004','App\\Database\\Migrations\\CreateVehiclesTable','default','App',1787709579,1),(19,'2024-01-01-000005','App\\Database\\Migrations\\CreateTravelRequestsTable','default','App',1787709579,1),(20,'2024-01-01-000006','App\\Database\\Migrations\\CreateGpsLogsTable','default','App',1787709579,1),(21,'2024-01-01-000007','App\\Database\\Migrations\\CreateToolsTable','default','App',1787709579,1),(22,'2024-01-01-000008','App\\Database\\Migrations\\CreateNotificationsTable','default','App',1787709579,1),(23,'2024-01-01-000009','App\\Database\\Migrations\\CreateBorrowsTable','default','App',1787709579,1),(24,'2024-01-01-000010','App\\Database\\Migrations\\CreateReturnsTable','default','App',1787709579,1),(25,'2024-01-01-000011','App\\Database\\Migrations\\CreatePredictionsTable','default','App',1787709579,1),(26,'2024-01-01-000012','App\\Database\\Migrations\\CreateActivityLogsTable','default','App',1787709579,1),(27,'2024-01-01-000013','App\\Database\\Migrations\\CreateReportsTable','default','App',1787709579,1),(28,'2024-01-01-000014','App\\Database\\Migrations\\CreateJanitorialLogsTable','default','App',1787709579,1),(29,'2024-01-01-000016','App\\Database\\Migrations\\CreateJobOrdersTable','default','App',1787709604,2),(30,'2024-01-01-000017','App\\Database\\Migrations\\CreatePersonnelAssignmentsTable','default','App',1787709604,2),(31,'2024-01-01-000018','App\\Database\\Migrations\\CreatePersonnelContractsTable','default','App',1787709604,2),(32,'2024-01-01-000019','App\\Database\\Migrations\\CreateDocumentRequirementTypesTable','default','App',1787709604,2),(33,'2024-01-01-000020','App\\Database\\Migrations\\CreatePersonnelDocumentsTable','default','App',1787709604,2),(34,'2024-01-01-000021','App\\Database\\Migrations\\AddEmploymentTypeToPersonnelTable','default','App',1787709604,2);
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
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'Vehicle Inspection','Routine vehicle health compliance check is scheduled tomorrow. System: FU-UBRA Fleet Telematics Service Engine','Operations Team','CRITICAL','Verified','system',1,'2026-07-28 19:18:13','2026-07-29 02:56:09'),(2,'Travel Reminder','Driver Mark has an assigned dispatch schedule tomorrow. Destination: Dumaguete City Top Nail Territory.','Driver Mark','MODERATE','Notified','email',1,'2026-07-28 19:18:17','2026-07-28 02:56:09'),(3,'Air-Con Cleaning','Air Conditioner Building A: A preventive system maintenance starts in 2 days. Facilities Dept.','Facilities Dept.','ROUTINE','Assigned','system',1,'2026-07-27 02:56:09','2026-07-27 02:56:09'),(4,'Janitorial Assignment','Weekly deep disinfection assignment schedule for Team B begins tomorrow. Team B Duty.','Team B','ROUTINE','Assigned','system',1,'2026-07-26 02:56:09','2026-07-26 02:56:09'),(5,'Inventory Low Stock','Critical spare parts and engine filters are low on spare parts inventory. Notify: Open Calendar Events.','Office Supplies','CRITICAL','Ordered','system',1,'2026-07-28 19:18:20','2026-07-29 02:56:09'),(6,'Vehicle Expiry','Registration for Utility Truck-04 has been safely completed earlier this week. Reference: FU Comms House Print Document.','Fleet Admin','ROUTINE','Reviewed','email',1,'2026-07-24 02:56:09','2026-07-24 02:56:09'),(8,'Fire Extinguisher Installed','New CO2 fire extinguisher (FE-E368E2) installed at College of Law building by Maisie Therese Tigmo.','Safety Team','ROUTINE','Verified','system',1,'2026-08-02 16:23:57','2026-08-02 15:49:02'),(12,'Maintenance Scheduled','Clean Urgent — WO-005 logged for University library on Aug 5, 2026.','Maintenance Team','ROUTINE','Acknowledged','system',1,'2026-08-02 17:29:52','2026-08-02 17:24:41'),(14,'Cleaning Scheduled','Cleaning scheduled for CCS Building on Aug 30, 2026. Notes: pleaseclean i!','Janitorial Staff','ROUTINE','Assigned','system',1,'2026-08-04 01:49:11','2026-08-03 15:42:45'),(15,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Scheduled','system',1,'2026-08-04 01:49:13','2026-08-04 01:48:59'),(16,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Assigned','system',1,'2026-08-04 01:49:08','2026-08-04 01:48:59'),(17,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Assigned','system',1,'2026-08-04 01:49:10','2026-08-04 01:48:59'),(18,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 03:07:33','2026-08-04 01:49:24'),(19,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 02:06:57','2026-08-04 01:49:24'),(20,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 02:06:55','2026-08-04 01:49:24'),(21,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 03:07:27','2026-08-04 02:22:49'),(22,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 03:07:30','2026-08-04 02:22:49'),(23,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:14','2026-08-04 03:15:42'),(24,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:47:11','2026-08-04 03:15:42'),(25,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:47:09','2026-08-04 03:15:42'),(26,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:18','2026-08-04 03:49:51'),(27,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:16','2026-08-04 03:49:51'),(28,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:24','2026-08-04 04:55:00'),(29,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:23','2026-08-04 04:55:00'),(30,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:20','2026-08-04 04:55:00'),(31,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:26','2026-08-04 05:11:49'),(32,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:43','2026-08-04 05:47:50'),(33,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:42','2026-08-04 05:47:50'),(34,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:39','2026-08-04 05:47:50'),(35,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:38','2026-08-04 05:47:50'),(36,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:36','2026-08-04 05:47:50'),(37,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:32','2026-08-04 05:47:50'),(38,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:31','2026-08-04 05:47:50'),(39,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:48:29','2026-08-04 05:47:50'),(40,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:48:28','2026-08-04 05:47:50'),(41,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:46','2026-08-04 05:48:52'),(42,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:43','2026-08-04 05:48:52'),(43,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:41','2026-08-04 05:48:52'),(44,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:37','2026-08-04 05:48:52'),(45,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:35','2026-08-04 05:48:52'),(46,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:32','2026-08-04 05:48:52'),(47,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:30','2026-08-04 05:48:52'),(48,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:49:02','2026-08-04 05:48:52'),(49,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:49:00','2026-08-04 05:48:52'),(50,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:45:00','2026-08-04 07:35:32'),(51,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:58','2026-08-04 07:35:32'),(52,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:56','2026-08-04 07:35:32'),(53,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:54','2026-08-04 07:35:32'),(54,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:53','2026-08-04 07:35:32'),(55,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:51','2026-08-04 07:35:32'),(56,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:50','2026-08-04 07:35:32'),(57,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 07:44:48','2026-08-04 07:35:32'),(58,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 07:44:46','2026-08-04 07:35:32'),(59,'Cleaning Scheduled','Cleaning scheduled for Admin Building on Aug 6, 2026.','Janitorial Staff','ROUTINE','Assigned','system',1,'2026-08-04 07:45:02','2026-08-04 07:43:56'),(63,'Trip Ticket Request','Trip ticket TR-20260804-0001 requested by Pedro Penduko to Tanjay asaggra on Aug 5, 2026.','Operations Office','MODERATE','Approved','system',1,'2026-08-04 09:23:16','2026-08-04 09:23:05'),(64,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 12:15:45','2026-08-04 09:25:16'),(65,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:08:55','2026-08-04 09:25:16'),(66,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:08:58','2026-08-04 09:25:16'),(67,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:00','2026-08-04 09:25:16'),(68,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:03','2026-08-04 09:25:16'),(69,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:07','2026-08-04 09:25:16'),(70,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:12','2026-08-04 09:25:16'),(71,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:09:14','2026-08-04 09:25:16'),(72,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:09:17','2026-08-04 09:25:16'),(73,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:06:52','2026-08-04 12:16:07'),(77,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:09','2026-08-04 13:18:53'),(78,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:06','2026-08-04 13:18:53'),(79,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:03','2026-08-04 13:18:53'),(80,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:00','2026-08-04 13:18:53'),(81,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:57','2026-08-04 13:18:53'),(82,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:52','2026-08-04 13:18:53'),(83,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:49','2026-08-04 13:18:53'),(84,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:33:46','2026-08-04 13:18:53'),(85,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:33:43','2026-08-04 13:18:53'),(86,'Trip Ticket Request','Trip ticket TR-20260804-0002 requested by Timothy Eraham to Bais City on Aug 5, 2026.','Operations Office','MODERATE','Acknowledged','system',1,'2026-08-04 13:34:11','2026-08-04 13:21:04'),(88,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:49','2026-08-04 13:38:42'),(89,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:52','2026-08-04 13:38:42'),(90,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:56','2026-08-04 13:38:42'),(91,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:06:07','2026-08-04 13:38:42'),(92,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:45','2026-08-04 13:38:42'),(93,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:50','2026-08-04 13:38:42'),(94,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:08:23','2026-08-04 13:38:42'),(95,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-21 13:08:26','2026-08-04 13:38:42'),(96,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-21 13:08:30','2026-08-04 13:38:42'),(97,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 12:19:49','2026-08-17 23:03:12'),(98,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:00','2026-08-17 23:03:12'),(99,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:41','2026-08-17 23:03:12'),(100,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 12:51:47','2026-08-21 12:34:32'),(101,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),(102,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),(103,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),(104,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 17:56:35','2026-08-21 13:12:52'),(105,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),(106,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),(107,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),(108,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 18:24:59'),(109,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:59','2026-08-29 22:38:54'),(110,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:57','2026-08-29 22:38:54'),(111,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:56','2026-08-29 22:38:54'),(112,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:53','2026-08-29 22:38:54'),(113,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-29 22:59:35','2026-08-29 22:38:54'),(114,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(115,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(116,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(117,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(118,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(119,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(120,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(121,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),(122,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 2 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:49:03','2026-08-29 22:54:20'),(123,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:49:01','2026-08-29 22:54:20'),(124,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-29 23:21:04','2026-08-29 23:16:48'),(125,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:35','2026-08-29 23:16:48'),(126,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:33','2026-08-29 23:16:48'),(127,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:32','2026-08-29 23:16:48'),(128,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',1,'2026-08-30 01:48:04','2026-08-29 23:16:48'),(129,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:07','2026-08-29 23:16:48'),(130,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:05','2026-08-29 23:16:48'),(131,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:38','2026-08-29 23:23:51'),(132,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:49','2026-08-30 00:30:08'),(133,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:48','2026-08-30 00:30:08'),(134,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 1 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:46','2026-08-30 00:30:08'),(135,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:44','2026-08-30 00:30:08'),(136,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:41','2026-08-30 00:30:08'),(137,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:28','2026-08-30 00:30:09'),(138,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:27','2026-08-30 00:30:09'),(139,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:25','2026-08-30 00:30:09'),(140,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:23','2026-08-30 00:30:09'),(141,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:20','2026-08-30 00:30:09'),(142,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:18','2026-08-30 00:30:09'),(143,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:16','2026-08-30 00:30:09'),(144,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:14','2026-08-30 00:30:09'),(145,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:13','2026-08-30 00:30:09'),(146,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:11','2026-08-30 00:30:09'),(147,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:08'),(148,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:32','2026-08-30 01:49:08'),(149,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:25','2026-08-30 01:49:08'),(150,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:23','2026-08-30 01:49:08'),(151,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:21','2026-08-30 01:49:08'),(152,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:19','2026-08-30 01:49:08'),(153,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:17','2026-08-30 01:49:08'),(154,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(155,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(156,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(157,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(158,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(159,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),(160,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Pending','system',0,NULL,'2026-08-30 02:23:58'),(161,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),(162,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 1 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),(163,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Pending','system',0,NULL,'2026-08-30 02:23:58'),(164,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),(165,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),(166,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(167,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(168,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(169,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(170,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(171,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(172,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(173,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),(174,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59');
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
  `employment_type` varchar(20) DEFAULT 'Regular',
  `position` varchar(100) DEFAULT NULL,
  `assigned_task` varchar(150) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_id` (`emp_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
INSERT INTO `personnel` VALUES (3,'EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',3,'Regular','Safety & Security Chief','GPS Route Validation','Active',0,NULL,'2026-07-18 20:20:56'),(4,'EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',7,'Regular','Carpenter','Science Lab Cleaning','Active',0,NULL,'2026-07-18 20:20:56'),(5,'EMP-2022-071','Pedro Penduko','pedro.penduko@foundation.edu.ph',5,'Regular','IT Support','Network Deployment','Active',0,NULL,'2026-07-18 20:20:56'),(6,'EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',2,'Regular','Driver','Van-01 Dispatch','Active',0,NULL,'2026-07-18 20:20:56'),(7,'EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',2,'Regular','Senior Driver','Bus-02 Assignment','Active',0,NULL,'2026-07-18 20:20:56'),(8,'EMP-2018-009','Dr. Helen Peralta','helen.peralta@foundation.edu.ph',6,'Regular','Department Head','Trip Approvals','Active',0,NULL,'2026-07-18 20:20:56'),(9,'EMP-2023-301','sherina Banosong','sherina.banosong@foundation.edu.ph',2,'Regular','Staff','Front Office Records Filing','Active',0,NULL,'2026-07-19 02:54:16'),(14,'EMP-2024-302','Timothy Eraham','timothy.eraham@foundation.edu.ph',5,'Regular','Driver','Van-02 Dispatch','Active',0,NULL,'2026-07-26 00:20:05'),(15,'EMP-2024-303','TimothyLincon','timothy.lincon@foundation.edu.ph',1,'Regular','Janitor','Library Restroom Cleaning','Active',0,NULL,'2026-07-26 12:13:38'),(16,'EMP-2023-304','Juan dela Cruz','juan.delacruz@foundation.edu.ph',8,'Regular','Maintenance','HVAC Unit Inspection - Bldg 14','Active',0,NULL,'2026-07-27 22:20:10'),(17,'EMP-2023-305','Juan dela Beto','juan.delabeto@foundation.edu.ph',1,'Regular','Janitor','Gymnasium Floor Maintenance','Active',0,NULL,'2026-07-27 22:22:00'),(18,'EMP-2018-306','Lapu-lapu','lapu.lapu@foundation.edu.ph',5,'Regular','Janitor','Admin Lobby Floor Care','Active',0,NULL,'2026-07-28 14:03:20'),(19,'EMP-2023-307','Juan Cruz','juancruz@example.com',1,'Regular','Driver',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(20,'EMP-2023-308','Mina Santos','minasantos@example.com',1,'Regular','Janitor',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(21,'EMP-2023-309','Rico Dela Cruz','ricodelacruz@example.com',1,'JobOrder','Carpenter','Perimeter Fence Painting Project (Completed)','On Leave',0,NULL,'2026-07-28 18:33:24'),(22,'EMP-2023-310','Armand Perez','armandperez@example.com',1,'JobOrder','Maintenance','College of Nursing Building Renovation','On Leave',0,NULL,'2026-07-28 18:33:24'),(23,'EMP-2026-835','Ricardo Reyes','ricardo.reyes@foundation.edu.ph',4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(24,'EMP-2026-449','Goyo Pascual','goyo.pascual@foundation.edu.ph',4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(25,'EMP-2026-440','Cardo Manalo','cardo.manalo@foundation.edu.ph',1,'Regular','Carpenter','Cabinet Fabrication','Active',0,NULL,'2026-07-31 20:16:53'),(26,'EMP-2026-765','Rodrigo Torres','rodrigo.torres@foundation.edu.ph',8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),(27,'EMP-2026-834','Josefa Mendoza','josefa.mendoza@foundation.edu.ph',1,'JobOrder','Maintenance Technician','University Library Electrical Rewiring','Active',0,NULL,'2026-07-31 20:16:53'),(28,'EMP-2026-100','Fernando Ocampo','fernando.ocampo@foundation.edu.ph',1,'Regular','Driver','Renovation Project Lead','On Leave',0,NULL,'2026-07-31 20:16:53'),(29,'EMP-2026-786','Cardo Domingo','cardo.domingo@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(30,'EMP-2026-654','Teresa Domingo','teresa.domingo@foundation.edu.ph',1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),(31,'EMP-2026-683','Emilio Reyes','emilio.reyes@foundation.edu.ph',2,'Regular','Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(32,'EMP-2026-631','Consolacion Garcia','consolacion.garcia@foundation.edu.ph',8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),(33,'EMP-2026-243','Antonio Reyes','antonio.reyes@foundation.edu.ph',2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),(34,'EMP-2026-283','Danilo Mendoza','danilo.mendoza@foundation.edu.ph',1,'JobOrder','Lead Carpenter','College of Nursing Building Renovation','Active',0,NULL,'2026-07-31 20:16:53'),(35,'EMP-2026-134','Rizal Bautista','rizal.bautista@foundation.edu.ph',2,'Regular','Driver','Utility Truck Duty','Active',0,NULL,'2026-07-31 20:16:53'),(36,'EMP-2026-201','Remedios Ocampo','remedios.ocampo@foundation.edu.ph',1,'Regular','Physical Plant Supr.','Preventive Maintenance','Active',0,NULL,'2026-07-31 20:16:53'),(37,'EMP-2026-887','Ricardo Mendoza','ricardo.mendoza@foundation.edu.ph',1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(38,'EMP-2026-305','Fernando Salazar','fernando.salazar@foundation.edu.ph',1,'Regular','Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(39,'EMP-2026-709','Andres Navarro','andres.navarro@foundation.edu.ph',8,'Regular','Accounting Staff','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(40,'EMP-2026-953','Diego Castillo','diego.castillo@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(41,'EMP-2026-172','Isabel Aquino','isabel.aquino@foundation.edu.ph',1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),(42,'EMP-2026-738','Fernando Navarro','fernando.navarro@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(43,'EMP-2026-970','Gabriela Pascual','gabriela.pascual@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(44,'EMP-2026-949','Emilio Rivera','emilio.rivera@foundation.edu.ph',4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(45,'EMP-2026-492','Maria Mendoza','maria.mendoza@foundation.edu.ph',1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(46,'EMP-2026-603','Rizal Garcia','rizal.garcia@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(47,'EMP-2026-626','Cardo Garcia','cardo.garcia@foundation.edu.ph',1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),(48,'EMP-2026-329','Diego Fernandez','diego.fernandez@foundation.edu.ph',1,'Regular','Maintenance Technician','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(49,'EMP-2026-617','Josefa Villanueva','josefa.villanueva@foundation.edu.ph',1,'JobOrder','Construction Worker','Campus Landscaping Improvement','Active',0,NULL,'2026-07-31 20:16:53'),(50,'EMP-2026-628','Goyo Castillo','goyo.castillo@foundation.edu.ph',6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(51,'EMP-2026-735','Antonio Mendoza','antonio.mendoza@foundation.edu.ph',2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),(52,'EMP-2026-815','Josefa Garcia','josefa.garcia@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(53,'EMP-2026-844','Goyo Ramos','goyo.ramos@foundation.edu.ph',1,'JobOrder','Construction Foreman','Campus Landscaping Improvement','Inactive',0,NULL,'2026-07-31 20:16:53'),(54,'EMP-2026-737','Juan Santos','juan.santos@foundation.edu.ph',4,'Regular','Cleaning Operative','CCS Building Cleaning','Active',0,NULL,'2026-07-31 20:16:53'),(55,'EMP-2026-272','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Regular','Construction Foreman','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(56,'EMP-2026-378','Fernando Cruz','fernando.cruz@foundation.edu.ph',3,'Regular','Guard','Guard House Duty','Active',0,NULL,'2026-07-31 20:16:53'),(57,'EMP-2026-271','Pedro Reyes','pedro.reyes@foundation.edu.ph',5,'Regular','IT Support','Helpdesk Support','Active',0,NULL,'2026-07-31 20:16:53'),(58,'EMP-2026-782','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),(59,'EMP-2026-792','Jose Torres','jose.torres@foundation.edu.ph',1,'Regular','Construction Foreman','Renovation Project Lead','Active',0,NULL,'2026-07-31 20:16:53'),(60,'EMP-2026-711','Cardo Navarro','cardo.navarro@foundation.edu.ph',3,'Regular','Guard','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(61,'EMP-2026-498','Fernando Reyes','fernando.reyes@foundation.edu.ph',1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),(62,'EMP-2026-487','Manuel Domingo','manuel.domingo@foundation.edu.ph',6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),(63,'EMP-2026-392','Josefa Ramos','josefa.ramos@foundation.edu.ph',6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(64,'EMP-2026-511','Corazon Castillo','corazon.castillo@foundation.edu.ph',1,'Regular','Physical Plant Supr.','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(65,'EMP-2026-385','Andres Garcia','andres.garcia@foundation.edu.ph',3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),(66,'EMP-2026-429','Diego Manalo','diego.manalo@foundation.edu.ph',3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),(67,'EMP-2026-646','Consolacion Reyes','consolacion.reyes@foundation.edu.ph',6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),(68,'EMP-2026-525','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),(69,'EMP-2026-292','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(70,'EMP-2026-877','Danilo Villanueva','danilo.villanueva@foundation.edu.ph',4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(71,'EMP-2026-157','Josefa Manalo','josefa.manalo@foundation.edu.ph',2,'Regular','Senior Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(72,'EMP-2026-743','Isabel Castillo','isabel.castillo@foundation.edu.ph',3,'Regular','Guard','CCTV Monitoring','Active',0,NULL,'2026-07-31 20:16:53'),(73,'20211735','Maisie Therese Tigmo','maisie.tigmo@foundation.edu.ph',1,'Regular','Maintenance Technician','Unassigned','Active',0,NULL,'2026-08-02 19:14:02'),(74,'20262020','Jose Protacio Rizal Mercado y Realonzo Realonda','pepe@gmail.com',1,'Regular','Maintenance Technician','To Inspect the Aircon ','Active',0,NULL,'2026-08-03 21:13:52'),(75,'EMP-2026-901','Maria Clara Santos','maria.santos@fuubra.local',5,'Regular','IT Equipment Custodian','IT asset custody and inventory','Active',0,NULL,'2026-08-03 23:48:23'),(76,'EMP-2026-902','Engr. James Diaz','james.diaz@fuubra.local',1,'Regular','Facilities Engineer','Tools and equipment custody','Active',0,NULL,'2026-08-03 23:48:23'),(77,'EMP-2026-903','John Doe','john.doe@fuubra.local',1,'JobOrder','Facilities Staff','General facilities support','Active',0,NULL,'2026-08-03 23:48:23'),(78,'EMP-2026-701','Bayani Cruz','bayani.cruz@foundation.edu.ph',1,'JobOrder','General Laborer','Campus Landscaping Improvement','Active',0,NULL,'2026-08-29 22:31:31'),(79,'EMP-2026-702','Melinda Reyes','melinda.reyes@foundation.edu.ph',1,'JobOrder','General Laborer','Campus Landscaping Improvement','Active',0,NULL,'2026-08-29 22:31:31');
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personnel_assignments`
--

DROP TABLE IF EXISTS `personnel_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnel_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `assignment_location` varchar(150) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  `assignment_start_date` date DEFAULT NULL,
  `assignment_end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `personnel_id` (`personnel_id`),
  KEY `job_order_id` (`job_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel_assignments`
--

LOCK TABLES `personnel_assignments` WRITE;
/*!40000 ALTER TABLE `personnel_assignments` DISABLE KEYS */;
INSERT INTO `personnel_assignments` VALUES (1,49,1,'Construction Worker','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(2,53,1,'Construction Foreman','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(3,78,1,'General Laborer','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(4,79,1,'General Laborer','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(5,34,2,'Carpenter','College of Nursing',1,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(6,22,2,'Maintenance','College of Nursing',1,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(7,27,3,'Maintenance Technician','University Library',1,'Col. Arthur Miller','2026-03-01','2026-07-31','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),(8,21,4,'Carpenter','Campus Perimeter',1,'Dr. Helen Peralta','2026-01-10','2026-03-10','COMPLETED',NULL,'2026-08-29 22:31:31',NULL),(9,77,2,'Driver','ccs',NULL,'Corazon Castillo','2026-08-29','2026-08-31','ACTIVE',NULL,'2026-08-29 22:51:45','2026-08-29 22:51:45');
/*!40000 ALTER TABLE `personnel_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personnel_contracts`
--

DROP TABLE IF EXISTS `personnel_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnel_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `job_order_id` int(11) DEFAULT NULL,
  `contract_number` varchar(60) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `contract_status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `renewal_status` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `personnel_id` (`personnel_id`),
  KEY `job_order_id` (`job_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel_contracts`
--

LOCK TABLES `personnel_contracts` WRITE;
/*!40000 ALTER TABLE `personnel_contracts` DISABLE KEYS */;
INSERT INTO `personnel_contracts` VALUES (1,49,1,'CT-2026-101','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(2,53,1,'CT-2026-102','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(3,78,1,'CT-2026-103','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(4,79,1,'CT-2026-104','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(5,34,2,'CT-2026-105','2026-06-15','2026-09-15','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(6,22,2,'CT-2026-106','2026-06-15','2026-09-15','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(7,27,3,'CT-2026-107','2026-03-01','2026-07-31','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),(8,21,4,'CT-2026-108','2026-01-10','2026-03-10','COMPLETED',NULL,NULL,'2026-08-29 22:31:31',NULL),(9,77,2,NULL,'2026-08-29','2026-08-31','ACTIVE',NULL,NULL,'2026-08-29 22:51:45','2026-08-29 22:51:45');
/*!40000 ALTER TABLE `personnel_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personnel_documents`
--

DROP TABLE IF EXISTS `personnel_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnel_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `document_number` varchar(80) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `remarks` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `personnel_id` (`personnel_id`),
  KEY `document_type_id` (`document_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel_documents`
--

LOCK TABLES `personnel_documents` WRITE;
/*!40000 ALTER TABLE `personnel_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `personnel_documents` ENABLE KEYS */;
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
-- Table structure for table `refill_log`
--

DROP TABLE IF EXISTS `refill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refill_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `quantity_added` decimal(8,2) NOT NULL,
  `unit` varchar(40) NOT NULL,
  `performed_by` varchar(100) NOT NULL,
  `performed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  KEY `performed_at` (`performed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refill_log`
--

LOCK TABLES `refill_log` WRITE;
/*!40000 ALTER TABLE `refill_log` DISABLE KEYS */;
INSERT INTO `refill_log` VALUES (1,1,'Floor Cleaner (Pine)',5.00,'Liters','Kenchie Terante','2026-08-30 03:29:17');
/*!40000 ALTER TABLE `refill_log` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_records`
--

LOCK TABLES `return_records` WRITE;
/*!40000 ALTER TABLE `return_records` DISABLE KEYS */;
INSERT INTO `return_records` VALUES (1,10,55,'John Doe','2026-08-01','Good',NULL,'2026-08-02 03:34:15'),(2,11,55,'John Doe','2026-08-01','Good',NULL,'2026-08-02 04:10:55'),(3,12,55,'Sherina Banosong','2026-08-02','Good',NULL,'2026-08-02 17:42:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safety_work_orders`
--

LOCK TABLES `safety_work_orders` WRITE;
/*!40000 ALTER TABLE `safety_work_orders` DISABLE KEYS */;
INSERT INTO `safety_work_orders` VALUES (1,'WO-001','FE-ADM-03 defective — pressure gauge broken','Admin Building','Cruz, M.','Tech Valdez','High','In Progress','2025-07-10',NULL,NULL,'2026-07-19 05:37:22',NULL),(2,'WO-002','FE-ENG-02 past expiry — needs replacement','Engineering','Flores, C.','Tech Ramos','High','Issue Logged','2025-07-12',NULL,NULL,'2026-07-19 05:37:22',NULL),(3,'WO-003','Missing FE slot — Admin lobby unprotected','Admin Building','Guard Santos','Purchasing Dept','Critical','Pending Parts','2025-07-14',NULL,NULL,'2026-07-19 05:37:22',NULL),(4,'WO-004','FE-SCI-02 not returning pressure after refill','Science Building','Lim, B.','Tech Valdez','Medium','Completed/Verified','2025-07-15',NULL,NULL,'2026-07-19 05:37:22',NULL),(6,'WO-005','Clean Urgent','University library','John Doe','Maintenance Team','Medium','Issue Logged','2026-08-05',NULL,NULL,'2026-08-03 01:24:41',NULL);
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
INSERT INTO `system_settings` VALUES (1,'system_name','UBRA Operational Portal','2026-08-17 23:27:39'),(2,'university','Foundation University','2026-07-22 05:49:14'),(3,'api_key','','2026-07-22 05:49:14'),(5,'smtp_host','smtp.gmail.com','2026-07-19 00:06:28'),(6,'smtp_port','587','2026-07-19 00:06:28'),(7,'smtp_user','','2026-07-19 00:06:28'),(8,'smtp_from','','2026-07-19 00:06:28'),(9,'smtp_name','FU-UBRA System','2026-07-19 00:06:28'),(10,'smtp_pass','','2026-07-19 00:06:28'),(11,'notif_maintenance','1','2026-07-19 00:06:28'),(12,'notif_vehicle','1','2026-07-19 00:06:28'),(13,'notif_janitorial','1','2026-07-19 00:06:28'),(14,'notif_asset','1','2026-07-19 00:06:28'),(15,'notif_travel','1','2026-07-19 00:06:28'),(16,'reminder_days','5','2026-07-19 00:06:28');
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
  `current_stock` decimal(8,2) DEFAULT NULL,
  `reorder_threshold` decimal(8,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools`
--

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES (2,'MacBook Pro 16','AST-92041','IT Equipment','Deans Office, CCS','Maria Clara Santos','Excellent','Borrowed',NULL,NULL,'2026-07-18 20:20:56',0,NULL,'2026-08-04 09:16:37'),(3,'Floors Buffer Matt','AST-03481','Janitorial','Janitor Depot B','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'2026-07-18 20:20:56',0,NULL,NULL),(4,'Sony Alpha A7 III','AST-00612','Media Studio','Media Center','Col. Arthur Miller','Poor','Available',NULL,NULL,'2026-07-18 20:20:56',0,NULL,NULL),(5,'Epson Projector X50','AST-77120','IT Equipment','AVR Room 2','Pedro Penduko','Good','Available',NULL,NULL,'2026-07-18 20:20:56',0,NULL,NULL),(6,'Industrial Vacuum','AST-55019','Janitorial','Housekeeping Store','Sonia G. Ramirez','Good','Borrowed',NULL,NULL,'2026-07-18 20:20:56',0,NULL,NULL),(7,'Cordless Drill Set','AST-30188','Tools','Maintenance Shop','Engr. James Diaz','Excellent','Available',NULL,NULL,'2026-07-18 20:20:56',0,NULL,NULL),(9,'Circular Saw','AST-22502','Tools Equipment','Maintenance Shop',NULL,'Fair','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(10,'Safety Helmet','AST-91612','Tools Equipment','Admin Building Storage','Pedro Penduko','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',1,'2026-08-03 22:16:02','2026-04-29 13:25:41'),(11,'HP LaserJet Printer','AST-18847','Electronic Devices','CCS Building Rm 204',NULL,'Good','Borrowed',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-17 13:25:41'),(12,'Hammer Drill','AST-59224','Tools Equipment','Media Center','Rodrigo S. Cruz','Excellent','Borrowed',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-03 13:25:41'),(13,'HDMI Cable 10m','AST-88557','Tools Equipment','Admin Building Storage',NULL,'Poor','Disposal',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(14,'Whiteboard Markers Set','AST-82485','Tools Equipment','Science Building Lab','Sonia G. Ramirez','Fair','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-07 13:25:41'),(15,'Hammer Drill','AST-27776','Tools Equipment','Library Storage','Sonia G. Ramirez','Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-28 13:25:41'),(16,'Hand Truck Dolly','AST-63457','Tools Equipment','Housekeeping Store','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-04 13:25:41'),(17,'Tablet iPad 10th Gen','AST-41450','Electronic Devices','AVR Room 2','Maria Clara Santos','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-29 13:25:41'),(18,'Ladder 8ft','AST-38363','Tools Equipment','Science Building Lab','Maria Clara Santos','Fair','Borrowed',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-23 13:25:41'),(19,'Extension Reel','AST-85475','Tools Equipment','IT Server Room',NULL,'Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-15 13:25:41'),(20,'Welding Rods Box','AST-53033','Consumable','Deans Office','Juan dela Cruz','Excellent','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-06-13 13:25:41'),(21,'Zip Ties Pack','AST-85804','Consumable','Engineering Workshop','Col. Arthur Miller','Excellent','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-07-03 13:25:41'),(22,'Ladder 8ft','AST-73437','Tools Equipment','Media Center','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-06-02 13:25:41'),(23,'Canon DSLR Camera','AST-17694','Electronic Devices','Housekeeping Store',NULL,'Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-11 13:25:41'),(24,'Table Saw','AST-61814','Tools Equipment','Gymnasium Storage','Pedro Penduko','Fair','Maintenance',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-15 13:25:41'),(25,'Bluetooth Speaker','AST-57379','Electronic Devices','Housekeeping Store',NULL,'Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-30 13:25:41'),(26,'Ladder 8ft','AST-14888','Tools Equipment','Main Utility Bldg','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-17 13:25:41'),(27,'Zip Ties Pack','AST-95359','Consumable','Housekeeping Store','Juan dela Cruz','Excellent','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-07-09 13:25:41'),(28,'Electrical Tape Roll','AST-46018','Consumable','Deans Office','Engr. James Diaz','Good','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-04-30 13:25:41'),(29,'Hammer Drill','AST-30960','Tools Equipment','Athletics Storage','Juan dela Cruz','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-07 13:25:41'),(30,'Camera Tripod','AST-83606','Tools Equipment','Main Utility Bldg','Engr. James Diaz','Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-24 13:25:41'),(31,'Pipe Wrench','AST-89976','Tools Equipment','Deans Office','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-04 13:25:41'),(32,'Tool Cabinet','AST-10921','Tools Equipment','Main Utility Bldg','Rodrigo S. Cruz','Poor','Borrowed',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-20 13:25:41'),(33,'Laptop Charger Adapter','AST-92882','Tools Equipment','Library Storage','Maria Clara Santos','Fair','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-26 13:25:41'),(34,'Laptop Charger Adapter','AST-66319','Tools Equipment','Housekeeping Store','Rodrigo S. Cruz','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-06-26 13:25:41'),(35,'Hammer Drill','AST-17624','Tools Equipment','Athletics Storage',NULL,'Poor','Maintenance',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-29 13:25:41'),(36,'HP LaserJet Printer','AST-21694','Electronic Devices','Main Utility Bldg','Pedro Penduko','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-16 13:25:41'),(37,'Disinfectant Spray','AST-91615','Consumable','Engineering Workshop','Dr. Helen Peralta','Good','Available',11.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-08-29 17:05:06'),(38,'Welding Rods Box','AST-84482','Consumable','Athletics Storage','Juan dela Cruz','Excellent','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-06-30 13:25:41'),(39,'HDMI Cable 10m','AST-74161','Tools Equipment','Maintenance Shop','Pedro Penduko','Poor','Disposal',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-16 13:25:41'),(40,'HDMI Cable 10m','AST-54117','Tools Equipment','Maintenance Shop','Maria Clara Santos','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-10 13:25:41'),(41,'Canon DSLR Camera','AST-67613','Electronic Devices','Admin Building Storage','Rodrigo S. Cruz','Good','Borrowed',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-06-24 13:25:41'),(42,'CCTV Camera Kit','AST-19879','Electronic Devices','IT Server Room','Engr. James Diaz','Poor','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-20 13:25:41'),(43,'Air Compressor','AST-56187','Tools Equipment','Media Center','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-06 13:25:41'),(44,'Camera Tripod','AST-36890','Tools Equipment','CCS Building Rm 204','Juan dela Cruz','Poor','Disposal',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-10 13:25:41'),(45,'Cordless Drill Set','AST-55542','Tools Equipment','Admin Building Storage','Dr. Helen Peralta','Poor','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-05-12 13:25:41'),(46,'Ladder 8ft','AST-42846','Tools Equipment','Admin Building Storage','Juan dela Cruz','Fair','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),(47,'Laptop Bag','AST-23150','Tools Equipment','Housekeeping Store','Juan dela Cruz','Fair','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-06-14 13:25:41'),(48,'Laptop Charger Adapter','AST-84251','Tools Equipment','Deans Office','Maria Clara Santos','Poor','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-06 13:25:41'),(49,'Wireless Microphone Set','AST-80958','Electronic Devices','Admin Building Storage','Maria Clara Santos','Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-06-05 13:25:41'),(50,'Cleaning Alcohol 1L','AST-48435','Consumable','AVR Room 2',NULL,'Good','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-04-15 13:25:41'),(51,'Extension Cord 20m','AST-73374','Tools Equipment','Engineering Workshop','Juan dela Cruz','Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-16 13:25:41'),(52,'Electrical Tape Roll','AST-18067','Consumable','Deans Office','Rodrigo S. Cruz','Good','Available',4.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-08-30 03:28:19'),(53,'Pipe Wrench','AST-55114','Tools Equipment','Gymnasium Storage','Engr. James Diaz','Poor','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-07-23 13:25:41'),(54,'Table Saw','AST-80661','Tools Equipment','Housekeeping Store','Engr. James Diaz','Good','Available',NULL,NULL,'2026-07-31 19:26:13',0,NULL,'2026-04-18 13:25:41'),(55,'Printer Ink Cartridge','AST-31961','Consumable','CCS Building Rm 204',NULL,'Good','Available',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-08-02 09:42:30'),(56,'Socket Wrench Set','AST-49752','Tools Equipment','Library Storage',NULL,'Poor','Disposal',NULL,NULL,'2026-07-31 19:26:13',1,'2026-08-04 03:07:04','2026-06-22 13:25:41'),(57,'Batteries AA Pack','AST-28474','Consumable','Housekeeping Store','Col. Arthur Miller','Poor','Maintenance',1.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-08-03 23:27:51'),(58,'Printer Ink Cartridge','AST-29735','Consumable','Science Building Lab','Maria Clara Santos','Excellent','Borrowed',21.00,1.00,'2026-07-31 19:26:13',0,NULL,'2026-08-21 12:18:06'),(59,'Push Broom','2152','Tools Equipment','Supply Room','Sonia G. Ramirez','Good','Available',NULL,NULL,'2026-08-03 15:34:22',0,NULL,'2026-08-03 15:34:22'),(60,'Jack-Hammer','200055','Tools Equipment','Cisco Lab','sherina Banosong','Excellent','Available',NULL,NULL,'2026-08-04 05:38:35',0,NULL,'2026-08-04 05:38:34'),(61,'Extension Cord 10m','62007','Tools Equipment','Library Main','Juan dela Beto','Good','Available',NULL,NULL,'2026-08-04 09:25:10',0,NULL,'2026-08-04 09:25:10'),(64,'Jack-Hammer','009091','Electronic Devices','Cisco Lab','Timothy Eraham','Good','Available',NULL,NULL,'2026-08-29 23:07:59',0,NULL,'2026-08-29 23:07:59'),(68,'HDMI','2345','Tools Equipment','Cisco Lab','Rodrigo S. Cruz','Excellent','Available',NULL,NULL,'2026-08-29 23:13:04',0,NULL,'2026-08-29 23:13:04'),(69,'Electrical tape','','','','','Excellent','Available',NULL,NULL,'2026-08-29 23:14:01',0,NULL,'2026-08-29 23:14:01'),(71,'Electrical Tape','AST-2346','Consumable','Cisco Lab','Pedro Penduko','Excellent','Available',NULL,NULL,'2026-08-29 23:15:19',0,NULL,'2026-08-29 23:15:19'),(72,'CCTV ','AST-2344','Electronic Devices','Cisco Lab','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'2026-08-29 23:16:28',0,NULL,'2026-08-29 23:16:28'),(73,'Jack Hammer','200056','Tools Equipment','North Campus','Juan dela Beto','Excellent','Available',NULL,NULL,'2026-08-29 23:46:24',0,NULL,'2026-08-29 23:46:24');
/*!40000 ALTER TABLE `tools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tools_refill_log`
--

DROP TABLE IF EXISTS `tools_refill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tools_refill_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) NOT NULL,
  `asset_name` varchar(150) NOT NULL,
  `quantity_added` decimal(8,2) NOT NULL,
  `performed_by` varchar(100) NOT NULL,
  `performed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tool_id` (`tool_id`),
  KEY `performed_at` (`performed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools_refill_log`
--

LOCK TABLES `tools_refill_log` WRITE;
/*!40000 ALTER TABLE `tools_refill_log` DISABLE KEYS */;
INSERT INTO `tools_refill_log` VALUES (1,52,'Electrical Tape Roll',3.00,'Kenchie Terante','2026-08-30 03:28:19');
/*!40000 ALTER TABLE `tools_refill_log` ENABLE KEYS */;
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
  `status` enum('Submitted','Reviewed','Approved','In Transit','Completed','Rejected','Cancelled') NOT NULL DEFAULT 'Submitted',
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
INSERT INTO `travel_requests` VALUES (1,'TR-20260729-0001',8,'Cebu City Hall','Official meeting with city officials','2026-07-29','08:00:00','17:00:00',1,6,2,'Completed','2026-08-04 06:40:27','2026-08-04 06:40:29',NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:49','2026-07-28 16:29:06','2026-08-04 05:21:49','2026-08-03 22:40:29'),(2,'TR-20260728-0001',9,'Mandaue Campus','Inventory transfer and outreach','2026-07-28','09:30:00','15:30:00',2,7,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:45','2026-07-28 16:29:06','2026-08-04 05:21:45','2026-07-28 16:29:06'),(3,'TR-20260730-0001',5,'DepEd Regional Office','Submission of accreditation documents','2026-07-30','07:00:00','12:00:00',5,7,2,'Completed','2026-08-01 19:36:32','2026-08-01 19:36:32','EMP-2023-210',1,'For Disposal',NULL,NULL,NULL,'2026-08-04 05:21:40','2026-07-28 16:29:06','2026-08-04 05:21:40','2026-07-28 12:26:12'),(4,'TR-20260601-0001',8,'Provincial Office','Inspection of maintenance requests','2026-06-01','07:15:00','13:00:00',1,14,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-07-01 02:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),(5,'TR-20260715-0001',9,'Lapu-Lapu Warehouse','Equipment pickup for Facilities','2026-07-15','10:00:00','16:00:00',4,19,3,'Cancelled',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:36','2026-07-28 16:29:06','2026-08-04 05:21:36','2026-07-28 16:29:06'),(6,'TR-20260726-0001',6,'Bacolod Provincial Capitol','Coordination meeting','2026-07-26','08:30:00','14:00:00',3,7,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:31','2026-07-28 16:42:00','2026-08-04 05:21:31','2026-07-28 16:42:00'),(7,'TR-20260410-0001',8,'Silliman University','Interagency site visit','2026-04-10','07:00:00','18:00:00',1,6,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-06-01 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),(8,'TR-20260320-0001',9,'Dumaguete Airport','Guest pickup','2026-03-20','06:00:00','09:00:00',2,19,2,'Rejected',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:20','2026-07-28 16:42:00','2026-08-04 05:21:20','2026-07-28 16:42:00'),(13,'TR-20260804-0001',5,'Tanjay asaggra','Regional outreach visit','2026-08-05','07:00:00','18:00:00',5,51,2,'Approved','2026-08-04 09:34:59',NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:08:40','2026-08-04 01:23:05','2026-08-19 16:03:31','2026-08-04 01:34:59'),(16,'TR-20260804-0002',14,'Bais City','Site visit and coordination','2026-08-05','07:30:00','05:00:00',5,31,7,'Submitted',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-08-04 05:21:04','2026-08-29 16:43:56','2026-08-04 05:21:04');
/*!40000 ALTER TABLE `travel_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trip_status_log`
--

DROP TABLE IF EXISTS `trip_status_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trip_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `travel_request_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `changed_by` varchar(100) NOT NULL,
  `changed_at` datetime NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_request_id` (`travel_request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trip_status_log`
--

LOCK TABLES `trip_status_log` WRITE;
/*!40000 ALTER TABLE `trip_status_log` DISABLE KEYS */;
INSERT INTO `trip_status_log` VALUES (1,1,'Completed','Dr. Helen Peralta','2026-07-29 00:29:06','Existing record - history predates status tracking'),(2,2,'Completed','sherina Banosong','2026-07-29 00:29:06','Existing record - history predates status tracking'),(3,3,'Completed','Pedro Penduko','2026-07-29 00:29:06','Existing record - history predates status tracking'),(4,4,'Completed','Dr. Helen Peralta','2026-07-29 00:29:06','Existing record - history predates status tracking'),(5,5,'Cancelled','sherina Banosong','2026-07-29 00:29:06','Existing record - history predates status tracking'),(6,6,'Completed','Juan dela Cruz','2026-07-29 00:42:00','Existing record - history predates status tracking'),(7,7,'Completed','Dr. Helen Peralta','2026-07-29 00:42:00','Existing record - history predates status tracking'),(8,8,'Rejected','sherina Banosong','2026-07-29 00:42:00','Existing record - history predates status tracking'),(9,13,'Approved','Pedro Penduko','2026-08-04 09:23:05','Existing record - history predates status tracking'),(10,16,'Submitted','Timothy Eraham','2026-08-04 13:21:04','Existing record - history predates status tracking');
/*!40000 ALTER TABLE `trip_status_log` ENABLE KEYS */;
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
INSERT INTO `users` VALUES (0,'Kenchie terante','admin@example.com','12345','12345','$2y$10$1GurcyUteQJBUJ4A3W7yNuDpr.oLHPp1fEf7kUmt5graLsPaUvvoG','Administrator',NULL,NULL,'2026-07-26 11:56:12'),(2,'Kenchie Terante','admin@fu.edu.ph','admin','20230251','$2b$12$c8vA1tfnL.JQGWsHuahepukT6/UGD41npxdErvEsNFlsGr7d4Rxwq','Administrator','Operations Office','1787991834_bae35548ba39207f21ec.jpeg','2026-07-18 22:18:46'),(5,'Sherina Banosong','sherina.banosong@foundationu.com','facilities','20230407','$2y$10$X7B8Zl3W/OOxATcABKwwqeFOvn77LV15ujUCcwd7QmMk6vfqNJcm6','Facilities',NULL,'1788029598_fca15c5bcb9244960756.jpg','2026-08-29 16:33:30'),(9,'Timothy Eraham','timothy.eraham@foundationu.com','security','10005','$2y$10$bAEiq/MrHZxqBLT3Y8j4nOCk3EQ0Fn3KuGGdmva5GEJSVGEveyuMG','Security','Safety & Security','1788029522_d47e818ca26ab4f0a98e.jpg','2026-08-21 20:32:29');
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
  `tire_pressure_psi` decimal(5,1) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (2,'Click25','466','Motorcycle',5,5,'Online','Due Soon',30.0,'Available',0,NULL,'2026-07-26 21:52:08','2026-08-04 05:21:49','2026-07-27 05:52:09'),(3,'Mitsubishi','90HJI87','4 Wheels',14,5,'Offline','Completed',32.5,'Available',0,NULL,'2026-07-27 06:23:47','2026-08-04 05:21:45','2026-07-27 14:23:47'),(5,'Yamaha','4567HUJI','Automatic Car ',14,7,'Online','Completed',33.0,'Available',0,NULL,'2026-07-29 17:07:42','2026-08-04 01:19:14','2026-07-30 01:07:42'),(6,'Toyota Hiace','5678','Van',7,3,'Online','Due Soon',31.5,'Available',0,NULL,'2026-08-03 07:29:05','2026-08-19 16:03:31','2026-08-03 07:29:05'),(7,'Zusuki','230345','V2-4Wheels',31,2,'Online','Completed',NULL,'In Use',0,NULL,'2026-08-04 01:24:15','2026-08-21 08:22:43','2026-08-04 01:24:15');
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

-- Dump completed on 2026-08-31 23:07:39
