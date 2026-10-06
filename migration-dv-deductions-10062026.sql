-- =====================================================================
-- Disbursement: several "other deductions" per DV.
--
-- tbl_disbursement_deduction holds each deduction (type + amount) of a DV,
-- e.g. "SI#16379 - 16 Calendar Days Delayed  680.13" and
--      "SI#16633 - 101 Calendar Days Delayed 4,293.31".
-- tbl_disbursement_hd keeps other_deductions as their SUM and
-- deduction_name as the types joined with "; ", so every total, net,
-- approval, LDDAP-ADA / RCI line and report reads the same values as before.
-- DVs saved before this keep their single deduction on the header; the
-- editor and the printout show it as one row until the DV is saved again.
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_disbursement_deduction` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `disbursement_id` int(11) NOT NULL,
  `dvno` varchar(100) NOT NULL DEFAULT '',
  `deduction_name` varchar(250) NOT NULL DEFAULT '',
  `amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_disbursement_deduction_dv` (`disbursement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
