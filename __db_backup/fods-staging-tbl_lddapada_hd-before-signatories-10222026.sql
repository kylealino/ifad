-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: fods-staging
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
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_lddapada_hd`
--

LOCK TABLES `tbl_lddapada_hd` WRITE;
/*!40000 ALTER TABLE `tbl_lddapada_hd` DISABLE KEYS */;
INSERT INTO `tbl_lddapada_hd` VALUES (8,'02-101101-08-817-2026','2026080817','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-07-27','01','101101',0,'JOVY S. MEDINA','CDS-LET','2026-09-08 08:39:14',1,0,0,'',''),(9,'02-101101-09-1057-2026','2026091057','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-22 10:15:42',1,0,0,'',''),(10,'02-101101-09-1058-2026','2026091058','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-22 17:01:38',1,0,0,'',''),(11,'02-101101-09-1060-2026','2026091060','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-22 17:17:57',1,0,0,'',''),(12,'02-101101-09-1059-2026','2026091059','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-22 17:25:13',1,0,0,'',''),(13,'02-101101-09-1061-2026','2026091061','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-22 17:51:26',1,0,0,'',''),(14,'02-101101-09-1062-2026','2026091062','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-09-22 17:58:08',1,0,0,'',''),(15,'02-101101-09-1063-2026','9926091063','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'','ADMIN-KYLE','2026-09-22 18:16:31',1,0,0,'',''),(16,'02-101101-09-1064-2026','2026091064','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-22','01','101101',0,'','ADMIN-KYLE','2026-09-22 18:22:09',1,0,0,'',''),(17,'01-101101-09-1095-2026','9926091095','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 11:05:38',1,0,0,'',''),(18,'01-101101-09-1096-2026','9926091096','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 12:03:48',1,0,0,'',''),(19,'01-101101-09-1099-2026','9926091099','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 13:13:40',1,0,0,'',''),(20,'02-101101-09-1097-2026','9926091097','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 14:29:05',1,0,0,'',''),(21,'02-101101-09-2000-2026','2026092000','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 15:29:09',1,0,0,'',''),(22,'02-101101-09-2002-2026','2026092002','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 16:33:15',1,0,0,'',''),(23,'02-101101-09-2003-2026','2026092003','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 16:43:37',1,0,0,'',''),(24,'02-101101-09-2004-2026','2026092004','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 17:04:18',1,0,0,'',''),(25,'02-101101-09-2006-2026','2026092006','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 17:17:16',1,0,0,'',''),(26,'02-101101-09-2007-2026','2026092007','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 17:24:53',1,0,0,'',''),(27,'02-101101-09-2008-2026','2026092008','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 17:49:22',1,0,0,'',''),(29,'02-101101-09-2009-2026','9926092009','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:09:05',1,0,0,'',''),(30,'02-101101-09-2010-2026','9926092010','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'','AS-KYLA','2026-09-29 18:12:37',1,0,0,'',''),(31,'02-101101-09-2011-2026','2026092011','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:17:37',1,0,0,'',''),(32,'02-101101-09-2012-2026','2026092012','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:19:03',1,0,0,'',''),(33,'02-101101-09-2013-2026','2026092013','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:20:59',1,0,0,'',''),(34,'02-101101-09-2014-2026','2026092014','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:21:36',1,0,0,'',''),(35,'02-101101-09-2015-2026','2026092015','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:22:21',1,0,0,'',''),(36,'01-101101-09-2016-2026','9926092016','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:31:00',1,0,0,'',''),(37,'01-101101-09-2017-2026','9926092017','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:31:44',1,0,0,'',''),(38,'02-101101-09-2018-2026','9926092018','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:32:38',1,0,0,'',''),(39,'02-101101-09-2019-2026','9926092019','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:33:58',1,0,0,'',''),(40,'02-101101-09-2020-2026','9926092020','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:34:42',1,0,0,'',''),(41,'01-101101-09-2021-2026','9926092021','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:35:54',1,0,0,'',''),(42,'01-101101-09-2022-2026','9926092022','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-09-29 18:36:35',1,0,0,'',''),(43,'06-101101-09-2023-2026','9926092023','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-09-29','01','101101',1,'JESTER C. VIRIÑA','AS-KYLA','2026-09-29 18:37:45',1,0,0,'',''),(44,'02-101101-10-2027-2026','2026102027','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 09:14:39',1,0,0,'',''),(45,'02-101101-10-2028-2026','2026102028','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 11:18:38',1,0,0,'',''),(46,'02-101101-10-2030-2026','2026102030','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 11:39:32',1,0,0,'',''),(47,'02-101101-10-2031-2026','2026102031','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 13:14:47',1,0,0,'',''),(48,'02-101101-10-2032-2026','2026102032','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 13:18:17',1,0,0,'',''),(49,'02-101101-10-2033-2026','2026102033','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 13:25:18',1,0,0,'',''),(50,'02-101101-10-2034-2026','2026102034','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',0,'JOVY S. MEDINA','AS-KYLA','2026-10-06 13:33:55',1,0,0,'',''),(51,'02-101101-10-2035-2026','9926102035','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-06','01','101101',1,'JOVY S. MEDINA','AS-KYLA','2026-10-06 14:16:24',1,0,0,'',''),(52,'TEST12312312312312','ASDASDASDASDAS','Land Bank of the Philippines - DOST Branch','2182-9001-36','2026-10-07','01','101101',0,'JOVY S. MEDINA','ADMIN-KYLE','2026-10-07 09:50:15',1,0,0,'','');
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
