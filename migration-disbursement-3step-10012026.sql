-- =====================================================================
-- Disbursement Approval becomes three steps: Box A (supervisor), then
-- Box C (accounting), then Box D (approved for payment).
--
-- Each step is decided by the signatory named on that box of the DV
-- (Box D follows the net amount, as printed on the DV); an Administrator
-- can decide any step. No new access codes: 40001 still opens the page
-- and 40005 is still needed to decide.
--
--   1. Box C and Box D approval columns on tbl_disbursement_hd
--      (Box A keeps the certa columns).
--   2. DVs approved under the old single accounting step stay approved:
--      their Box C and Box D are filled from that approval. A DV whose
--      Box A was approved in the new flow has an APPROVED_BOX_A history
--      row and is left alone.
--
-- Runs on both MariaDB (local) and MySQL 8 (staging): the columns are
-- guarded with information_schema checks instead of the MariaDB-only
-- ADD COLUMN IF NOT EXISTS. Safe to re-run. Deploy together with the code.
-- Backup: __db_backup/fods-local disbursement tables (pre-disbursement-3step-10012026).sql
-- =====================================================================

-- Staging's default collation is utf8mb4_0900_ai_ci while every FODS table
-- is utf8mb4_general_ci; pin the connection so literals compare cleanly.
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

-- The UPDATE below filters on non-key columns; safe-update mode (on by
-- default in MySQL Workbench) would block it.
SET SQL_SAFE_UPDATES = 0;

-- 1. Box C / Box D approval columns
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'is_approved_certc') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `is_approved_certc` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'is_disapproved_certc') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `is_disapproved_certc` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'is_approved_certd') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `is_approved_certd` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'is_disapproved_certd') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `is_disapproved_certd` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'certc_remarks') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `certc_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'certd_remarks') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `certd_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'certc_approver') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `certc_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'certd_approver') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `certd_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. DVs already approved by accounting stay fully approved
UPDATE tbl_disbursement_hd d
SET
  d.`is_approved_certc` = 1,
  d.`certc_approver` = d.`certa_approver`,
  d.`certc_remarks` = d.`certa_remarks`,
  d.`is_approved_certd` = 1,
  d.`certd_approver` = d.`certa_approver`
WHERE d.`is_approved_certa` = 1
  AND d.`is_disapproved_certa` = 0
  AND d.`is_approved_certc` = 0
  AND d.`is_disapproved_certc` = 0
  AND NOT EXISTS (
    SELECT 1 FROM tbl_disbursement_history h
    WHERE h.`transaction_id` = d.`recid` AND h.`action_type` = 'APPROVED_BOX_A'
  );
