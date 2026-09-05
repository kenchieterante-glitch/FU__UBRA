-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: college_management
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
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `classID` int(11) NOT NULL AUTO_INCREMENT,
  `courseID` varchar(10) NOT NULL,
  `sectionName` varchar(10) NOT NULL,
  `teacherID` int(11) DEFAULT NULL,
  `semester` varchar(20) NOT NULL,
  `year` year(4) NOT NULL,
  `room` varchar(20) DEFAULT NULL,
  `schedule` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`classID`),
  UNIQUE KEY `uq_class_offering` (`courseID`,`sectionName`,`semester`,`year`),
  KEY `fk_class_teacher` (`teacherID`),
  CONSTRAINT `fk_class_course` FOREIGN KEY (`courseID`) REFERENCES `courses` (`courseID`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_teacher` FOREIGN KEY (`teacherID`) REFERENCES `teachers` (`teacherID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'CS101','A',1,'1st Semester',2026,'Room 201','M-F 10:00-11:00'),(2,'CS102','A',1,'1st Semester',2026,'Room 202','M-F 10:00-11:00');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `classlist`
--

DROP TABLE IF EXISTS `classlist`;
/*!50001 DROP VIEW IF EXISTS `classlist`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `classlist` AS SELECT
 1 AS `classID`,
  1 AS `courseID`,
  1 AS `courseName`,
  1 AS `sectionName`,
  1 AS `semester`,
  1 AS `year`,
  1 AS `room`,
  1 AS `schedule`,
  1 AS `teacherName`,
  1 AS `studentID`,
  1 AS `studentName`,
  1 AS `studentEmail`,
  1 AS `enrollmentID`,
  1 AS `status` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `courseID` varchar(10) NOT NULL,
  `courseName` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `units` decimal(3,1) NOT NULL DEFAULT 3.0,
  `departmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`courseID`),
  KEY `fk_course_department` (`departmentID`),
  CONSTRAINT `fk_course_department` FOREIGN KEY (`departmentID`) REFERENCES `departments` (`departmentID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES ('CS101','Database Management Systems','Fundamentals of relational databases and SQL.',3.0,1),('CS102','Object Oriented Programming','OOP concepts, design, and implementation.',3.0,1);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `departmentID` int(11) NOT NULL AUTO_INCREMENT,
  `departmentName` varchar(100) NOT NULL,
  PRIMARY KEY (`departmentID`),
  UNIQUE KEY `departmentName` (`departmentName`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'College of Computer Studies');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `enrollmentID` int(11) NOT NULL AUTO_INCREMENT,
  `studentID` int(11) NOT NULL,
  `classID` int(11) NOT NULL,
  `enrollmentDate` date DEFAULT curdate(),
  `status` enum('Enrolled','Dropped','Completed') NOT NULL DEFAULT 'Enrolled',
  PRIMARY KEY (`enrollmentID`),
  UNIQUE KEY `uq_student_class` (`studentID`,`classID`),
  KEY `fk_enroll_class` (`classID`),
  CONSTRAINT `fk_enroll_class` FOREIGN KEY (`classID`) REFERENCES `classes` (`classID`) ON DELETE CASCADE,
  CONSTRAINT `fk_enroll_student` FOREIGN KEY (`studentID`) REFERENCES `students` (`studentID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,1,1,'2026-08-25','Enrolled'),(2,2,1,'2026-08-25','Enrolled'),(3,3,1,'2026-08-25','Enrolled'),(4,4,1,'2026-08-25','Enrolled'),(5,5,1,'2026-08-25','Enrolled'),(6,6,1,'2026-08-25','Enrolled'),(7,7,1,'2026-08-25','Enrolled'),(8,8,1,'2026-08-25','Enrolled'),(9,9,1,'2026-08-25','Enrolled'),(10,10,1,'2026-08-25','Enrolled'),(11,11,1,'2026-08-25','Enrolled'),(12,12,1,'2026-08-25','Enrolled'),(13,13,1,'2026-08-25','Enrolled'),(14,14,1,'2026-08-25','Enrolled'),(15,15,1,'2026-08-25','Enrolled'),(16,16,1,'2026-08-25','Enrolled'),(17,17,1,'2026-08-25','Enrolled'),(18,18,1,'2026-08-25','Enrolled'),(19,19,1,'2026-08-25','Enrolled'),(20,20,1,'2026-08-25','Enrolled'),(21,21,2,'2026-08-25','Enrolled'),(22,22,2,'2026-08-25','Enrolled'),(23,23,2,'2026-08-25','Enrolled'),(24,24,2,'2026-08-25','Enrolled'),(25,25,2,'2026-08-25','Enrolled'),(26,26,2,'2026-08-25','Enrolled'),(27,27,2,'2026-08-25','Enrolled'),(28,28,2,'2026-08-25','Enrolled'),(29,29,2,'2026-08-25','Enrolled'),(30,30,2,'2026-08-25','Enrolled'),(31,31,2,'2026-08-25','Enrolled'),(32,32,2,'2026-08-25','Enrolled'),(33,33,2,'2026-08-25','Enrolled'),(34,34,2,'2026-08-25','Enrolled'),(35,35,2,'2026-08-25','Enrolled');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grades` (
  `gradeID` int(11) NOT NULL AUTO_INCREMENT,
  `enrollmentID` int(11) NOT NULL,
  `participation` decimal(5,2) DEFAULT 0.00,
  `performance` decimal(5,2) DEFAULT 0.00,
  `majorExam` decimal(5,2) DEFAULT 0.00,
  `finalOutput` decimal(5,2) DEFAULT 0.00,
  `finalGrade` decimal(5,2) DEFAULT NULL,
  `dateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`gradeID`),
  UNIQUE KEY `enrollmentID` (`enrollmentID`),
  CONSTRAINT `fk_grade_enrollment` FOREIGN KEY (`enrollmentID`) REFERENCES `enrollments` (`enrollmentID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` VALUES (1,1,74.64,75.38,98.75,85.27,85.28,'2026-08-25 12:10:05'),(2,2,86.27,91.18,62.29,85.45,80.30,'2026-08-25 12:10:05'),(3,3,85.09,94.85,66.30,98.63,86.44,'2026-08-25 12:10:05'),(4,4,72.40,71.50,83.80,88.63,80.42,'2026-08-25 12:10:05'),(5,5,77.06,69.20,95.61,73.62,79.23,'2026-08-25 12:10:05'),(6,6,87.84,86.68,76.77,85.43,83.45,'2026-08-25 12:10:05'),(7,7,85.68,97.71,68.17,90.07,85.35,'2026-08-25 12:10:05'),(8,8,77.16,78.85,86.87,75.50,80.08,'2026-08-25 12:10:05'),(9,9,79.49,91.32,62.90,81.04,78.53,'2026-08-25 12:10:05'),(10,10,99.95,99.86,62.93,72.46,80.57,'2026-08-25 12:10:05'),(11,11,77.96,97.66,95.23,95.77,94.39,'2026-08-25 12:10:05'),(12,12,81.09,70.52,93.35,89.62,84.16,'2026-08-25 12:10:05'),(13,13,88.35,99.55,86.16,65.27,84.13,'2026-08-25 12:10:05'),(14,14,94.51,75.48,86.54,97.86,87.41,'2026-08-25 12:10:05'),(15,15,74.03,69.04,64.28,84.36,72.71,'2026-08-25 12:10:05'),(16,16,78.17,86.17,88.70,72.13,81.92,'2026-08-25 12:10:05'),(17,17,89.03,74.24,79.54,96.69,84.04,'2026-08-25 12:10:05'),(18,18,95.38,68.23,76.94,74.68,75.49,'2026-08-25 12:10:05'),(19,19,70.11,91.99,85.48,74.17,82.50,'2026-08-25 12:10:05'),(20,20,92.24,84.31,77.11,65.34,77.25,'2026-08-25 12:10:05'),(21,21,72.26,95.91,96.16,84.10,90.08,'2026-08-25 12:10:05'),(22,22,95.04,85.39,65.92,69.46,75.73,'2026-08-25 12:10:05'),(23,23,79.25,96.46,91.84,95.12,92.95,'2026-08-25 12:10:05'),(24,24,96.97,72.35,69.98,68.60,72.98,'2026-08-25 12:10:05'),(25,25,93.40,95.94,76.26,86.72,87.02,'2026-08-25 12:10:05'),(26,26,74.64,97.55,94.58,99.17,94.85,'2026-08-25 12:10:05'),(27,27,94.32,95.85,60.99,90.78,83.72,'2026-08-25 12:10:05'),(28,28,79.97,97.58,92.09,95.24,93.47,'2026-08-25 12:10:05'),(29,29,94.32,74.34,91.49,68.78,79.81,'2026-08-25 12:10:05'),(30,30,96.17,95.05,68.90,93.58,86.88,'2026-08-25 12:10:05'),(31,31,83.81,75.68,91.81,72.97,80.52,'2026-08-25 12:10:05'),(32,32,70.71,71.76,73.13,95.25,79.11,'2026-08-25 12:10:05'),(33,33,99.01,74.77,85.66,78.99,81.73,'2026-08-25 12:10:05'),(34,34,99.43,83.77,97.57,69.04,85.06,'2026-08-25 12:10:05'),(35,35,99.11,71.25,98.50,74.29,83.12,'2026-08-25 12:10:05');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `studentID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`studentID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'Antonio Cruz','2005-04-08','Barangay Punta Taytay','Bacolod City','Negros Occidental','Philippines','antonio.cruz1@student.college.edu.ph','09182719583'),(2,'Mikaela Torres','2006-01-01','Barangay Bata','Bacolod City','Negros Occidental','Philippines','mikaela.torres2@student.college.edu.ph','09154668136'),(3,'Manuel Manalo','2003-09-07','Barangay Bata','Bacolod City','Negros Occidental','Philippines','manuel.manalo3@student.college.edu.ph','09448038374'),(4,'Kristine Pacheco','2005-01-25','Barangay Bata','Bacolod City','Negros Occidental','Philippines','kristine.pacheco4@student.college.edu.ph','09208090293'),(5,'Angelo Domingo','2004-06-04','Barangay Alijis','Bacolod City','Negros Occidental','Philippines','angelo.domingo5@student.college.edu.ph','09157374122'),(6,'Isabella Rosales','2005-10-09','Barangay Vista Alegre','Bacolod City','Negros Occidental','Philippines','isabella.rosales6@student.college.edu.ph','09128707870'),(7,'Kobe Ramos','2006-02-18','Barangay Sum-ag','Bacolod City','Negros Occidental','Philippines','kobe.ramos7@student.college.edu.ph','09287067228'),(8,'Dianne Aquino','2003-01-22','Barangay Punta Taytay','Bacolod City','Negros Occidental','Philippines','dianne.aquino8@student.college.edu.ph','09245855124'),(9,'Miguel Pascual','2003-07-09','Barangay Vista Alegre','Bacolod City','Negros Occidental','Philippines','miguel.pascual9@student.college.edu.ph','09397120868'),(10,'Andrea Valdez','2004-11-09','Barangay Handumanan','Bacolod City','Negros Occidental','Philippines','andrea.valdez10@student.college.edu.ph','09143871230'),(11,'Kobe Navarro','2006-07-09','Barangay Alijis','Bacolod City','Negros Occidental','Philippines','kobe.navarro11@student.college.edu.ph','09454684531'),(12,'Grace Bautista','2003-06-13','Barangay Singcang','Bacolod City','Negros Occidental','Philippines','grace.bautista12@student.college.edu.ph','09272110460'),(13,'Luis Aguilar','2006-07-21','Barangay Singcang','Bacolod City','Negros Occidental','Philippines','luis.aguilar13@student.college.edu.ph','09393396987'),(14,'Angela Mendoza','2005-12-19','Barangay Singcang','Bacolod City','Negros Occidental','Philippines','angela.mendoza14@student.college.edu.ph','09377700828'),(15,'Marco Pascual','2006-02-25','Barangay Alijis','Bacolod City','Negros Occidental','Philippines','marco.pascual15@student.college.edu.ph','09132839607'),(16,'Camille Castro','2006-10-03','Barangay Tangub','Bacolod City','Negros Occidental','Philippines','camille.castro16@student.college.edu.ph','09347402509'),(17,'Adrian Sarmiento','2005-09-28','Barangay Banago','Bacolod City','Negros Occidental','Philippines','adrian.sarmiento17@student.college.edu.ph','09102921859'),(18,'Mikaela Domingo','2005-02-10','Barangay Tangub','Bacolod City','Negros Occidental','Philippines','mikaela.domingo18@student.college.edu.ph','09373653446'),(19,'Gabriel Reyes','2005-09-25','Barangay Punta Taytay','Bacolod City','Negros Occidental','Philippines','gabriel.reyes19@student.college.edu.ph','09219517485'),(20,'Isabella Rivera','2004-03-12','Barangay Vista Alegre','Bacolod City','Negros Occidental','Philippines','isabella.rivera20@student.college.edu.ph','09209897858'),(21,'Juan Aguilar','2003-02-12','Barangay Estefania','Bacolod City','Negros Occidental','Philippines','juan.aguilar21@student.college.edu.ph','09295017343'),(22,'Ana Navarro','2003-02-24','Barangay Sum-ag','Bacolod City','Negros Occidental','Philippines','ana.navarro22@student.college.edu.ph','09412161193'),(23,'Kobe Mendoza','2006-09-06','Barangay Alijis','Bacolod City','Negros Occidental','Philippines','kobe.mendoza23@student.college.edu.ph','09269852897'),(24,'Christine Enriquez','2004-12-10','Barangay Singcang','Bacolod City','Negros Occidental','Philippines','christine.enriquez24@student.college.edu.ph','09357264956'),(25,'Gabriel Alvarez','2003-04-08','Barangay Estefania','Bacolod City','Negros Occidental','Philippines','gabriel.alvarez25@student.college.edu.ph','09146672134'),(26,'Maria Pascual','2004-01-03','Barangay Bata','Bacolod City','Negros Occidental','Philippines','maria.pascual26@student.college.edu.ph','09134841005'),(27,'Miguel Santos','2005-02-17','Barangay Vista Alegre','Bacolod City','Negros Occidental','Philippines','miguel.santos27@student.college.edu.ph','09255672073'),(28,'Trisha Del Rosario','2004-12-19','Barangay Banago','Bacolod City','Negros Occidental','Philippines','trisha.delrosario28@student.college.edu.ph','09468930103'),(29,'Manuel Tolentino','2006-04-04','Barangay Tangub','Bacolod City','Negros Occidental','Philippines','manuel.tolentino29@student.college.edu.ph','09168231838'),(30,'Faith Enriquez','2006-12-02','Barangay Granada','Bacolod City','Negros Occidental','Philippines','faith.enriquez30@student.college.edu.ph','09162016911'),(31,'Paolo Marquez','2003-04-07','Barangay Tangub','Bacolod City','Negros Occidental','Philippines','paolo.marquez31@student.college.edu.ph','09229997381'),(32,'Samantha Mendoza','2004-05-15','Barangay Granada','Bacolod City','Negros Occidental','Philippines','samantha.mendoza32@student.college.edu.ph','09252264748'),(33,'Gabriel Flores','2003-02-25','Barangay Villamonte','Bacolod City','Negros Occidental','Philippines','gabriel.flores33@student.college.edu.ph','09253790237'),(34,'Nicole Ignacio','2004-07-02','Barangay Estefania','Bacolod City','Negros Occidental','Philippines','nicole.ignacio34@student.college.edu.ph','09207358113'),(35,'Juan Ocampo','2006-05-14','Barangay Taculing','Bacolod City','Negros Occidental','Philippines','juan.ocampo35@student.college.edu.ph','09459164991');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `teacherID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `departmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`teacherID`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_teacher_department` (`departmentID`),
  CONSTRAINT `fk_teacher_department` FOREIGN KEY (`departmentID`) REFERENCES `departments` (`departmentID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,'John Doe','1985-04-12','Barangay 1','Bacolod City','Negros Occidental','Philippines','john.doe@college.edu.ph','09171234560',1),(2,'Maria Santos','1980-03-15','Barangay Villamonte','Bacolod City','Negros Occidental','Philippines','maria.santos@college.edu.ph','09171234561',1),(3,'Ramon Reyes','1978-07-22','Barangay Mandalagan','Bacolod City','Negros Occidental','Philippines','ramon.reyes@college.edu.ph','09181234562',1),(4,'Angelica Cruz','1990-11-04','Barangay Alijis','Bacolod City','Negros Occidental','Philippines','angelica.cruz@college.edu.ph','09191234563',1),(5,'Ferdinand Aquino','1983-01-30','Barangay Singcang','Bacolod City','Negros Occidental','Philippines','ferdinand.aquino@college.edu.ph','09201234564',1),(6,'Josephine Bautista','1987-09-12','Barangay Taculing','Bacolod City','Negros Occidental','Philippines','josephine.bautista@college.edu.ph','09211234565',1),(7,'Ricardo Dela Cruz','1975-05-18','Barangay Handumanan','Bacolod City','Negros Occidental','Philippines','ricardo.delacruz@college.edu.ph','09221234566',1),(8,'Cristina Flores','1992-02-27','Barangay Granada','Bacolod City','Negros Occidental','Philippines','cristina.flores@college.edu.ph','09231234567',1),(9,'Manuel Garcia','1981-08-09','Barangay Estefania','Bacolod City','Negros Occidental','Philippines','manuel.garcia@college.edu.ph','09241234568',1);
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'college_management'
--

--
-- Final view structure for view `classlist`
--

/*!50001 DROP VIEW IF EXISTS `classlist`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `classlist` AS select `c`.`classID` AS `classID`,`co`.`courseID` AS `courseID`,`co`.`courseName` AS `courseName`,`c`.`sectionName` AS `sectionName`,`c`.`semester` AS `semester`,`c`.`year` AS `year`,`c`.`room` AS `room`,`c`.`schedule` AS `schedule`,`t`.`name` AS `teacherName`,`s`.`studentID` AS `studentID`,`s`.`name` AS `studentName`,`s`.`email` AS `studentEmail`,`e`.`enrollmentID` AS `enrollmentID`,`e`.`status` AS `status` from ((((`classes` `c` join `courses` `co` on(`co`.`courseID` = `c`.`courseID`)) left join `teachers` `t` on(`t`.`teacherID` = `c`.`teacherID`)) join `enrollments` `e` on(`e`.`classID` = `c`.`classID` and `e`.`status` = 'Enrolled')) join `students` `s` on(`s`.`studentID` = `e`.`studentID`)) order by `c`.`classID`,`s`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-31 23:08:03
