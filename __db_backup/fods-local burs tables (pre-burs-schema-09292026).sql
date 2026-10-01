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
-- Table structure for table `tbl_burs_hd`
--

DROP TABLE IF EXISTS `tbl_burs_hd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(50) NOT NULL,
  `particulars` varchar(250) NOT NULL,
  `funding_source` varchar(100) NOT NULL,
  `payee_name` varchar(100) NOT NULL,
  `payee_office` varchar(100) NOT NULL,
  `payee_address` varchar(250) NOT NULL,
  `certified_a` varchar(100) NOT NULL,
  `position_a` varchar(100) NOT NULL,
  `certified_b` varchar(100) NOT NULL,
  `position_b` varchar(100) NOT NULL,
  `is_pending` int(11) NOT NULL DEFAULT 1,
  `is_approved_certa` int(11) NOT NULL DEFAULT 0,
  `is_disapproved_certa` int(11) NOT NULL DEFAULT 0,
  `is_approved_certb` int(11) NOT NULL DEFAULT 0,
  `is_disapproved_certb` int(11) NOT NULL DEFAULT 0,
  `certa_remarks` varchar(100) NOT NULL,
  `certb_remarks` varchar(100) NOT NULL,
  `certa_approver` varchar(50) NOT NULL,
  `certb_approver` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  `burs_date` date DEFAULT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_hd`
--

LOCK TABLES `tbl_burs_hd` WRITE;
/*!40000 ALTER TABLE `tbl_burs_hd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_hd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_direct_ps_dt`
--

DROP TABLE IF EXISTS `tbl_burs_direct_ps_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_direct_ps_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_direct_ps_dt`
--

LOCK TABLES `tbl_burs_direct_ps_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_direct_ps_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_direct_ps_dt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_indirect_ps_dt`
--

DROP TABLE IF EXISTS `tbl_burs_indirect_ps_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_indirect_ps_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_indirect_ps_dt`
--

LOCK TABLES `tbl_burs_indirect_ps_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_indirect_ps_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_indirect_ps_dt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_direct_mooe_dt`
--

DROP TABLE IF EXISTS `tbl_burs_direct_mooe_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_direct_mooe_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_direct_mooe_dt`
--

LOCK TABLES `tbl_burs_direct_mooe_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_direct_mooe_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_direct_mooe_dt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_indirect_mooe_dt`
--

DROP TABLE IF EXISTS `tbl_burs_indirect_mooe_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_indirect_mooe_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_indirect_mooe_dt`
--

LOCK TABLES `tbl_burs_indirect_mooe_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_indirect_mooe_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_indirect_mooe_dt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_direct_co_dt`
--

DROP TABLE IF EXISTS `tbl_burs_direct_co_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_direct_co_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_direct_co_dt`
--

LOCK TABLES `tbl_burs_direct_co_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_direct_co_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_direct_co_dt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_burs_indirect_co_dt`
--

DROP TABLE IF EXISTS `tbl_burs_indirect_co_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_burs_indirect_co_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `project_title` varchar(250) NOT NULL,
  `responsibility_code` varchar(100) NOT NULL,
  `mfopaps_code` varchar(100) NOT NULL,
  `sub_object_code` varchar(200) NOT NULL,
  `uacs_code` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_by` varchar(50) NOT NULL,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_burs_indirect_co_dt`
--

LOCK TABLES `tbl_burs_indirect_co_dt` WRITE;
/*!40000 ALTER TABLE `tbl_burs_indirect_co_dt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_burs_indirect_co_dt` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-29 16:41:05
