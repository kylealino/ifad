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
-- Table structure for table `tbl_ppmp_dt`
--

DROP TABLE IF EXISTS `tbl_ppmp_dt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_ppmp_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ppmp_id` int(11) NOT NULL,
  `ppmpno` varchar(100) NOT NULL,
  `item_desc` varchar(250) NOT NULL,
  `item_type` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `mop` varchar(50) NOT NULL,
  `is_preproc` varchar(10) NOT NULL,
  `proc_start` varchar(50) NOT NULL,
  `proc_end` varchar(50) NOT NULL,
  `expected_delivery_from` varchar(50) NOT NULL,
  `expected_delivery_to` varchar(50) NOT NULL,
  `funding_source` varchar(50) NOT NULL,
  `estimated_budget` decimal(15,2) NOT NULL,
  `attached_document` varchar(100) NOT NULL,
  `remarks` varchar(150) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_ppmp_dt`
--

LOCK TABLES `tbl_ppmp_dt` WRITE;
/*!40000 ALTER TABLE `tbl_ppmp_dt` DISABLE KEYS */;
INSERT INTO `tbl_ppmp_dt` VALUES (100,4,'PPMP-01','WASTEBASKET','Goods',1,'pc',1.00,'Direct Acquisition','Yes','2026-03','2026-03','2026-03','2026-03','Trust Fund',1.00,'test','TEST','ADMIN-KYLE','2026-04-07 07:30:16'),(101,5,'PPMP-02','ANALYSIS','Goods',1,'LOT',500000.00,'SVP','No','2026-04','2026-04','2026-05','2026-05','General Fund',500000.00,'N/A','N/A','ADMIN-KYLE','2026-04-07 07:30:23'),(102,3,'PPMP-03','TWINE, plastic','Goods',1,'pack',50.00,'Direct Acquisition','Yes','2025-12','2025-12','2025-12','2025-12','General Fund',50.00,'test','test','ADMIN-KYLE','2026-04-07 07:30:34'),(103,2,'PPMP-04','TWINE, plastic','Goods',1,'pack',50.00,'Direct Acquisition','Yes','2025-12','2025-12','2025-12','2025-12','General Fund',50.00,'test','test','ADMIN-KYLE','2026-04-07 07:30:44'),(104,2,'PPMP-04','SIGN PEN, Extra fine tip, Blue','Goods',2,'pack',22.00,'Direct Acquisition','No','2025-06','2025-03','2025-02','2025-04','General Fund',22.00,'test','test','ADMIN-KYLE','2026-04-07 07:30:44'),(105,2,'PPMP-04','RAGS','Infrastructure',2,'pack',333.00,'Direct Acquisition','Yes','2025-02','2025-10','2025-03','2025-04','General Fund',33.00,'test','test','ADMIN-KYLE','2026-04-07 07:30:44'),(131,6,'31-2026-1','Refrigerator Bottle Thermometer -5 to 15C, 30mL ','Goods',1,'btl',5000.00,'SVP','No','2026-06','2026-07','2026-07','2026-08','General Fund',10000.00,'','','PPT-USER','2026-06-02 14:22:55'),(132,6,'31-2026-1','Freezer Bottle Thermometer -30/-5C, 30mL','Goods',4,'btl',5000.00,'SVP','No','2026-06','2026-08','2026-08','2026-08','General Fund',20000.00,'','','PPT-USER','2026-06-02 14:22:55'),(155,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'pc',1000.00,'Direct Acquisition','No','2025-12','2025-12','2025-12','2025-12','General Fund',11111.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(156,1,'PPMP-05','asd','Goods',1,'btl',1000.00,'Direct Acquisition','No','2025-01','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(157,1,'PPMP-05','asd','Goods',1,'btl',1000.00,'Direct Contracting','No','2025-01','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(158,1,'PPMP-05','WASTEBASKET','Goods',1,'pack',1000.00,'Public Bidding','No','2025-01','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(159,1,'PPMP-05','WRAPPING PAPER, kraft','Goods',1,'btl',1000.00,'SVP','No','2025-01','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(160,1,'PPMP-05','TWINE, plastic','Goods',1,'box',1000.00,'Direct Contracting','No','2025-01','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(161,1,'PPMP-05','TONER CARTRIDGE, HP Q2612A, Black','Goods',1,'cyl',1000.00,'Direct Acquisition','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(162,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'crtg',1000.00,'Direct Contracting','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(163,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'box',1000.00,'Direct Contracting','No','2025-01','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(164,1,'PPMP-05','TONER CARTRIDGE, HP CF412A (HP410A), Yellow','Goods',1,'box',1000.00,'Direct Contracting','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(165,1,'PPMP-05','TONER CARTRIDGE, HP Q2612A, Black','Goods',1,'btl',1000.00,'Direct Contracting','No','2025-06','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(166,1,'PPMP-05','TONER CARTRIDGE, HP CF413A (HP410A), Magenta','Goods',1,'pack',1000.00,'SVP','No','2025-10','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(167,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'btl',1000.00,'SVP','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(168,1,'PPMP-05','TONER CARTRIDGE, HP CF412A (HP410A), Yellow','Goods',1,'pack',1000.00,'SVP','No','2025-06','2025-12','2025-01','2025-12','Project Fund (Externally Funded)',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(169,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'box',1000.00,'Direct Acquisition','No','2025-06','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(170,1,'PPMP-05','TONER CARTRIDGE, HP Q2612A, Black','Goods',1,'cyl',1000.00,'Direct Acquisition','No','2025-02','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(171,1,'PPMP-05','TONER CARTRIDGE, HP CF411A (HP410A), Cyan','Goods',1,'pack',1000.00,'Direct Contracting','No','2025-03','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(172,1,'PPMP-05','TONER CARTRIDGE, HP CF413A (HP410A), Magenta','Goods',1,'pack',1000.00,'Public Bidding','No','2025-07','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(173,1,'PPMP-05','TRASHBAG, XXL size','Goods',1,'box',1000.00,'Public Bidding','No','2025-02','2025-12','2025-01','2025-12','Trust Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(174,1,'PPMP-05','TONER CARTRIDGE, HP CF413A (HP410A), Magenta','Goods',1,'cyl',1000.00,'Direct Contracting','No','2025-06','2025-12','2025-01','2025-12','Project Fund (Externally Funded)',1000.00,'test','test','PPT-USER','2026-07-07 14:09:35'),(175,1,'PPMP-05','TONER CARTRIDGE, HP CF413A (HP410A), Magenta','Goods',1,'box',1000.00,'SVP','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:36'),(176,1,'PPMP-05','TWINE, plastic','Goods',1,'box',1000.00,'Public Bidding','No','2025-06','2025-12','2025-01','2025-12','General Fund',1000.00,'test','test','PPT-USER','2026-07-07 14:09:36'),(177,1,'PPMP-05','','',0,'',0.00,'','','','','','','',0.00,'','','PPT-USER','2026-07-07 14:09:36'),(182,7,'TESTORIGPPMP','ITEM 1','Goods',50,'pc',100.00,'SVP','Yes','2026-11','2026-11','2026-11','2026-11','Trust Fund',5000.00,'TEST','TEST','ADMIN-KYLE','2026-11-19 18:07:34'),(183,7,'TESTORIGPPMP','ITEM 2','Goods',25,'pc',250.00,'SVP','Yes','2026-11','2026-11','2026-11','2026-11','General Fund',6250.00,'TEST','TEST','ADMIN-KYLE','2026-11-19 18:07:34');
/*!40000 ALTER TABLE `tbl_ppmp_dt` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 17:00:42
