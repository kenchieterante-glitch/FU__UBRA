/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action` varchar(150) DEFAULT NULL,
  `logged_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,2,'Personnel Monitoring','Assigned John Doe to Job Order JO-2026-002','2026-08-29 22:51:45'),
(4,2,'Vehicle','Kenchie Terante: Logged fuel for Zusuki — 5L at 5km','2026-09-20 16:02:21'),
(5,2,'Vehicle','Kenchie Terante: Logged fuel for Zusuki — 5L at 10km','2026-09-20 16:06:37'),
(6,9,'Safety','Timothy Eraham: Verified notification: Fire Extinguisher Expiring Soon','2026-09-20 20:33:45'),
(7,5,'Personnel','Sherina Banosong: Acknowledged notification: Personnel Document Incomplete','2026-09-20 20:35:36'),
(8,5,'Settings','Sherina Banosong: Updated general settings','2026-09-20 21:21:08'),
(9,2,'Personnel','Kenchie Terante: Added personnel Eddie Murphy (202101999)','2026-09-21 14:11:36'),
(10,2,'Janitorial','Kenchie Terante: Refilled Glass Cleaner (Window Spray) by 6 Bottles','2026-09-21 14:20:05'),
(11,2,'Janitorial','Kenchie Terante: Refilled Disinfectant Spray by 5 Bottles','2026-09-21 14:20:21'),
(12,2,'Janitorial','Kenchie Terante: Refilled Liquid Hand Soap by 5 Liters','2026-09-21 14:20:57'),
(13,2,'Janitorial','Kenchie Terante: Refilled Trash Bag by 4 Rolls','2026-09-21 14:21:10'),
(14,2,'Janitorial','Kenchie Terante: Added inventory item Tissue','2026-09-21 14:24:27'),
(15,2,'Janitorial','Kenchie Terante: Added inventory item Tissue Paper','2026-09-21 14:31:36'),
(16,2,'Settings','Kenchie Terante: Updated AI configuration','2026-09-30 21:58:05'),
(17,2,'Settings','Kenchie Terante: Updated AI configuration','2026-09-30 22:21:49'),
(18,2,'Safety','Kenchie Terante: Set installer for aircon unit #9 to Fernando Reyes','2026-10-01 02:23:18'),
(19,12,'Safety','Security Test Account: Key \"ZZ Key\" scanned out to ZZ Tester','2026-10-03 22:36:30'),
(20,12,'Safety','Security Test Account: Key \"ZZ Key\" returned by ZZ Tester','2026-10-03 22:36:31');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `aircon_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aircon_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aircon_unit_id` int(11) NOT NULL,
  `task_name` varchar(150) NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aircon_unit_id` (`aircon_unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aircon_checklist_items` WRITE;
/*!40000 ALTER TABLE `aircon_checklist_items` DISABLE KEYS */;
INSERT INTO `aircon_checklist_items` VALUES
(15,3,'Clean or replace air filter',1,'2026-07-04 02:00:00'),
(16,3,'Check refrigerant level',1,'2026-07-04 05:00:00'),
(17,3,'Inspect drainage line',1,'2026-07-04 01:00:00'),
(18,3,'Test thermostat / airflow',1,'2026-07-04 04:00:00'),
(19,3,'Clean condenser coils',1,'2026-07-04 05:00:00'),
(20,3,'Check electrical connections',1,'2026-07-04 01:00:00'),
(21,3,'Record unit temperature output',1,'2026-09-05 19:15:15'),
(22,4,'Clean or replace air filter',1,'2026-07-29 02:00:00'),
(23,4,'Check refrigerant level',1,'2026-07-29 04:00:00'),
(24,4,'Inspect drainage line',1,'2026-07-29 05:00:00'),
(25,4,'Test thermostat / airflow',1,'2026-07-29 05:00:00'),
(26,4,'Clean condenser coils',1,'2026-07-29 01:00:00'),
(27,4,'Check electrical connections',1,'2026-07-29 01:00:00'),
(28,4,'Record unit temperature output',0,NULL),
(29,5,'Clean or replace air filter',1,'2026-07-13 01:00:00'),
(30,5,'Check refrigerant level',1,'2026-07-13 03:00:00'),
(31,5,'Inspect drainage line',1,'2026-07-13 05:00:00'),
(32,5,'Test thermostat / airflow',1,'2026-09-05 19:22:04'),
(33,5,'Clean condenser coils',1,'2026-09-05 19:22:04'),
(34,5,'Check electrical connections',1,'2026-09-05 19:22:04'),
(35,5,'Record unit temperature output',1,'2026-09-05 19:22:04'),
(36,6,'Clean or replace air filter',1,'2026-07-13 01:00:00'),
(37,6,'Check refrigerant level',1,'2026-07-13 03:00:00'),
(38,6,'Inspect drainage line',1,'2026-07-13 05:00:00'),
(39,6,'Test thermostat / airflow',1,'2026-07-13 05:00:00'),
(40,6,'Clean condenser coils',1,'2026-07-13 05:00:00'),
(41,6,'Check electrical connections',1,'2026-07-13 04:00:00'),
(42,6,'Record unit temperature output',0,NULL),
(43,7,'Clean or replace air filter',1,'2026-07-17 04:00:00'),
(44,7,'Check refrigerant level',0,NULL),
(45,7,'Inspect drainage line',0,NULL),
(46,7,'Test thermostat / airflow',0,NULL),
(47,7,'Clean condenser coils',0,NULL),
(48,7,'Check electrical connections',0,NULL),
(49,7,'Record unit temperature output',0,NULL),
(50,8,'Clean or replace air filter',1,'2026-06-08 04:00:00'),
(51,8,'Check refrigerant level',1,'2026-06-08 03:00:00'),
(52,8,'Inspect drainage line',1,'2026-06-08 03:00:00'),
(53,8,'Test thermostat / airflow',1,'2026-06-08 01:00:00'),
(54,8,'Clean condenser coils',1,'2026-06-08 04:00:00'),
(55,8,'Check electrical connections',1,'2026-06-08 03:00:00'),
(56,8,'Record unit temperature output',1,'2026-09-05 19:22:05'),
(57,9,'Clean or replace air filter',1,'2026-06-21 03:00:00'),
(58,9,'Check refrigerant level',1,'2026-06-21 03:00:00'),
(59,9,'Inspect drainage line',1,'2026-06-21 04:00:00'),
(60,9,'Test thermostat / airflow',1,'2026-06-21 02:00:00'),
(61,9,'Clean condenser coils',1,'2026-06-21 04:00:00'),
(62,9,'Check electrical connections',1,'2026-06-21 02:00:00'),
(63,9,'Record unit temperature output',0,NULL),
(64,10,'Clean or replace air filter',1,'2026-06-15 01:00:00'),
(65,10,'Check refrigerant level',1,'2026-06-15 05:00:00'),
(66,10,'Inspect drainage line',1,'2026-06-15 02:00:00'),
(67,10,'Test thermostat / airflow',0,NULL),
(68,10,'Clean condenser coils',0,NULL),
(69,10,'Check electrical connections',0,NULL),
(70,10,'Record unit temperature output',0,NULL);
/*!40000 ALTER TABLE `aircon_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `aircon_inspection_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aircon_inspection_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `entry_date` date DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `aircon_type` varchar(50) DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aircon_inspection_entries` WRITE;
/*!40000 ALTER TABLE `aircon_inspection_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `aircon_inspection_entries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `aircon_inspection_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aircon_inspection_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `performed_by` varchar(150) DEFAULT NULL,
  `date_submitted` date DEFAULT NULL,
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` date DEFAULT NULL,
  `approved_by` varchar(150) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aircon_inspection_logs` WRITE;
/*!40000 ALTER TABLE `aircon_inspection_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `aircon_inspection_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `aircon_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aircon_units` WRITE;
/*!40000 ALTER TABLE `aircon_units` DISABLE KEYS */;
INSERT INTO `aircon_units` VALUES
(3,'College of Education Building','Ground Floor','AC-EDU-G1','2026-07-04','2026-10-02','Operational','Cardo Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(4,'College of Education Building','2nd Floor','AC-EDU-2F','2026-07-29','2026-10-27','Operational','Cardo Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(5,'College of Business Economics and Accountancy','Ground Floor','AC-BEA-G1','2026-07-13','2026-10-11','Needs Cleaning','Fernando Reyes',NULL,'2026-08-04 00:19:27',NULL),
(6,'College of Art & Sciences Building','2nd Floor','AC-ART-2F','2026-07-13','2026-10-11','Operational','Remedios Mendoza',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(7,'College of Art & Sciences Building','3rd Floor','AC-ART-3F','2026-07-17','2026-10-15','Not Working','Remedios Mendoza',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(8,'University Library','Ground Floor','AC-LIB-G1','2026-06-08','2026-09-06','Operational','Josefa Garcia',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(9,'Administration Building','Ground Floor','AC-ADM-G1','2026-06-21','2026-09-19','Operational','Cardo Garcia','Fernando Reyes','2026-08-04 00:19:27','2026-10-01 02:23:18'),
(10,'Administration Building','2nd Floor','AC-ADM-2F','2026-06-15','2026-09-13','Needs Cleaning','Fernando Reyes',NULL,'2026-08-04 00:19:27','2026-08-04 05:07:03'),
(11,'University Library','2nd Floor','AC-LIB-2F','2026-08-04','2026-10-06','Operational','Josefa Garcia','Cardo Garcia','2026-10-03 17:50:50','2026-10-03 17:51:50'),
(12,'Executive House','Ground Floor','AC-EXE-G1','2026-07-05','2026-09-28','Needs Cleaning','Josefa Garcia','Cardo Garcia','2026-10-03 17:51:50',NULL),
(13,'College of Law Building','2nd Floor','AC-LAW-2F','2026-07-25','2026-09-21','Operational','Remedios Mendoza','Fernando Reyes','2026-10-03 17:51:50',NULL),
(14,'Guest House','Ground Floor','AC-GST-G1','2026-08-24','2026-11-17','Operational','Cardo Garcia','Cardo Garcia','2026-10-03 17:51:50',NULL),
(15,'HRM Kitchen','Ground Floor','AC-HRM-G1','2026-09-03','2026-12-02','Not Working','Josefa Garcia','Fernando Reyes','2026-10-03 17:51:50',NULL),
(16,'College of Nursing','1st Floor','AC-NUR-1F','2026-09-13','2026-12-22','Operational','Remedios Mendoza','Cardo Garcia','2026-10-03 17:51:50',NULL),
(17,'Bunk House','Ground Floor','AC-BNK-G1','2026-06-25','2026-09-13','Operational','Fernando Reyes','Josefa Garcia','2026-10-03 17:51:50',NULL),
(18,'Registrar\'s Office','1st Floor','AC-REG-1F','2026-09-23','2026-10-08','Operational','Cardo Garcia','Fernando Reyes','2026-10-03 17:51:50',NULL);
/*!40000 ALTER TABLE `aircon_units` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `borrow_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrow_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) DEFAULT NULL,
  `quantity` decimal(8,2) DEFAULT 1.00,
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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `borrow_records` WRITE;
/*!40000 ALTER TABLE `borrow_records` DISABLE KEYS */;
INSERT INTO `borrow_records` VALUES
(1,2,1.00,'Pedro Penduko','IT Support','2026-07-29','2026-08-05','Returned','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-09-05 15:16:41'),
(2,3,1.00,'Mina Santos','Housekeeping','2026-07-20','2026-07-27','Returned','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-07-29 00:29:06'),
(3,4,1.00,'sherina Banosong','Administration','2026-06-15','2026-06-22','Returned','2026-07-29 00:29:06',1,'2026-07-01 09:00:00','For Disposal',NULL,NULL,NULL,'2026-07-29 00:29:06'),
(4,5,1.00,'Dr. Helen Peralta','College of IT','2026-07-28','2026-08-04','Returned','2026-07-29 00:29:06',0,NULL,'None',NULL,NULL,NULL,'2026-09-05 15:16:19'),
(5,6,1.00,'Armand Perez','Facilities','2026-05-10','2026-05-17','Returned','2026-07-29 00:29:06',1,'2026-07-15 14:00:00','Disposed','2026-07-15 00:00:00',NULL,NULL,'2026-07-29 00:29:06'),
(6,7,1.00,'Sonia G. Ramirez','Facilities','2026-07-29','2026-08-02','Returned','2026-07-29 00:29:06',0,NULL,'For Disposal',NULL,NULL,NULL,'2026-09-05 14:50:00'),
(7,2,1.00,'Rico Dela Cruz','Facilities','2026-07-25','2026-08-01','Returned','2026-07-29 00:42:00',0,NULL,'None',NULL,NULL,NULL,'2026-09-05 14:47:27'),
(8,3,1.00,'Timothy Eraham','Housekeeping','2026-04-10','2026-04-17','Returned','2026-07-29 00:42:00',1,'2026-06-01 10:00:00','None',NULL,NULL,NULL,'2026-07-29 00:42:00'),
(9,7,1.00,'Juan dela beto','Facilities','2026-03-01','2026-03-08','Returned','2026-07-29 00:42:00',1,'2026-06-20 09:00:00','Disposed','2026-06-20 00:00:00',NULL,NULL,'2026-07-29 00:42:00'),
(10,55,1.00,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:34:15',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 19:34:15'),
(11,55,1.00,'John Doe','Facilities','2026-08-01','2026-08-08','Returned','2026-08-01 19:38:32',0,NULL,'None',NULL,NULL,NULL,'2026-08-01 20:10:55'),
(12,55,1.00,'Sherina Banosong','Logistics','2026-08-02','2026-08-09','Returned','2026-08-02 09:42:30',0,NULL,'None',NULL,NULL,NULL,'2026-08-02 09:42:30'),
(13,74,1.00,'Dr. Helen Peralta','Student Affairs','2026-09-01','2026-09-10','Returned','2026-09-05 17:29:15',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(14,76,1.00,'Rico Dela Cruz','Athletics','2026-09-01','2026-09-10','Returned','2026-09-05 17:29:15',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(15,6,1.00,'Sonia G. Ramirez','Janitorial','2026-08-28','2026-09-04','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(16,11,1.00,'Pedro Penduko','IT Support','2026-08-25','2026-09-01','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(17,12,1.00,'Juan dela Cruz','Maintenance','2026-09-02','2026-09-09','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(18,18,1.00,'Rodrigo S. Cruz','Facilities','2026-08-20','2026-08-27','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(19,32,1.00,'Juan dela Beto','Maintenance','2026-09-03','2026-09-12','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(20,41,1.00,'Sherina Banosong','Admin Office','2026-09-01','2026-09-06','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(21,58,1.00,'Mina Santos','Records Office','2026-08-30','2026-09-03','Returned','2026-09-05 17:40:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(22,75,1.00,'Rico Dela Cruz','Athletics','2026-09-02','2026-09-09','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(23,77,1.00,'Mina Santos','Athletics','2026-09-01','2026-09-08','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(24,78,1.00,'Juan Cruz','Athletics','2026-08-29','2026-09-05','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(25,79,1.00,'Lapu-lapu','Student Affairs','2026-09-03','2026-09-10','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(26,80,1.00,'Timothy Eraham','Athletics','2026-08-30','2026-09-06','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(27,81,1.00,'Dr. Helen Peralta','Student Affairs','2026-09-01','2026-09-08','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(28,82,1.00,'Sonia G. Ramirez','Athletics','2026-08-25','2026-09-01','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(29,83,1.00,'Rodrigo S. Cruz','Athletics','2026-09-02','2026-09-09','Returned','2026-09-05 17:42:40',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(30,2,1.00,'Sonia G. Ramirez','IT Support','2026-09-02','2026-09-08','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(31,3,1.00,'Pedro Penduko','Janitorial','2026-09-01','2026-09-09','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(32,4,1.00,'Juan dela Cruz','Media Office','2026-08-31','2026-09-10','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(33,5,1.00,'Rodrigo S. Cruz','IT Support','2026-08-30','2026-09-11','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(34,7,1.00,'Dr. Helen Peralta','Maintenance','2026-08-29','2026-09-12','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(35,9,1.00,'sherina Banosong','Maintenance','2026-08-28','2026-09-13','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(36,14,1.00,'Timothy Eraham','Maintenance','2026-08-27','2026-09-14','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(37,15,1.00,'TimothyLincon','Maintenance','2026-08-26','2026-09-15','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(38,16,1.00,'Juan dela Cruz','Maintenance','2026-08-25','2026-09-16','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(39,17,1.00,'Juan dela Beto','Maintenance','2026-09-03','2026-09-07','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(40,19,1.00,'Lapu-lapu','Maintenance','2026-09-02','2026-09-08','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(41,20,1.00,'Juan Cruz','Facilities','2026-09-01','2026-09-09','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(42,21,1.00,'Mina Santos','Facilities','2026-08-31','2026-09-10','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(43,22,1.00,'Rico Dela Cruz','Maintenance','2026-08-30','2026-09-11','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(44,23,1.00,'Armand Perez','Maintenance','2026-08-29','2026-09-12','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(45,25,1.00,'Ricardo Reyes','Maintenance','2026-08-28','2026-09-13','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(46,26,1.00,'Goyo Pascual','Maintenance','2026-08-27','2026-09-14','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(47,27,1.00,'Cardo Manalo','Facilities','2026-08-26','2026-09-15','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(48,28,1.00,'Rodrigo Torres','Facilities','2026-08-25','2026-09-16','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(49,29,1.00,'Josefa Mendoza','Maintenance','2026-09-03','2026-09-07','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(50,30,1.00,'Fernando Ocampo','Maintenance','2026-09-02','2026-09-08','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(51,31,1.00,'Cardo Domingo','Maintenance','2026-09-01','2026-09-09','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(52,33,1.00,'Teresa Domingo','Maintenance','2026-08-31','2026-09-10','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(53,34,1.00,'Emilio Reyes','Maintenance','2026-08-30','2026-09-11','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(54,36,1.00,'Consolacion Garcia','Maintenance','2026-08-29','2026-09-12','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(55,37,1.00,'Antonio Reyes','Facilities','2026-08-28','2026-09-13','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(56,38,1.00,'Danilo Mendoza','Facilities','2026-08-27','2026-09-14','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(57,40,1.00,'Rizal Bautista','Maintenance','2026-08-26','2026-09-15','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(58,42,1.00,'Remedios Ocampo','Maintenance','2026-08-25','2026-09-16','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(59,43,1.00,'Ricardo Mendoza','Maintenance','2026-09-03','2026-09-07','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(60,45,1.00,'Fernando Salazar','Maintenance','2026-09-02','2026-09-08','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(61,46,1.00,'Andres Navarro','Maintenance','2026-09-01','2026-09-09','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(62,47,1.00,'Diego Castillo','Maintenance','2026-08-31','2026-09-10','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(63,48,1.00,'Isabel Aquino','Maintenance','2026-08-30','2026-09-11','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(64,49,1.00,'Fernando Navarro','Maintenance','2026-08-29','2026-09-12','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(65,50,1.00,'Gabriela Pascual','Facilities','2026-08-28','2026-09-13','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(66,51,1.00,'Emilio Rivera','Maintenance','2026-08-27','2026-09-14','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(67,52,1.00,'Maria Mendoza','Facilities','2026-08-26','2026-09-15','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(68,53,1.00,'Rizal Garcia','Maintenance','2026-08-25','2026-09-16','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(69,54,1.00,'Cardo Garcia','Maintenance','2026-09-03','2026-09-07','Returned','2026-09-05 17:45:21',0,NULL,'Disposed','2026-09-05 00:00:00',2,'data:image/png;base64,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','2026-09-06 01:06:36'),
(70,55,1.00,'Diego Fernandez','Facilities','2026-09-02','2026-09-08','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(71,59,1.00,'Josefa Villanueva','Maintenance','2026-09-01','2026-09-09','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(72,60,1.00,'Goyo Castillo','Maintenance','2026-08-31','2026-09-10','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(73,61,1.00,'Antonio Mendoza','Maintenance','2026-08-30','2026-09-11','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(74,64,1.00,'Josefa Garcia','Maintenance','2026-08-29','2026-09-12','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(75,68,1.00,'Goyo Ramos','Maintenance','2026-08-28','2026-09-13','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(76,71,1.00,'Juan Santos','Facilities','2026-08-27','2026-09-14','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(77,72,1.00,'Maria Domingo','Maintenance','2026-08-26','2026-09-15','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(78,73,1.00,'Fernando Cruz','Maintenance','2026-08-25','2026-09-16','Returned','2026-09-05 17:45:21',0,NULL,'None',NULL,NULL,NULL,'2026-09-06 01:06:36'),
(93,2,1.00,'Rico Dela Cruz','Athletics','2026-09-06','2026-09-13','Returned','2026-09-06 01:30:35',0,NULL,'None',NULL,NULL,NULL,'2026-10-04 02:47:47'),
(94,74,1.00,'Coach Reyes (sample)','Athletics','2026-10-02','2026-10-09','Borrowed','2026-10-04 02:27:14',0,NULL,'None',NULL,NULL,NULL,'2026-10-04 02:27:14'),
(95,77,1.00,'Mina Santos (sample)','Athletics','2026-09-25','2026-10-01','Borrowed','2026-10-04 02:27:14',0,NULL,'None',NULL,NULL,NULL,'2026-10-04 02:27:14'),
(96,82,1.00,'PE Instructor Cruz (sample)','Student Affairs','2026-10-03','2026-10-05','Borrowed','2026-10-04 02:27:14',0,NULL,'None',NULL,NULL,NULL,'2026-10-04 02:27:14');
/*!40000 ALTER TABLE `borrow_records` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `consumable_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consumable_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category` enum('Cleaning Detergent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Detergent',
  `department` varchar(100) NOT NULL DEFAULT 'Facilities',
  `unit` varchar(40) NOT NULL DEFAULT 'Pieces',
  `building` varchar(150) DEFAULT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `location_note` varchar(150) DEFAULT NULL,
  `current_stock` decimal(8,2) NOT NULL DEFAULT 0.00,
  `reorder_threshold` decimal(8,2) NOT NULL DEFAULT 5.00,
  `last_refill` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `consumable_inventory` WRITE;
/*!40000 ALTER TABLE `consumable_inventory` DISABLE KEYS */;
INSERT INTO `consumable_inventory` VALUES
(1,'Floor Cleaner (Pine)','Cleaning Detergent','Facilities','Liters',NULL,NULL,NULL,83.00,5.00,'2026-08-30','2026-07-19 05:37:22','2026-10-03 21:49:01'),
(2,'Toilet Bowl Cleaner','Cleaning Detergent','Facilities','Bottles',NULL,NULL,NULL,22.00,3.00,'2026-08-04','2026-07-19 05:37:22','2026-10-03 21:49:01'),
(3,'Trash Liners (Large)','Disposable','Facilities','Rolls',NULL,NULL,NULL,9.00,5.00,'2026-09-05','2026-07-19 05:37:22','2026-09-05 18:03:01'),
(4,'Mop Heads','Tools','Facilities','Pieces',NULL,NULL,NULL,6.00,3.00,'2025-07-01','2026-07-19 05:37:22',NULL),
(5,'Disinfectant Spray','Cleaning Detergent','Facilities','Bottles',NULL,NULL,NULL,7.00,4.00,'2026-09-21','2026-07-19 05:37:22','2026-10-03 21:49:01'),
(6,'Tissue Paper (Rolls)','Disposable','Facilities','Rolls',NULL,NULL,NULL,30.00,10.00,'2025-07-12','2026-07-19 05:37:22',NULL),
(7,'Liquid Hand Soap','Cleaning Detergent','Facilities','Liters',NULL,NULL,NULL,8.00,4.00,'2026-09-21','2026-07-19 05:37:22','2026-10-03 21:49:01'),
(8,'Brooms','Tools','Facilities','Pieces',NULL,NULL,NULL,12.00,4.00,'2025-06-15','2026-07-19 05:37:22',NULL),
(9,'Glass Cleaner (Window Spray)','Cleaning Detergent','Facilities','Bottles',NULL,NULL,NULL,6.00,4.00,'2026-09-21','2026-08-03 20:43:20','2026-10-03 21:49:01'),
(10,'Trash Bag','Disposable','Facilities','Rolls',NULL,NULL,NULL,14.00,10.00,'2026-09-21','2026-08-29 23:18:48','2026-09-21 14:21:10'),
(11,'Tissue','Disposable','Facilities','1',NULL,NULL,NULL,1.00,0.00,'2026-09-21','2026-09-21 14:24:27',NULL),
(12,'Tissue Paper','Disposable','Facilities','Rolls',NULL,NULL,NULL,10.00,5.00,'2026-09-21','2026-09-21 14:31:36',NULL),
(13,'Trash Bags (Large)','Disposable','Facilities','Rolls','Administration Building','Ground Floor','Janitor closet',0.00,5.00,'2026-10-03','2026-10-03 17:50:50','2026-10-03 17:51:50'),
(14,'Bleach','Cleaning Detergent','Facilities','Liters','Executive House','Ground Floor','Supply room',12.00,4.00,'2026-10-03','2026-10-03 17:51:50','2026-10-03 21:49:01'),
(15,'Paper Towels','Disposable','Facilities','Rolls','College of Law Building','2nd Floor','Storage',25.00,8.00,'2026-10-03','2026-10-03 17:51:50',NULL),
(16,'Mop Bucket','Tools','Facilities','Pieces','Guest House','Ground Floor','Janitor closet',3.00,2.00,'2026-10-03','2026-10-03 17:51:50',NULL),
(17,'Dish Soap','Cleaning Detergent','Facilities','Bottles','HRM Kitchen','Ground Floor','Kitchen storage',0.00,5.00,'2026-10-03','2026-10-03 17:51:50','2026-10-03 21:49:01'),
(18,'Broom','Tools','Facilities','Pieces','College of Nursing','1st Floor','Janitor closet',6.00,3.00,'2026-10-03','2026-10-03 17:51:50',NULL),
(19,'Hand Soap Refill','Disposable','Facilities','Liters','Bunk House','Ground Floor',NULL,0.00,4.00,'2026-10-03','2026-10-03 17:51:50',NULL);
/*!40000 ALTER TABLE `consumable_inventory` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `consumable_stock_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consumable_stock_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `zone` varchar(255) DEFAULT NULL,
  `quantity_left` decimal(8,2) NOT NULL,
  `unit` varchar(40) NOT NULL,
  `reported_by` varchar(150) DEFAULT NULL,
  `reported_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_item_id` (`inventory_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `consumable_stock_reports` WRITE;
/*!40000 ALTER TABLE `consumable_stock_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `consumable_stock_reports` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES
(1,'Facilities','2026-07-18 20:20:56'),
(2,'Logistics','2026-07-18 20:20:56'),
(3,'Security','2026-07-18 20:20:56'),
(4,'Housekeeping','2026-07-18 20:20:56'),
(5,'College of IT','2026-07-18 20:20:56'),
(6,'Administration','2026-07-18 20:20:56'),
(7,'Athletics','2026-07-18 20:20:56'),
(8,'Finance','2026-07-18 20:20:56');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `disposal_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `disposal_logs` WRITE;
/*!40000 ALTER TABLE `disposal_logs` DISABLE KEYS */;
INSERT INTO `disposal_logs` VALUES
(1,'borrow',5,8,NULL,'2026-07-15','Industrial Vacuum beyond repair; unit decommissioned.','2026-07-29 00:29:06'),
(2,'borrow',9,8,NULL,'2026-06-20','Cordless drill set damaged; unit disposed per inspection.','2026-07-29 00:42:00'),
(3,'borrow',69,2,'data:image/png;base64,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','2026-09-05','','2026-09-05 20:15:47');
/*!40000 ALTER TABLE `disposal_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `document_requirement_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `document_requirement_types` WRITE;
/*!40000 ALTER TABLE `document_requirement_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_requirement_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `equipment_maintenance_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_maintenance_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `entry_date` date DEFAULT NULL,
  `asset_name` varchar(150) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `maintenance_frequency` varchar(50) DEFAULT NULL,
  `work_description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `performed_by` varchar(150) DEFAULT NULL,
  `signature` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `equipment_maintenance_entries` WRITE;
/*!40000 ALTER TABLE `equipment_maintenance_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_maintenance_entries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `equipment_maintenance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_maintenance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(150) DEFAULT NULL,
  `date_submitted` date DEFAULT NULL,
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` date DEFAULT NULL,
  `approved_by` varchar(150) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `equipment_maintenance_logs` WRITE;
/*!40000 ALTER TABLE `equipment_maintenance_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_maintenance_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `facility_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_id` int(11) NOT NULL,
  `section` varchar(150) NOT NULL,
  `item_code` varchar(10) NOT NULL,
  `item_label` text NOT NULL,
  `rating` enum('C','MI','MJ','N/A') DEFAULT NULL,
  `corrective_action` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_id` (`checklist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `facility_checklist_items` WRITE;
/*!40000 ALTER TABLE `facility_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `facility_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `facility_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspector` varchar(150) DEFAULT NULL,
  `building_area` varchar(150) DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `inspection_type` varchar(100) DEFAULT NULL,
  `overall_condition` enum('Excellent','Satisfactory','Unsatisfactory') DEFAULT NULL,
  `summary_findings` text DEFAULT NULL,
  `corrective_action_plan` text DEFAULT NULL,
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` date DEFAULT NULL,
  `approved_by` varchar(150) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `facility_checklists` WRITE;
/*!40000 ALTER TABLE `facility_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `facility_checklists` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `facility_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(150) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `nfc_uid` varchar(100) NOT NULL,
  `status` enum('Available','Borrowed') NOT NULL DEFAULT 'Available',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nfc_uid` (`nfc_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `facility_keys` WRITE;
/*!40000 ALTER TABLE `facility_keys` DISABLE KEYS */;
INSERT INTO `facility_keys` VALUES
(4,'Admin Filing Room Key',NULL,NULL,'KEY-8E4BF210','Available','2026-10-04 10:48:12','2026-10-04 10:48:12'),
(5,'Conference Room Key',NULL,NULL,'KEY-908094ED','Available','2026-10-04 10:48:14','2026-10-04 10:48:14'),
(6,'Gymnasium Storage Key',NULL,NULL,'KEY-5F7E4FDF','Available','2026-10-04 10:48:19','2026-10-04 10:48:19'),
(7,'Lab Cabinet Keys',NULL,NULL,'KEY-7A14AC3B','Available','2026-10-04 10:48:21','2026-10-04 10:48:21'),
(8,'Library Storeroom Key',NULL,NULL,'KEY-D6BC7BA5','Available','2026-10-04 10:48:22','2026-10-04 10:48:22'),
(9,'Server Room Key',NULL,NULL,'KEY-9C5458C1','Available','2026-10-04 10:48:24','2026-10-04 10:48:24');
/*!40000 ALTER TABLE `facility_keys` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fire_extinguishers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fire_extinguishers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` varchar(30) NOT NULL COMMENT 'e.g. FE-ADM-01',
  `type` enum('CO2','Dry Chemical','Wet Chemical','Foam') NOT NULL DEFAULT 'CO2',
  `location` varchar(120) NOT NULL COMMENT 'Building/Area name',
  `floor` varchar(30) NOT NULL DEFAULT 'Ground Floor',
  `department_id` int(11) DEFAULT NULL,
  `weight_kg` decimal(5,1) NOT NULL DEFAULT 6.0,
  `last_inspection` date DEFAULT NULL,
  `next_due` date DEFAULT NULL,
  `status` enum('New','Refillable','Defective','Missing') NOT NULL DEFAULT 'New',
  `year_acquired` year(4) DEFAULT NULL,
  `installed_on` date DEFAULT NULL,
  `expires_on` date DEFAULT NULL,
  `inspector` varchar(100) DEFAULT NULL,
  `assigned_guard` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_id` (`unit_id`),
  KEY `idx_location` (`location`),
  KEY `idx_status` (`status`),
  KEY `idx_next_due` (`next_due`),
  KEY `fire_extinguishers_department_id_foreign` (`department_id`),
  CONSTRAINT `fire_extinguishers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `fire_extinguishers` WRITE;
/*!40000 ALTER TABLE `fire_extinguishers` DISABLE KEYS */;
INSERT INTO `fire_extinguishers` VALUES
(28,'FE-7FC697','CO2','College of Education Building','Ground Floor',NULL,10.0,'2026-08-03','2026-10-10','New',2026,'2026-08-02','2027-04-18','Apolinario Mabini','Fernando Navarro',NULL,'2026-08-02 23:05:23','2026-10-04 00:59:50'),
(29,'FE-A3A248','CO2','College of Art & Sciences Building','Ground Floor',NULL,20.0,'2026-07-15','2027-01-01','New',2026,'2026-08-02','2027-05-25','Armand Perez','Josefa Garcia',NULL,'2026-08-02 23:12:49','2026-10-04 00:59:50'),
(30,'FE-6E7EF8','Dry Chemical','Executive House','Ground Floor',NULL,2.0,'2026-08-31','2027-08-04','New',2026,'2026-08-02','2027-07-01','Cardo Garcia','Fernando Cruz',NULL,'2026-08-02 23:24:17','2026-10-04 00:59:50'),
(31,'FE-14C453','CO2','Animation Lab / ROTC Office','Ground Floor',NULL,3.0,'2026-11-30','2027-08-04','New',2026,'2026-08-02','2027-08-07','Diego Fernandez','Cardo Navarro',NULL,'2026-08-02 23:26:39','2026-10-04 00:59:50'),
(32,'FE-C5AED1','CO2','College of Business Economics and Accountancy','Ground Floor',NULL,5.0,'2026-10-12','2027-08-04','New',2026,'2026-08-02','2027-09-13','Fernando Reyes','Andres Garcia',NULL,'2026-08-02 23:28:26','2026-10-04 00:59:50'),
(33,'FE-E368E2','CO2','College of Law Building','Ground Floor',NULL,3.0,'2026-09-25','2027-08-06','New',2026,'2026-08-02','2026-09-15','Jose Protacio Rizal Mercado y Realonzo Realonda','Diego Manalo',NULL,'2026-08-02 23:49:02','2026-10-04 00:59:50'),
(34,'FE-B76F14','CO2','College of Education Building','2nd Floor',NULL,10.0,'2026-06-13','2027-06-13','New',2026,'2026-08-04','2026-10-22','Josefa Mendoza','Remedios Mendoza',NULL,'2026-08-04 00:19:26','2026-10-04 00:59:50'),
(35,'FE-EEB8BB','Dry Chemical','College of Education Building','3rd Floor',NULL,5.0,'2026-04-05','2027-04-05','New',2026,'2026-08-04','2026-11-28','Juan dela Cruz','Isabel Castillo',NULL,'2026-08-04 00:19:26','2026-10-04 00:59:50'),
(36,'FE-7E1DDD','CO2','College of Business Economics and Accountancy','2nd Floor',NULL,10.0,'2026-07-05','2027-07-05','Refillable',2026,'2026-08-04','2027-01-04','Maisie Therese Tigmo','Col. Arthur Miller',NULL,'2026-08-04 00:19:26','2026-10-04 00:59:50'),
(37,'FE-A9AE7D','Foam','College of Art & Sciences Building','2nd Floor',NULL,6.0,'2026-01-30','2027-01-30','New',2026,'2026-08-04','2027-02-10','Remedios Mendoza','Fernando Navarro',NULL,'2026-08-04 00:19:26','2026-10-04 00:59:50'),
(38,'FE-B8C6CB','CO2','College of Art & Sciences Building','3rd Floor',NULL,10.0,'2026-05-13','2027-05-13','New',2026,'2026-08-04','2027-03-19','Teresa Domingo','Josefa Garcia',NULL,'2026-08-04 00:19:27','2026-10-04 00:59:50'),
(39,'FE-84A046','Dry Chemical','University Cafeteria, Bookstore, Sewing','Ground Floor',NULL,5.0,'2026-02-12','2027-02-12','New',2026,'2026-08-04','2027-04-25','Apolinario Mabini','Fernando Cruz',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(40,'FE-74B590','CO2','University Library','Ground Floor',NULL,10.0,'2026-02-23','2027-02-23','New',2025,'2026-08-04','2027-06-01','Armand Perez','Cardo Navarro',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(41,'FE-B2D5A3','CO2','University Library','2nd Floor',NULL,6.0,'2026-03-10','2027-03-10','Refillable',2023,'2026-08-04','2027-07-08','Cardo Garcia','Andres Garcia',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(42,'FE-C0E3F2','Wet Chemical','Guest House','Ground Floor',NULL,3.0,'2026-02-14','2027-02-14','New',2026,'2026-08-04','2027-08-14','Diego Fernandez','Diego Manalo',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(43,'FE-1D9DB5','Wet Chemical','HRM Kitchen','Ground Floor',NULL,6.0,'2026-02-21','2027-02-21','New',2025,'2026-08-04','2027-09-20','Fernando Reyes','Remedios Mendoza',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(44,'FE-9F925F','CO2','LG Sinco Computer Center Building','Ground Floor',NULL,10.0,'2026-01-29','2027-01-29','New',2026,'2026-08-04','2026-09-22','Jose Protacio Rizal Mercado y Realonzo Realonda','Isabel Castillo',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(45,'FE-6D0A41','CO2','LG Sinco Computer Center Building','2nd Floor',NULL,10.0,'2026-03-12','2027-03-12','New',2026,'2026-08-04','2026-10-29','Josefa Mendoza','Col. Arthur Miller',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(46,'FE-EAD4E5','Dry Chemical','Sofia Soller Sinco Hall','Ground Floor',NULL,6.0,'2026-06-17','2027-06-17','New',2024,'2026-08-04','2026-12-05','Juan dela Cruz','Fernando Navarro',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(47,'FE-79594D','Dry Chemical','Art & Science Laboratories / Audio Visual Rooms','Ground Floor',NULL,5.0,'2026-02-27','2027-02-27','Defective',2021,'2026-08-04','2027-01-11','Maisie Therese Tigmo','Josefa Garcia',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(48,'FE-85BE4D','CO2','College of Nursing','Ground Floor',NULL,10.0,'2026-02-26','2027-02-26','New',2026,'2026-08-04','2027-02-17','Remedios Mendoza','Fernando Cruz',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(49,'FE-B4766A','Foam','College of Nursing','2nd Floor',NULL,6.0,'2026-03-02','2027-03-02','New',2025,'2026-08-04','2027-03-26','Teresa Domingo','Cardo Navarro',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(50,'FE-BE8D98','CO2','Administration Building','Ground Floor',NULL,10.0,'2026-07-09','2027-07-09','New',2026,'2026-08-04','2027-05-02','Apolinario Mabini','Andres Garcia',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(51,'FE-4FBD01','Dry Chemical','Administration Building','2nd Floor',NULL,5.0,'2026-06-24','2027-06-24','Refillable',2022,'2026-08-04','2027-06-08','Armand Perez','Diego Manalo',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(52,'FE-0C362A','Dry Chemical','Registrar\'s Office','Ground Floor',NULL,3.0,'2025-12-13','2026-12-13','New',2025,'2026-08-04','2027-07-15','Cardo Garcia','Remedios Mendoza',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(53,'FE-2F7B1B','CO2','Old College of Industrial Engineering and Technology','Ground Floor',NULL,10.0,'2026-04-14','2026-07-21','Missing',2020,'2026-08-04','2027-08-21','Diego Fernandez','Isabel Castillo',NULL,'2026-08-04 03:43:30','2026-10-04 00:59:50'),
(54,'FE-B1541D','CO2','Parade Ground','Ground Floor',NULL,6.0,'2025-08-15','2026-08-15','New',2025,'2026-08-04','2027-09-27','Fernando Reyes','Col. Arthur Miller',NULL,'2026-08-04 04:47:13','2026-10-04 00:59:50'),
(55,'FE-FC7DFC','Dry Chemical','Guest House','Ground Floor',NULL,5.0,'2025-08-08','2026-08-08','New',2024,'2026-08-04','2026-09-29','Jose Protacio Rizal Mercado y Realonzo Realonda','Fernando Navarro',NULL,'2026-08-04 04:47:13','2026-10-04 00:59:50'),
(56,'FE-E0C2BE','CO2','Executive House','Ground Floor',NULL,10.0,'2025-09-17','2026-09-17','Refillable',2023,'2026-08-04','2026-11-05','Josefa Mendoza','Josefa Garcia',NULL,'2026-08-04 04:47:13','2026-10-04 00:59:50'),
(57,'FE-D6F5E7','CO2','College of Agriculture and SIE','Ground Floor',NULL,10.0,'2026-07-24','2027-07-24','New',2026,'2026-08-04','2026-12-12','Juan dela Cruz','Fernando Cruz',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(58,'FE-DF7293','Dry Chemical','College of Agriculture and SIE','2nd Floor',NULL,5.0,'2026-01-18','2027-01-18','New',2025,'2026-08-04','2027-01-18','Maisie Therese Tigmo','Cardo Navarro',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(59,'FE-05FF77','CO2','College of Agriculture and SIE','3rd Floor',NULL,10.0,'2025-12-19','2026-12-19','Refillable',2022,'2026-08-04','2027-02-24','Remedios Mendoza','Andres Garcia',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(60,'FE-33C25D','Dry Chemical','Museo de Vicente','Ground Floor',NULL,3.0,'2026-03-12','2027-03-12','New',2025,'2026-08-04','2027-04-02','Teresa Domingo','Diego Manalo',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(61,'FE-01F04A','Wet Chemical','Bunk House','Ground Floor',NULL,3.0,'2026-05-04','2027-05-04','New',2026,'2026-08-04','2027-05-09','Apolinario Mabini','Remedios Mendoza',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(62,'FE-072B05','CO2','Electric Pump House','Ground Floor',NULL,6.0,'2026-07-10','2027-07-10','New',2024,'2026-08-04','2027-06-15','Armand Perez','Isabel Castillo',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(63,'FE-82161C','Dry Chemical','Business and Finance Office','Ground Floor',NULL,5.0,'2026-02-25','2027-02-25','New',2026,'2026-08-04','2027-07-22','Cardo Garcia','Col. Arthur Miller',NULL,'2026-08-04 05:09:24','2026-10-04 00:59:50'),
(65,'FE-LIB-014','Dry Chemical','University Library','Ground Floor',NULL,10.0,'2026-09-05','2027-09-05','New',2026,'2026-09-05','2026-08-30','Diego Fernandez',NULL,NULL,'2026-09-05 19:22:18','2026-10-04 00:59:50'),
(66,'FE-ADM-021','CO2','Administration building','Ground Floor',NULL,5.0,'2026-09-04','2027-09-04','New',2026,'2026-09-05','2026-10-06','Fernando Reyes',NULL,NULL,'2026-09-05 19:22:19','2026-10-04 00:59:50'),
(67,'FE-B10AF1','CO2','College of Art & Sciences Building','4th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2026-11-12','Josefa Mendoza',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(68,'FE-C22BE2','Dry Chemical','College of Business Economics and Accountancy','3rd Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2026-12-19','Juan dela Cruz',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(69,'FE-D33CF3','Dry Chemical','College of Business Economics and Accountancy','4th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-01-25','Maisie Therese Tigmo',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(70,'FE-E44D04','CO2','College of Education Building','4th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-03-03','Remedios Mendoza',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(71,'FE-F55E15','CO2','College of Law Building','1st Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-04-09','Teresa Domingo',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(72,'FE-A66F26','CO2','College of Nursing','3rd Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-05-16','Apolinario Mabini',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(73,'FE-B77037','CO2','College of Nursing','4th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-06-22','Armand Perez',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(74,'FE-C88148','CO2','College of Nursing','5th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-07-29','Cardo Garcia',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(75,'FE-D99259','CO2','College of Nursing','6th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2027-09-04','Diego Fernandez',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(76,'FE-EA0A6A','CO2','College of Nursing','7th Floor',NULL,10.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2026-09-06','Fernando Reyes',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50'),
(77,'FE-FB1B7B','Wet Chemical','University Library','3rd Floor',NULL,6.0,'2026-08-15','2027-08-15','New',2026,'2026-09-19','2026-10-13','Jose Protacio Rizal Mercado y Realonzo Realonda',NULL,NULL,'2026-09-19 22:45:28','2026-10-04 00:59:50');
/*!40000 ALTER TABLE `fire_extinguishers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `floor_plan_markers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `floor_plan_markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_file` varchar(200) NOT NULL,
  `equipment_type` enum('Fire Extinguisher','Fire Alarm','Smoke Detector','Emergency Exit Sign') NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `x_pct` decimal(6,3) NOT NULL,
  `y_pct` decimal(6,3) NOT NULL,
  `status` enum('Working','Needs Repair','Missing') NOT NULL DEFAULT 'Working',
  `expires_on` date DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_file` (`plan_file`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `floor_plan_markers` WRITE;
/*!40000 ALTER TABLE `floor_plan_markers` DISABLE KEYS */;
INSERT INTO `floor_plan_markers` VALUES
(14,'ADMINandARTS_Groundﬂoorplan.png','Fire Extinguisher','TEST FE-1',81.000,49.120,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(15,'ADMINandARTS_Groundﬂoorplan.png','Fire Extinguisher','TEST FE-2',68.660,49.000,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(16,'ADMINandARTS_Groundﬂoorplan.png','Fire Extinguisher','TEST FE-3',31.620,55.940,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(17,'ADMINandARTS_Groundﬂoorplan.png','Fire Extinguisher','TEST FE-4',75.670,56.330,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(18,'ADMINandARTS_Groundﬂoorplan.png','Smoke Detector','TEST SD-1',78.380,32.080,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(19,'ADMINandARTS_Groundﬂoorplan.png','Smoke Detector','TEST SD-2',73.190,32.770,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(20,'ADMINandARTS_Groundﬂoorplan.png','Smoke Detector','TEST SD-3',48.820,33.090,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(21,'ADMINandARTS_Groundﬂoorplan.png','Smoke Detector','TEST SD-4',90.230,34.740,'Working','2027-04-04','Test Data','2026-10-04 00:41:44'),
(22,'ADMINandARTS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',65.590,31.620,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(23,'ADMINandARTS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',70.230,31.560,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(24,'ADMINandARTS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-3',9.930,32.590,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(25,'ADMINandARTS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-4',35.540,32.600,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(26,'ADMINandARTS_SecondFloorPlan.png','Smoke Detector','TEST SD-1',61.330,33.120,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(27,'ADMINandARTS_SecondFloorPlan.png','Smoke Detector','TEST SD-2',77.810,34.280,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(28,'ADMINandARTS_SecondFloorPlan.png','Smoke Detector','TEST SD-3',9.890,39.800,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(29,'ADMINandARTS_SecondFloorPlan.png','Smoke Detector','TEST SD-4',18.260,39.800,'Working','2027-04-04','Test Data','2026-10-04 00:41:44'),
(30,'Agriculture Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',84.310,37.760,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(31,'Agriculture Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',31.630,79.050,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(32,'Agriculture Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',87.440,71.430,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(33,'Agriculture Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',23.910,79.350,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(34,'Agriculture Building_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',5.980,77.500,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(35,'Arts and Sciences _FourthFloorPlan.png','Fire Extinguisher','TEST FE-1',63.230,63.160,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(36,'Arts and Sciences_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',16.970,35.610,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(37,'Arts and Sciences_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',18.990,36.510,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(38,'Arts and Sciences_GroundFloorPlan.png','Fire Extinguisher','TEST FE-3',71.860,40.600,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(39,'Arts and Sciences_GroundFloorPlan.png','Fire Extinguisher','TEST FE-4',66.040,53.900,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(40,'Arts and Sciences_GroundFloorPlan.png','Smoke Detector','TEST SD-1',74.310,28.450,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(41,'Arts and Sciences_GroundFloorPlan.png','Smoke Detector','TEST SD-2',9.640,28.600,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(42,'Arts and Sciences_GroundFloorPlan.png','Smoke Detector','TEST SD-3',12.470,40.020,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(43,'Arts and Sciences_GroundFloorPlan.png','Smoke Detector','TEST SD-4',72.690,46.340,'Working','2027-04-04','Test Data','2026-10-04 00:41:44'),
(44,'Arts and Sciences_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',17.190,35.630,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(45,'Arts and Sciences_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',66.900,48.820,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(46,'Arts and Sciences_SecondFloorPlan.png','Fire Extinguisher','TEST FE-3',19.840,64.700,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(47,'Arts and Sciences_SecondFloorPlan.png','Fire Extinguisher','TEST FE-4',70.790,67.010,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(48,'Arts and Sciences_SecondFloorPlan.png','Smoke Detector','TEST SD-1',10.230,23.070,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(49,'Arts and Sciences_SecondFloorPlan.png','Smoke Detector','TEST SD-2',75.010,32.310,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(50,'Arts and Sciences_SecondFloorPlan.png','Smoke Detector','TEST SD-3',10.230,41.440,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(51,'Arts and Sciences_SecondFloorPlan.png','Smoke Detector','TEST SD-4',53.340,58.370,'Working','2027-04-04','Test Data','2026-10-04 00:41:44'),
(52,'Arts and Sciences_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',17.320,35.930,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(53,'Arts and Sciences_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-2',68.140,40.180,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(54,'Arts and Sciences_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-3',58.260,73.410,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(55,'Arts and Sciences_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-4',15.930,88.410,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(56,'Arts and Sciences_ThirdFloorPlan.png','Smoke Detector','TEST SD-1',76.360,24.280,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(57,'Arts and Sciences_ThirdFloorPlan.png','Smoke Detector','TEST SD-2',10.100,39.450,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(58,'Arts and Sciences_ThirdFloorPlan.png','Smoke Detector','TEST SD-3',76.840,46.180,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(59,'Arts and Sciences_ThirdFloorPlan.png','Smoke Detector','TEST SD-4',10.100,58.680,'Working','2027-04-04','Test Data','2026-10-04 00:41:44'),
(60,'Business and Administration_FourthFloorPlan.png','Fire Extinguisher','TEST FE-1',26.440,82.510,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(61,'Business and Administration_FourthFloorPlan.png','Fire Extinguisher','TEST FE-2',73.900,82.490,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(62,'Business and Administration_FourthFloorPlan.png','Smoke Detector','TEST SD-1',35.410,50.020,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(63,'Business and Administration_FourthFloorPlan.png','Smoke Detector','TEST SD-2',64.430,50.020,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(64,'Business and Administration_FourthFloorPlan.png','Smoke Detector','TEST SD-3',14.330,57.780,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(65,'Business and Administration_FourthFloorPlan.png','Smoke Detector','TEST SD-4',85.980,57.770,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(66,'Business and Administration_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',54.070,76.100,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(67,'Business and Administration_GroundFloorPlan.png','Smoke Detector','TEST SD-1',91.920,34.860,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(68,'Business and Administration_GroundFloorPlan.png','Smoke Detector','TEST SD-2',24.960,44.670,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(69,'Business and Administration_GroundFloorPlan.png','Smoke Detector','TEST SD-3',44.080,44.660,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(70,'Business and Administration_GroundFloorPlan.png','Smoke Detector','TEST SD-4',74.380,50.070,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(71,'Business and Administration_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',54.740,71.290,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(72,'Business and Administration_SecondFloorPlan.png','Smoke Detector','TEST SD-1',36.940,44.960,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(73,'Business and Administration_SecondFloorPlan.png','Smoke Detector','TEST SD-2',87.750,44.960,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(74,'Business and Administration_SecondFloorPlan.png','Smoke Detector','TEST SD-3',70.000,44.980,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(75,'Business and Administration_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',53.520,77.470,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(76,'Business and Administration_ThirdFloorPlan.png','Smoke Detector','TEST SD-1',26.110,48.090,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(77,'Business and Administration_ThirdFloorPlan.png','Smoke Detector','TEST SD-2',44.370,48.090,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(78,'Business and Administration_ThirdFloorPlan.png','Smoke Detector','TEST SD-3',69.240,48.090,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(79,'Business and Administration_ThirdFloorPlan.png','Smoke Detector','TEST SD-4',87.490,48.090,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(80,'EDUC_FourthFloorPlan.png','Fire Extinguisher','TEST FE-1',45.900,38.620,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(81,'EDUC_FourthFloorPlan.png','Smoke Detector','TEST SD-1',19.470,60.630,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(82,'EDUC_FourthFloorPlan.png','Smoke Detector','TEST SD-2',83.310,61.270,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(83,'EDUC_FourthFloorPlan.png','Smoke Detector','TEST SD-3',44.290,64.560,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(84,'EDUC_FourthFloorPlan.png','Smoke Detector','TEST SD-4',64.790,64.560,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(85,'EDUC_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',4.260,37.410,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(86,'EDUC_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',67.700,41.950,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(87,'EDUC_GroundFloorPlan.png','Fire Extinguisher','TEST FE-3',27.300,55.270,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(88,'EDUC_GroundFloorPlan.png','Smoke Detector','TEST SD-1',13.740,32.680,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(89,'EDUC_GroundFloorPlan.png','Smoke Detector','TEST SD-2',38.270,59.090,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(90,'EDUC_GroundFloorPlan.png','Smoke Detector','TEST SD-3',82.140,60.010,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(91,'EDUC_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',21.370,45.810,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(92,'EDUC_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',48.460,62.770,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(93,'EDUC_SecondFloorPlan.png','Smoke Detector','TEST SD-1',17.640,67.610,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(94,'EDUC_SecondFloorPlan.png','Smoke Detector','TEST SD-2',34.860,67.610,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(95,'EDUC_SecondFloorPlan.png','Smoke Detector','TEST SD-3',64.970,67.620,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(96,'EDUC_SecondFloorPlan.png','Smoke Detector','TEST SD-4',82.250,67.620,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(97,'EDUC_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',48.240,60.320,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(98,'EDUC_ThirdFloorPlan.png','Smoke Detector','TEST SD-1',14.090,65.340,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(99,'EDUC_ThirdFloorPlan.png','Smoke Detector','TEST SD-2',32.850,65.340,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(100,'EDUC_ThirdFloorPlan.png','Smoke Detector','TEST SD-3',67.020,65.340,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(101,'EDUC_ThirdFloorPlan.png','Smoke Detector','TEST SD-4',86.470,65.340,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(102,'IT Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',42.890,44.040,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(103,'IT Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',42.700,54.510,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(104,'IT Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',39.250,26.140,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(105,'IT Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',32.910,64.380,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(106,'IT Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-3',32.040,68.550,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(107,'IT Building_SecondFloorPlan.png','Smoke Detector','TEST SD-1',48.340,26.380,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(108,'IT Building_SecondFloorPlan.png','Smoke Detector','TEST SD-2',39.750,35.720,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(109,'IT Building_SecondFloorPlan.png','Smoke Detector','TEST SD-3',56.110,42.730,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(110,'IT Building_SecondFloorPlan.png','Smoke Detector','TEST SD-4',31.020,44.840,'Missing',NULL,'Test Data','2026-10-04 00:41:44'),
(111,'IT Building_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',21.890,34.580,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(112,'IT Building_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-2',84.060,46.020,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(113,'IT Building_ThirdFloorPlan.png','Smoke Detector','TEST SD-1',33.320,40.080,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(114,'IT Building_ThirdFloorPlan.png','Smoke Detector','TEST SD-2',78.870,41.870,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(115,'IT Building_ThirdFloorPlan.png','Smoke Detector','TEST SD-3',40.950,41.740,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(116,'IT Building_ThirdFloorPlan.png','Smoke Detector','TEST SD-4',85.280,42.880,'Working','2027-05-10','Test Data','2026-10-04 00:41:44'),
(117,'Kennel Caf_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',68.740,40.790,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(118,'Kennel Caf_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',13.310,52.650,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(119,'Kennel Caf_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',33.720,25.530,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(120,'Kennel Caf_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',55.540,78.330,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(121,'Law Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',33.120,54.700,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(122,'Law Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',32.590,66.370,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(123,'Law Building_GroundFloorPlan.png','Smoke Detector','TEST SD-1',50.860,77.060,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(124,'Law Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',29.050,48.050,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(125,'Main Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',74.600,51.700,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(126,'Main Building_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',11.200,70.700,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(127,'Main Building_GroundFloorPlan.png','Smoke Detector','TEST SD-1',50.000,50.000,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(128,'Main Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',60.400,49.500,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(129,'Main Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',32.800,64.600,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(130,'Main Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-3',8.200,70.000,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(131,'Main Building_SecondFloorPlan.png','Fire Extinguisher','TEST FE-4',34.400,78.000,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(132,'Main Building_SecondFloorPlan.png','Smoke Detector','TEST SD-1',45.000,40.000,'Working','2026-10-24','Test Data','2026-10-04 00:41:44'),
(133,'Main Library_Ground FloorPlan.png','Fire Extinguisher','TEST FE-1',54.190,31.120,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(134,'Main Library_Ground FloorPlan.png','Fire Extinguisher','TEST FE-2',77.580,37.420,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(135,'Main Library_Ground FloorPlan.png','Fire Extinguisher','TEST FE-3',32.390,89.430,'Working','2027-06-01','Test Data','2026-10-04 00:41:44'),
(136,'Main Library_Ground FloorPlan.png','Fire Extinguisher','TEST FE-4',18.120,90.200,'Needs Repair','2027-03-01','Test Data','2026-10-04 00:41:44'),
(137,'Main Library_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',61.910,90.560,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(138,'Main Library_ThirdFloorPlan.png','Fire Extinguisher','TEST FE-1',62.420,90.440,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(139,'Museum.png','Fire Extinguisher','TEST FE-1',32.870,37.830,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(140,'Museum.png','Fire Extinguisher','TEST FE-2',32.180,81.250,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(141,'SSS_GroundFloorPlan.png','Fire Extinguisher','TEST FE-1',87.000,21.580,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(142,'SSS_GroundFloorPlan.png','Fire Extinguisher','TEST FE-2',26.270,65.670,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(143,'SSS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-1',47.850,30.720,'Working','2026-08-15','Test Data','2026-10-04 00:41:44'),
(144,'SSS_SecondFloorPlan.png','Fire Extinguisher','TEST FE-2',47.570,83.880,'Working','2026-10-16','Test Data','2026-10-04 00:41:44'),
(145,'Agriculture Building_ThirdFloorPlan.png','Fire Extinguisher',NULL,16.664,58.133,'Working',NULL,'Security Test Account','2026-10-04 00:47:48');
/*!40000 ALTER TABLE `floor_plan_markers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fuel_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fuel_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `odometer_km` decimal(10,1) NOT NULL,
  `liters_filled` decimal(8,2) NOT NULL,
  `logged_at` date NOT NULL,
  `logged_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `fuel_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `fuel_logs` WRITE;
/*!40000 ALTER TABLE `fuel_logs` DISABLE KEYS */;
INSERT INTO `fuel_logs` VALUES
(1,7,5.0,5.00,'2026-09-20','Kenchie Terante','','2026-09-20 16:02:21'),
(2,7,10.0,5.00,'2026-09-21','Kenchie Terante','','2026-09-20 16:06:37');
/*!40000 ALTER TABLE `fuel_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `gps_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `gps_logs` WRITE;
/*!40000 ALTER TABLE `gps_logs` DISABLE KEYS */;
INSERT INTO `gps_logs` VALUES
(1,2,'FU-GPS-802',9.3103500,123.3080000,'Strong (98%)','Online','2026-07-31 23:20:24'),
(2,3,'FU-GPS-431',9.3050000,123.3010000,'Strong (91%)','Online','2026-07-31 23:20:24'),
(3,5,'FU-GPS-204',9.3120000,123.3150000,'Medium (74%)','Online','2026-07-31 23:20:24');
/*!40000 ALTER TABLE `gps_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `janitorial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `janitorial` WRITE;
/*!40000 ALTER TABLE `janitorial` DISABLE KEYS */;
/*!40000 ALTER TABLE `janitorial` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `janitorial_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `janitorial_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_name` varchar(120) NOT NULL,
  `assigned_zone` varchar(120) NOT NULL,
  `floor` varchar(30) NOT NULL DEFAULT 'Ground Floor',
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL,
  `date_assigned` date NOT NULL,
  `status` enum('Active','Off Duty','On Leave') NOT NULL DEFAULT 'Active',
  `priority` enum('Routine','Urgent') NOT NULL DEFAULT 'Routine',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_assigned`),
  KEY `idx_zone` (`assigned_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `janitorial_assignments` WRITE;
/*!40000 ALTER TABLE `janitorial_assignments` DISABLE KEYS */;
INSERT INTO `janitorial_assignments` VALUES
(1,'Bautista, M.','Admin Building','Ground Floor','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(2,'Dizon, L.','Library','Ground Floor','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(3,'Fernandez, G.','Science Building','Ground Floor','06:00:00','14:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(4,'Hernandez, K.','Gymnasium','Ground Floor','05:00:00','13:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(5,'Ignacio, P.','Canteen','Ground Floor','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(6,'Javier, C.','Engineering','Ground Floor','08:00:00','16:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(7,'Lacson, A.','CCS Building','Ground Floor','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(8,'Mendez, R.','Clinic','Ground Floor','07:00:00','15:00:00','2026-07-19','Active','Routine','2026-07-19 05:37:22'),
(10,'Janitorial Staff','CCS Building','Ground Floor','08:00:00','17:00:00','2026-08-30','Active','Routine','2026-08-03 15:42:45'),
(11,'Janitorial Staff','Admin Building','Ground Floor','08:00:00','17:00:00','2026-08-06','Active','Routine','2026-08-04 07:43:56'),
(12,'Ramos, E.','Canteen','Ground Floor','13:00:00','21:00:00','2026-10-03','Active','Routine','2026-10-03 18:29:44'),
(13,'Aquino, J.','Clinic','Ground Floor','14:00:00','22:00:00','2026-10-03','Active','Routine','2026-10-03 18:29:44'),
(14,'Villanueva, M.','Science Building','Ground Floor','15:00:00','23:00:00','2026-10-03','Active','Urgent','2026-10-03 18:29:44'),
(15,'Dela Cruz, P.','Gymnasium','Ground Floor','12:00:00','20:00:00','2026-10-03','Active','Routine','2026-10-03 18:30:23'),
(16,'Reyes, A.','Engineering','Ground Floor','13:00:00','21:00:00','2026-10-03','Active','Routine','2026-10-03 18:30:23'),
(17,'Santos, L.','Admin Building','2nd Floor','12:30:00','20:30:00','2026-10-03','Active','Routine','2026-10-03 18:30:23'),
(18,'Garcia, R.','Library','Ground Floor','14:00:00','22:00:00','2026-10-03','Active','Routine','2026-10-03 18:30:23'),
(19,'Mercado, T.','CCS Building','2nd Floor','15:00:00','23:00:00','2026-10-03','Active','Urgent','2026-10-03 18:30:23'),
(20,'Lim, C.','Gymnasium','2nd Floor','16:00:00','00:00:00','2026-10-03','Off Duty','Routine','2026-10-03 18:30:23'),
(21,'Bautista, J.','Clinic','2nd Floor','12:00:00','18:00:00','2026-10-03','Active','Routine','2026-10-03 18:30:23');
/*!40000 ALTER TABLE `janitorial_assignments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `janitorial_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `janitorial_inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `building` varchar(150) NOT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `inspection_month` char(7) NOT NULL,
  `result` enum('Passed','Needs Attention') NOT NULL,
  `inspected_by` varchar(150) NOT NULL,
  `notes` text DEFAULT NULL,
  `inspected_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `building` (`building`,`inspection_month`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `janitorial_inspections` WRITE;
/*!40000 ALTER TABLE `janitorial_inspections` DISABLE KEYS */;
INSERT INTO `janitorial_inspections` VALUES
(2,'University Library',NULL,'2026-10','Passed','Facilities Test Account',NULL,'2026-10-03 17:50:50'),
(3,'College of Nursing',NULL,'2026-10','Passed','Facilities Test Account',NULL,'2026-10-03 17:50:50'),
(4,'Administration Building',NULL,'2026-10','Needs Attention','Facilities Test Account',NULL,'2026-10-03 17:50:50'),
(5,'College of Law Building',NULL,'2026-10','Passed','Facilities Test Account',NULL,'2026-10-03 17:51:50'),
(6,'Executive House',NULL,'2026-10','Passed','Facilities Test Account',NULL,'2026-10-03 17:51:50'),
(7,'Guest House',NULL,'2026-10','Needs Attention','Facilities Test Account','Lights in hallway need replacing','2026-10-03 17:51:50'),
(8,'HRM Kitchen',NULL,'2026-10','Passed','Facilities Test Account',NULL,'2026-10-03 17:51:50'),
(11,'University Cafeteria, Bookstore, Sewing',NULL,'2026-10','Passed','Facilities Test Account','okay natu','2026-10-03 20:53:05'),
(13,'Guest House',NULL,'2026-10','Passed','Facilities Test Account','Goods Nani','2026-10-03 20:57:34');
/*!40000 ALTER TABLE `janitorial_inspections` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `janitorial_task_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `janitorial_task_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `zone` varchar(255) NOT NULL,
  `task_name` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `performed_by` varchar(150) DEFAULT NULL,
  `archived_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assignment_id` (`assignment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `janitorial_task_history` WRITE;
/*!40000 ALTER TABLE `janitorial_task_history` DISABLE KEYS */;
INSERT INTO `janitorial_task_history` VALUES
(2,1,'Admin Building','Sweep & mop corridors','done','2025-07-18 07:30:00','Bautista, M.','2026-09-28 10:38:38'),
(3,1,'Admin Building','Clean restrooms — Floor 1','done','2025-07-18 08:00:00','Bautista, M.','2026-09-28 10:38:38'),
(4,1,'Admin Building','Empty trash bins','done','2025-07-18 08:30:00','Bautista, M.','2026-09-28 10:38:38'),
(5,1,'Admin Building','Wipe window sills','done','2025-07-18 09:00:00','Bautista, M.','2026-09-28 10:38:38'),
(6,1,'Admin Building','Mop main lobby','done','2025-07-18 09:30:00','Bautista, M.','2026-09-28 10:38:38'),
(7,1,'Admin Building','Replenish soap & tissue','done','2025-07-18 10:00:00','Bautista, M.','2026-09-28 10:38:38'),
(8,2,'Library','Dust bookshelves','done','2025-07-18 07:15:00','Dizon, L.','2026-09-28 10:38:38'),
(9,2,'Library','Vacuum reading area','done','2025-07-18 07:45:00','Dizon, L.','2026-09-28 10:38:38'),
(10,2,'Library','Mop entrance','done','2025-07-18 08:15:00','Dizon, L.','2026-09-28 10:38:38'),
(11,3,'Science Building','Sweep lab corridors','done','2025-07-18 06:30:00','Fernandez, G.','2026-09-28 10:38:38'),
(12,4,'Gymnasium','Sweep gym floor','done','2025-07-18 05:30:00','Hernandez, K.','2026-09-28 10:38:38'),
(13,4,'Gymnasium','Mop court','done','2025-07-18 06:00:00','Hernandez, K.','2026-09-28 10:38:38'),
(14,4,'Gymnasium','Clean locker rooms','done','2025-07-18 06:45:00','Hernandez, K.','2026-09-28 10:38:38'),
(15,4,'Gymnasium','Empty trash bins','done','2025-07-18 07:15:00','Hernandez, K.','2026-09-28 10:38:38'),
(16,5,'Canteen','Wipe dining tables','done','2025-07-18 07:00:00','Ignacio, P.','2026-09-28 10:38:38'),
(17,5,'Canteen','Sweep floor','done','2025-07-18 07:20:00','Ignacio, P.','2026-09-28 10:38:38'),
(18,5,'Canteen','Mop canteen floor','done','2025-07-18 07:45:00','Ignacio, P.','2026-09-28 10:38:38'),
(19,5,'Canteen','Clean restrooms','done','2025-07-18 08:15:00','Ignacio, P.','2026-09-28 10:38:38'),
(20,6,'Engineering','Sweep corridors','done','2025-07-18 08:10:00','Javier, C.','2026-09-28 10:38:38'),
(21,7,'CCS Building','Sweep corridors','done','2025-07-18 07:10:00','Lacson, A.','2026-09-28 10:38:38'),
(22,7,'CCS Building','Mop server room hallway','done','2025-07-18 07:35:00','Lacson, A.','2026-09-28 10:38:38'),
(23,7,'CCS Building','Clean restrooms','done','2025-07-18 08:00:00','Lacson, A.','2026-09-28 10:38:38'),
(24,7,'CCS Building','Wipe workstations','done','2025-07-18 08:30:00','Lacson, A.','2026-09-28 10:38:38'),
(25,7,'CCS Building','Empty trash','done','2025-07-18 09:00:00','Lacson, A.','2026-09-28 10:38:38'),
(26,8,'Clinic','Sanitize consultation room','done','2025-07-18 07:05:00','Mendez, R.','2026-09-28 10:38:38'),
(27,8,'Clinic','Mop clinic floor','done','2025-07-18 07:30:00','Mendez, R.','2026-09-28 10:38:38'),
(28,8,'Clinic','Clean restroom','done','2025-07-18 07:55:00','Mendez, R.','2026-09-28 10:38:38'),
(29,8,'Clinic','Replace biohazard bags','done','2025-07-18 08:20:00','Mendez, R.','2026-09-28 10:38:38'),
(30,2,'Library','Dust bookshelves','done','2026-10-03 17:50:50','Dizon, L.','2026-10-06 20:30:04'),
(31,2,'Library','Vacuum reading area','done','2026-10-03 17:50:50','Dizon, L.','2026-10-06 20:30:04'),
(32,2,'Library','Mop entrance','done','2026-10-03 17:50:50','Dizon, L.','2026-10-06 20:30:04'),
(33,11,'Admin Building','Scheduled Cleaning: Admin Building','done','2026-10-03 17:50:50','Janitorial Staff','2026-10-06 20:30:04'),
(34,12,'Canteen','Sweep dining area','done','2026-10-03 18:29:44','Ramos, E.','2026-10-06 20:30:04'),
(35,12,'Canteen','Wipe tables','done','2026-10-03 18:29:44','Ramos, E.','2026-10-06 20:30:04'),
(36,13,'Clinic','Disinfect waiting area','done','2026-10-03 18:29:44','Aquino, J.','2026-10-06 20:30:04'),
(37,13,'Clinic','Clean restroom','done','2026-10-03 18:29:44','Aquino, J.','2026-10-06 20:30:04'),
(38,13,'Clinic','Refill soap dispensers','done','2026-10-03 18:29:44','Aquino, J.','2026-10-06 20:30:04'),
(39,15,'Gymnasium','Sweep court','done','2026-10-03 18:30:23','Dela Cruz, P.','2026-10-06 20:30:04'),
(40,16,'Engineering','Clean workshop floor','done','2026-10-03 18:30:23','Reyes, A.','2026-10-06 20:30:04'),
(41,16,'Engineering','Wipe equipment tables','done','2026-10-03 18:30:23','Reyes, A.','2026-10-06 20:30:04'),
(42,18,'Library','Sweep reading room','done','2026-10-03 18:30:23','Garcia, R.','2026-10-06 20:30:04'),
(43,18,'Library','Dust bookshelves','done','2026-10-03 18:30:23','Garcia, R.','2026-10-06 20:30:04'),
(44,18,'Library','Clean restroom','done','2026-10-03 18:30:23','Garcia, R.','2026-10-06 20:30:04'),
(45,18,'Library','Empty trash bins','done','2026-10-03 18:30:23','Garcia, R.','2026-10-06 20:30:04'),
(46,21,'Clinic','Disinfect exam rooms','done','2026-10-03 18:30:23','Bautista, J.','2026-10-06 20:30:04'),
(47,21,'Clinic','Restock tissue','done','2026-10-03 18:30:23','Bautista, J.','2026-10-06 20:30:04'),
(48,21,'Clinic','Mop floor','done','2026-10-03 18:30:23','Bautista, J.','2026-10-06 20:30:04');
/*!40000 ALTER TABLE `janitorial_task_history` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `janitorial_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `janitorial_tasks` WRITE;
/*!40000 ALTER TABLE `janitorial_tasks` DISABLE KEYS */;
INSERT INTO `janitorial_tasks` VALUES
(1,1,'Sweep & mop corridors',0,NULL,'2026-07-19 05:37:22'),
(2,1,'Clean restrooms — Floor 1',0,NULL,'2026-07-19 05:37:22'),
(3,1,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),
(4,1,'Wipe window sills',0,NULL,'2026-07-19 05:37:22'),
(5,1,'Mop main lobby',0,NULL,'2026-07-19 05:37:22'),
(6,1,'Replenish soap & tissue',0,NULL,'2026-07-19 05:37:22'),
(7,1,'Clean comfort rooms — Floor 2',0,NULL,'2026-07-19 05:37:22'),
(8,1,'General sanitizing',0,NULL,'2026-07-19 05:37:22'),
(9,2,'Dust bookshelves',0,NULL,'2026-07-19 05:37:22'),
(10,2,'Vacuum reading area',0,NULL,'2026-07-19 05:37:22'),
(11,2,'Mop entrance',0,NULL,'2026-07-19 05:37:22'),
(12,2,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),
(13,2,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),
(14,2,'Wipe computer tables',0,NULL,'2026-07-19 05:37:22'),
(15,3,'Sweep lab corridors',0,NULL,'2026-07-19 05:37:22'),
(16,3,'Mop stairs',0,NULL,'2026-07-19 05:37:22'),
(17,3,'Empty lab trash',0,NULL,'2026-07-19 05:37:22'),
(18,3,'Sanitize lab benches',0,NULL,'2026-07-19 05:37:22'),
(19,3,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),
(20,4,'Sweep gym floor',0,NULL,'2026-07-19 05:37:22'),
(21,4,'Mop court',0,NULL,'2026-07-19 05:37:22'),
(22,4,'Clean locker rooms',0,NULL,'2026-07-19 05:37:22'),
(23,4,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),
(24,5,'Wipe dining tables',0,NULL,'2026-07-19 05:37:22'),
(25,5,'Sweep floor',0,NULL,'2026-07-19 05:37:22'),
(26,5,'Mop canteen floor',0,NULL,'2026-07-19 05:37:22'),
(27,5,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),
(28,5,'Empty grease traps',0,NULL,'2026-07-19 05:37:22'),
(29,5,'Sanitize counter tops',0,NULL,'2026-07-19 05:37:22'),
(30,5,'Replace trash liners',0,NULL,'2026-07-19 05:37:22'),
(31,6,'Sweep corridors',0,NULL,'2026-07-19 05:37:22'),
(32,6,'Mop workshop floor',0,NULL,'2026-07-19 05:37:22'),
(33,6,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),
(34,6,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),
(35,6,'Wipe notice boards',0,NULL,'2026-07-19 05:37:22'),
(36,6,'Sanitize door handles',0,NULL,'2026-07-19 05:37:22'),
(37,7,'Sweep corridors',0,NULL,'2026-07-19 05:37:22'),
(38,7,'Mop server room hallway',0,NULL,'2026-07-19 05:37:22'),
(39,7,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),
(40,7,'Wipe workstations',0,NULL,'2026-07-19 05:37:22'),
(41,7,'Empty trash',0,NULL,'2026-07-19 05:37:22'),
(42,8,'Sanitize consultation room',0,NULL,'2026-07-19 05:37:22'),
(43,8,'Mop clinic floor',0,NULL,'2026-07-19 05:37:22'),
(44,8,'Clean restroom',0,NULL,'2026-07-19 05:37:22'),
(45,8,'Replace biohazard bags',0,NULL,'2026-07-19 05:37:22'),
(47,10,'Scheduled Cleaning: CCS Building',0,NULL,'2026-08-03 15:42:45'),
(48,11,'Scheduled Cleaning: Admin Building',0,NULL,'2026-08-04 07:43:56'),
(49,12,'Sweep dining area',0,NULL,'2026-10-03 18:29:44'),
(50,12,'Wipe tables',0,NULL,'2026-10-03 18:29:44'),
(51,12,'Mop kitchen floor',0,NULL,'2026-10-03 18:29:44'),
(52,12,'Empty trash bins',0,NULL,'2026-10-03 18:29:44'),
(53,13,'Disinfect waiting area',0,NULL,'2026-10-03 18:29:44'),
(54,13,'Clean restroom',0,NULL,'2026-10-03 18:29:44'),
(55,13,'Refill soap dispensers',0,NULL,'2026-10-03 18:29:44'),
(56,14,'Sweep laboratory floor',0,NULL,'2026-10-03 18:29:44'),
(57,14,'Clean lab benches',0,NULL,'2026-10-03 18:29:44'),
(58,14,'Empty trash bins',0,NULL,'2026-10-03 18:29:44'),
(59,14,'Mop corridor',0,NULL,'2026-10-03 18:29:44'),
(60,15,'Sweep court',0,NULL,'2026-10-03 18:30:23'),
(61,15,'Mop court',0,NULL,'2026-10-03 18:30:23'),
(62,15,'Empty trash bins',0,NULL,'2026-10-03 18:30:23'),
(63,16,'Clean workshop floor',0,NULL,'2026-10-03 18:30:23'),
(64,16,'Wipe equipment tables',0,NULL,'2026-10-03 18:30:23'),
(65,16,'Empty trash bins',0,NULL,'2026-10-03 18:30:23'),
(66,16,'Restock supplies',0,NULL,'2026-10-03 18:30:23'),
(67,17,'Dust office desks',0,NULL,'2026-10-03 18:30:23'),
(68,17,'Clean windows',0,NULL,'2026-10-03 18:30:23'),
(69,17,'Mop hallway',0,NULL,'2026-10-03 18:30:23'),
(70,18,'Sweep reading room',0,NULL,'2026-10-03 18:30:23'),
(71,18,'Dust bookshelves',0,NULL,'2026-10-03 18:30:23'),
(72,18,'Clean restroom',0,NULL,'2026-10-03 18:30:23'),
(73,18,'Empty trash bins',0,NULL,'2026-10-03 18:30:23'),
(74,19,'Clean computer lab',0,NULL,'2026-10-03 18:30:23'),
(75,19,'Wipe keyboards',0,NULL,'2026-10-03 18:30:23'),
(76,19,'Empty trash bins',0,NULL,'2026-10-03 18:30:23'),
(77,20,'Clean bleachers',0,NULL,'2026-10-03 18:30:23'),
(78,20,'Mop locker rooms',0,NULL,'2026-10-03 18:30:23'),
(79,21,'Disinfect exam rooms',0,NULL,'2026-10-03 18:30:23'),
(80,21,'Restock tissue',0,NULL,'2026-10-03 18:30:23'),
(81,21,'Mop floor',0,NULL,'2026-10-03 18:30:23');
/*!40000 ALTER TABLE `janitorial_tasks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `job_orders` WRITE;
/*!40000 ALTER TABLE `job_orders` DISABLE KEYS */;
INSERT INTO `job_orders` VALUES
(1,'JO-2026-001','Campus Landscaping Improvement','Grounds Beautification Phase 2','Construction Worker',1,'Main Campus Grounds',4,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE','Ongoing landscaping and grounds improvement across the main campus.',NULL,0,'2026-08-29 22:31:31',NULL),
(2,'JO-2026-002','College of Nursing Building Renovation','CoN Facility Upgrade','Carpenter',1,'College of Nursing',6,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE','Interior renovation works for the College of Nursing building.',NULL,0,'2026-08-29 22:31:31',NULL),
(3,'JO-2026-003','University Library Electrical Rewiring','Library Systems Upgrade','Maintenance Technician',1,'University Library',3,'Col. Arthur Miller','2026-03-01','2026-07-31','ACTIVE','Full electrical rewiring of the University Library building.',NULL,0,'2026-08-29 22:31:31',NULL),
(4,'JO-2026-004','Perimeter Fence Painting Project','Campus Exterior Maintenance','Carpenter',1,'Campus Perimeter',2,'Dr. Helen Peralta','2026-01-10','2026-03-10','COMPLETED','Repainting of the campus perimeter fence and gates.',NULL,0,'2026-08-29 22:31:31',NULL),
(5,'JO-2026-005','New Gymnasium Construction Support','Gymnasium Expansion','Construction Foreman',1,'Gymnasium Site',5,'Remedios Ocampo','2026-10-01','2027-04-01','PENDING','Support labor for the new gymnasium construction project.',NULL,0,'2026-08-29 22:31:31',NULL);
/*!40000 ALTER TABLE `job_orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `key_borrow_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `key_borrow_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_number` varchar(20) NOT NULL,
  `borrower_id` varchar(50) NOT NULL COMMENT 'Scanned ID number',
  `trip_ticket_id` int(11) DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `department` varchar(100) NOT NULL,
  `key_item` varchar(150) NOT NULL COMMENT 'What key/item was borrowed',
  `key_id` int(11) DEFAULT NULL,
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
  KEY `key_borrow_logs_key_id_foreign` (`key_id`),
  CONSTRAINT `fk_keylog_trip` FOREIGN KEY (`trip_ticket_id`) REFERENCES `travel_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_borrow_logs_key_id_foreign` FOREIGN KEY (`key_id`) REFERENCES `facility_keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `key_borrow_logs` WRITE;
/*!40000 ALTER TABLE `key_borrow_logs` DISABLE KEYS */;
INSERT INTO `key_borrow_logs` VALUES
(1,'KL-001','EMP-2021-042',NULL,'Dela Cruz, J.','Library','Library Storeroom Key',NULL,'2026-07-29 07:30:00','2026-07-29 12:00:00','Returned','Santos, J.','2026-07-19 05:37:22'),
(2,'KL-002','EMP-2022-118',NULL,'Magsaysay, R.','CCS','Server Room Key',NULL,'2026-07-29 08:15:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),
(3,'KL-003','FAC-2020-007',NULL,'Torres, F.','Science','Lab Cabinet Keys',NULL,'2026-07-29 09:00:00','2026-07-29 10:30:00','Returned','Dela Cruz, P.','2026-07-19 05:37:22'),
(4,'KL-004','EMP-2019-055',NULL,'Reyes, A.','Admin','Admin Filing Room Key',NULL,'2026-07-29 10:45:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),
(5,'KL-005','EMP-2020-034',NULL,'Juan dela Cruz','Logistics','Library storeroom key',NULL,'2026-08-01 19:34:59','2026-08-01 19:35:00','Returned','Test Guard','2026-08-02 03:34:59'),
(7,'KL-006','EMP-2023-090',NULL,'Ramirez, S.','Athletics','Gymnasium Storage Key',NULL,'2026-10-01 06:45:00',NULL,'Active','Santos, J.','2026-10-01 02:27:39'),
(8,'KL-007','EMP-2018-021',NULL,'Peralta, H.','Administration','Conference Room Key',NULL,'2026-10-01 08:00:00','2026-10-01 09:15:00','Returned','Santos, J.','2026-10-01 02:27:39');
/*!40000 ALTER TABLE `key_borrow_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `mechanical_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mechanical_equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `equipment_type` varchar(80) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `status` enum('Operational','Needs Repair','Under Maintenance','Out of Service') NOT NULL DEFAULT 'Operational',
  `last_service` date DEFAULT NULL,
  `next_service` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `mechanical_equipment` WRITE;
/*!40000 ALTER TABLE `mechanical_equipment` DISABLE KEYS */;
INSERT INTO `mechanical_equipment` VALUES
(1,'ME-GEN-01','Diesel Generator 50kVA','Generator','Power House','Operational','2026-08-25','2026-11-23','Sample data','2026-10-04 01:15:24'),
(2,'ME-GEN-02','Standby Generator 20kVA','Generator','Administration Building','Needs Repair','2026-07-01','2026-09-29','Sample data - fails to start','2026-10-04 01:15:24'),
(3,'ME-PMP-01','Main Water Pump','Water Pump','Pump House','Operational','2026-09-14','2026-12-13','Sample data','2026-10-04 01:15:24'),
(4,'ME-PMP-02','Booster Pump','Water Pump','College of Law Building','Under Maintenance','2026-10-01','2026-10-16','Sample data','2026-10-04 01:15:24'),
(5,'ME-CMP-01','Air Compressor','Compressor','Motor Pool Shop','Operational','2026-08-05','2026-10-09','Sample data','2026-10-04 01:15:24'),
(6,'ME-MOW-01','Ride-on Lawn Mower','Grounds Equipment','Motor Pool Shop','Needs Repair','2026-06-06','2026-09-04','Sample data','2026-10-04 01:15:24'),
(7,'ME-MOW-02','Brush Cutter','Grounds Equipment','Motor Pool Shop','Operational','2026-09-19','2026-12-18','Sample data','2026-10-04 01:15:24'),
(8,'ME-WLD-01','Welding Machine','Workshop Equipment','Carpentry Shop','Out of Service','2026-03-18',NULL,'Sample data - beyond repair','2026-10-04 01:15:24');
/*!40000 ALTER TABLE `mechanical_equipment` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(15,'2024-01-01-000001','App\\Database\\Migrations\\CreateUsersTable','default','App',1787709579,1),
(16,'2024-01-01-000002','App\\Database\\Migrations\\CreateDepartmentsTable','default','App',1787709579,1),
(17,'2024-01-01-000003','App\\Database\\Migrations\\CreatePersonnelTable','default','App',1787709579,1),
(18,'2024-01-01-000004','App\\Database\\Migrations\\CreateVehiclesTable','default','App',1787709579,1),
(19,'2024-01-01-000005','App\\Database\\Migrations\\CreateTravelRequestsTable','default','App',1787709579,1),
(20,'2024-01-01-000006','App\\Database\\Migrations\\CreateGpsLogsTable','default','App',1787709579,1),
(21,'2024-01-01-000007','App\\Database\\Migrations\\CreateToolsTable','default','App',1787709579,1),
(22,'2024-01-01-000008','App\\Database\\Migrations\\CreateNotificationsTable','default','App',1787709579,1),
(23,'2024-01-01-000009','App\\Database\\Migrations\\CreateBorrowsTable','default','App',1787709579,1),
(24,'2024-01-01-000010','App\\Database\\Migrations\\CreateReturnsTable','default','App',1787709579,1),
(25,'2024-01-01-000011','App\\Database\\Migrations\\CreatePredictionsTable','default','App',1787709579,1),
(26,'2024-01-01-000012','App\\Database\\Migrations\\CreateActivityLogsTable','default','App',1787709579,1),
(27,'2024-01-01-000013','App\\Database\\Migrations\\CreateReportsTable','default','App',1787709579,1),
(28,'2024-01-01-000014','App\\Database\\Migrations\\CreateJanitorialLogsTable','default','App',1787709579,1),
(29,'2024-01-01-000016','App\\Database\\Migrations\\CreateJobOrdersTable','default','App',1787709604,2),
(30,'2024-01-01-000017','App\\Database\\Migrations\\CreatePersonnelAssignmentsTable','default','App',1787709604,2),
(31,'2024-01-01-000018','App\\Database\\Migrations\\CreatePersonnelContractsTable','default','App',1787709604,2),
(32,'2024-01-01-000019','App\\Database\\Migrations\\CreateDocumentRequirementTypesTable','default','App',1787709604,2),
(33,'2024-01-01-000020','App\\Database\\Migrations\\CreatePersonnelDocumentsTable','default','App',1787709604,2),
(34,'2024-01-01-000021','App\\Database\\Migrations\\AddEmploymentTypeToPersonnelTable','default','App',1787709604,2),
(35,'2026-09-05-000001','AppDatabaseMigrationsCreateMaintenanceFormsTables','default','App',1788608812,3),
(36,'2026-09-12-000001','App\\Database\\Migrations\\AddUnitToToolsTable','default','App',1789200315,4),
(37,'2026-09-12-000002','App\\Database\\Migrations\\AddDepartmentToFireExtinguishersTable','default','App',1789200315,4),
(38,'2026-09-12-000003','App\\Database\\Migrations\\CreateFuelLogsTable','default','App',1789200315,4),
(39,'2026-09-14-000001','App\\Database\\Migrations\\CreateFacilityKeysTable','default','App',1789396190,5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `motorpool_wo_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `motorpool_wo_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `work_order_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `changed_by` varchar(150) NOT NULL,
  `changed_at` datetime NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `work_order_id` (`work_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `motorpool_wo_history` WRITE;
/*!40000 ALTER TABLE `motorpool_wo_history` DISABLE KEYS */;
INSERT INTO `motorpool_wo_history` VALUES
(1,1,'Pending','Sample Requester','2026-10-03 01:15:24','Work order created'),
(2,2,'Pending','Sample Requester','2026-10-01 01:15:24','Work order created'),
(3,3,'Pending','Sample Requester','2026-09-20 01:15:24','Work order created'),
(4,4,'Pending','Sample Requester','2026-10-02 01:15:24','Work order created'),
(5,5,'Pending','Sample Requester','2026-10-03 21:15:24','Work order created'),
(6,6,'Pending','Sample Requester','2026-09-25 01:15:24','Work order created'),
(7,7,'Pending','Sample Requester','2026-09-14 01:15:24','Work order created'),
(8,2,'In Progress','Motor Pool Mechanic','2026-10-02 01:15:24','Work started'),
(9,3,'In Progress','Motor Pool Mechanic','2026-09-24 01:15:24','Work started'),
(10,4,'In Progress','Motor Pool Mechanic','2026-10-03 01:15:24','Work started'),
(11,6,'In Progress','Motor Pool Mechanic','2026-09-28 01:15:24','Work started'),
(15,3,'Completed','Motor Pool Mechanic','2026-09-24 01:15:24','Work finished'),
(16,6,'Completed','Motor Pool Mechanic','2026-09-28 01:15:24','Work finished'),
(18,7,'Cancelled','Motor Pool Head','2026-09-15 01:15:24','Request cancelled'),
(22,2,'Completed','Assets Test Account','2026-10-04 04:32:13','Work finished'),
(23,1,'In Progress','Assets Test Account','2026-10-04 13:19:14','Work started'),
(24,1,'Completed','Assets Test Account','2026-10-04 13:19:19','Work finished');
/*!40000 ALTER TABLE `motorpool_wo_history` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `motorpool_work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `motorpool_work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wo_number` varchar(30) NOT NULL,
  `wo_type` enum('Vehicle Repair','Mechanical Equipment') NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `issue` text NOT NULL,
  `priority` enum('Routine','Urgent') NOT NULL DEFAULT 'Routine',
  `status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `requested_by` varchar(150) NOT NULL,
  `assigned_to` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wo_number` (`wo_number`),
  KEY `wo_type` (`wo_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `motorpool_work_orders` WRITE;
/*!40000 ALTER TABLE `motorpool_work_orders` DISABLE KEYS */;
INSERT INTO `motorpool_work_orders` VALUES
(1,'MP-001','Vehicle Repair',2,NULL,'Air conditioning not cooling','Routine','Completed','Sample Requester','Assets Test Account','2026-10-03 01:15:24','2026-10-04 13:19:19','2026-10-04 13:19:19'),
(2,'MP-002','Vehicle Repair',3,NULL,'Brake noise when stopping','Urgent','Completed','Sample Requester','Motor Pool Mechanic','2026-10-01 01:15:24','2026-10-04 04:32:13','2026-10-04 04:32:13'),
(3,'MP-003','Vehicle Repair',5,NULL,'Replace worn front tires','Routine','Completed','Sample Requester','Motor Pool Mechanic','2026-09-20 01:15:24','2026-09-24 01:15:24','2026-09-24 01:15:24'),
(4,'MP-004','Mechanical Equipment',NULL,2,'Generator fails to start','Urgent','In Progress','Sample Requester','Motor Pool Mechanic','2026-10-02 01:15:24','2026-10-03 01:15:24',NULL),
(5,'MP-005','Mechanical Equipment',NULL,6,'Engine overheats after 10 minutes','Routine','Pending','Sample Requester',NULL,'2026-10-03 21:15:24',NULL,NULL),
(6,'MP-006','Mechanical Equipment',NULL,4,'Replace pump seal','Routine','Completed','Sample Requester','Motor Pool Mechanic','2026-09-25 01:15:24','2026-09-28 01:15:24','2026-09-28 01:15:24'),
(7,'MP-007','Vehicle Repair',5,NULL,'Request cancelled - vehicle sold','Routine','Cancelled','Sample Requester',NULL,'2026-09-14 01:15:24','2026-09-15 01:15:24',NULL);
/*!40000 ALTER TABLE `motorpool_work_orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,'Vehicle Inspection','Routine vehicle health compliance check is scheduled tomorrow. System: FU-UBRA Fleet Telematics Service Engine','Operations Team','CRITICAL','Verified','system',1,'2026-07-28 19:18:13','2026-07-29 02:56:09'),
(2,'Travel Reminder','Driver Mark has an assigned dispatch schedule tomorrow. Destination: Dumaguete City Top Nail Territory.','Driver Mark','MODERATE','Notified','email',1,'2026-07-28 19:18:17','2026-07-28 02:56:09'),
(3,'Air-Con Cleaning','Air Conditioner Building A: A preventive system maintenance starts in 2 days. Facilities Dept.','Facilities Dept.','ROUTINE','Assigned','system',1,'2026-07-27 02:56:09','2026-07-27 02:56:09'),
(4,'Janitorial Assignment','Weekly deep disinfection assignment schedule for Team B begins tomorrow. Team B Duty.','Team B','ROUTINE','Assigned','system',1,'2026-07-26 02:56:09','2026-07-26 02:56:09'),
(5,'Inventory Low Stock','Critical spare parts and engine filters are low on spare parts inventory. Notify: Open Calendar Events.','Office Supplies','CRITICAL','Ordered','system',1,'2026-07-28 19:18:20','2026-07-29 02:56:09'),
(6,'Vehicle Expiry','Registration for Utility Truck-04 has been safely completed earlier this week. Reference: FU Comms House Print Document.','Fleet Admin','ROUTINE','Reviewed','email',1,'2026-07-24 02:56:09','2026-07-24 02:56:09'),
(8,'Fire Extinguisher Installed','New CO2 fire extinguisher (FE-E368E2) installed at College of Law building by Maisie Therese Tigmo.','Safety Team','ROUTINE','Verified','system',1,'2026-08-02 16:23:57','2026-08-02 15:49:02'),
(12,'Maintenance Scheduled','Clean Urgent — WO-005 logged for University library on Aug 5, 2026.','Maintenance Team','ROUTINE','Acknowledged','system',1,'2026-08-02 17:29:52','2026-08-02 17:24:41'),
(14,'Cleaning Scheduled','Cleaning scheduled for CCS Building on Aug 30, 2026. Notes: pleaseclean i!','Janitorial Staff','ROUTINE','Assigned','system',1,'2026-08-04 01:49:11','2026-08-03 15:42:45'),
(15,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Scheduled','system',1,'2026-08-04 01:49:13','2026-08-04 01:48:59'),
(16,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Assigned','system',1,'2026-08-04 01:49:08','2026-08-04 01:48:59'),
(17,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Assigned','system',1,'2026-08-04 01:49:10','2026-08-04 01:48:59'),
(18,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 03:07:33','2026-08-04 01:49:24'),
(19,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 02:06:57','2026-08-04 01:49:24'),
(20,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 02:06:55','2026-08-04 01:49:24'),
(21,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 03:07:27','2026-08-04 02:22:49'),
(22,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 03:07:30','2026-08-04 02:22:49'),
(23,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:14','2026-08-04 03:15:42'),
(24,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:47:11','2026-08-04 03:15:42'),
(25,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:47:09','2026-08-04 03:15:42'),
(26,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:18','2026-08-04 03:49:51'),
(27,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:16','2026-08-04 03:49:51'),
(28,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:24','2026-08-04 04:55:00'),
(29,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:23','2026-08-04 04:55:00'),
(30,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:20','2026-08-04 04:55:00'),
(31,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:47:26','2026-08-04 05:11:49'),
(32,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:43','2026-08-04 05:47:50'),
(33,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:42','2026-08-04 05:47:50'),
(34,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:39','2026-08-04 05:47:50'),
(35,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:38','2026-08-04 05:47:50'),
(36,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:36','2026-08-04 05:47:50'),
(37,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:32','2026-08-04 05:47:50'),
(38,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:48:31','2026-08-04 05:47:50'),
(39,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:48:29','2026-08-04 05:47:50'),
(40,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:48:28','2026-08-04 05:47:50'),
(41,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:46','2026-08-04 05:48:52'),
(42,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:43','2026-08-04 05:48:52'),
(43,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:41','2026-08-04 05:48:52'),
(44,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:37','2026-08-04 05:48:52'),
(45,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:35','2026-08-04 05:48:52'),
(46,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:32','2026-08-04 05:48:52'),
(47,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 05:50:30','2026-08-04 05:48:52'),
(48,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:49:02','2026-08-04 05:48:52'),
(49,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 05:49:00','2026-08-04 05:48:52'),
(50,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:45:00','2026-08-04 07:35:32'),
(51,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:58','2026-08-04 07:35:32'),
(52,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:56','2026-08-04 07:35:32'),
(53,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:54','2026-08-04 07:35:32'),
(54,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:53','2026-08-04 07:35:32'),
(55,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:51','2026-08-04 07:35:32'),
(56,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 07:44:50','2026-08-04 07:35:32'),
(57,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 07:44:48','2026-08-04 07:35:32'),
(58,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 07:44:46','2026-08-04 07:35:32'),
(59,'Cleaning Scheduled','Cleaning scheduled for Admin Building on Aug 6, 2026.','Janitorial Staff','ROUTINE','Assigned','system',1,'2026-08-04 07:45:02','2026-08-04 07:43:56'),
(63,'Trip Ticket Request','Trip ticket TR-20260804-0001 requested by Pedro Penduko to Tanjay asaggra on Aug 5, 2026.','Operations Office','MODERATE','Approved','system',1,'2026-08-04 09:23:16','2026-08-04 09:23:05'),
(64,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 12:15:45','2026-08-04 09:25:16'),
(65,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:08:55','2026-08-04 09:25:16'),
(66,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:08:58','2026-08-04 09:25:16'),
(67,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:00','2026-08-04 09:25:16'),
(68,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:03','2026-08-04 09:25:16'),
(69,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:07','2026-08-04 09:25:16'),
(70,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:09:12','2026-08-04 09:25:16'),
(71,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:09:14','2026-08-04 09:25:16'),
(72,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:09:17','2026-08-04 09:25:16'),
(73,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:06:52','2026-08-04 12:16:07'),
(77,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:09','2026-08-04 13:18:53'),
(78,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:06','2026-08-04 13:18:53'),
(79,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:03','2026-08-04 13:18:53'),
(80,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:34:00','2026-08-04 13:18:53'),
(81,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:57','2026-08-04 13:18:53'),
(82,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:52','2026-08-04 13:18:53'),
(83,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 13:33:49','2026-08-04 13:18:53'),
(84,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:33:46','2026-08-04 13:18:53'),
(85,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-04 13:33:43','2026-08-04 13:18:53'),
(86,'Trip Ticket Request','Trip ticket TR-20260804-0002 requested by Timothy Eraham to Bais City on Aug 5, 2026.','Operations Office','MODERATE','Acknowledged','system',1,'2026-08-04 13:34:11','2026-08-04 13:21:04'),
(88,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:49','2026-08-04 13:38:42'),
(89,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:52','2026-08-04 13:38:42'),
(90,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:03:56','2026-08-04 13:38:42'),
(91,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B1541D at Parade Ground is due for inspection/refill soon (next due 2026-08-15).','Safety Team','MODERATE','Verified','system',1,'2026-08-04 14:06:07','2026-08-04 13:38:42'),
(92,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-FC7DFC at Guest House is due for inspection/refill soon (next due 2026-08-08).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:45','2026-08-04 13:38:42'),
(93,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:50','2026-08-04 13:38:42'),
(94,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:08:23','2026-08-04 13:38:42'),
(95,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-21 13:08:26','2026-08-04 13:38:42'),
(96,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-21 13:08:30','2026-08-04 13:38:42'),
(97,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 12:19:49','2026-08-17 23:03:12'),
(98,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:00','2026-08-17 23:03:12'),
(99,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 13:07:41','2026-08-17 23:03:12'),
(100,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 12:51:47','2026-08-21 12:34:32'),
(101,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),
(102,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),
(103,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:52'),
(104,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-21 17:56:35','2026-08-21 13:12:52'),
(105,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),
(106,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),
(107,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 13:12:53'),
(108,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-21 18:24:59'),
(109,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:59','2026-08-29 22:38:54'),
(110,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:57','2026-08-29 22:38:54'),
(111,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:56','2026-08-29 22:38:54'),
(112,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:53','2026-08-29 22:38:54'),
(113,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 17 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-29 22:59:35','2026-08-29 22:38:54'),
(114,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(115,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(116,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(117,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(118,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(119,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(120,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(121,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',1,'2026-08-29 22:58:40','2026-08-29 22:38:54'),
(122,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 2 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:49:03','2026-08-29 22:54:20'),
(123,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:49:01','2026-08-29 22:54:20'),
(124,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-29 23:21:04','2026-08-29 23:16:48'),
(125,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:35','2026-08-29 23:16:48'),
(126,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:33','2026-08-29 23:16:48'),
(127,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:32','2026-08-29 23:16:48'),
(128,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',1,'2026-08-30 01:48:04','2026-08-29 23:16:48'),
(129,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:07','2026-08-29 23:16:48'),
(130,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:05','2026-08-29 23:16:48'),
(131,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:48:38','2026-08-29 23:23:51'),
(132,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:49','2026-08-30 00:30:08'),
(133,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:48','2026-08-30 00:30:08'),
(134,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 1 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:46','2026-08-30 00:30:08'),
(135,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-08-30 01:48:44','2026-08-30 00:30:08'),
(136,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:41','2026-08-30 00:30:08'),
(137,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 16 day(s).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:28','2026-08-30 00:30:09'),
(138,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:27','2026-08-30 00:30:09'),
(139,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:25','2026-08-30 00:30:09'),
(140,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:23','2026-08-30 00:30:09'),
(141,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:20','2026-08-30 00:30:09'),
(142,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:18','2026-08-30 00:30:09'),
(143,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:16','2026-08-30 00:30:09'),
(144,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:14','2026-08-30 00:30:09'),
(145,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:13','2026-08-30 00:30:09'),
(146,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-08-30 01:48:11','2026-08-30 00:30:09'),
(147,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7E1DDD at College of Business Economics and Accountancy is due for inspection/refill soon (next due 2027-07-05).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:08'),
(148,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:32','2026-08-30 01:49:08'),
(149,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:25','2026-08-30 01:49:08'),
(150,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:23','2026-08-30 01:49:08'),
(151,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Verified','system',1,'2026-08-30 01:49:21','2026-08-30 01:49:08'),
(152,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:19','2026-08-30 01:49:08'),
(153,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Verified','system',1,'2026-08-30 01:49:17','2026-08-30 01:49:08'),
(154,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-B2D5A3 at University Library is due for inspection/refill soon (next due 2027-03-10).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(155,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-4FBD01 at Administration Building is due for inspection/refill soon (next due 2027-06-24).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(156,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-E0C2BE at Executive House is due for inspection/refill soon (next due 2026-09-17).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(157,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-05FF77 at College of Agriculture and SIE is due for inspection/refill soon (next due 2026-12-19).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(158,'Aircon Needs Cleaning','Aircon unit AC-BEA-G1 at College of Business Economics and Accountancy needs cleaning.','Maintenance Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(159,'Aircon Needs Cleaning','Aircon unit AC-ADM-2F at Administration Building needs cleaning.','Maintenance Team','MODERATE','Pending','system',0,NULL,'2026-08-30 01:49:26'),
(160,'Job Order Expired','Job Order JO-2026-003 (University Library Electrical Rewiring) has expired.','Head of Facilities','CRITICAL','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(161,'Job Order Expiring Soon','Job Order JO-2026-002 (College of Nursing Building Renovation) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(162,'Contract Expiring Soon','John Doe\'s Job Order contract (ID 9) will expire in 1 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(163,'Contract Expired','Josefa Mendoza\'s Job Order contract (ID 7) has expired.','Head of Facilities','CRITICAL','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(164,'Contract Expiring Soon','Armand Perez\'s Job Order contract (ID 6) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(165,'Contract Expiring Soon','Danilo Mendoza\'s Job Order contract (ID 5) will expire in 16 day(s).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:58'),
(166,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-09-05 15:35:35','2026-08-30 02:23:59'),
(167,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-09-05 15:35:37','2026-08-30 02:23:59'),
(168,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-09-05 15:35:39','2026-08-30 02:23:59'),
(169,'Personnel Document Incomplete','Goyo Ramos has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(170,'Personnel Document Incomplete','Josefa Villanueva has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(171,'Personnel Document Incomplete','Danilo Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(172,'Personnel Document Incomplete','Josefa Mendoza has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(173,'Personnel Document Incomplete','Armand Perez has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(174,'Personnel Document Incomplete','Rico Dela Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-08-30 02:23:59'),
(175,'Contract Expired','John Doe\'s Job Order contract (ID 9) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-09-05 15:35:33','2026-09-03 12:12:11'),
(176,'Fire Extinguisher Installed','New Dry Chemical fire extinguisher (FE-2F9C37) installed at an unspecified building.','Safety Team','ROUTINE','Verified','system',1,'2026-09-05 20:09:44','2026-09-05 19:13:57'),
(177,'Fire Extinguisher Installed','New Dry Chemical fire extinguisher (FE-LIB-014) installed at University library by Fernando Cruz.','Safety Team','ROUTINE','Verified','system',1,'2026-09-05 20:09:42','2026-09-05 19:22:18'),
(178,'Fire Extinguisher Installed','New CO2 fire extinguisher (FE-ADM-021) installed at Administration building by Cardo Navarro.','Safety Team','ROUTINE','Verified','system',1,'2026-09-05 20:09:40','2026-09-05 19:22:19'),
(179,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7FC697 at College of Education Building is due for inspection/refill soon (next due 2026-10-10).','Safety Team','MODERATE','Verified','system',1,'2026-09-20 20:33:45','2026-09-12 16:00:35'),
(180,'Contract Expired','John Doe\'s Job Order contract (ID 9) has expired.','Head of Facilities','CRITICAL','Acknowledged','system',1,'2026-09-12 21:59:07','2026-09-12 18:17:34'),
(181,'Personnel Document Incomplete','Melinda Reyes has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Acknowledged','system',1,'2026-09-20 20:35:36','2026-09-12 18:17:34'),
(182,'Personnel Document Incomplete','Bayani Cruz has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-09-12 18:17:34'),
(183,'Personnel Document Incomplete','John Doe has incomplete requirements (0/0 documents verified).','Head of Facilities','MODERATE','Pending','system',0,NULL,'2026-09-12 18:17:34'),
(184,'Tool Borrowed','MacBook Pro 16 borrowed by Rico Dela Cruz (Athletics) — due back on Sep 13, 2026.','Tools & Equipment Office','MODERATE','Pending','system',1,'2026-09-19 17:51:26','2026-09-06 01:30:35'),
(185,'Fire Extinguisher Expiring Soon','Fire extinguisher FE-7FC697 at College of Education Building is due for inspection/refill soon (next due 2026-10-10).','Safety Team','MODERATE','Pending','system',0,NULL,'2026-09-21 13:58:45'),
(186,'Aircon Needs Cleaning','Aircon unit AC-EXE-G1 at Executive House needs cleaning.','Maintenance Team','MODERATE','Pending','system',0,NULL,'2026-10-03 23:02:28'),
(187,'Motor Pool Work Order','MP-002 (Vehicle Repair) is urgent: brake noise when stopping - work in progress.','Motor Pool','CRITICAL','Pending','system',0,NULL,'2026-10-02 02:00:18'),
(188,'Motor Pool Work Order','MP-004 (Mechanical Equipment) is urgent: Standby Generator 20kVA fails to start.','Motor Pool','CRITICAL','Pending','system',0,NULL,'2026-10-03 02:00:18'),
(189,'Motor Pool Work Order','MP-001 (Vehicle Repair) is waiting to be started: air conditioning not cooling.','Motor Pool','MODERATE','Pending','system',0,NULL,'2026-10-03 06:00:18'),
(190,'Motor Pool Work Order','MP-005 (Mechanical Equipment) is waiting to be started: Ride-on Lawn Mower overheats.','Motor Pool','MODERATE','Pending','system',0,NULL,'2026-10-03 22:00:18'),
(191,'Equipment Needs Repair','ME-GEN-02 Standby Generator 20kVA is marked Needs Repair and its service is overdue.','Motor Pool','CRITICAL','Pending','system',0,NULL,'2026-10-01 02:00:18'),
(192,'Equipment Needs Repair','ME-MOW-01 Ride-on Lawn Mower is marked Needs Repair and its service is overdue.','Motor Pool','MODERATE','Pending','system',0,NULL,'2026-10-01 02:00:18'),
(193,'Vehicle Service Due','Yamaha (4567HUJI) service is overdue (brake inspection).','Motor Pool','MODERATE','Pending','system',0,NULL,'2026-10-03 21:00:18'),
(194,'Vehicle Service Due','Mitsubishi (90HJI87) has a service coming up in the next 14 days.','Motor Pool','ROUTINE','Pending','system',0,NULL,'2026-10-03 20:00:18'),
(195,'Sports Equipment Overdue','Badminton Racket Set (4pcs) was due back 3 days ago - borrowed by Mina Santos (Athletics).','Sports Equipment Office','CRITICAL','Pending','system',0,NULL,'2026-10-03 23:27:14'),
(196,'Sports Equipment Borrowed','Gym Mats (Set of 5) borrowed by PE Instructor Cruz - due back tomorrow.','Sports Equipment Office','ROUTINE','Pending','system',0,NULL,'2026-10-03 02:27:14'),
(197,'Sports Equipment Borrowed','Basketball (Molten GG7) borrowed by Coach Reyes - due back in 5 days.','Sports Equipment Office','ROUTINE','Pending','system',0,NULL,'2026-10-02 02:27:14'),
(198,'Sports Equipment Needs Repair','Table Tennis Paddle Set is in Poor condition.','Sports Equipment Office','MODERATE','Pending','system',0,NULL,'2026-10-03 21:27:14'),
(200,'Motor Pool Work Order','MP-002 is now Completed.','Motor Pool','ROUTINE','Pending','system',0,NULL,'2026-10-04 04:32:13'),
(201,'Motor Pool Work Order','MP-001 is now In Progress.','Motor Pool','ROUTINE','Pending','system',0,NULL,'2026-10-04 13:19:14'),
(202,'Motor Pool Work Order','MP-001 is now Completed.','Motor Pool','ROUTINE','Pending','system',1,'2026-10-04 13:20:21','2026-10-04 13:19:20');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
INSERT INTO `personnel` VALUES
(3,'EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',NULL,3,'Regular','Safety & Security Chief','GPS Route Validation','Active',0,NULL,'2026-07-18 20:20:56'),
(4,'EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',NULL,7,'Regular','Carpenter','Science Lab Cleaning','Active',0,NULL,'2026-07-18 20:20:56'),
(5,'EMP-2022-071','Pedro Penduko',NULL,NULL,NULL,'Regular',NULL,NULL,'Active',0,NULL,'2026-07-18 20:20:56'),
(6,'EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',NULL,2,'Regular','Driver','Van-01 Dispatch','Active',0,NULL,'2026-07-18 20:20:56'),
(7,'EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',NULL,2,'Regular','Senior Driver','Bus-02 Assignment','Active',0,NULL,'2026-07-18 20:20:56'),
(8,'EMP-2018-009','Dr. Helen Peralta','helen.peralta@foundation.edu.ph',NULL,6,'Regular','Department Head','Trip Approvals','Active',0,NULL,'2026-07-18 20:20:56'),
(9,'EMP-2023-301','sherina Banosong','sherina.banosong@foundation.edu.ph',NULL,2,'Regular','Staff','Front Office Records Filing','Active',0,NULL,'2026-07-19 02:54:16'),
(14,'EMP-2024-302','Timothy Eraham','timothy.eraham@foundation.edu.ph',NULL,5,'Regular','Driver','Van-02 Dispatch','Active',0,NULL,'2026-07-26 00:20:05'),
(15,'EMP-2024-303','TimothyLincon','timothy.lincon@foundation.edu.ph',NULL,1,'Regular','Janitor','Library Restroom Cleaning','Active',0,NULL,'2026-07-26 12:13:38'),
(16,'EMP-2023-304','Juan dela Cruz','juan.delacruz@foundation.edu.ph',NULL,8,'Regular','Maintenance','HVAC Unit Inspection - Bldg 14','Active',0,NULL,'2026-07-27 22:20:10'),
(17,'EMP-2023-305','Juan dela Beto','juan.delabeto@foundation.edu.ph',NULL,1,'Regular','Janitor','Gymnasium Floor Maintenance','Active',0,NULL,'2026-07-27 22:22:00'),
(18,'EMP-2018-306','Lapu-lapu','lapu.lapu@foundation.edu.ph',NULL,5,'Regular','Janitor','Admin Lobby Floor Care','Active',0,NULL,'2026-07-28 14:03:20'),
(19,'EMP-2023-307','Juan Cruz','juancruz@example.com',NULL,1,'Regular','Driver',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),
(20,'EMP-2023-308','Mina Santos','minasantos@example.com',NULL,1,'Regular','Janitor',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),
(21,'EMP-2023-309','Rico Dela Cruz','ricodelacruz@example.com',NULL,1,'JobOrder','Carpenter','Perimeter Fence Painting Project (Completed)','On Leave',0,NULL,'2026-07-28 18:33:24'),
(22,'EMP-2023-310','Armand Perez','armandperez@example.com',NULL,1,'JobOrder','Maintenance','College of Nursing Building Renovation','On Leave',0,NULL,'2026-07-28 18:33:24'),
(23,'EMP-2026-835','Ricardo Reyes','ricardo.reyes@foundation.edu.ph',NULL,4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(24,'EMP-2026-449','Goyo Pascual','goyo.pascual@foundation.edu.ph',NULL,4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(25,'EMP-2026-440','Cardo Manalo','cardo.manalo@foundation.edu.ph',NULL,1,'Regular','Carpenter','Cabinet Fabrication','Active',0,NULL,'2026-07-31 20:16:53'),
(26,'EMP-2026-765','Rodrigo Torres','rodrigo.torres@foundation.edu.ph',NULL,8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),
(27,'EMP-2026-834','Josefa Mendoza','josefa.mendoza@foundation.edu.ph',NULL,1,'JobOrder','Maintenance Technician','University Library Electrical Rewiring','Active',0,NULL,'2026-07-31 20:16:53'),
(28,'EMP-2026-100','Fernando Ocampo','fernando.ocampo@foundation.edu.ph',NULL,1,'Regular','Driver','Renovation Project Lead','On Leave',0,NULL,'2026-07-31 20:16:53'),
(29,'EMP-2026-786','Cardo Domingo','cardo.domingo@foundation.edu.ph',NULL,6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),
(30,'EMP-2026-654','Teresa Domingo','teresa.domingo@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),
(31,'EMP-2026-683','Emilio Reyes','emilio.reyes@foundation.edu.ph',NULL,2,'Regular','Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(32,'EMP-2026-631','Consolacion Garcia','consolacion.garcia@foundation.edu.ph',NULL,8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),
(33,'EMP-2026-243','Antonio Reyes','antonio.reyes@foundation.edu.ph',NULL,2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),
(34,'EMP-2026-283','Danilo Mendoza','danilo.mendoza@foundation.edu.ph',NULL,1,'JobOrder','Lead Carpenter','College of Nursing Building Renovation','Active',0,NULL,'2026-07-31 20:16:53'),
(35,'EMP-2026-134','Rizal Bautista','rizal.bautista@foundation.edu.ph',NULL,2,'Regular','Driver','Utility Truck Duty','Active',0,NULL,'2026-07-31 20:16:53'),
(36,'EMP-2026-201','Remedios Ocampo','remedios.ocampo@foundation.edu.ph',NULL,1,'Regular','Physical Plant Supr.','Preventive Maintenance','Active',0,NULL,'2026-07-31 20:16:53'),
(37,'EMP-2026-887','Ricardo Mendoza','ricardo.mendoza@foundation.edu.ph',NULL,1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(38,'EMP-2026-305','Fernando Salazar','fernando.salazar@foundation.edu.ph',NULL,1,'Regular','Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(39,'EMP-2026-709','Andres Navarro','andres.navarro@foundation.edu.ph',NULL,8,'Regular','Accounting Staff','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(40,'EMP-2026-953','Diego Castillo','diego.castillo@foundation.edu.ph',NULL,6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),
(41,'EMP-2026-172','Isabel Aquino','isabel.aquino@foundation.edu.ph',NULL,1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),
(42,'EMP-2026-738','Fernando Navarro','fernando.navarro@foundation.edu.ph',NULL,3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),
(43,'EMP-2026-970','Gabriela Pascual','gabriela.pascual@foundation.edu.ph',NULL,6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),
(44,'EMP-2026-949','Emilio Rivera','emilio.rivera@foundation.edu.ph',NULL,4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(45,'EMP-2026-492','Maria Mendoza','maria.mendoza@foundation.edu.ph',NULL,1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(46,'EMP-2026-603','Rizal Garcia','rizal.garcia@foundation.edu.ph',NULL,6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),
(47,'EMP-2026-626','Cardo Garcia','cardo.garcia@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),
(48,'EMP-2026-329','Diego Fernandez','diego.fernandez@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(49,'EMP-2026-617','Josefa Villanueva','josefa.villanueva@foundation.edu.ph',NULL,1,'JobOrder','Construction Worker','Campus Landscaping Improvement','Active',0,NULL,'2026-07-31 20:16:53'),
(50,'EMP-2026-628','Goyo Castillo','goyo.castillo@foundation.edu.ph',NULL,6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(51,'EMP-2026-735','Antonio Mendoza','antonio.mendoza@foundation.edu.ph',NULL,2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),
(52,'EMP-2026-815','Josefa Garcia','josefa.garcia@foundation.edu.ph',NULL,3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),
(53,'EMP-2026-844','Goyo Ramos','goyo.ramos@foundation.edu.ph',NULL,1,'JobOrder','Construction Foreman','Campus Landscaping Improvement','Inactive',0,NULL,'2026-07-31 20:16:53'),
(54,'EMP-2026-737','Juan Santos','juan.santos@foundation.edu.ph',NULL,4,'Regular','Cleaning Operative','CCS Building Cleaning','Active',0,NULL,'2026-07-31 20:16:53'),
(55,'EMP-2026-272','Maria Domingo','maria.domingo@foundation.edu.ph',NULL,1,'Regular','Construction Foreman','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(56,'EMP-2026-378','Fernando Cruz','fernando.cruz@foundation.edu.ph',NULL,3,'Regular','Guard','Guard House Duty','Active',0,NULL,'2026-07-31 20:16:53'),
(57,'EMP-2026-271','Pedro Reyes','pedro.reyes@foundation.edu.ph',NULL,5,'Regular','IT Support','Helpdesk Support','Active',0,NULL,'2026-07-31 20:16:53'),
(58,'EMP-2026-782','Maria Domingo','maria.domingo@foundation.edu.ph',NULL,1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),
(59,'EMP-2026-792','Jose Torres','jose.torres@foundation.edu.ph',NULL,1,'Regular','Construction Foreman','Renovation Project Lead','Active',0,NULL,'2026-07-31 20:16:53'),
(60,'EMP-2026-711','Cardo Navarro','cardo.navarro@foundation.edu.ph',NULL,3,'Regular','Guard','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(61,'EMP-2026-498','Fernando Reyes','fernando.reyes@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),
(62,'EMP-2026-487','Manuel Domingo','manuel.domingo@foundation.edu.ph',NULL,6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),
(63,'EMP-2026-392','Josefa Ramos','josefa.ramos@foundation.edu.ph',NULL,6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),
(64,'EMP-2026-511','Corazon Castillo','corazon.castillo@foundation.edu.ph',NULL,1,'Regular','Physical Plant Supr.','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(65,'EMP-2026-385','Andres Garcia','andres.garcia@foundation.edu.ph',NULL,3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),
(66,'EMP-2026-429','Diego Manalo','diego.manalo@foundation.edu.ph',NULL,3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),
(67,'EMP-2026-646','Consolacion Reyes','consolacion.reyes@foundation.edu.ph',NULL,6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),
(68,'EMP-2026-525','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),
(69,'EMP-2026-292','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',NULL,3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),
(70,'EMP-2026-877','Danilo Villanueva','danilo.villanueva@foundation.edu.ph',NULL,4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(71,'EMP-2026-157','Josefa Manalo','josefa.manalo@foundation.edu.ph',NULL,2,'Regular','Senior Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),
(72,'EMP-2026-743','Isabel Castillo','isabel.castillo@foundation.edu.ph',NULL,3,'Regular','Guard','CCTV Monitoring','Active',0,NULL,'2026-07-31 20:16:53'),
(73,'20211735','Maisie Therese Tigmo','maisie.tigmo@foundation.edu.ph',NULL,1,'Regular','Maintenance Technician','Unassigned','Active',0,NULL,'2026-08-02 19:14:02'),
(74,'20262020','Jose Protacio Rizal Mercado y Realonzo Realonda','pepe@gmail.com',NULL,1,'Regular','Maintenance Technician','To Inspect the Aircon ','Active',0,NULL,'2026-08-03 21:13:52'),
(75,'EMP-2026-901','Maria Clara Santos','maria.santos@fuubra.local',NULL,5,'Regular','IT Equipment Custodian','IT asset custody and inventory','Active',0,NULL,'2026-08-03 23:48:23'),
(76,'EMP-2026-902','Engr. James Diaz','james.diaz@fuubra.local',NULL,1,'Regular','Facilities Engineer','Tools and equipment custody','Active',0,NULL,'2026-08-03 23:48:23'),
(77,'EMP-2026-903','John Doe','john.doe@fuubra.local',NULL,1,'JobOrder','Facilities Staff','General facilities support','Active',0,NULL,'2026-08-03 23:48:23'),
(78,'EMP-2026-701','Bayani Cruz','bayani.cruz@foundation.edu.ph',NULL,1,'JobOrder','General Laborer','Campus Landscaping Improvement','Active',0,NULL,'2026-08-29 22:31:31'),
(79,'EMP-2026-702','Melinda Reyes','melinda.reyes@foundation.edu.ph',NULL,1,'JobOrder','General Laborer','Campus Landscaping Improvement','Active',0,NULL,'2026-08-29 22:31:31'),
(80,'18150321','Maisie Therese Tigmo','maisietherese.tigmo@foundationu.com',NULL,6,'Regular','Carpenter','Fixed the Door Knob','Active',0,NULL,'2026-09-05 22:09:53'),
(81,'2023001','Apolinario Mabini','brain@gmail.com',NULL,3,'Regular','Maintenance Technician','Map The Lobby','Active',0,NULL,'2026-09-06 01:41:31'),
(82,'2022002','Eddie Murphy','meow2@gmail.com',NULL,6,'Regular','Administrator','Hanging Cabinet','Active',0,NULL,'2026-09-06 01:45:50'),
(83,'20021001','Mama Cita','sotanghon@gmail.com',NULL,3,'Regular','IT Equipment Custodian','DOOR KNOB FIXING','Active',0,NULL,'2026-09-06 01:46:57'),
(84,'202101999','Eddie Murphy','eddie.murphy@gmail.com','09123456789',2,'Regular','Carpenter','PlyWood Shining','Active',0,NULL,'2026-09-21 14:11:36');
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personnel_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `personnel_assignments` WRITE;
/*!40000 ALTER TABLE `personnel_assignments` DISABLE KEYS */;
INSERT INTO `personnel_assignments` VALUES
(1,49,1,'Construction Worker','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(2,53,1,'Construction Foreman','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(3,78,1,'General Laborer','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(4,79,1,'General Laborer','Main Campus Grounds',1,'Remedios Ocampo','2026-07-01','2027-02-28','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(5,34,2,'Carpenter','College of Nursing',1,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(6,22,2,'Maintenance','College of Nursing',1,'Corazon Castillo','2026-06-15','2026-09-15','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(7,27,3,'Maintenance Technician','University Library',1,'Col. Arthur Miller','2026-03-01','2026-07-31','ACTIVE',NULL,'2026-08-29 22:31:31',NULL),
(8,21,4,'Carpenter','Campus Perimeter',1,'Dr. Helen Peralta','2026-01-10','2026-03-10','COMPLETED',NULL,'2026-08-29 22:31:31',NULL),
(9,77,2,'Driver','ccs',NULL,'Corazon Castillo','2026-08-29','2026-08-31','ACTIVE',NULL,'2026-08-29 22:51:45','2026-08-29 22:51:45');
/*!40000 ALTER TABLE `personnel_assignments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personnel_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `personnel_contracts` WRITE;
/*!40000 ALTER TABLE `personnel_contracts` DISABLE KEYS */;
INSERT INTO `personnel_contracts` VALUES
(1,49,1,'CT-2026-101','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(2,53,1,'CT-2026-102','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(3,78,1,'CT-2026-103','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(4,79,1,'CT-2026-104','2026-07-01','2027-02-28','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(5,34,2,'CT-2026-105','2026-06-15','2026-09-15','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(6,22,2,'CT-2026-106','2026-06-15','2026-09-15','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(7,27,3,'CT-2026-107','2026-03-01','2026-07-31','ACTIVE',NULL,NULL,'2026-08-29 22:31:31',NULL),
(8,21,4,'CT-2026-108','2026-01-10','2026-03-10','COMPLETED',NULL,NULL,'2026-08-29 22:31:31',NULL),
(9,77,2,NULL,'2026-08-29','2026-08-31','ACTIVE',NULL,NULL,'2026-08-29 22:51:45','2026-08-29 22:51:45');
/*!40000 ALTER TABLE `personnel_contracts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personnel_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `personnel_documents` WRITE;
/*!40000 ALTER TABLE `personnel_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `personnel_documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) DEFAULT NULL,
  `insight_text` varchar(255) DEFAULT NULL,
  `suggestion_text` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `predictions` WRITE;
/*!40000 ALTER TABLE `predictions` DISABLE KEYS */;
INSERT INTO `predictions` VALUES
(1,'Dashboard','Three trips are scheduled this week.','Schedule Maintenance','2026-07-18 20:20:56'),
(2,'Dashboard','Vehicle Van-01 inspection is due tomorrow.','Generate Report','2026-07-18 20:20:56'),
(3,'Dashboard','Inventory of cleaning chemicals is running low.','Notify Personnel','2026-07-18 20:20:56');
/*!40000 ALTER TABLE `predictions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `refill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `refill_log` WRITE;
/*!40000 ALTER TABLE `refill_log` DISABLE KEYS */;
INSERT INTO `refill_log` VALUES
(1,1,'Floor Cleaner (Pine)',5.00,'Liters','Kenchie Terante','2026-08-30 03:29:17'),
(2,3,'Trash Liners (Large)',5.00,'Rolls','Kenchie Terante','2026-09-05 18:03:01'),
(3,9,'Glass Cleaner (Window Spray)',6.00,'Bottles','Kenchie Terante','2026-09-21 14:20:05'),
(4,5,'Disinfectant Spray',5.00,'Bottles','Kenchie Terante','2026-09-21 14:20:21'),
(5,7,'Liquid Hand Soap',5.00,'Liters','Kenchie Terante','2026-09-21 14:20:57'),
(6,10,'Trash Bag',4.00,'Rolls','Kenchie Terante','2026-09-21 14:21:10');
/*!40000 ALTER TABLE `refill_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES
(1,'Facilities Management Report - Last 30 Days',NULL,'','Draft',NULL,0,NULL,'2026-07-25 08:22:54','2026-07-28 16:42:49','2026-07-25 16:22:54'),
(2,'Monthly Tools Utilization Report',8,'Asset Inventory','Completed',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),
(3,'Vehicle Fleet Maintenance Summary',5,'Vehicle Fleet','Completed',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),
(4,'Q2 Travel Operations Report',9,'Travel Operations','Pending',NULL,0,NULL,'2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),
(5,'Janitorial Performance FY2025',8,'Janitorial Performance','Completed',NULL,1,'2026-07-10 00:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),
(6,'Fire Extinguisher Compliance Audit',3,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),
(7,'Campus Cleaning Performance - June',8,'Janitorial Performance','Completed',NULL,1,'2026-07-05 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:49','2026-07-28 16:42:00'),
(8,'Safety Drill Readiness Report',3,'Maintenance Compliance','Draft',NULL,0,NULL,'2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),
(10,'Aircon Cleaning Completed — AC-EDU-G1 (College of Education Building)',NULL,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-09-05 11:15:15','2026-09-05 12:09:45','2026-09-05 11:15:15'),
(11,'Aircon Cleaning Completed — AC-BEA-G1 (College of Business Economics and Accountancy)',NULL,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-09-05 11:22:04','2026-09-05 12:09:45','2026-09-05 11:22:04'),
(12,'Aircon Cleaning Completed — AC-LIB-G1 (University Library)',NULL,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-09-05 11:22:05','2026-09-05 12:09:45','2026-09-05 11:22:05'),
(13,'Fire Extinguisher Replaced/Renewed — FE-LIB-014 (University library)',NULL,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-09-05 11:22:18','2026-09-05 12:09:45','2026-09-05 11:22:18'),
(14,'Fire Extinguisher Replaced/Renewed — FE-ADM-021 (Administration building)',NULL,'Maintenance Compliance','Completed',NULL,0,NULL,'2026-09-05 11:22:19','2026-09-05 12:09:45','2026-09-05 11:22:19');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `restroom_checklist_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `restroom_checklist_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_id` int(11) NOT NULL,
  `entry_date` date DEFAULT NULL,
  `entry_time` time DEFAULT NULL,
  `empty_trash` tinyint(1) NOT NULL DEFAULT 0,
  `refill_paper` tinyint(1) NOT NULL DEFAULT 0,
  `refill_soap` tinyint(1) NOT NULL DEFAULT 0,
  `clean_floor` tinyint(1) NOT NULL DEFAULT 0,
  `clean_sink` tinyint(1) NOT NULL DEFAULT 0,
  `clean_toilet` tinyint(1) NOT NULL DEFAULT 0,
  `cleaned_by` varchar(150) DEFAULT NULL,
  `signature` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_id` (`checklist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `restroom_checklist_entries` WRITE;
/*!40000 ALTER TABLE `restroom_checklist_entries` DISABLE KEYS */;
INSERT INTO `restroom_checklist_entries` VALUES
(3,3,'2026-09-20','21:32:00',0,0,0,0,0,0,'',NULL);
/*!40000 ALTER TABLE `restroom_checklist_entries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `restroom_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `restroom_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(150) NOT NULL,
  `reviewed_by` varchar(150) DEFAULT NULL,
  `reviewed_date` date DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `restroom_checklists` WRITE;
/*!40000 ALTER TABLE `restroom_checklists` DISABLE KEYS */;
INSERT INTO `restroom_checklists` VALUES
(2,'Cisco Lab',NULL,NULL,0,'2026-09-05 20:09:20','2026-09-05 20:09:20'),
(3,'Test Restroom API',NULL,NULL,0,'2026-09-05 22:15:00','2026-09-05 22:15:00');
/*!40000 ALTER TABLE `restroom_checklists` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `return_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) DEFAULT NULL,
  `tool_id` int(11) DEFAULT NULL,
  `quantity_returned` decimal(8,2) DEFAULT 1.00,
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `return_records` WRITE;
/*!40000 ALTER TABLE `return_records` DISABLE KEYS */;
INSERT INTO `return_records` VALUES
(1,10,55,1.00,'John Doe','2026-08-01','Good',NULL,'2026-08-02 03:34:15'),
(2,11,55,1.00,'John Doe','2026-08-01','Good',NULL,'2026-08-02 04:10:55'),
(3,12,55,1.00,'Sherina Banosong','2026-08-02','Good',NULL,'2026-08-02 17:42:30'),
(5,93,2,1.00,'Rico Dela Cruz','2026-10-04','Excellent',NULL,'2026-10-04 02:47:47');
/*!40000 ALTER TABLE `return_records` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `safety_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `safety_equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_type` enum('Fire Alarm','Smoke Detector','Emergency Exit Sign') NOT NULL,
  `code` varchar(50) NOT NULL,
  `building` varchar(150) NOT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `location_note` varchar(150) DEFAULT NULL,
  `status` enum('Working','Needs Repair','Missing') NOT NULL DEFAULT 'Working',
  `last_checked` date DEFAULT NULL,
  `next_check` date DEFAULT NULL,
  `installed_on` date DEFAULT NULL,
  `expires_on` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `building` (`building`),
  KEY `equipment_type` (`equipment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `safety_equipment` WRITE;
/*!40000 ALTER TABLE `safety_equipment` DISABLE KEYS */;
INSERT INTO `safety_equipment` VALUES
(1,'Fire Alarm','FA-ADM-01','Administration Building','Ground Floor','Main lobby','Working','2026-09-03','2026-12-02','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(2,'Fire Alarm','FA-LIB-01','University Library','2nd Floor','Reading room','Needs Repair','2026-07-15','2026-09-28','2026-10-03',NULL,'Siren is very weak','2026-10-03 22:30:52'),
(3,'Fire Alarm','FA-NUR-01','College of Nursing','Ground Floor','Hallway','Working','2026-09-13','2026-12-12','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(4,'Smoke Detector','SD-ADM-01','Administration Building','2nd Floor','Records room','Working','2026-09-08','2026-10-08','2026-10-03','2027-04-14',NULL,'2026-10-03 22:30:52'),
(5,'Smoke Detector','SD-LAW-01','College of Law Building','Ground Floor','Faculty office','Missing','2026-07-05','2026-09-23','2026-10-03','2027-06-06','Detector removed, not replaced','2026-10-03 22:30:52'),
(6,'Smoke Detector','SD-EDU-01','College of Education Building','3rd Floor','Computer room','Working','2026-08-24','2026-11-22','2026-10-03','2026-10-02',NULL,'2026-10-03 22:30:52'),
(7,'Smoke Detector','SD-CAF-01','University Cafeteria, Bookstore, Sewing','Ground Floor','Kitchen','Needs Repair','2026-08-04','2026-10-06','2026-10-03','2026-11-24','Sensitive, false alarms','2026-10-03 22:30:52'),
(8,'Emergency Exit Sign','ES-ADM-01','Administration Building','Ground Floor','Front exit','Working','2026-09-18','2027-01-01','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(9,'Emergency Exit Sign','ES-LIB-01','University Library','Ground Floor','Back stairs','Needs Repair','2026-07-25','2026-10-01','2026-10-03',NULL,'Light is out','2026-10-03 22:30:52'),
(10,'Emergency Exit Sign','ES-NUR-01','College of Nursing','2nd Floor','East stairs','Working','2026-09-23','2026-12-22','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(11,'Emergency Exit Sign','FE-ADM-01','Administration Building','Ground Floor','Rear door','Working','2026-09-11','2026-12-07','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(12,'Emergency Exit Sign','FE-LAW-01','College of Law Building','2nd Floor','Side stairwell','Working','2026-08-29','2026-10-07','2026-10-03',NULL,NULL,'2026-10-03 22:30:52'),
(13,'Emergency Exit Sign','FE-LIB-01','University Library','Ground Floor','Back door','Needs Repair','2026-07-20','2026-09-25','2026-10-03',NULL,'Door sticks, hard to open','2026-10-03 22:30:52'),
(14,'Emergency Exit Sign','FE-EDU-01','College of Education Building','Ground Floor','North corridor','Working','2026-09-05','2026-12-04','2026-10-03',NULL,NULL,'2026-10-03 22:30:52');
/*!40000 ALTER TABLE `safety_equipment` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `safety_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `safety_inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `building` varchar(150) NOT NULL,
  `inspection_month` char(7) NOT NULL,
  `safety_status` enum('Safe','Needs Attention','Unsafe') NOT NULL,
  `remarks` text DEFAULT NULL,
  `inspected_by` varchar(150) NOT NULL,
  `inspected_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `building` (`building`,`inspection_month`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `safety_inspections` WRITE;
/*!40000 ALTER TABLE `safety_inspections` DISABLE KEYS */;
INSERT INTO `safety_inspections` VALUES
(1,'Administration Building','2026-10','Safe',NULL,'Timothy Eraham','2026-10-03 22:30:52'),
(2,'University Library','2026-10','Needs Attention','Fire alarm siren weak, exit sign light out','Timothy Eraham','2026-10-03 22:30:52'),
(3,'College of Law Building','2026-10','Unsafe','Smoke detector missing in faculty office','Timothy Eraham','2026-10-03 22:30:52'),
(4,'College of Nursing','2026-10','Safe',NULL,'Timothy Eraham','2026-10-03 22:30:52'),
(5,'Administration Building','2026-09','Safe','All good last month','Timothy Eraham','2026-09-03 22:30:52'),
(6,'University Library','2026-09','Safe',NULL,'Timothy Eraham','2026-09-03 22:30:52'),
(7,'College of Law Building','2026-09','Needs Attention','Exit sign dim','Timothy Eraham','2026-09-03 22:30:52'),
(9,'College of Law Building','2026-10','Safe','Good','Security Test Account','2026-10-04 03:44:32'),
(10,'College of Education Building','2026-10','Needs Attention',NULL,'Security Test Account','2026-10-04 03:46:39');
/*!40000 ALTER TABLE `safety_inspections` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `safety_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `safety_reports` WRITE;
/*!40000 ALTER TABLE `safety_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `safety_reports` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `safety_work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `safety_work_orders` WRITE;
/*!40000 ALTER TABLE `safety_work_orders` DISABLE KEYS */;
INSERT INTO `safety_work_orders` VALUES
(1,'WO-001','FE-ADM-03 defective — pressure gauge broken','Admin Building','Cruz, M.','Tech Valdez','High','In Progress','2025-07-10',NULL,NULL,'2026-07-19 05:37:22',NULL),
(2,'WO-002','FE-ENG-02 past expiry — needs replacement','Engineering','Flores, C.','Tech Ramos','High','Issue Logged','2025-07-12',NULL,NULL,'2026-07-19 05:37:22',NULL),
(3,'WO-003','Missing FE slot — Admin lobby unprotected','Admin Building','Guard Santos','Purchasing Dept','Critical','Pending Parts','2025-07-14',NULL,NULL,'2026-07-19 05:37:22',NULL),
(4,'WO-004','FE-SCI-02 not returning pressure after refill','Science Building','Lim, B.','Tech Valdez','Medium','Completed/Verified','2025-07-15',NULL,NULL,'2026-07-19 05:37:22',NULL),
(6,'WO-005','Clean Urgent','University library','John Doe','Maintenance Team','Medium','Issue Logged','2026-08-05',NULL,NULL,'2026-08-03 01:24:41',NULL);
/*!40000 ALTER TABLE `safety_work_orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES
(1,'system_name','UBRA Operational Portal','2026-08-17 23:27:39'),
(2,'university','Foundation University','2026-07-22 05:49:14'),
(3,'api_key','55b360a53a0b44f06e2e869bf36a284a','2026-09-05 20:18:51'),
(5,'smtp_host','smtp.gmail.com','2026-07-19 00:06:28'),
(6,'smtp_port','587','2026-07-19 00:06:28'),
(7,'smtp_user','','2026-07-19 00:06:28'),
(8,'smtp_from','','2026-07-19 00:06:28'),
(9,'smtp_name','FU-UBRA System','2026-07-19 00:06:28'),
(10,'smtp_pass','','2026-07-19 00:06:28'),
(11,'notif_maintenance','1','2026-07-19 00:06:28'),
(12,'notif_vehicle','1','2026-07-19 00:06:28'),
(13,'notif_janitorial','1','2026-07-19 00:06:28'),
(14,'notif_asset','1','2026-07-19 00:06:28'),
(15,'notif_travel','1','2026-07-19 00:06:28'),
(16,'reminder_days','5','2026-07-19 00:06:28'),
(17,'ai_api_key','REDACTED_API_KEY','2026-09-30 22:20:01');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
  `unit` varchar(40) NOT NULL DEFAULT 'pcs',
  `is_facilities` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES
(2,'MacBook Pro 16','AST-92041','IT Equipment','Deans Office, CCS','Maria Clara Santos','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-10-04 02:47:47'),
(3,'Floors Buffer Matt','AST-03481','Janitorial','Janitor Depot B','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-09-06 01:06:36'),
(4,'Sony Alpha A7 III','AST-00612','Media Studio','Media Center','Col. Arthur Miller','Poor','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-09-06 01:06:36'),
(5,'Epson Projector X50','AST-77120','IT Equipment','AVR Room 2','Pedro Penduko','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-09-06 01:06:36'),
(6,'Industrial Vacuum','AST-55019','Janitorial','Housekeeping Store','Sonia G. Ramirez','Good','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-09-06 01:06:36'),
(7,'Cordless Drill Set','AST-30188','Tools','Maintenance Shop','Engr. James Diaz','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-18 20:20:56',0,NULL,'2026-09-06 01:06:36'),
(9,'Circular Saw','AST-22502','Power Tools','Maintenance Shop',NULL,'Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(10,'Safety Helmet','AST-91612','Tools','Admin Building Storage','Pedro Penduko','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',1,'2026-08-03 22:16:02','2026-04-29 13:25:41'),
(11,'HP LaserJet Printer','AST-18847','IT Equipment','CCS Building Rm 204',NULL,'Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(12,'Hammer Drill','AST-59224','Power Tools','Media Center','Rodrigo S. Cruz','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(13,'HDMI Cable 10m','AST-88557','IT Equipment','Admin Building Storage',NULL,'Poor','Disposal',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-04-12 13:25:41'),
(14,'Whiteboard Markers Set','AST-82485','Tools','Science Building Lab','Sonia G. Ramirez','Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(15,'Hammer Drill','AST-27776','Power Tools','Library Storage','Sonia G. Ramirez','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(16,'Hand Truck Dolly','AST-63457','Janitorial','Housekeeping Store','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(17,'Tablet iPad 10th Gen','AST-41450','IT Equipment','AVR Room 2','Maria Clara Santos','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(18,'Ladder 8ft','AST-38363','Tools','Science Building Lab','Maria Clara Santos','Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(19,'Extension Reel','AST-85475','Tools','IT Server Room',NULL,'Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(20,'Welding Rods Box','AST-53033','Consumable','Deans Office','Juan dela Cruz','Excellent','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(21,'Zip Ties Pack','AST-85804','Consumable','Engineering Workshop','Col. Arthur Miller','Excellent','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(22,'Ladder 8ft','AST-73437','Tools','Media Center','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(23,'Canon DSLR Camera','AST-17694','Media Studio','Housekeeping Store',NULL,'Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(24,'Table Saw','AST-61814','Power Tools','Gymnasium Storage','Pedro Penduko','Fair','Maintenance',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-04-15 13:25:41'),
(25,'Bluetooth Speaker','AST-57379','Media Studio','Housekeeping Store',NULL,'Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(26,'Ladder 8ft','AST-14888','Tools','Main Utility Bldg','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(27,'Zip Ties Pack','AST-95359','Consumable','Housekeeping Store','Juan dela Cruz','Excellent','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(28,'Electrical Tape Roll','AST-46018','Consumable','Deans Office','Engr. James Diaz','Good','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(29,'Hammer Drill','AST-30960','Power Tools','Athletics Storage','Juan dela Cruz','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(30,'Camera Tripod','AST-83606','Media Studio','Main Utility Bldg','Engr. James Diaz','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(31,'Pipe Wrench','AST-89976','Tools','Deans Office','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(32,'Tool Cabinet','AST-10921','Tools','Main Utility Bldg','Rodrigo S. Cruz','Poor','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(33,'Laptop Charger Adapter','AST-92882','IT Equipment','Library Storage','Maria Clara Santos','Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(34,'Laptop Charger Adapter','AST-66319','IT Equipment','Housekeeping Store','Rodrigo S. Cruz','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(35,'Hammer Drill','AST-17624','Power Tools','Athletics Storage',NULL,'Poor','Maintenance',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-07-29 13:25:41'),
(36,'HP LaserJet Printer','AST-21694','IT Equipment','Main Utility Bldg','Pedro Penduko','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(37,'Disinfectant Spray','AST-91615','Consumable','Engineering Workshop','Dr. Helen Peralta','Good','Available',11.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(38,'Welding Rods Box','AST-84482','Consumable','Athletics Storage','Juan dela Cruz','Excellent','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(39,'HDMI Cable 10m','AST-74161','IT Equipment','Maintenance Shop','Pedro Penduko','Poor','Disposal',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-04-16 13:25:41'),
(40,'HDMI Cable 10m','AST-54117','IT Equipment','Maintenance Shop','Maria Clara Santos','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(41,'Canon DSLR Camera','AST-67613','Media Studio','Admin Building Storage','Rodrigo S. Cruz','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(42,'CCTV Camera Kit','AST-19879','Media Studio','IT Server Room','Engr. James Diaz','Poor','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(43,'Air Compressor','AST-56187','Power Tools','Media Center','Dr. Helen Peralta','Excellent','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(44,'Camera Tripod','AST-36890','Media Studio','CCS Building Rm 204','Juan dela Cruz','Poor','Disposal',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-07-10 13:25:41'),
(45,'Cordless Drill Set','AST-55542','Power Tools','Admin Building Storage','Dr. Helen Peralta','Poor','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(46,'Ladder 8ft','AST-42846','Tools','Admin Building Storage','Juan dela Cruz','Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(47,'Laptop Bag','AST-23150','IT Equipment','Housekeeping Store','Juan dela Cruz','Fair','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(48,'Laptop Charger Adapter','AST-84251','IT Equipment','Deans Office','Maria Clara Santos','Poor','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(49,'Wireless Microphone Set','AST-80958','Media Studio','Admin Building Storage','Maria Clara Santos','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(50,'Cleaning Alcohol 1L','AST-48435','Consumable','AVR Room 2',NULL,'Good','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(51,'Extension Cord 20m','AST-73374','Tools','Engineering Workshop','Juan dela Cruz','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(52,'Electrical Tape Roll','AST-18067','Consumable','Deans Office','Rodrigo S. Cruz','Good','Available',4.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(53,'Pipe Wrench','AST-55114','Tools','Gymnasium Storage','Engr. James Diaz','Poor','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(54,'Table Saw','AST-80661','Power Tools','Housekeeping Store','Engr. James Diaz','Good','Available',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(55,'Printer Ink Cartridge','AST-31961','Consumable','CCS Building Rm 204',NULL,'Good','Available',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(56,'Socket Wrench Set','AST-49752','Tools','Library Storage',NULL,'Poor','Disposal',NULL,NULL,'pcs',0,'2026-07-31 19:26:13',1,'2026-08-04 03:07:04','2026-06-22 13:25:41'),
(57,'Batteries AA Pack','AST-28474','Consumable','Housekeeping Store','Col. Arthur Miller','Poor','Maintenance',1.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-08-03 23:27:51'),
(58,'Printer Ink Cartridge','AST-29735','Consumable','Science Building Lab','Maria Clara Santos','Excellent','Available',21.00,1.00,'pcs',0,'2026-07-31 19:26:13',0,NULL,'2026-09-06 01:06:36'),
(59,'Push Broom','2152','Janitorial','Supply Room','Sonia G. Ramirez','Good','Available',NULL,NULL,'pcs',0,'2026-08-03 15:34:22',0,NULL,'2026-09-06 01:06:36'),
(60,'Jack-Hammer','200055','Power Tools','Cisco Lab','sherina Banosong','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-04 05:38:35',0,NULL,'2026-09-06 01:06:36'),
(61,'Extension Cord 10m','62007','Tools','Library Main','Juan dela Beto','Good','Available',NULL,NULL,'pcs',0,'2026-08-04 09:25:10',0,NULL,'2026-09-06 01:06:36'),
(64,'Jack-Hammer','009091','Power Tools','Cisco Lab','Timothy Eraham','Good','Available',NULL,NULL,'pcs',0,'2026-08-29 23:07:59',0,NULL,'2026-09-06 01:06:36'),
(68,'HDMI','2345','IT Equipment','Cisco Lab','Rodrigo S. Cruz','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-29 23:13:04',0,NULL,'2026-09-06 01:06:36'),
(69,'Electrical tape','','','','','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-29 23:14:01',1,'2026-09-05 14:53:00','2026-08-29 23:14:01'),
(71,'Electrical Tape','AST-2346','Consumable','Cisco Lab','Pedro Penduko','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-29 23:15:19',0,NULL,'2026-09-06 01:06:36'),
(72,'CCTV ','AST-2344','Media Studio','Cisco Lab','Sonia G. Ramirez','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-29 23:16:28',0,NULL,'2026-09-06 01:06:36'),
(73,'Jack Hammer','200056','Power Tools','North Campus','Juan dela Beto','Excellent','Available',NULL,NULL,'pcs',0,'2026-08-29 23:46:24',0,NULL,'2026-09-06 01:06:36'),
(74,'Basketball (Molten GG7)','AST-41001','Sports Equipment','Gymnasium Storage',NULL,'Excellent','Borrowed',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-10-04 02:27:14'),
(75,'Volleyball (Mikasa V300W)','AST-41002','Sports Equipment','Gymnasium Storage',NULL,'Excellent','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-09-06 01:06:36'),
(76,'Volleyball Net Set','AST-41003','Sports Equipment','Gymnasium Storage',NULL,'Good','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-09-06 01:06:36'),
(77,'Badminton Racket Set (4pcs)','AST-41004','Sports Equipment','PE Equipment Room',NULL,'Good','Borrowed',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-10-04 02:27:14'),
(78,'Badminton Net & Pole Set','AST-41005','Sports Equipment','PE Equipment Room',NULL,'Good','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-09-06 01:06:36'),
(79,'Table Tennis Paddle Set','AST-41006','Sports Equipment','PE Equipment Room',NULL,'Poor','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-10-04 02:27:14'),
(80,'Soccer Ball (Size 5)','AST-41007','Sports Equipment','Gymnasium Storage',NULL,'Excellent','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-09-06 01:06:36'),
(81,'Tennis Racket','AST-41008','Sports Equipment','Sports Complex','','Good','Available',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-09-19 21:21:42'),
(82,'Gym Mats (Set of 5)','AST-41009','Sports Equipment','Gymnasium Storage',NULL,'Good','Borrowed',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-10-04 02:27:14'),
(83,'Baseball Bat & Glove Set','AST-41010','Sports Equipment','Sports Complex',NULL,'Fair','Maintenance',NULL,NULL,'pcs',0,'2026-09-05 17:27:28',0,NULL,'2026-10-04 02:27:14');
/*!40000 ALTER TABLE `tools` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tools_refill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `tools_refill_log` WRITE;
/*!40000 ALTER TABLE `tools_refill_log` DISABLE KEYS */;
INSERT INTO `tools_refill_log` VALUES
(1,52,'Electrical Tape Roll',3.00,'Kenchie Terante','2026-08-30 03:28:19');
/*!40000 ALTER TABLE `tools_refill_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `travel_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `travel_requests` WRITE;
/*!40000 ALTER TABLE `travel_requests` DISABLE KEYS */;
INSERT INTO `travel_requests` VALUES
(1,'TR-20260729-0001',8,'Cebu City Hall','Official meeting with city officials','2026-07-29','08:00:00','17:00:00',1,6,2,'Completed','2026-08-04 06:40:27','2026-08-04 06:40:29',NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:49','2026-07-28 16:29:06','2026-08-04 05:21:49','2026-08-03 22:40:29'),
(2,'TR-20260728-0001',9,'Mandaue Campus','Inventory transfer and outreach','2026-07-28','09:30:00','15:30:00',2,7,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:45','2026-07-28 16:29:06','2026-08-04 05:21:45','2026-07-28 16:29:06'),
(3,'TR-20260730-0001',5,'DepEd Regional Office','Submission of accreditation documents','2026-07-30','07:00:00','12:00:00',5,7,2,'Completed','2026-08-01 19:36:32','2026-08-01 19:36:32','EMP-2023-210',1,'For Disposal',NULL,NULL,NULL,'2026-08-04 05:21:40','2026-07-28 16:29:06','2026-08-04 05:21:40','2026-07-28 12:26:12'),
(4,'TR-20260601-0001',8,'Provincial Office','Inspection of maintenance requests','2026-06-01','07:15:00','13:00:00',1,14,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-07-01 02:00:00','2026-07-28 16:29:06','2026-07-28 16:29:06','2026-07-28 16:29:06'),
(5,'TR-20260715-0001',9,'Lapu-Lapu Warehouse','Equipment pickup for Facilities','2026-07-15','10:00:00','16:00:00',4,19,3,'Cancelled',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:36','2026-07-28 16:29:06','2026-08-04 05:21:36','2026-07-28 16:29:06'),
(6,'TR-20260726-0001',6,'Bacolod Provincial Capitol','Coordination meeting','2026-07-26','08:30:00','14:00:00',3,7,2,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:31','2026-07-28 16:42:00','2026-08-04 05:21:31','2026-07-28 16:42:00'),
(7,'TR-20260410-0001',8,'Silliman University','Interagency site visit','2026-04-10','07:00:00','18:00:00',1,6,3,'Completed',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-06-01 00:00:00','2026-07-28 16:42:00','2026-07-28 16:42:00','2026-07-28 16:42:00'),
(8,'TR-20260320-0001',9,'Dumaguete Airport','Guest pickup','2026-03-20','06:00:00','09:00:00',2,19,2,'Rejected',NULL,NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:21:20','2026-07-28 16:42:00','2026-08-04 05:21:20','2026-07-28 16:42:00'),
(13,'TR-20260804-0001',5,'Tanjay asaggra','Regional outreach visit','2026-08-05','07:00:00','18:00:00',5,51,2,'Approved','2026-08-04 09:34:59',NULL,NULL,1,'None',NULL,NULL,NULL,'2026-08-04 05:08:40','2026-08-04 01:23:05','2026-08-19 16:03:31','2026-08-04 01:34:59'),
(16,'TR-20260804-0002',14,'Bais City','Site visit and coordination','2026-08-05','07:30:00','05:00:00',5,31,7,'Rejected',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-08-04 05:21:04','2026-10-03 17:54:33','2026-10-03 17:54:33'),
(17,'TR-20261001-0001',8,'Cebu City - DepEd Regional Office','Submit compliance documents','2026-10-01','09:00:00','17:00:00',6,6,6,'Approved',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-09-30 18:27:29','2026-09-30 18:27:29','2026-09-30 18:27:29'),
(18,'TR-20261001-0002',9,'Bacolod City - Supplier Pickup','Pick up construction supplies','2026-10-01','07:30:00','16:00:00',1,7,3,'In Transit','2026-10-01 07:35:00',NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-09-30 18:27:29','2026-09-30 18:27:29','2026-09-30 18:27:29'),
(19,'TR-SAMPLE-0001',3,'Dumaguete City Hall','Deliver documents (sample)','2026-10-04','08:00:00','12:00:00',NULL,5,2,'Approved',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:23:59','2026-10-03 17:23:59'),
(20,'TR-SAMPLE-0002',3,'Sibulan Campus','Inter-campus meeting (sample)','2026-10-05','09:00:00','15:00:00',NULL,7,6,'Approved',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:23:59','2026-10-03 17:23:59'),
(21,'TR-SAMPLE-0003',3,'Bacong Town Plaza','Student activity transport (sample)','2026-10-07','07:30:00','17:00:00',NULL,14,5,'Approved',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:54:28','2026-10-03 17:54:28'),
(22,'TR-SAMPLE-0004',3,'Valencia Supply Depot','Pick up supplies (sample)','2026-09-30','08:00:00','11:30:00',NULL,6,7,'Completed',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:23:59','2026-10-03 17:23:59'),
(23,'TR-SAMPLE-0005',3,'Airport - Sibulan','Guest pick-up (sample)','2026-09-25','13:00:00','16:00:00',NULL,19,3,'Completed',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:23:59','2026-10-03 17:23:59'),
(24,'TR-SAMPLE-0006',3,'Dauin Marine Park','Field trip (sample)','2026-10-02','06:30:00','18:00:00',NULL,5,2,'Reviewed',NULL,NULL,NULL,0,'None',NULL,NULL,NULL,NULL,'2026-10-03 17:23:59','2026-10-03 17:23:59','2026-10-03 17:23:59');
/*!40000 ALTER TABLE `travel_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `trip_status_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `travel_request_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `changed_by` varchar(100) NOT NULL,
  `changed_at` datetime NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_request_id` (`travel_request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `trip_status_log` WRITE;
/*!40000 ALTER TABLE `trip_status_log` DISABLE KEYS */;
INSERT INTO `trip_status_log` VALUES
(1,1,'Completed','Dr. Helen Peralta','2026-07-29 00:29:06','Existing record - history predates status tracking'),
(2,2,'Completed','sherina Banosong','2026-07-29 00:29:06','Existing record - history predates status tracking'),
(3,3,'Completed','Pedro Penduko','2026-07-29 00:29:06','Existing record - history predates status tracking'),
(4,4,'Completed','Dr. Helen Peralta','2026-07-29 00:29:06','Existing record - history predates status tracking'),
(5,5,'Cancelled','sherina Banosong','2026-07-29 00:29:06','Existing record - history predates status tracking'),
(6,6,'Completed','Juan dela Cruz','2026-07-29 00:42:00','Existing record - history predates status tracking'),
(7,7,'Completed','Dr. Helen Peralta','2026-07-29 00:42:00','Existing record - history predates status tracking'),
(8,8,'Rejected','sherina Banosong','2026-07-29 00:42:00','Existing record - history predates status tracking'),
(9,13,'Approved','Pedro Penduko','2026-08-04 09:23:05','Existing record - history predates status tracking'),
(10,16,'Submitted','Timothy Eraham','2026-08-04 13:21:04','Existing record - history predates status tracking'),
(12,21,'Approved','Assets Test Account','2026-10-04 01:54:28','Approved by Asset Acquisition and Monitoring'),
(13,16,'Rejected','Assets Test Account','2026-10-04 01:54:33','Rejected by Asset Acquisition and Monitoring');
/*!40000 ALTER TABLE `trip_status_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ubra_chat_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ubra_chat_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=297 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ubra_chat_logs` WRITE;
/*!40000 ALTER TABLE `ubra_chat_logs` DISABLE KEYS */;
INSERT INTO `ubra_chat_logs` VALUES
(153,'10010','user','hello mr.ubra','2026-09-20 21:05:36'),
(154,'10010','assistant','Hello! I am **Mr. UBRA**, your Intelligent Operations Assistant for Foundation University\'s Buildings and Grounds Integrated Management System.\n\nHere is a quick snapshot of our current operational status:\n* **Vehicles:** 3 available (out of 5 total)\n* **Personnel:** 55 on duty (out of 77 total)\n* **Assets:** 75 logged in system\n\nHow can I assist you with UBRA operations today?','2026-09-20 21:05:36'),
(155,'10010','user','Ip-ha ang mga consumable nga hapit na mahurot og mahimo e notify sa notifications center palihug para ma verify nko asap','2026-09-20 21:06:32'),
(156,'10010','assistant','Atong gi-check ug gi-process ang inventory para sa **Consumables**:\n\n* **Hapit Na Mahurot (Low-Stock Items):** **4 ka items**\n* **Notification Status:** Na-send na ang alert sa **Notifications Center** para sa imong verification ASAP.\n\nPalihug tan-awa ang Notifications Center sa imong system dashboard para ma-review ug ma-approve ang kinahanglan nga restocking. Pahibaloa lang ko kon naa pa koy laing matabang!','2026-09-20 21:06:32'),
(271,'10005','user','Give me a fleet health check — vehicle status, GPS status, and anything needing attention.','2026-10-02 14:07:25'),
(272,'10005','assistant','**Fleet Health Check – Summary (as of 02 Oct 2026, 14:07)**  \n\n- **Total fleet:** **5** vehicles  \n- **Available now:** **3** vehicles  \n- **In use / assigned:** **2** vehicles (the ones currently not listed as available)  \n\n**GPS Tracking**  \n- No GPS‑offline alerts are shown in the system snapshot.  \n- If any vehicle’s live tracker shows “no signal” or “inactive,” please verify in the **Vehicle Management → GPS Tracker** page.\n\n**Items Needing Attention**  \n- **Open maintenance work orders:** **4** (may involve vehicles or other assets). Review the **Maintenance (Safety) → Work Orders** list to see if any are tied to fleet equipment.  \n- **Fuel‑need predictions:** Not displayed in the snapshot; check the **Vehicle Management → Fuel Logs** module for any vehicles flagged for low fuel or upcoming refuel.  \n- **Trip tickets awaiting dispatch/approval:** **3** pending. Ensure drivers have approved tickets before dispatch to avoid scheduling conflicts.\n\n**Recommended Actions**  \n1. Open **Vehicle Management** and confirm the status (available, in‑service, under maintenance) of the two vehicles currently not listed as available.  \n2. Run the **GPS Tracker** live view to spot any devices showing “offline” or “error.”  \n3. Cross‑reference the **4 open maintenance work orders** with the fleet list; close or reassign any that pertain to vehicles.  \n4. Approve or reject the **3 pending trip tickets** to keep travel operations on schedule.  \n\nFor detailed per‑vehicle information (e.g., mileage, service due dates, GPS health), navigate to **Vehicle Management → Fleet Overview**. Let me know if you’d like a specific report (e.g., “generate a pdf report for fleet status this week”) and I’ll trigger the downloadable file.','2026-10-02 14:07:25'),
(275,'20230251','user','Give me a fleet health check — vehicle status, GPS status, and anything needing attention.','2026-10-02 15:26:53'),
(276,'20230251','assistant','**UBRA Fleet Health Check (as of Oct 2 2026 15:26)**  \n\n| Item | Current Status | Action Needed |\n|------|----------------|----------------|\n| **Total Vehicles** | **5** | – |\n| **Available** | **3** (idle) | No immediate action |\n| **In Use** | **2** (assigned to trips) | Verify driver logs & fuel levels |\n| **GPS Trackers** | Live status view available in **Vehicle Management → GPS Tracker** | • Confirm that both active units show a green “online” signal. <br>• Flag any unit showing “offline” or “signal lost” for replacement/repair. |\n| **Trip Tickets** | **3** tickets awaiting dispatch/approval | • Review and approve or reject the pending tickets to free vehicle capacity. |\n| **Maintenance Alerts** | No vehicle‑specific work orders listed in the snapshot, but **4 open maintenance work orders** exist system‑wide (may include vehicles). | • Open **Maintenance → Work Orders** and filter by “Vehicle” to address any pending repairs. |\n| **Safety Inspections** | Not listed for vehicles (fire‑extinguisher inspections are overdue). | • Ensure any required vehicle safety checks (e.g., brake, tire, oil) are up‑to‑date; schedule if missing. |\n\n### Recommended Immediate Steps\n1. **Check GPS health** – go to *Vehicle Management → GPS Tracker* and verify that the two vehicles currently in use report live locations.  \n2. **Process pending trip tickets** – approve/dispatch the three awaiting tickets to avoid idle time.  \n3. **Cross‑check work orders** – filter open work orders for vehicle‑related items and schedule repairs.  \n4. **Confirm routine vehicle inspections** – run a quick check in the Vehicle Management module to ensure all required preventative maintenance (oil change, tire rotation, safety inspection) is logged and up‑to‑date.\n\nIf you need a detailed PDF/Excel **fleet health report** (including GPS signal logs, mileage, fuel consumption, and upcoming maintenance), just let me know and I’ll generate the downloadable file for you.','2026-10-02 15:26:53'),
(281,'10030','user','unsay update sa Safety & Security?','2026-10-04 03:59:42'),
(282,'10030','assistant','**Update sa Safety & Security (Alas 03:59 AM, Oct 4 2026)**  \n\n**Safety**  \n- **4** overdue fire‑extinguisher inspections – kinahanglan i‑schedule dayon aron malikayan penalties.  \n- **4** bukas nga maintenance work orders – tan‑awa ang *Maintenance* module para sa detalye sa mga pending tasks ug assign technicians.  \n\n**Security**  \n- **Vehicles:** 5 total; **3** available for patrol/response.  \n- **Personnel on duty:** **56** of 78 staff – may **22** off‑duty; siguroha nga coverage sa critical entry points.  \n- **Tools & Equipment borrowed:** **3** items currently out – verify return status sa *Tools Management* kung gigamit sa security ops.  \n\n**Suggested next steps**  \n1. Prioritize scheduling of the 4 overdue extinguisher inspections (Safety).  \n2. Review and close the 4 open work orders (Maintenance).  \n3. Verify vehicle availability for any upcoming security patrols or incident response.  \n4. Cross‑check borrowed tools to confirm they are returned before shift change.  \n\nKung gusto nimo ang detalyadong lista sa mga work order o inspeksyon, adto sa **Maintenance (Safety)** module. Kung kinahanglan nimo ang vehicle‑tracker o trip‑ticket status, adto sa **Vehicle Management**.','2026-10-04 03:59:42'),
(283,'10030','user','unsay update sa janitorial?','2026-10-04 04:00:33'),
(284,'10030','assistant','**Janitorial Update (Alas 04:00 AM, Oct 4 2026)**  \n\n- **Cleaning coverage:** **3** of **8** zones have been cleaned today ≈ 38 % completion.  \n- **Remaining zones:** 5 zones still pending cleaning for the current shift.  \n- **Staff on duty:** 56 security personnel are on shift; janitorial staff assignments are managed in the **Janitorial Monitoring** module (you can view exact crew allocations there).  \n\n**Recommended actions**  \n1. **Check the Janitorial Monitoring page** to see which crew members are assigned to the 5 pending zones and verify their shift status.  \n2. If additional support is needed, consider reallocating available security staff for spot‑cleaning of high‑traffic areas until janitorial crews arrive.  \n3. Monitor the **Cleaning Completion KPI** on the Dashboard to ensure the daily target (all 8 zones) is met before the end of the shift.  \n\nLet me know if you need a detailed zone‑by‑zone status or want to generate a cleaning‑report (e.g., “generate a pdf report for janitorial today”).','2026-10-04 04:00:33'),
(285,'10040','user','unsay update sa janitorial','2026-10-04 04:40:22'),
(286,'10040','assistant','**Janitorial Update (as of 04:40 AM, Oct 4, 2026)**  \n\n- **Zones cleaned today:** **3 / 8**  \n- **Remaining zones to clean:** 5 (scheduled for later today)  \n\nThe Janitorial Monitoring module shows the current progress for today’s cleaning schedule. Let me know if you need the detailed zone list, staff assignments, or a cleaning‑completion report.','2026-10-04 04:40:22'),
(287,'10030','user','How many vehicles are available today?','2026-10-04 12:50:23'),
(288,'10030','assistant','That\'s outside your account\'s access. This assistant only answers about **Safety and Security Department** — fire safety equipment (extinguishers, smoke detectors, alarms, exit signs), floor plans, guard monitoring and keys, and safety inspections. Please ask the account that handles that area, or ask me something about your own department.','2026-10-04 12:50:23'),
(289,'10050','user','Which fire extinguishers are expired?','2026-10-04 12:50:31'),
(290,'10050','assistant','I’m sorry, but I can only provide information about Sports equipment. If you need help with the sports equipment records, condition, location, or borrowing/returns, just let me know!','2026-10-04 12:50:31'),
(291,'10050','user','generate a pdf report for vehicle this month','2026-10-04 12:50:35'),
(292,'10050','assistant','That\'s outside your account\'s access. This assistant only answers about **Sports Equipment Monitoring** — sports equipment records, equipment condition and location, and borrowing and returns. Please ask the account that handles that area, or ask me something about your own department.','2026-10-04 12:50:35'),
(293,'10040','user','How many motor pool work orders are open and how many are urgent? Answer in one line.','2026-10-04 12:50:44'),
(294,'10040','assistant','Open work orders: **3**; Urgent: **1**.','2026-10-04 12:50:44'),
(295,'10040','user','Any aircon units overdue for cleaning?','2026-10-04 12:50:49'),
(296,'10040','assistant','That\'s outside your account\'s access. This assistant only answers about **Asset Acquisition and Monitoring Department** — vehicles and drivers, vehicle maintenance, mechanical equipment, motor pool work orders, trip tickets, and the GPS tracker. Please ask the account that handles that area, or ask me something about your own department.','2026-10-04 12:50:49');
/*!40000 ALTER TABLE `ubra_chat_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(0,'Kenchie terante','admin@example.com','12345','12345','$2y$10$1GurcyUteQJBUJ4A3W7yNuDpr.oLHPp1fEf7kUmt5graLsPaUvvoG','Administrator',NULL,NULL,'2026-07-26 11:56:12'),
(2,'Kenchie Terante','admin@fu.edu.ph','admin','20230251','$2b$12$c8vA1tfnL.JQGWsHuahepukT6/UGD41npxdErvEsNFlsGr7d4Rxwq','Administrator','Operations Office','1787991834_bae35548ba39207f21ec.jpeg','2026-07-18 22:18:46'),
(5,'Sherina Banosong','sherina.banosong@foundationu.com','facilities','20230407','$2y$10$X7B8Zl3W/OOxATcABKwwqeFOvn77LV15ujUCcwd7QmMk6vfqNJcm6','Facilities',NULL,'1788029598_fca15c5bcb9244960756.jpg','2026-08-29 16:33:30'),
(9,'Timothy Eraham','timothy.eraham@foundationu.com','security','10005','$2y$10$bAEiq/MrHZxqBLT3Y8j4nOCk3EQ0Fn3KuGGdmva5GEJSVGEveyuMG','Security','Safety & Security','1788029522_d47e818ca26ab4f0a98e.jpg','2026-08-21 20:32:29'),
(10,'Maisie Therese Tigmo','janitorial@fu-ubra.local','janitorial','10010','$2y$10$PCVcLtcX319cDb4FqgyZOuHDcy3bPj/JpC21itpZSOdVRo3EBFyGK','Janitorial',NULL,'1789909237_3e7efa2c8eab9ec54d1f.jpg','2026-09-20 20:53:31'),
(11,'Facilities Test Account','facilities.test@foundationu.local','facilities_test','10020','$2y$10$bWPZG.CKHw8AShc0uYV.fO9xC4MD.LI8m7FfK2We8neMgh7LY5/Hy','Facilities','Facilities',NULL,'2026-10-03 15:00:55'),
(12,'Security Test Account','security.test@foundationu.local','security_test','10030','$2y$10$WdW62spkFCt0jlF5fOOaMOrOGqQ.65mq0epq6HWQSwf4k.7.W0Dy6','Security','Security',NULL,'2026-10-03 22:35:01'),
(13,'Assets Test Account','assets.test@foundationu.local','assets_test','10040','$2y$10$DyXP2dO2KIxG1LdNKW2wnuVASr.dpGKGC4vRVpKbEaIFbNEBJI4gC','Assets','Asset Acquisition and Monitoring',NULL,'2026-10-04 01:10:16'),
(14,'Sports Test Account','sports.test@foundationu.local','sports_test','10050','$2y$10$fOiVapffyFPdx9kt.NWSguiDDRgndabBwLtWbobYd04Iru9TzIcsm','Sports','Sports Equipment Monitoring',NULL,'2026-10-04 02:27:14'),
(15,'Admin Test Account','admin.test@foundationu.local','admin_test','10099','$2y$10$64S3tTK3nb0GQ7fsG2jHUerL49HXLztIxlcEtd/T6zsLvCh6O8Vuu','Administrator','Operations Office',NULL,'2026-10-06 20:28:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_inspection_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_inspection_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `plate_no` varchar(50) DEFAULT NULL,
  `odometer_reading` varchar(50) DEFAULT NULL,
  `mechanic_inspector` varchar(150) DEFAULT NULL,
  `next_pm_schedule` date DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_inspection_checklists` WRITE;
/*!40000 ALTER TABLE `vehicle_inspection_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_inspection_checklists` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_inspection_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_inspection_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `item_label` varchar(255) NOT NULL,
  `response` enum('Yes','No') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_id` (`checklist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_inspection_items` WRITE;
/*!40000 ALTER TABLE `vehicle_inspection_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_inspection_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `service_type` varchar(120) NOT NULL,
  `serviced_on` date NOT NULL,
  `odometer_km` decimal(10,1) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `performed_by` varchar(150) DEFAULT NULL,
  `next_due` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_maintenance` WRITE;
/*!40000 ALTER TABLE `vehicle_maintenance` DISABLE KEYS */;
INSERT INTO `vehicle_maintenance` VALUES
(1,2,'Change Oil','2026-09-04',44000.0,2500.00,'Motor Pool Shop','2027-02-07','Sample data','2026-10-04 01:15:24'),
(2,2,'Tire Rotation','2026-07-21',45500.0,800.00,'Motor Pool Shop','2027-02-07','Sample data','2026-10-04 01:15:24'),
(3,2,'Brake Inspection','2026-06-06',42000.0,1800.00,'Casa Service Center','2026-11-27','Sample data','2026-10-04 01:15:24'),
(4,3,'Change Oil','2026-09-04',45000.0,2500.00,'Motor Pool Shop','2026-12-02','Sample data','2026-10-04 01:15:24'),
(5,3,'Tire Rotation','2026-07-21',46500.0,800.00,'Motor Pool Shop','2026-12-02','Sample data','2026-10-04 01:15:24'),
(6,3,'Brake Inspection','2026-06-06',43000.0,1800.00,'Casa Service Center','2026-10-05','Sample data','2026-10-04 01:15:24'),
(7,5,'Change Oil','2026-09-04',47000.0,2500.00,'Motor Pool Shop','2026-11-18','Sample data','2026-10-04 01:15:24'),
(8,5,'Tire Rotation','2026-07-21',48500.0,800.00,'Motor Pool Shop','2026-11-18','Sample data','2026-10-04 01:15:24'),
(9,5,'Brake Inspection','2026-06-06',45000.0,1800.00,'Casa Service Center','2026-09-19','Sample data','2026-10-04 01:15:24'),
(10,6,'Change Oil','2026-09-04',48000.0,2500.00,'Motor Pool Shop','2027-01-10','Sample data','2026-10-04 01:15:24'),
(11,6,'Tire Rotation','2026-07-21',49500.0,800.00,'Motor Pool Shop','2027-01-10','Sample data','2026-10-04 01:15:24'),
(12,6,'Brake Inspection','2026-06-06',46000.0,1800.00,'Casa Service Center','2026-10-26','Sample data','2026-10-04 01:15:24'),
(13,7,'Change Oil','2026-09-04',49000.0,2500.00,'Motor Pool Shop','2026-11-04','Sample data','2026-10-04 01:15:24'),
(14,7,'Tire Rotation','2026-07-21',50500.0,800.00,'Motor Pool Shop','2026-11-04','Sample data','2026-10-04 01:15:24'),
(15,7,'Brake Inspection','2026-06-06',47000.0,1800.00,'Casa Service Center','2026-12-02','Sample data','2026-10-04 01:15:24');
/*!40000 ALTER TABLE `vehicle_maintenance` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_name` varchar(255) NOT NULL,
  `plate_no` varchar(50) NOT NULL,
  `gps_device_id` varchar(50) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES
(2,'Click25','466',NULL,'Motorcycle',5,5,'Online','Due Soon',30.0,'Available',0,NULL,'2026-07-26 21:52:08','2026-08-04 05:21:49','2026-07-27 05:52:09'),
(3,'Mitsubishi','90HJI87',NULL,'4 wheels',19,6,'Offline','Completed',32.5,'Reserved',0,NULL,'2026-07-27 06:23:47','2026-09-19 13:56:23','2026-07-27 14:23:47'),
(5,'Yamaha','4567HUJI',NULL,'Automatic Car ',14,7,'Online','Completed',33.0,'Available',0,NULL,'2026-07-29 17:07:42','2026-08-04 01:19:14','2026-07-30 01:07:42'),
(6,'Toyota Hiace','5678',NULL,'Van',7,3,'Online','Due Soon',31.5,'Available',0,NULL,'2026-08-03 07:29:05','2026-08-19 16:03:31','2026-08-03 07:29:05'),
(7,'Zusuki','230345',NULL,'V2-4Wheels',6,2,'Online','Completed',NULL,'In Use',0,NULL,'2026-08-04 01:24:15','2026-09-05 14:10:46','2026-08-04 01:24:15');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `building` varchar(150) NOT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `priority` enum('Routine','Urgent') NOT NULL DEFAULT 'Routine',
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `requested_by` varchar(150) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `building` (`building`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `work_orders` WRITE;
/*!40000 ALTER TABLE `work_orders` DISABLE KEYS */;
INSERT INTO `work_orders` VALUES
(2,'Broken door lock','Administration Building','Ground Floor','Front office door does not lock','Urgent','Pending','Facilities Test Account','2026-10-03 17:50:50',NULL,NULL),
(3,'Leaking faucet','University Library','2nd Floor',NULL,'Routine','In Progress','Facilities Test Account','2026-10-03 17:50:50','2026-10-03 17:50:50',NULL),
(4,'Replace ceiling light','College of Nursing','2nd Floor',NULL,'Routine','Completed','Facilities Test Account','2026-10-03 17:50:50','2026-10-03 17:50:50','2026-10-03 17:50:50'),
(5,'Repaint hallway wall','Museo de Vicente','Ground Floor',NULL,'Routine','Completed','Facilities Test Account','2026-10-03 17:50:50','2026-10-03 17:50:50','2026-10-03 17:50:50'),
(6,'Clogged sink','Executive House','1st Floor','Kitchen sink drains slowly','Routine','Pending','Maisie Therese Tigmo','2026-10-03 17:51:50',NULL,NULL),
(7,'Flickering lights','College of Law Building','2nd Floor',NULL,'Routine','In Progress','Cardo Garcia','2026-10-03 17:51:50','2026-10-03 17:57:24',NULL),
(8,'Water heater not working','Guest House','Ground Floor','No hot water in room 3','Urgent','In Progress','Josefa Garcia','2026-10-03 17:51:50','2026-10-03 17:51:50',NULL),
(9,'Broken window latch','HRM Kitchen','Ground Floor',NULL,'Routine','In Progress','Fernando Reyes','2026-10-03 17:51:50','2026-10-03 17:51:50',NULL);
/*!40000 ALTER TABLE `work_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

