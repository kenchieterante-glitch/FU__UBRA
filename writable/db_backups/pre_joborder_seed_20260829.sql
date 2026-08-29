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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
INSERT INTO `personnel` VALUES (3,'EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',3,'Regular','Safety & Security Chief','GPS Route Validation','Active',0,NULL,'2026-07-18 20:20:56'),(4,'EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',7,'Regular','Carpenter','Science Lab Cleaning','Active',0,NULL,'2026-07-18 20:20:56'),(5,'EMP-2022-071','Pedro Penduko','pedro.penduko@foundation.edu.ph',5,'Regular','IT Support','Network Deployment','Active',0,NULL,'2026-07-18 20:20:56'),(6,'EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',2,'Regular','Driver','Van-01 Dispatch','Active',0,NULL,'2026-07-18 20:20:56'),(7,'EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',2,'Regular','Senior Driver','Bus-02 Assignment','Active',0,NULL,'2026-07-18 20:20:56'),(8,'EMP-2018-009','Dr. Helen Peralta','helen.peralta@foundation.edu.ph',6,'Regular','Department Head','Trip Approvals','Active',0,NULL,'2026-07-18 20:20:56'),(9,'EMP-2023-301','sherina Banosong','sherina.banosong@foundation.edu.ph',2,'Regular','Staff','Front Office Records Filing','Active',0,NULL,'2026-07-19 02:54:16'),(14,'EMP-2024-302','Timothy Eraham','timothy.eraham@foundation.edu.ph',5,'Regular','Driver','Van-02 Dispatch','Active',0,NULL,'2026-07-26 00:20:05'),(15,'EMP-2024-303','TimothyLincon','timothy.lincon@foundation.edu.ph',1,'Regular','Janitor','Library Restroom Cleaning','Active',0,NULL,'2026-07-26 12:13:38'),(16,'EMP-2023-304','Juan dela Cruz','juan.delacruz@foundation.edu.ph',8,'Regular','Maintenance','HVAC Unit Inspection - Bldg 14','Active',0,NULL,'2026-07-27 22:20:10'),(17,'EMP-2023-305','Juan dela Beto','juan.delabeto@foundation.edu.ph',1,'Regular','Janitor','Gymnasium Floor Maintenance','Active',0,NULL,'2026-07-27 22:22:00'),(18,'EMP-2018-306','Lapu-lapu','lapu.lapu@foundation.edu.ph',5,'Regular','Janitor','Admin Lobby Floor Care','Active',0,NULL,'2026-07-28 14:03:20'),(19,'EMP-2023-307','Juan Cruz','juancruz@example.com',1,'Regular','Driver',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(20,'EMP-2023-308','Mina Santos','minasantos@example.com',1,'Regular','Janitor',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(21,'EMP-2023-309','Rico Dela Cruz','ricodelacruz@example.com',1,'Regular','Carpenter',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(22,'EMP-2023-310','Armand Perez','armandperez@example.com',1,'Regular','Maintenance',NULL,'On Leave',0,NULL,'2026-07-28 18:33:24'),(23,'EMP-2026-835','Ricardo Reyes','ricardo.reyes@foundation.edu.ph',4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(24,'EMP-2026-449','Goyo Pascual','goyo.pascual@foundation.edu.ph',4,'Regular','Janitor','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(25,'EMP-2026-440','Cardo Manalo','cardo.manalo@foundation.edu.ph',1,'Regular','Carpenter','Cabinet Fabrication','Active',0,NULL,'2026-07-31 20:16:53'),(26,'EMP-2026-765','Rodrigo Torres','rodrigo.torres@foundation.edu.ph',8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),(27,'EMP-2026-834','Josefa Mendoza','josefa.mendoza@foundation.edu.ph',1,'Regular','Maintenance Technician','Electrical Repair','Active',0,NULL,'2026-07-31 20:16:53'),(28,'EMP-2026-100','Fernando Ocampo','fernando.ocampo@foundation.edu.ph',1,'Regular','Driver','Renovation Project Lead','On Leave',0,NULL,'2026-07-31 20:16:53'),(29,'EMP-2026-786','Cardo Domingo','cardo.domingo@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(30,'EMP-2026-654','Teresa Domingo','teresa.domingo@foundation.edu.ph',1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),(31,'EMP-2026-683','Emilio Reyes','emilio.reyes@foundation.edu.ph',2,'Regular','Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(32,'EMP-2026-631','Consolacion Garcia','consolacion.garcia@foundation.edu.ph',8,'Regular','Accounting Staff','Payroll Processing','Active',0,NULL,'2026-07-31 20:16:53'),(33,'EMP-2026-243','Antonio Reyes','antonio.reyes@foundation.edu.ph',2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),(34,'EMP-2026-283','Danilo Mendoza','danilo.mendoza@foundation.edu.ph',1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),(35,'EMP-2026-134','Rizal Bautista','rizal.bautista@foundation.edu.ph',2,'Regular','Driver','Utility Truck Duty','Active',0,NULL,'2026-07-31 20:16:53'),(36,'EMP-2026-201','Remedios Ocampo','remedios.ocampo@foundation.edu.ph',1,'Regular','Physical Plant Supr.','Preventive Maintenance','Active',0,NULL,'2026-07-31 20:16:53'),(37,'EMP-2026-887','Ricardo Mendoza','ricardo.mendoza@foundation.edu.ph',1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(38,'EMP-2026-305','Fernando Salazar','fernando.salazar@foundation.edu.ph',1,'Regular','Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(39,'EMP-2026-709','Andres Navarro','andres.navarro@foundation.edu.ph',8,'Regular','Accounting Staff','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(40,'EMP-2026-953','Diego Castillo','diego.castillo@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(41,'EMP-2026-172','Isabel Aquino','isabel.aquino@foundation.edu.ph',1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),(42,'EMP-2026-738','Fernando Navarro','fernando.navarro@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(43,'EMP-2026-970','Gabriela Pascual','gabriela.pascual@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(44,'EMP-2026-949','Emilio Rivera','emilio.rivera@foundation.edu.ph',4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(45,'EMP-2026-492','Maria Mendoza','maria.mendoza@foundation.edu.ph',1,'Regular','Lead Carpenter','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(46,'EMP-2026-603','Rizal Garcia','rizal.garcia@foundation.edu.ph',6,'Regular','Administrator','Department Coordination','Active',0,NULL,'2026-07-31 20:16:53'),(47,'EMP-2026-626','Cardo Garcia','cardo.garcia@foundation.edu.ph',1,'Regular','Maintenance Technician','Plumbing Repair','Active',0,NULL,'2026-07-31 20:16:53'),(48,'EMP-2026-329','Diego Fernandez','diego.fernandez@foundation.edu.ph',1,'Regular','Maintenance Technician','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(49,'EMP-2026-617','Josefa Villanueva','josefa.villanueva@foundation.edu.ph',1,'Regular','Construction Worker','Scaffolding Setup','Active',0,NULL,'2026-07-31 20:16:53'),(50,'EMP-2026-628','Goyo Castillo','goyo.castillo@foundation.edu.ph',6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(51,'EMP-2026-735','Antonio Mendoza','antonio.mendoza@foundation.edu.ph',2,'Regular','Senior Driver','Long Haul Route','Active',0,NULL,'2026-07-31 20:16:53'),(52,'EMP-2026-815','Josefa Garcia','josefa.garcia@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(53,'EMP-2026-844','Goyo Ramos','goyo.ramos@foundation.edu.ph',1,'Regular','Construction Foreman','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(54,'EMP-2026-737','Juan Santos','juan.santos@foundation.edu.ph',4,'Regular','Cleaning Operative','CCS Building Cleaning','Active',0,NULL,'2026-07-31 20:16:53'),(55,'EMP-2026-272','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Regular','Construction Foreman','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(56,'EMP-2026-378','Fernando Cruz','fernando.cruz@foundation.edu.ph',3,'Regular','Guard','Guard House Duty','Active',0,NULL,'2026-07-31 20:16:53'),(57,'EMP-2026-271','Pedro Reyes','pedro.reyes@foundation.edu.ph',5,'Regular','IT Support','Helpdesk Support','Active',0,NULL,'2026-07-31 20:16:53'),(58,'EMP-2026-782','Maria Domingo','maria.domingo@foundation.edu.ph',1,'Regular','Lead Carpenter','Custom Furniture Build','Active',0,NULL,'2026-07-31 20:16:53'),(59,'EMP-2026-792','Jose Torres','jose.torres@foundation.edu.ph',1,'Regular','Construction Foreman','Renovation Project Lead','Active',0,NULL,'2026-07-31 20:16:53'),(60,'EMP-2026-711','Cardo Navarro','cardo.navarro@foundation.edu.ph',3,'Regular','Guard','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(61,'EMP-2026-498','Fernando Reyes','fernando.reyes@foundation.edu.ph',1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),(62,'EMP-2026-487','Manuel Domingo','manuel.domingo@foundation.edu.ph',6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),(63,'EMP-2026-392','Josefa Ramos','josefa.ramos@foundation.edu.ph',6,'Regular','Office Staff','Unassigned','On Leave',0,NULL,'2026-07-31 20:16:53'),(64,'EMP-2026-511','Corazon Castillo','corazon.castillo@foundation.edu.ph',1,'Regular','Physical Plant Supr.','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(65,'EMP-2026-385','Andres Garcia','andres.garcia@foundation.edu.ph',3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),(66,'EMP-2026-429','Diego Manalo','diego.manalo@foundation.edu.ph',3,'Regular','Security Officer','Night Shift Patrol','Active',0,NULL,'2026-07-31 20:16:53'),(67,'EMP-2026-646','Consolacion Reyes','consolacion.reyes@foundation.edu.ph',6,'Regular','Office Staff','Front Desk Duty','Active',0,NULL,'2026-07-31 20:16:53'),(68,'EMP-2026-525','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',1,'Regular','Maintenance Technician','AC Maintenance A','Active',0,NULL,'2026-07-31 20:16:53'),(69,'EMP-2026-292','Remedios Mendoza','remedios.mendoza@foundation.edu.ph',3,'Regular','Security Officer','Visitor Screening','Active',0,NULL,'2026-07-31 20:16:53'),(70,'EMP-2026-877','Danilo Villanueva','danilo.villanueva@foundation.edu.ph',4,'Regular','Janitor','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(71,'EMP-2026-157','Josefa Manalo','josefa.manalo@foundation.edu.ph',2,'Regular','Senior Driver','Unassigned','Inactive',0,NULL,'2026-07-31 20:16:53'),(72,'EMP-2026-743','Isabel Castillo','isabel.castillo@foundation.edu.ph',3,'Regular','Guard','CCTV Monitoring','Active',0,NULL,'2026-07-31 20:16:53'),(73,'20211735','Maisie Therese Tigmo','maisie.tigmo@foundation.edu.ph',1,'Regular','Maintenance Technician','Unassigned','Active',0,NULL,'2026-08-02 19:14:02'),(74,'20262020','Jose Protacio Rizal Mercado y Realonzo Realonda','pepe@gmail.com',1,'Regular','Maintenance Technician','To Inspect the Aircon ','Active',0,NULL,'2026-08-03 21:13:52'),(75,'EMP-2026-901','Maria Clara Santos','maria.santos@fuubra.local',5,'Regular','IT Equipment Custodian','IT asset custody and inventory','Active',0,NULL,'2026-08-03 23:48:23'),(76,'EMP-2026-902','Engr. James Diaz','james.diaz@fuubra.local',1,'Regular','Facilities Engineer','Tools and equipment custody','Active',0,NULL,'2026-08-03 23:48:23'),(77,'EMP-2026-903','John Doe','john.doe@fuubra.local',1,'Regular','Facilities Staff','General facilities support','Active',0,NULL,'2026-08-03 23:48:23');
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_orders`
--

LOCK TABLES `job_orders` WRITE;
/*!40000 ALTER TABLE `job_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_orders` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel_assignments`
--

LOCK TABLES `personnel_assignments` WRITE;
/*!40000 ALTER TABLE `personnel_assignments` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel_contracts`
--

LOCK TABLES `personnel_contracts` WRITE;
/*!40000 ALTER TABLE `personnel_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `personnel_contracts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-29 22:31:26
