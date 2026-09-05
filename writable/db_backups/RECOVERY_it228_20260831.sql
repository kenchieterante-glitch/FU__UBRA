-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: it228
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
-- Table structure for table `it228a`
--

DROP TABLE IF EXISTS `it228a`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `it228a` (
  `id_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `course` varchar(10) NOT NULL,
  `level` int(11) NOT NULL,
  `e_status` varchar(5) NOT NULL,
  `sex` char(1) NOT NULL,
  PRIMARY KEY (`id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it228a`
--

LOCK TABLES `it228a` WRITE;
/*!40000 ALTER TABLE `it228a` DISABLE KEYS */;
INSERT INTO `it228a` VALUES ('***20211002','DIPUTADO, DENZEL CLIFF M.','denzel.diputado@foundationu.com','BSIT',4,'C','M'),('20090936','MARANGA, IAN CHRISTIAN N.','ianchristian.maranga@foundationu.com','BSIT',4,'R','M'),('20171770','MINEL, KHENT AARON N.','khentaaron.minel@foundationu.com','BSIT',4,'C','M'),('20210058','MANNHART, TERY A.','tery.mannhart@foundationu.com','BSIT',3,'C','M'),('20210256','SALVORO, ZAIRA O.','zaira.salvoro@foundationu.com','BSIT',3,'C','F'),('20211034','KABRISTANTE, KARYL ANNE F.','karylanne.kabristante@foundationu.com','BSIT',3,'C','F'),('20211746','DINGAL, RENZ RUE G.','renzrue.dingal@foundationu.com','BSIT',3,'C','M'),('20220744','CARCUEVA, JORELL L.','jorell.carcueva@foundationu.com','BSIT',3,'C','M'),('20220877','MARIBAO, JOHN ZION A.','johnzion.maribao@foundationu.com','BSIT',3,'C','M'),('20221016','RENACIA, JOSEPH ANTHONY N.','josephanthony.renacia@foundationu.com','BSIT',3,'C','M'),('20230027','FABURADA, NEIL ALLEN S.','neilallen.faburada@foundationu.com','BSIT',3,'C','M'),('20230069','QUISEL, ALTHEA S.','althea.quisel@foundationu.com','BSIT',3,'C','F'),('20230146','LIBOSADA, JOHASH MIGUEL V.','johashmiguel.libosada@foundationu.com','BSIT',3,'C','M'),('20230506','JUMAWAN, JANZEN JAY G.','janzenjay.jumawan@foundationu.com','BSIT',3,'C','M'),('20230527','SUMANOY, JESSA MAE T.','jessamae.sumanoy@foundationu.com','BSIT',3,'C','F'),('20230574','GABOTERO, RYLE ANTHONY M.','ryleanthony.gabotero@foundationu.com','BSIT',3,'C','M'),('20231241','MANDIGAL, MARK YSTANLEY MARTIN B.','markystanleymartin.mandigal@foundationu.com','BSIT',3,'C','M'),('20241426','GIRASOL, JUNE ABBY R.','juneabby.girasol@foundationu.com','BSIT',3,'C','F'),('20241440','LOPEZ, JOSHUA D.','joshua.lopez@foundationu.com','BSIT',3,'C','M'),('20241729','MACIAS, RUZIEL JOHN V.','ruzieljohn.macias@foundationu.com','BSIT',3,'C','M');
/*!40000 ALTER TABLE `it228a` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it228b`
--

DROP TABLE IF EXISTS `it228b`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `it228b` (
  `id_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `course` varchar(10) NOT NULL,
  `level` int(11) NOT NULL,
  `e_status` varchar(5) NOT NULL,
  `sex` char(1) NOT NULL,
  PRIMARY KEY (`id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it228b`
--

LOCK TABLES `it228b` WRITE;
/*!40000 ALTER TABLE `it228b` DISABLE KEYS */;
INSERT INTO `it228b` VALUES ('20210967','MUSTERA, GERALD G.','gerald.gumahad@foundationu.com','BSIT',3,'C','M'),('20211735','TIGMO, MAISIE THERESE A.','maisietherese.tigmo@foundationu.com','BSIT',3,'C','F'),('20220713','MIRAS, ASHIE JOICE G.','ashiejoice.miras@foundationu.com','BSIT',3,'C','F'),('20220796','DULLA, JHEMUEL LLOYD E.','jhemuellloyd.dulla@foundationu.com','BSIT',3,'C','M'),('20220990','ESCA?O, RAY ERVIN K.','rayervin.escaNo@foundationu.com','BSIT',3,'C','M'),('20221070','BANGALANDO, TOM T.','tom.bangalando@foundationu.com','BSIT',3,'C','M'),('20230015','GERALDINO, KENETH C.','keneth.geraldino@foundationu.com','BSIT',3,'C','M'),('20230042','LANCISO, JAN MICHAEL B.','janmichael.lanciso@foundationu.com','BSIT',3,'C','M'),('20230078','DECIPOLO, PAUL T.','paul.decipolo@foundationu.com','BSIT',3,'C','M'),('20230107','PATULA, CARL MCTHETS .','carlmcthets.patula@foundationu.com','BSIT',3,'C','M'),('20230137','NARVAS, JERIAH MARGARETTE R.','jeriahmargarette.narvas@foundationu.com','BSIT',3,'C','F'),('20230182','FERNANDEZ, MARK JOSEPH M.','markjoseph.fernandez@foundationu.com','BSIT',3,'C','M'),('20230250','RICAFORT, CARL WILSON U.','carlwilson.ricafort@foundationu.com','BSIT',3,'C','M'),('20230251','TERANTE, KENCHIE T.','kenchie.terante@foundationu.com','BSIT',3,'C','M'),('20230407','BANOSONG, SHERINA .','sherina.banosong@foundationu.com','BSIT',3,'C','F'),('20230466','ILAGAN, DAVE I.','dave.ilagan@foundationu.com','BSIT',3,'C','M'),('20230477','BENABENTE, JASON .','jason.benabente@foundationu.com','BSIT',3,'C','M'),('20230511','ESTANDARTE, EARL JOHN D.','earljohn.estandarte@foundationu.com','BSIT',3,'C','M'),('20230604','ALCAIDE, AJ J.','aj.alcaide@foundationu.com','BSIT',3,'C','M'),('20230744','CAFINO, OLSEN NATHANIEL P.','olsennathaniel.cafino@foundationu.com','BSIT',3,'C','M'),('20230877','UDTOHAN, RAFAEL B.','rafael.udtohan@foundationu.com','BSIT',3,'C','M'),('20230910','POLLENTE, GABRIELLE V.','gabrielle.pollente@foundationu.com','BSIT',3,'C','M'),('20241341','LIM, JUSTINE C.','justine.lim@foundationu.com','BSIT',3,'C','F'),('20241690','ERAHAM, TIMOTHY A.','timothy.eraham@foundationu.com','BSIT',3,'C','M');
/*!40000 ALTER TABLE `it228b` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'it228'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-31 23:08:04
