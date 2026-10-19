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
-- Table structure for table `tbl_conversation_member`
--

DROP TABLE IF EXISTS `tbl_conversation_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_conversation_member` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_read_id` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_archived` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `conv_user` (`conv_id`,`username`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_conversation_member`
--

LOCK TABLES `tbl_conversation_member` WRITE;
/*!40000 ALTER TABLE `tbl_conversation_member` DISABLE KEYS */;
INSERT INTO `tbl_conversation_member` VALUES (1,1,'ADMIN-KYLE',16,'ADMIN-KYLE','2026-09-30 10:56:11',0),(2,1,'ADMIN-JOVY',16,'ADMIN-KYLE','2026-09-30 10:56:11',0),(12,6,'ADMIN-KYLE',20,'ADMIN-KYLE','2026-09-30 11:40:27',1),(13,6,'OD-LUCIEDEN',0,'ADMIN-KYLE','2026-09-30 11:40:27',0),(14,6,'FAD-ALEXIS',0,'ADMIN-KYLE','2026-09-30 11:40:27',0),(19,8,'ADMIN-KYLE',21,'ADMIN-KYLE','2026-09-30 13:54:36',0),(20,8,'ADMIN-JOVY',0,'ADMIN-KYLE','2026-09-30 13:54:36',0),(21,8,'PO-DIVORAH',0,'ADMIN-KYLE','2026-09-30 13:54:36',0),(22,8,'NAMD-EVA',0,'ADMIN-KYLE','2026-09-30 13:54:36',0),(23,8,'NAMD-EMILY',0,'ADMIN-KYLE','2026-09-30 13:54:36',0);
/*!40000 ALTER TABLE `tbl_conversation_member` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19  9:26:52
