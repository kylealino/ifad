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
-- Table structure for table `tbl_disbursement_hd`
--

DROP TABLE IF EXISTS `tbl_disbursement_hd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_disbursement_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(50) NOT NULL,
  `particulars` varchar(250) NOT NULL,
  `funding_source` varchar(100) NOT NULL,
  `payee_name` varchar(100) NOT NULL,
  `payee_office` varchar(100) NOT NULL,
  `payee_address` varchar(250) NOT NULL,
  `certified_a` varchar(100) NOT NULL,
  `position_a` varchar(100) NOT NULL,
  `certified_c` varchar(100) NOT NULL,
  `position_c` varchar(100) NOT NULL,
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
  `disbursement_date` date DEFAULT NULL,
  `dvno` varchar(100) NOT NULL,
  `fund_cluster_code` varchar(50) NOT NULL,
  `is_vatable` int(11) NOT NULL DEFAULT 0,
  `vat_percent` int(11) NOT NULL,
  `ewt_percent` int(11) NOT NULL,
  `pt_percent` int(11) NOT NULL,
  `other_deductions` decimal(15,2) NOT NULL,
  `deduction_name` varchar(100) NOT NULL,
  `sss` decimal(15,2) NOT NULL,
  `hdmf` decimal(15,2) NOT NULL,
  `pagibig_mp2` decimal(15,2) NOT NULL,
  `pagibig_loan` decimal(15,2) NOT NULL,
  `hmo` decimal(15,2) NOT NULL,
  `wtax` decimal(15,2) NOT NULL,
  `income_tax` decimal(15,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `gross_amount` decimal(25,2) NOT NULL,
  `total_deduction` decimal(25,2) NOT NULL,
  `net_amount` decimal(25,2) NOT NULL,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `idx1` (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_disbursement_hd`
--

LOCK TABLES `tbl_disbursement_hd` WRITE;
/*!40000 ALTER TABLE `tbl_disbursement_hd` DISABLE KEYS */;
INSERT INTO `tbl_disbursement_hd` VALUES (3,'02-101101-2026-06-0670','TO OBLIGATE PAYMENT FOR COS JULY 1 TO DECEMBER 31, 2026.','101101','FNRI PAYROLL','','','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','','',1,0,0,0,0,'','','','','2026-07-09 16:43:00','ADMIN-KYLE','2026-06-30','DV012932','01',1,5,2,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Services',261600.00,16350.00,245250.00),(4,'02-101101-2026-06-0669','TO OBLIGATE PAYMENT FOR COS JULY 1 TO DECEMBER 31, 2026.	','101101','FNRI PAYROLL','','','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','','',1,0,0,0,0,'','','','','2026-07-13 16:28:38','ADMIN-KYLE','2026-06-30','DV07132026','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',260100.00,10404.00,249696.00),(5,'02-101101-2026-06-0661','TO OBLIGATE PAYMENT FOR PO# 325-2026.	','101101','RCJ TRADING AND GENERAL MERCHANDISE','','381 Brgy. Santor, Tanuan City , Batangas','LEAH C. DAJAY','Supervising SRS','','',1,0,0,0,0,'','','','','2026-07-13 16:29:27','ADMIN-KYLE','2026-06-30','DV071320261','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',2730080.00,109203.20,2620876.80),(6,'02-101101-2026-06-0651','TO OBLIGATE PAYMENT FOR PO# 390-2026.','101101','LIFELINE DIAGNOSTICS SUPPLIES, INC.','','#1225 Quezon Avenue, Brgy. Sta. Cruz, Quezon City','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','','',1,0,0,0,0,'','','','','2026-07-14 13:27:00','ADMIN-KYLE','2026-06-30','DEC1820','07',0,0,2,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Services',65902.00,3295.10,62606.90),(7,'TESTORS07142026','TO OBLIGATE PAYMENT TESTORS','101101','168 PARAGON INTERNATIONAL GENERAL CONTRACTOR AND EQUIPMENT','','39 Del Monte Ave., La Loma, QC','MA. LILIBETH P. DASCO','Supervising SRS','','',1,0,0,0,0,'','','','','2026-07-14 13:52:04','ADMIN-KYLE','2026-07-14','TESTDV001','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',55000.00,2200.00,52800.00),(9,'SERIALTEST1','TO OBLIGATE PAYMENT ','101101','347 SCHOOL OFFICE SUPPLIES INC.','','','JESTER C. VIRIÑA','SUPERVISING AO, FAD/SAU','','',1,0,0,0,0,'','','','','2026-07-23 13:04:22','ADMIN-KYLE','2026-07-23','TESTDV0723V1','01',0,0,2,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Services',30000.00,1500.00,28500.00),(10,'TESTORSV1','TO OBLIGATE PAYMENT TESTORSV1','101101','ABADAY, JOHN PAUL M.','','','DIVORAH V. AGUILA','Planning Officer IV','','',1,0,0,0,0,'','','','','2026-07-23 13:13:18','ADMIN-KYLE','2026-07-23','TESTDVVV1','01',1,5,1,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',40000.00,2142.86,37857.14),(11,'01-104102-2026-06-0644','TO OBLIGATE PAYMENT FOR RLIP-GS FOR THE MONTH OF JUNE 2026 AND ADDITIONAL MONTHS.','104102','GOVERNMENT SERVICE INSURANCE SYSTEM','Pasay City, Metro Manila','Philippines','ALEXIS M. ORTIZ','CHIEF ADMINISTRATIVE OFFICER','','',1,0,0,0,0,'','','','','2026-08-25 11:28:43','ADMIN-KYLE','2026-06-30','DVTEST08252026V1','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',1002174.79,40086.99,962087.80),(12,'02-101101-2025-09-1233','TO OBLIGATE PAYMENT OF REPLENISHMENT EXPENSES','101101','PARANI, MA. STEPHANIE N.','','','MILDRED O. GUIRINDOLA, Ph. D.','Chief SRS','','',1,0,0,0,0,'','','','','2026-08-25 11:30:21','ADMIN-KYLE','2025-09-30','DVTEST08252026V2','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',218000.00,8720.00,209280.00),(13,'02-101101-2026-06-0656','TO OBLIGATE PAYMENT FOR PO# 398-2026. RARARARARARARARARARARARARARARARARARARARARARARARARARA','101101','AE SAMONTE MERCHANDISE','','G/F 1128 Escoda St., Paco Manila','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','JAMES C. LUCENA','Accountant II',1,0,0,0,0,'','','','','2026-08-25 15:30:32','ADMIN-KYLE','2026-06-30','dvtestrarararara','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',3650.00,146.00,3504.00),(14,'02-101101-2026-06-0657','TO OBLIGATE PAYMENT FOR PO# 399-2026.','101101','MCSA MARKETING ','','Rm. 206 Kim Siu Ching Foundation Bldg., #473 Sto. Cristo St., Binondo, Manila','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',1,0,0,0,0,'','','','','2026-09-02 10:53:04','ADMIN-KYLE','2026-06-30','asdasdasdasda','01',0,0,0,0,0.00,'',2.00,2.00,2.00,2.00,2.00,2.00,0.00,'Payroll',4525.00,12.00,4513.00),(15,'02-101101-2026-06-0664','TO OBLIGATE PAYMENT FOR PO# 324-2026.','101101','BNT GENERAL MERCHANDISE','','CM DELOS REYES AVENUE, BRGY. DAGATAN AMADEO CAVITE','MILDRED O. GUIRINDOLA, Ph. D.','Chief SRS','JAMES C. LUCENA','Accountant II',1,0,0,0,0,'','','','','2026-09-03 17:41:56','ADMIN-KYLE','2026-06-30','123123123123','01',0,0,1,3,6873.92,'SI#6171-391 Calendar Days Delayed',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',423827.00,23827.00,400000.00),(16,'02-101101-2026-06-0647','TO OBLIGATE PAYMENT FOR REPLENISHMENT.','101101','RYAN CHRISTOPHER B. BAQUIT (PCFC)','DOST-FNRI','Bicutan, Taguig City','MILDRED O. GUIRINDOLA, Ph. D.','Chief SRS','JAMES C. LUCENA','Accountant II',1,0,0,0,0,'','','','','2026-09-03 18:05:50','ADMIN-KYLE','2026-06-30','','01',0,0,0,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,913.00,'Income Tax',26913.00,913.00,26000.00),(17,'02-101101-2026-06-0647','TO OBLIGATE PAYMENT FOR REPLENISHMENT.','101101','RYAN CHRISTOPHER B. BAQUIT (PCFC)','DOST-FNRI','Bicutan, Taguig City','MILDRED O. GUIRINDOLA, Ph. D.','Chief SRS','JAMES C. LUCENA','Accountant II',0,0,0,0,0,'','','AS-BONJ','','2026-09-07 13:45:58','ADMIN-KYLE','2026-06-30','dvtest1111','',0,0,1,3,836.48,'test',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',26913.00,1913.00,25000.00),(18,'02-101101-2026-06-0603','TO OBLIGATE PAYMENT FOR REIMBURSEMENT.','101101','MARICAR D. ALBAO','','','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',0,0,0,0,0,'','','AS-BONJ','','2026-09-07 13:46:40','ADMIN-KYLE','2026-06-24','dvtest22222','',0,0,0,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,5000.00,'Income Tax',15000.00,5000.00,10000.00),(19,'02-101101-2026-06-0560','TO OBLIGATE PAYMENT FOR PO# 343-2026.','101101','SHIMADZU PHILIPPINES CORPORATION','','19/F Marajo Tower, 26th St., cor. 4th Ave., Bonifacio Global Taguig, Taguig City','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','JAMES C. LUCENA','Accountant II',0,0,0,0,0,'','','AS-BONJ','','2026-09-07 13:47:09','ADMIN-KYLE','2026-06-18','dvtest3333','',0,0,0,0,0.00,'',100.00,100.00,100.00,100.00,100.00,100.00,0.00,'Payroll',60600.00,600.00,60000.00),(20,'02-101101-2026-06-0566','TO OBLIGATE PAYMENT FOR PO# 364-2026','101101','SHIMADZU PHILIPPINES CORPORATION','','19/F Marajo Tower, 26th St., cor. 4th Ave., Bonifacio Global Taguig, Taguig City','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',0,1,0,0,0,'test aproval logs','','AS-BONJ','','2026-09-07 13:50:38','ADMIN-KYLE','2026-06-18','dvtest51223333','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',34170.00,1366.80,32803.20),(21,'TESTORSTODVRARA','TO OBLIGATE PAYMENT ','101101','MUGA, MARICEL C.','','','DIVORAH V. AGUILA','Planning Officer IV','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',1,0,0,0,0,'','','','','2026-09-09 14:27:23','ADMIN-KYLE','2026-09-09','TESTDVTOORSRARA','01',1,5,1,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',55000.00,2946.43,52053.57),(22,'SERIALTEST1','TO OBLIGATE PAYMENT ','101101','347 SCHOOL OFFICE SUPPLIES INC.','','','JESTER C. VIRIÑA','SUPERVISING AO, FAD/SAU','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',1,0,0,0,0,'','','','','2026-09-22 17:05:42','ADMIN-KYLE','2026-07-23','21031231231231231231231','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',30000.00,1200.00,28800.00),(23,'02-101101-2026-06-0665','TO OBLIGATE PAYMENT FOR COS JUNE 16 TO AUGUST 31, 2026.','101101','FNRI PAYROLL','','','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',1,0,0,0,0,'','','','','2026-09-29 15:55:20','ADMIN-KYLE','2026-06-30','testdv','01',0,0,0,0,500.00,'-',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Manual',2400.00,500.00,1900.00),(24,'02-101101-2026-06-0666','TO OBLIGATE PAYMENT FOR COS JULY 1 TO DECEMBER 31, 2026.','101101','FNRI PAYROLL','','','ROSEMARIE J. DUMAG, RCh., MSc.','Chief SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',1,0,0,0,0,'','','','','2026-09-29 15:55:41','ADMIN-KYLE','2026-06-30','testdv2','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',260100.00,10404.00,249696.00),(25,'02-101101-2026-06-0663','TO OBLIGATE PAYMENT FOR PO# 400-2026.','101101','PA TRADING CORP.','','Unit 116 Cattleya Condo., R. Cruz St., Pinagbuhatan, Pasig City','LEAH C. DAJAY','Supervising SRS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',0,1,0,0,0,'test','','ADMIN-KYLE','','2026-09-30 08:46:11','ADMIN-KYLE','2026-06-30','TESTDV09302026','01',0,0,0,0,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'',11165.00,0.00,11165.00),(26,'ORSTEST1111','TO OBLIGATE PAYMENT ORSTEST1111','101101','168 PARAGON INTERNATIONAL GENERAL CONTRACTOR AND EQUIPMENT','','39 Del Monte Ave., La Loma, QC','RICAMAE V. LARRAZABAL','SRS II, NSIS','BONJOBIE F. CAJANO, CPA, CTT','Accountant III',0,1,0,0,0,'','','ADMIN-KYLE','','2026-09-30 13:26:59','ADMIN-KYLE','2026-09-30','DVTEST1111','01',0,0,1,3,0.00,'',0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Goods',6000.00,240.00,5760.00);
/*!40000 ALTER TABLE `tbl_disbursement_hd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_disbursement_history`
--

DROP TABLE IF EXISTS `tbl_disbursement_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_disbursement_history` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL COMMENT 'Reference to tbl_disbursement_hd.recid',
  `serialno` varchar(50) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL COMMENT 'ADDED, UPDATED, APPROVED_CERTA, DISAPPROVED_CERTA, APPROVED_CERTB, DISAPPROVED_CERTB',
  `action_status` varchar(20) DEFAULT NULL COMMENT 'PENDING, APPROVED, DISAPPROVED',
  `action_by` varchar(100) DEFAULT NULL,
  `action_by_role` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `field_changes` text DEFAULT NULL COMMENT 'JSON format of changed fields',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_date_created` (`date_created`),
  KEY `idx_serialno` (`serialno`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Disbursement transaction history log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_disbursement_history`
--

LOCK TABLES `tbl_disbursement_history` WRITE;
/*!40000 ALTER TABLE `tbl_disbursement_history` DISABLE KEYS */;
INSERT INTO `tbl_disbursement_history` VALUES (1,20,'02-101101-2026-06-0566','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"particulars\",\"old\":\"TO OBLIGATE PAYMENT FOR PO# 364-2026TATATATATA\",\"new\":\"TO OBLIGATE PAYMENT FOR PO# 364-2026GAGAAGAA\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 06:22:51'),(2,20,'02-101101-2026-06-0566','APPROVED_CERTA','APPROVED','AS-BONJ','Accountant III','test aproval logs','{\"approver\":\"AS-BONJ\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 06:46:32'),(3,20,'02-101101-2026-06-0566','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"dvno\",\"old\":\"dvtest555\",\"new\":\"dvtest5556666\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 06:53:13'),(4,20,'02-101101-2026-06-0566','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"dvno\",\"old\":\"dvtest5556666\",\"new\":\"dvtest512312312\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 06:55:30'),(5,20,'02-101101-2026-06-0566','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"dvno\",\"old\":\"dvtest512312312\",\"new\":\"dvtest51223333\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-08 06:56:56'),(6,20,'02-101101-2026-06-0566','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"certified_c\",\"old\":\"JAMES C. LUCENA\",\"new\":\"BONJOBIE F. CAJANO, CPA, CTT\"},{\"field\":\"position_c\",\"old\":\"Accountant II\",\"new\":\"Accountant III\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-09 05:44:24'),(7,21,'TESTORSTODVRARA','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-09 06:27:23'),(8,21,'TESTORSTODVRARA','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"dvno\",\"old\":\"\",\"new\":\"TESTDVTOORSRARA\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-09 06:29:11'),(9,21,'TESTORSTODVRARA','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"payee_name\",\"old\":\"ABEGAIL M. ANGELES\",\"new\":\"MUGA, MARICEL C.\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-22 08:50:22'),(10,22,'SERIALTEST1','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-22 09:05:42'),(11,23,'02-101101-2026-06-0665','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 07:55:20'),(12,24,'02-101101-2026-06-0666','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 07:55:41'),(13,23,'02-101101-2026-06-0665','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 07:56:53'),(14,23,'02-101101-2026-06-0665','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 07:57:22'),(15,23,'02-101101-2026-06-0665','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"gross_amount\",\"old\":\"54000.00\",\"new\":\"2400.00\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 08:07:12'),(16,23,'02-101101-2026-06-0665','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"net_amount\",\"old\":\"2400.00\",\"new\":\"1900.00\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 08:07:34'),(17,23,'02-101101-2026-06-0665','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-29 08:07:52'),(18,25,'02-101101-2026-06-0663','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 00:46:11'),(19,25,'02-101101-2026-06-0663','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"gross_amount\",\"old\":\"9165.00\",\"new\":\"11165.00\"},{\"field\":\"net_amount\",\"old\":\"9165.00\",\"new\":\"11165.00\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 00:57:23'),(20,25,'02-101101-2026-06-0663','APPROVED','APPROVED','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','test','{\"approver\":\"ADMIN-KYLE\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 00:57:38'),(21,26,'ORSTEST1111','ADDED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','New disbursement transaction created',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 05:26:59'),(22,26,'ORSTEST1111','UPDATED','PENDING','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','Transaction details updated','[{\"field\":\"dvno\",\"old\":\"\",\"new\":\"DVTEST1111\"}]','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 05:27:14'),(23,26,'ORSTEST1111','APPROVED','APPROVED','ADMIN-KYLE','PROJECT TECHNICAL SPECIALIST I','','{\"approver\":\"ADMIN-KYLE\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 05:27:25');
/*!40000 ALTER TABLE `tbl_disbursement_history` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01  8:11:27
