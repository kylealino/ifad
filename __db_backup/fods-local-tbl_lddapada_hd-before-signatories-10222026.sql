-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: fods-local
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
-- Table structure for table `tbl_lddapada_hd`
--

DROP TABLE IF EXISTS `tbl_lddapada_hd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_lddapada_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `lddapadano` varchar(100) NOT NULL,
  `ckno` varchar(50) NOT NULL,
  `mds_branch` varchar(100) NOT NULL,
  `mds_accountno` varchar(50) NOT NULL,
  `lddapada_date` date NOT NULL,
  `fund_cluster_code` varchar(10) NOT NULL,
  `funding_source` varchar(50) NOT NULL,
  `is_ci` int(11) DEFAULT 0,
  `ada_approver` varchar(50) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_pending` int(11) NOT NULL DEFAULT 1,
  `is_approved` int(11) NOT NULL DEFAULT 0,
  `is_disapproved` int(11) NOT NULL DEFAULT 0,
  `approver` varchar(50) NOT NULL DEFAULT '',
  `remarks` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_lddapada_hd`
--

LOCK TABLES `tbl_lddapada_hd` WRITE;
/*!40000 ALTER TABLE `tbl_lddapada_hd` DISABLE KEYS */;
INSERT INTO `tbl_lddapada_hd` VALUES (16,'01-104102-05-001-2026','9926050001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-05-04','01','104102',1,'','ADMIN-KYLE','2026-05-04 10:13:08',1,0,0,'',''),(17,'01-104102-05-002-2026','2026050001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-05-04','01','104102',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-05-04 10:13:27',1,0,0,'',''),(18,'07-101101-05-003-2026','992605002','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-05-04','07','101101',1,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-05-04 11:30:20',1,0,0,'',''),(19,'01-101101-05-001-2026','9926050001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-05-26','01','101101',1,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-05-26 13:55:35',1,0,0,'',''),(20,'01-101101-06-001-2026','2026060001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-06-22','01','101101',0,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-06-22 12:34:06',1,0,0,'',''),(21,'01-101101-06-002-2026','202606002','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-06-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-06-22 14:34:24',1,0,0,'',''),(22,'01-101101-07-001-2026','992607004','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-07-23','01','101101',1,'JOVY S. MEDINA','ADMIN-KYLE','2026-07-23 08:36:16',1,0,0,'',''),(23,'01-101101-07-002-2026','202607003','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-07-23','01','101101',0,'','ADMIN-KYLE','2026-07-23 13:08:05',1,0,0,'',''),(24,'01-101101-07-003-2026','992607006','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-07-23','01','101101',1,'','ADMIN-KYLE','2026-07-23 13:14:11',1,0,0,'',''),(25,'01-101101-08-001-2026','2026080001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-08-24','01','101101',0,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-08-24 14:44:41',1,0,0,'',''),(26,'01-104102-08-001-2026','2026080001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-08-25','01','104102',0,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-08-25 11:29:11',1,0,0,'',''),(27,'01-101101-08-002-2026','9926080001','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-08-25','01','101101',1,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-08-25 11:30:44',1,0,0,'',''),(28,'1231231231','202609002','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-08','01','101101',0,'','ADMIN-KYLE','2026-09-08 08:40:20',1,0,0,'',''),(29,'teasdasd','rarara','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-08','01','101101',0,'','ADMIN-KYLE','2026-09-08 10:00:47',1,0,0,'',''),(30,'test','2026091057','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-09-22 10:44:39',1,0,0,'',''),(31,'LDDAPADATEST1111','CHK1111','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-30','01','101101',1,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-30 13:28:09',0,1,0,'ADMIN-KYLE',''),(32,'LDDAPADATEST10062026V1','CHK10062026','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JESTER C. VIRIÑA','ADMIN-KYLE','2026-10-06 09:21:59',1,0,0,'','');
/*!40000 ALTER TABLE `tbl_lddapada_hd` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 11:22:10
