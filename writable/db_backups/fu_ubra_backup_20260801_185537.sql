-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: fu_ubra
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (3,2,'Guard','Key borrowed: Test Room Key by Activity Test','2026-07-30 09:47:28');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_tokens`
--

DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user` (`user_id`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_tokens`
--

LOCK TABLES `api_tokens` WRITE;
/*!40000 ALTER TABLE `api_tokens` DISABLE KEYS */;
INSERT INTO `api_tokens` VALUES (1,2,'4c94342e0c4e4ac710a998d230a1a9a7d6862d4cbb22bd6ea11383470ba963f6','2026-07-28 10:56:44',NULL,NULL),(2,2,'185a5b62c21a02532d63bc6f7f542b08dce037bf5e823f9c39663a30d3c504fe','2026-07-28 10:57:02',NULL,NULL),(3,2,'df4add7dc9b498ccb2c65518dacd3a6735e5d3129785ba968983c4dd9e35050c','2026-07-28 10:57:02',NULL,NULL),(4,2,'daafc37d296c527bf125713f020817a50e53b40ee2c57a65cfa949b1e798fbaf','2026-07-28 11:01:25',NULL,NULL),(5,2,'884629390e33efe2c1de521e726b8a7aba813b74e74d773646f47a06d1053a3f','2026-07-28 11:01:36',NULL,NULL),(6,2,'fc7a27c34783b3d1a178c7c57c5b32ab1988b5468c372cbe37c052e57f490ffa','2026-07-28 11:01:46',NULL,NULL),(7,2,'12ba5d56ddbcd32865aa1cc318ecd03f77aeb62a1b0a5d623fb19d2b61e54f61','2026-07-28 11:01:57',NULL,NULL),(8,2,'5056af04edb59d1c4a12fbfd2e8b630eca67d08cf74417f7d90bf1c3498fcd14','2026-07-28 11:02:08',NULL,NULL),(9,2,'24d6a81e30e42c05f939b912010dcd1223b10f8dbecfe855d0ff91b8ba4dd444','2026-07-28 11:12:02',NULL,NULL),(10,2,'d99d92ba8c72f14ff008e3252e625d049e363bec18f6110edc79a692c51a4c26','2026-07-28 11:12:13',NULL,NULL),(11,2,'481d38313551c2b74c2aa0a5b1b1347aaf3a292e4f26a7c2ccd360209283a01c','2026-07-28 11:12:30',NULL,NULL),(12,2,'a680d659a3494e6377d8d8aa5712549c81b62eab42ba1329068b936eda2340c4','2026-07-28 11:12:40',NULL,NULL),(13,2,'23d51c3484dc3afff14b1364bcb5dae005fb8056c32f990efd5aeeafd15c0633','2026-07-28 11:13:39',NULL,NULL),(14,2,'f6440f4a795921ebc51d1a4cc13622d5b92e20bef4eb8848f59395e9cac1df50','2026-07-28 11:14:16',NULL,NULL),(15,2,'2d96921b1ef8ba892a24adf788edb85fdd7c7f29329fa0746464404fdc73c5b2','2026-07-28 11:14:53',NULL,NULL),(16,2,'ffc614e3e648f4ff4722e27be220a18af856935a31b5451e1beb348a99ef40bb','2026-07-28 11:15:14',NULL,NULL),(17,2,'e7b50c92dd5e4f707f991ca3890f14b0c4e8fa5fd3677435dfd20ae3e87a4846','2026-07-28 11:15:33',NULL,NULL),(18,2,'6c21bb6e7ce70a30ed3d104f2cf77e276298e6eace84a82e01dcc47e07b00f0c','2026-07-28 11:17:39',NULL,NULL),(19,2,'65e70d406eb50dfb622d1140480a4c478eb9789aba03dd14b4e8a76e0b2c022a','2026-07-28 11:32:04',NULL,NULL),(20,2,'1cb405d472c64cbbd5144e754a2a3f309b97fad7ed4c18677b68de2a9dad4e47','2026-07-28 11:32:05',NULL,NULL),(21,2,'9c470d21b30f0039f155604a8cb04e62f385ec1e83ad45f9a0384782a3f316ab','2026-07-28 11:35:00',NULL,NULL),(22,2,'e9665df71281c528ab07e79275d6692c1e91daf74607bad8c33f771854bb3332','2026-07-30 00:47:51',NULL,NULL),(23,2,'a850adfecb89c2821176fd8582f2d6c1fb00d8fb02455c7325c89dd26bf784f5','2026-07-30 01:43:56',NULL,NULL),(24,2,'66a8821550c5d14d01f460b577714ef9b69da16a634aa6e9fb3aa493c40ae8c4','2026-07-30 01:46:51',NULL,NULL),(25,2,'5b99cdb2a4d8aac04057c8559074d20bb6fb8e8dcfd26dcad94d8c0497b0ce9e','2026-07-30 01:47:13',NULL,NULL),(26,2,'c500e2f2459a485f40179e5e24d5c41dbba682a3b4dd161417bf894bd469fa95','2026-07-30 01:47:28',NULL,NULL),(27,2,'4e6f189530512b3b0dac8526234c76b25c7cd89483c4ceb90f66867f7b3a1742','2026-07-30 01:49:41',NULL,NULL),(28,2,'c6df4a00681569e7664d56dc716077ee66ad07354bea05a2f22783231de9e5dd','2026-07-30 01:50:25',NULL,NULL);
/*!40000 ALTER TABLE `api_tokens` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow_records`
--

LOCK TABLES `borrow_records` WRITE;
/*!40000 ALTER TABLE `borrow_records` DISABLE KEYS */;
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
INSERT INTO `consumable_inventory` VALUES (1,'Floor Cleaner (Pine)','Cleaning Agent','Liters',42.00,5.00,'2026-07-28','2026-07-19 05:37:22','2026-07-28 11:01:58'),(2,'Toilet Bowl Cleaner','Cleaning Agent','Bottles',7.00,3.00,'2025-07-08','2026-07-19 05:37:22',NULL),(3,'Trash Liners (Large)','Disposable','Rolls',4.00,5.00,'2025-07-05','2026-07-19 05:37:22',NULL),(4,'Mop Heads','Tools','Pieces',6.00,3.00,'2025-07-01','2026-07-19 05:37:22',NULL),(5,'Disinfectant Spray','Cleaning Agent','Bottles',2.00,4.00,'2025-06-28','2026-07-19 05:37:22',NULL),(6,'Tissue Paper (Rolls)','Disposable','Rolls',30.00,10.00,'2025-07-12','2026-07-19 05:37:22',NULL),(7,'Liquid Hand Soap','Cleaning Agent','Liters',3.00,4.00,'2025-07-09','2026-07-19 05:37:22',NULL),(8,'Brooms','Tools','Pieces',12.00,4.00,'2025-06-15','2026-07-19 05:37:22',NULL);
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
INSERT INTO `fire_extinguishers` VALUES (1,'','','',0.0,NULL,NULL,'',NULL,NULL,NULL,'updated via api test','2026-07-19 05:37:22','2026-07-28 11:01:46'),(2,'FE-ADM-02','Dry Chemical','Admin Building',6.0,'2025-01-10','2025-04-10','Refillable',2021,'Cruz, M.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(3,'FE-ADM-03','CO2','Admin Building',10.0,'2024-11-05','2025-02-05','Defective',2018,'Cruz, M.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(4,'FE-LIB-01','Dry Chemical','Library',10.0,'2025-02-01','2025-08-01','New',2023,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(5,'FE-LIB-02','CO2','Library',6.0,'2025-02-01','2025-08-01','New',2024,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(6,'FE-LIB-03','Foam','Library',9.0,'2025-01-15','2025-04-15','Refillable',2020,'Reyes, A.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(7,'FE-SCI-01','CO2','Science Building',10.0,'2025-01-20','2025-07-20','New',2023,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(8,'FE-SCI-02','Dry Chemical','Science Building',6.0,'2024-10-01','2025-01-01','Defective',2017,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(9,'FE-SCI-03','CO2','Science Building',10.0,'2025-03-01','2025-09-01','Refillable',2022,'Lim, B.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(10,'FE-GYM-01','Dry Chemical','Gymnasium',9.0,'2025-02-10','2025-08-10','New',2024,'Santos, R.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(11,'FE-GYM-02','CO2','Gymnasium',10.0,'2025-02-10','2025-08-10','New',2024,'Santos, R.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(12,'FE-CAN-01','Wet Chemical','Canteen',6.0,'2025-01-25','2025-04-25','Refillable',2021,'Gomez, T.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(13,'FE-CAN-02','Wet Chemical','Canteen',6.0,'2025-01-25','2025-04-25','Refillable',2021,'Gomez, T.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(14,'FE-ENG-01','CO2','Engineering',10.0,'2025-03-01','2025-09-01','New',2023,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(15,'FE-ENG-02','Dry Chemical','Engineering',6.0,'2024-09-01','2024-12-01','Defective',2016,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(16,'FE-ENG-03','CO2','Engineering',9.0,'2025-02-15','2025-05-15','Refillable',2022,'Flores, C.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(17,'FE-CCS-01','CO2','CCS Building',10.0,'2025-03-10','2025-09-10','New',2024,'Aquino, D.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(18,'FE-CCS-02','Dry Chemical','CCS Building',10.0,'2025-03-10','2025-09-10','New',2024,'Aquino, D.','Guard Dela Cruz',NULL,'2026-07-19 05:37:22',NULL),(19,'FE-CLI-01','CO2','Clinic',6.0,'2025-01-05','2025-07-05','New',2023,'Torres, L.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(20,'FE-CLI-02','Wet Chemical','Clinic',6.0,'2025-01-05','2025-04-05','Refillable',2020,'Torres, L.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(21,'FE-GRD-01','Dry Chemical','Guard House',6.0,'2025-02-20','2025-08-20','New',2023,'Mendoza, P.','Guard Santos',NULL,'2026-07-19 05:37:22',NULL),(22,'FE-TEST-001','CO2','Lobby',5.0,NULL,NULL,'New',NULL,NULL,NULL,NULL,'2026-07-28 11:01:26','2026-07-28 11:01:26');
/*!40000 ALTER TABLE `fire_extinguishers` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_assignments`
--

LOCK TABLES `janitorial_assignments` WRITE;
/*!40000 ALTER TABLE `janitorial_assignments` DISABLE KEYS */;
INSERT INTO `janitorial_assignments` VALUES (1,'Bautista, M.','Admin Building','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(2,'Dizon, L.','Library','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(3,'Fernandez, G.','Science Building','06:00:00','14:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(4,'Hernandez, K.','Gymnasium','05:00:00','13:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(5,'Ignacio, P.','Canteen','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(6,'Javier, C.','Engineering','08:00:00','16:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(7,'Lacson, A.','CCS Building','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(8,'Mendez, R.','Clinic','07:00:00','15:00:00','2026-07-19','Active','2026-07-19 05:37:22'),(9,'Test Staff','Zone A','08:00:00','16:00:00','2026-07-28','Off Duty','2026-07-28 19:01:47');
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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `janitorial_tasks`
--

LOCK TABLES `janitorial_tasks` WRITE;
/*!40000 ALTER TABLE `janitorial_tasks` DISABLE KEYS */;
INSERT INTO `janitorial_tasks` VALUES (1,1,'Sweep & mop corridors',0,NULL,'2026-07-19 05:37:22'),(2,1,'Clean restrooms — Floor 1',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(3,1,'Empty trash bins',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(4,1,'Wipe window sills',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(5,1,'Mop main lobby',1,'2025-07-18 09:30:00','2026-07-19 05:37:22'),(6,1,'Replenish soap & tissue',1,'2025-07-18 10:00:00','2026-07-19 05:37:22'),(7,1,'Clean comfort rooms — Floor 2',0,NULL,'2026-07-19 05:37:22'),(8,1,'General sanitizing',0,NULL,'2026-07-19 05:37:22'),(9,2,'Dust bookshelves',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(10,2,'Vacuum reading area',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(11,2,'Mop entrance',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(12,2,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(13,2,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(14,2,'Wipe computer tables',0,NULL,'2026-07-19 05:37:22'),(15,3,'Sweep lab corridors',1,'2025-07-18 06:30:00','2026-07-19 05:37:22'),(16,3,'Mop stairs',0,NULL,'2026-07-19 05:37:22'),(17,3,'Empty lab trash',0,NULL,'2026-07-19 05:37:22'),(18,3,'Sanitize lab benches',0,NULL,'2026-07-19 05:37:22'),(19,3,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(20,4,'Sweep gym floor',1,'2025-07-18 05:30:00','2026-07-19 05:37:22'),(21,4,'Mop court',1,'2025-07-18 06:00:00','2026-07-19 05:37:22'),(22,4,'Clean locker rooms',1,'2025-07-18 06:45:00','2026-07-19 05:37:22'),(23,4,'Empty trash bins',1,'2025-07-18 07:15:00','2026-07-19 05:37:22'),(24,5,'Wipe dining tables',1,'2025-07-18 07:00:00','2026-07-19 05:37:22'),(25,5,'Sweep floor',1,'2025-07-18 07:20:00','2026-07-19 05:37:22'),(26,5,'Mop canteen floor',1,'2025-07-18 07:45:00','2026-07-19 05:37:22'),(27,5,'Clean restrooms',1,'2025-07-18 08:15:00','2026-07-19 05:37:22'),(28,5,'Empty grease traps',0,NULL,'2026-07-19 05:37:22'),(29,5,'Sanitize counter tops',0,NULL,'2026-07-19 05:37:22'),(30,5,'Replace trash liners',0,NULL,'2026-07-19 05:37:22'),(31,6,'Sweep corridors',1,'2025-07-18 08:10:00','2026-07-19 05:37:22'),(32,6,'Mop workshop floor',0,NULL,'2026-07-19 05:37:22'),(33,6,'Clean restrooms',0,NULL,'2026-07-19 05:37:22'),(34,6,'Empty trash bins',0,NULL,'2026-07-19 05:37:22'),(35,6,'Wipe notice boards',0,NULL,'2026-07-19 05:37:22'),(36,6,'Sanitize door handles',0,NULL,'2026-07-19 05:37:22'),(37,7,'Sweep corridors',1,'2025-07-18 07:10:00','2026-07-19 05:37:22'),(38,7,'Mop server room hallway',1,'2025-07-18 07:35:00','2026-07-19 05:37:22'),(39,7,'Clean restrooms',1,'2025-07-18 08:00:00','2026-07-19 05:37:22'),(40,7,'Wipe workstations',1,'2025-07-18 08:30:00','2026-07-19 05:37:22'),(41,7,'Empty trash',1,'2025-07-18 09:00:00','2026-07-19 05:37:22'),(42,8,'Sanitize consultation room',1,'2025-07-18 07:05:00','2026-07-19 05:37:22'),(43,8,'Mop clinic floor',1,'2025-07-18 07:30:00','2026-07-19 05:37:22'),(44,8,'Clean restroom',1,'2025-07-18 07:55:00','2026-07-19 05:37:22'),(45,8,'Replace biohazard bags',1,'2025-07-18 08:20:00','2026-07-19 05:37:22'),(46,9,'Sweep floor',0,NULL,'2026-07-28 19:01:57');
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
  KEY `idx_scan_in` (`scan_in`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `key_borrow_logs`
--

LOCK TABLES `key_borrow_logs` WRITE;
/*!40000 ALTER TABLE `key_borrow_logs` DISABLE KEYS */;
INSERT INTO `key_borrow_logs` VALUES (1,'KL-001','EMP-2021-042','Dela Cruz, J.','Library','Library Storeroom Key','2025-07-18 07:30:00','2025-07-18 12:00:00','Returned','Santos, J.','2026-07-19 05:37:22'),(2,'KL-002','EMP-2022-118','Magsaysay, R.','CCS','Server Room Key','2025-07-18 08:15:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),(3,'KL-003','FAC-2020-007','Torres, F.','Science','Lab Cabinet Keys','2025-07-18 09:00:00','2025-07-18 10:30:00','Returned','Dela Cruz, P.','2026-07-19 05:37:22'),(4,'KL-004','EMP-2019-055','Reyes, A.','Admin','Admin Filing Room Key','2025-07-18 10:45:00',NULL,'Active','Santos, J.','2026-07-19 05:37:22'),(5,'KL-20260728-0001','Juan Cruz','Juan Cruz','IT','Server Room','2026-07-28 11:01:37','2026-07-28 11:01:47','Returned','Pedro','2026-07-28 19:01:37');
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
INSERT INTO `notifications` VALUES (1,'Vehicle Inspection','Routine vehicle health compliance check is scheduled tomorrow. System: FU-UBRA Fleet Telematics Service Engine','Operations Team','CRITICAL','Read','system',0,NULL,'2026-07-18 22:47:37'),(2,'Travel Reminder','Driver Mark has an assigned dispatch schedule tomorrow. Destination: Dumaguete City Top Nail Territory.','Driver Mark','MODERATE','Read','email',0,NULL,'2026-07-18 21:47:37'),(3,'Air-Con Cleaning','Air Conditioner Building A: A preventive system maintenance starts in 2 days. Facilities Dept.','Facilities Dept.','ROUTINE','Read','system',1,NULL,'2026-07-18 20:47:37'),(4,'Janitorial Assignment','Weekly deep disinfection assignment schedule for Team B begins tomorrow. Team B Duty.','Team B','ROUTINE','Completed','system',1,NULL,'2026-07-18 19:47:37'),(5,'Inventory Low Stock','Critical spare parts and engine filters are low on spare parts inventory. Notify: Open Calendar Events.','Office Supplies','CRITICAL','Read','system',0,NULL,'2026-07-18 18:47:37'),(6,'Vehicle Expiry','Registration for Utility Truck-04 has been safely completed earlier this week. Reference: FU Comms House Print Document.','Fleet Admin','ROUTINE','Resolved','email',1,NULL,'2026-07-18 17:47:37');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
INSERT INTO `personnel` VALUES (3,'EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',3,'Safety & Security Chief','GPS Route Validation','Active','2026-07-18 20:20:56'),(4,'EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',7,'Cleaning Operative','Science Lab Cleaning','On Leave','2026-07-18 20:20:56'),(5,'EMP-2022-071','Pedro Penduko','pedro.penduko@foundation.edu.ph',5,'IT Support','Network Deployment','Active','2026-07-18 20:20:56'),(6,'EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',2,'Driver','Van-01 Dispatch','Active','2026-07-18 20:20:56'),(7,'EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',2,'Senior Driver','Bus-02 Assignment','Active','2026-07-18 20:20:56'),(9,'20230407','sherina Banosong','sherina.rina@gmail.com',2,'Staff','Fixed the Door Knob','Active','2026-07-19 02:54:16'),(14,'20241690','Timothy Eraham','erahamtimothy@gmail.com',5,'Driver','Fixed the Door Knob','Active','2026-07-26 00:20:05'),(15,'20241691','TimothyLincon','lincontimothy@gmail.com',1,'Maintenance','Fixed the Door Knob','Active','2026-07-26 12:13:38'),(16,'20201206','Asaiah Gel','asaiahgel@gmail.com',5,'Administrator','test','Active','2026-07-27 13:38:12'),(17,'62626','ana mae','adjb@gmail.com',2,'Maintenance','water test','Active','2026-07-27 13:44:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'Facilities Management Report — Last 30 Days',NULL,'','Draft',NULL,0,NULL,'2026-07-25 08:22:54','2026-07-25 08:22:54','2026-07-25 16:22:54'),(2,'Test Report',2,'safety','Completed',NULL,0,NULL,'2026-07-28 03:02:08','2026-07-28 03:02:08','2026-07-28 11:02:08'),(3,'Delete Me',2,'test','Completed',NULL,1,'2026-07-28 03:02:09','2026-07-28 03:02:09','2026-07-28 03:02:09','2026-07-28 11:02:09');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_records`
--

LOCK TABLES `return_records` WRITE;
/*!40000 ALTER TABLE `return_records` DISABLE KEYS */;
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
INSERT INTO `safety_work_orders` VALUES (1,'WO-001','FE-ADM-03 defective — pressure gauge broken','Admin Building','Cruz, M.','Tech Valdez','High','In Progress','2025-07-10',NULL,'updated','2026-07-19 05:37:22','2026-07-28 11:01:47'),(2,'WO-002','FE-ENG-02 past expiry — needs replacement','Engineering','Flores, C.','Tech Ramos','High','Issue Logged','2025-07-12',NULL,NULL,'2026-07-19 05:37:22',NULL),(3,'WO-003','Missing FE slot — Admin lobby unprotected','Admin Building','Guard Santos','Purchasing Dept','Critical','Pending Parts','2025-07-14',NULL,NULL,'2026-07-19 05:37:22',NULL),(4,'WO-004','FE-SCI-02 not returning pressure after refill','Science Building','Lim, B.','Tech Valdez','Medium','Completed/Verified','2025-07-15',NULL,NULL,'2026-07-19 05:37:22',NULL),(5,'WO-20260728-0001','Broken AC','Room 2','Juan','Maria','High','Issue Logged','2026-07-28',NULL,'test','2026-07-28 11:01:36','2026-07-28 11:01:36');
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
INSERT INTO `system_settings` VALUES (1,'system_name','FU-UBRA Operational Portal','2026-07-22 05:49:14'),(2,'university','Foundation University','2026-07-22 05:49:14'),(3,'api_key','','2026-07-22 05:49:14'),(4,'theme','Dark Theme','2026-07-22 05:49:14'),(5,'smtp_host','smtp.gmail.com','2026-07-19 00:06:28'),(6,'smtp_port','587','2026-07-19 00:06:28'),(7,'smtp_user','','2026-07-19 00:06:28'),(8,'smtp_from','','2026-07-19 00:06:28'),(9,'smtp_name','FU-UBRA System','2026-07-19 00:06:28'),(10,'smtp_pass','','2026-07-19 00:06:28'),(11,'notif_maintenance','1','2026-07-19 00:06:28'),(12,'notif_vehicle','1','2026-07-19 00:06:28'),(13,'notif_janitorial','1','2026-07-19 00:06:28'),(14,'notif_asset','1','2026-07-19 00:06:28'),(15,'notif_travel','1','2026-07-19 00:06:28'),(16,'reminder_days','5','2026-07-19 00:06:28');
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
  `total_quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `availability` varchar(50) DEFAULT 'Available',
  `created_at` datetime DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools`
--

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES (2,'MacBook Pro 16','AST-92041','IT Equipment','Deans Office, CCS','Maria Clara Santos','Excellent',1,0,'Borrowed','2026-07-18 20:20:56',0,NULL,NULL),(3,'Floors Buffer Matt','AST-03481','Janitorial','Janitor Depot B','Sonia G. Ramirez','Excellent',1,1,'Available','2026-07-18 20:20:56',0,NULL,NULL),(4,'Sony Alpha A7 III','AST-00612','Media Studio','Media Center','Col. Arthur Miller','Poor',1,1,'Available','2026-07-18 20:20:56',0,NULL,NULL),(5,'Epson Projector X50','AST-77120','IT Equipment','AVR Room 2','Pedro Penduko','Good',1,1,'Available','2026-07-18 20:20:56',0,NULL,NULL),(6,'Industrial Vacuum','AST-55019','Janitorial','Housekeeping Store','Sonia G. Ramirez','Good',1,0,'Borrowed','2026-07-18 20:20:56',0,NULL,NULL),(7,'Cordless Drill Set','AST-30188','Tools','Maintenance Shop','Engr. James Diaz','Excellent',1,1,'Available','2026-07-18 20:20:56',0,NULL,NULL);
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
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_id` (`trip_id`),
  KEY `requester_id` (`requester_id`),
  KEY `department_id` (`department_id`),
  KEY `assigned_driver_id` (`assigned_driver_id`),
  KEY `assigned_vehicle_id` (`assigned_vehicle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_requests`
--

LOCK TABLES `travel_requests` WRITE;
/*!40000 ALTER TABLE `travel_requests` DISABLE KEYS */;
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
INSERT INTO `users` VALUES (0,'John Doe','user@example.com','12345','12345','$2y$10$oToUil7tkY0OxER6k3jyD.L0yRWYhOZWoct.oLddVzcT7D2vAlWNO','staff',NULL,NULL,'2026-07-26 11:56:12'),(2,'Kenchie Terante','admin@fu.edu.ph','admin',NULL,'$2b$12$c8vA1tfnL.JQGWsHuahepukT6/UGD41npxdErvEsNFlsGr7d4Rxwq','Administrator','Operations Office',NULL,'2026-07-18 22:18:46');
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
  `registry_date` date DEFAULT NULL,
  `gas_type` varchar(50) DEFAULT NULL,
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
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (1,'Travis','2032450','4 wheels',NULL,NULL,3,1,'Online','Completed','Reserved',0,NULL,'2026-07-25 04:47:40','2026-07-25 04:47:40','2026-07-25 12:47:40');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-01 18:55:37
