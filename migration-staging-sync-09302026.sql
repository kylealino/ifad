-- =====================================================================
-- Staging sync: brings an old-structure FODS database (staging, MySQL 8)
-- up to the current local structure, and remaps the access codes.
--
-- Replaces running the separate migration-*-0929/0930 files one by one:
-- those use MariaDB-only syntax (ADD COLUMN IF NOT EXISTS) that fails on
-- MySQL 8. Everything here is guarded with information_schema checks, so
-- it is safe to re-run if it stops halfway.
--
-- Covers: rbac, access-codes, budget-approval-module, burs-schema,
-- disbursement-approve, lddapada-ada-approval, approval-modules,
-- messages-module, messages-archive-attachments, messages-group-members,
-- ticket-access + ticket-desk (net effect: no 37002), plus tbl_ors_hd
-- (ip_address, NOT NULL tax/amount columns), which had no migration file.
--
-- Not synced on purpose:
--   tbl_support_*          old Support chat, superseded by Messages
--   tbl_lddapada_dt.remarks staging varchar(300) > local 250; left wider
--
-- BACK UP STAGING FIRST. Run as one script (phpMyAdmin Import, or
--   mysql -u <user> -p <db> < migration-staging-sync-09302026.sql).
-- =====================================================================

-- Staging is MySQL 8: its default collation is utf8mb4_0900_ai_ci while
-- every FODS table is utf8mb4_general_ci. Pin the connection so literals
-- and temporary tables below compare cleanly with the table columns.
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

-- Several UPDATE/DELETE statements below filter on non-key columns;
-- safe-update mode (on by default in MySQL Workbench) would block them.
SET SQL_SAFE_UPDATES = 0;


-- =====================================================================
-- PART 1 - STRUCTURE
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1.1 Roles (RBAC)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `is_active` int(11) NOT NULL DEFAULT 1,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `uq_tbl_roles_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_role_access` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `access_code` varchar(50) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `uq_tbl_role_access` (`role_id`,`access_code`),
  KEY `idx_tbl_role_access_role_id` (`role_id`),
  CONSTRAINT `fk_tbl_role_access_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`recid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 1.2 myua_user: role_id + FK, drop the plaintext hash_value column
--     (logins use hash_password; hash_value is not in the local schema)
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'role_id') = 0,
  "ALTER TABLE `myua_user` ADD COLUMN `role_id` int(11) NULL DEFAULT NULL AFTER `position`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND CONSTRAINT_NAME = 'fk_myua_user_role') = 0,
  "ALTER TABLE `myua_user` ADD CONSTRAINT `fk_myua_user_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`recid`) ON DELETE SET NULL", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'hash_value') > 0,
  "ALTER TABLE `myua_user` DROP COLUMN `hash_value`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 1.3 tbl_ors_hd: ip_address, and the tax/amount columns become
--     NOT NULL with defaults (staging has them nullable)
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ors_hd' AND COLUMN_NAME = 'ip_address') = 0,
  "ALTER TABLE `tbl_ors_hd` ADD COLUMN `ip_address` varchar(100) NOT NULL DEFAULT '' AFTER `ors_date`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

UPDATE `tbl_ors_hd` SET
  `is_vatable`        = COALESCE(`is_vatable`, 0),
  `vat_percent`       = COALESCE(`vat_percent`, 0),
  `ewt_percent`       = COALESCE(`ewt_percent`, 0),
  `pt_percent`        = COALESCE(`pt_percent`, 0),
  `other_deductions`  = COALESCE(`other_deductions`, 0),
  `deduction_name`    = COALESCE(`deduction_name`, ''),
  `sss`               = COALESCE(`sss`, 0),
  `hdmf`              = COALESCE(`hdmf`, 0),
  `pagibig_mp2`       = COALESCE(`pagibig_mp2`, 0),
  `pagibig_loan`      = COALESCE(`pagibig_loan`, 0),
  `hmo`               = COALESCE(`hmo`, 0),
  `wtax`              = COALESCE(`wtax`, 0),
  `income_tax`        = COALESCE(`income_tax`, 0),
  `category`          = COALESCE(`category`, ''),
  `gross_amount`      = COALESCE(`gross_amount`, 0),
  `total_deduction`   = COALESCE(`total_deduction`, 0),
  `net_amount`        = COALESCE(`net_amount`, 0),
  `fund_cluster_code` = COALESCE(`fund_cluster_code`, ''),
  `dvno`              = COALESCE(`dvno`, '');

ALTER TABLE `tbl_ors_hd`
  MODIFY `is_vatable` int(11) NOT NULL DEFAULT 0,
  MODIFY `vat_percent` int(11) NOT NULL DEFAULT 0,
  MODIFY `ewt_percent` int(11) NOT NULL DEFAULT 0,
  MODIFY `pt_percent` int(11) NOT NULL DEFAULT 0,
  MODIFY `other_deductions` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `deduction_name` varchar(100) NOT NULL DEFAULT '',
  MODIFY `sss` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `hdmf` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `pagibig_mp2` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `pagibig_loan` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `hmo` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `wtax` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `income_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  MODIFY `category` varchar(50) NOT NULL DEFAULT '',
  MODIFY `gross_amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  MODIFY `total_deduction` decimal(25,2) NOT NULL DEFAULT 0.00,
  MODIFY `net_amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  MODIFY `fund_cluster_code` varchar(50) NOT NULL DEFAULT '',
  MODIFY `dvno` varchar(100) NOT NULL DEFAULT '';

-- ---------------------------------------------------------------------
-- 1.4 tbl_burs_hd: same header columns as tbl_ors_hd (BURS = ORS twin)
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'ip_address') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `ip_address` varchar(100) NOT NULL DEFAULT '' AFTER `burs_date`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'is_vatable') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `is_vatable` int(11) NOT NULL DEFAULT 0 AFTER `ip_address`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'vat_percent') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `vat_percent` int(11) NOT NULL DEFAULT 0 AFTER `is_vatable`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'ewt_percent') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `ewt_percent` int(11) NOT NULL DEFAULT 0 AFTER `vat_percent`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'pt_percent') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `pt_percent` int(11) NOT NULL DEFAULT 0 AFTER `ewt_percent`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'other_deductions') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `other_deductions` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pt_percent`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'deduction_name') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `deduction_name` varchar(100) NOT NULL DEFAULT '' AFTER `other_deductions`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'sss') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `sss` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `deduction_name`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'hdmf') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `hdmf` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `sss`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'pagibig_mp2') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `pagibig_mp2` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `hdmf`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'pagibig_loan') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `pagibig_loan` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pagibig_mp2`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'hmo') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `hmo` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pagibig_loan`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'wtax') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `wtax` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `hmo`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'income_tax') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `income_tax` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `wtax`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'category') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `category` varchar(50) NOT NULL DEFAULT '' AFTER `income_tax`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'gross_amount') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `gross_amount` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `category`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'total_deduction') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `total_deduction` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `gross_amount`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'net_amount') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `net_amount` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `total_deduction`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'fund_cluster_code') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `fund_cluster_code` varchar(50) NOT NULL DEFAULT '' AFTER `net_amount`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND COLUMN_NAME = 'dvno') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD COLUMN `dvno` varchar(100) NOT NULL DEFAULT '' AFTER `fund_cluster_code`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_hd' AND INDEX_NAME = 'idx1') = 0,
  "ALTER TABLE `tbl_burs_hd` ADD UNIQUE KEY `idx1` (`recid`)", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 1.5 BURS line tables: program_title (same position as the ORS tables)
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_direct_ps_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_direct_ps_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_id`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_indirect_ps_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_indirect_ps_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_title`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_direct_mooe_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_direct_mooe_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_title`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_indirect_mooe_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_indirect_mooe_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_title`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_direct_co_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_direct_co_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_title`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_burs_indirect_co_dt' AND COLUMN_NAME = 'program_title') = 0,
  "ALTER TABLE `tbl_burs_indirect_co_dt` ADD COLUMN `program_title` varchar(250) NOT NULL DEFAULT '' AFTER `project_title`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 1.6 LDDAP-ADA approval columns
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'is_pending') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `is_pending` int(11) NOT NULL DEFAULT 1 AFTER `added_at`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'is_approved') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `is_approved` int(11) NOT NULL DEFAULT 0 AFTER `is_pending`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'is_disapproved') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `is_disapproved` int(11) NOT NULL DEFAULT 0 AFTER `is_approved`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'approver') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `approver` varchar(50) NOT NULL DEFAULT '' AFTER `is_disapproved`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'remarks') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `remarks` varchar(250) NOT NULL DEFAULT '' AFTER `approver`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 1.7 ADA approval columns + added_by/added_at, and general_ci collation
--     (unicode_ci made ADA/DV number comparisons fail)
-- ---------------------------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'is_pending') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `is_pending` int(11) NOT NULL DEFAULT 1 AFTER `position_b`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'is_approved') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `is_approved` int(11) NOT NULL DEFAULT 0 AFTER `is_pending`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'is_disapproved') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `is_disapproved` int(11) NOT NULL DEFAULT 0 AFTER `is_approved`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'approver') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `approver` varchar(50) NOT NULL DEFAULT '' AFTER `is_disapproved`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'remarks') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `remarks` varchar(250) NOT NULL DEFAULT '' AFTER `approver`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'added_by') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `added_by` varchar(50) NOT NULL DEFAULT '' AFTER `remarks`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ada' AND COLUMN_NAME = 'added_at') = 0,
  "ALTER TABLE `tbl_ada` ADD COLUMN `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `added_by`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- existing ADAs had no encoded date: use their ADA date (only rows not stamped yet).
-- recid > 0 satisfies MySQL safe-update mode (Workbench/phpMyAdmin); zero
-- dates ('0000-00-00') are skipped because strict mode rejects them in a datetime.
UPDATE `tbl_ada` SET `added_at` = `ada_date`
WHERE `recid` > 0
  AND `added_by` = ''
  AND `ada_date` IS NOT NULL
  AND `ada_date` > '1000-01-01'
  AND DATE(`added_at`) <> `ada_date`;

ALTER TABLE `tbl_ada` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 1.8 Messages: chats
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_conversation_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_type` varchar(10) NOT NULL DEFAULT 'direct',
  `title` varchar(100) NOT NULL DEFAULT '',
  `last_msg_at` datetime DEFAULT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversation_member` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_read_id` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_archived` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `conv_user` (`conv_id`,`username`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversation_msg` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_system` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`recid`),
  KEY `conv_id` (`conv_id`,`recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversation_attachment` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `orig_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(100) NOT NULL DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT 0,
  `is_image` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `msg_id` (`msg_id`),
  KEY `conv_id` (`conv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- in case the chat tables already existed from an earlier partial run
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_conversation_member' AND COLUMN_NAME = 'is_archived') = 0,
  "ALTER TABLE `tbl_conversation_member` ADD COLUMN `is_archived` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_conversation_msg' AND COLUMN_NAME = 'is_system') = 0,
  "ALTER TABLE `tbl_conversation_msg` ADD COLUMN `is_system` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 1.9 Messages: support tickets
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_ticket_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(20) NOT NULL DEFAULT '',
  `category` varchar(20) NOT NULL DEFAULT 'Request',
  `priority` varchar(10) NOT NULL DEFAULT 'Normal',
  `module` varchar(100) NOT NULL DEFAULT '',
  `subject` varchar(150) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Open',
  `handled_by` varchar(50) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `added_by` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `ticket_id` (`ticket_id`,`recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_attachment` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL DEFAULT 0,
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `orig_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(50) NOT NULL DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_seen` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_read_id` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  UNIQUE KEY `ticket_user` (`ticket_id`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================================
-- PART 2 - ACCESS CODES AND ROLES (data the new code depends on)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 2.1 Starter roles, seeded from the OLD access code catalog
--     (families 1-15). Editable later in User Management > Roles.
-- ---------------------------------------------------------------------
INSERT INTO `tbl_roles` (`role_name`, `description`, `added_by`)
SELECT * FROM (SELECT
  'Administrator' AS role_name, 'Full access to every module' AS description, 'SYSTEM' AS added_by
  UNION ALL SELECT 'Budget Officer', 'Prepares and updates budget allotments', 'SYSTEM'
  UNION ALL SELECT 'Budget Approver', 'Approves/disapproves budget allotments', 'SYSTEM'
  UNION ALL SELECT 'ORS Processor', 'Prepares and updates ORS entries', 'SYSTEM'
  UNION ALL SELECT 'ORS Approver', 'Approves/disapproves ORS certifications', 'SYSTEM'
  UNION ALL SELECT 'SAOB Officer', 'Prepares SAOB reports', 'SYSTEM'
  UNION ALL SELECT 'User Administrator', 'Manages user accounts, roles and access', 'SYSTEM'
  UNION ALL SELECT 'Payee Maintenance Officer', 'Maintains the payee master list', 'SYSTEM'
  UNION ALL SELECT 'Project Maintenance Officer', 'Maintains the project master list', 'SYSTEM'
  UNION ALL SELECT 'PPMP Officer', 'Prepares the PPMP', 'SYSTEM'
  UNION ALL SELECT 'UACS Maintenance Officer', 'Maintains UACS codes', 'SYSTEM'
  UNION ALL SELECT 'Supplies Officer', 'Manages supplies/products records', 'SYSTEM'
  UNION ALL SELECT 'BURS Officer', 'Prepares and updates BURS entries', 'SYSTEM'
  UNION ALL SELECT 'Abstract Officer', 'Prepares the abstract of canvass', 'SYSTEM'
  UNION ALL SELECT 'Disbursement Officer', 'Prepares disbursement vouchers', 'SYSTEM'
  UNION ALL SELECT 'Cashier - LDDAP-ADA', 'Prepares LDDAP-ADA', 'SYSTEM'
  UNION ALL SELECT 'Cashier - RCI', 'Prepares RCI', 'SYSTEM'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `tbl_roles` r WHERE r.role_name = seed.role_name);

-- Administrator: every module in the catalog
INSERT INTO `tbl_role_access` (`role_id`, `access_code`, `added_by`)
SELECT r.recid, m.access_code, 'SYSTEM'
FROM `tbl_roles` r
CROSS JOIN `tbl_access_modules` m
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM `tbl_role_access` ra WHERE ra.role_id = r.recid AND ra.access_code = m.access_code);

-- Family-scoped starter roles (old codes: <family><3-digit action>)
INSERT INTO `tbl_role_access` (`role_id`, `access_code`, `added_by`)
SELECT r.recid, m.access_code, 'SYSTEM'
FROM `tbl_access_modules` m
JOIN (
  SELECT 'Budget Officer' role_name, '1' family, '001,002,003' suffixes
  UNION ALL SELECT 'Budget Approver', '1', '004,005'
  UNION ALL SELECT 'ORS Processor', '2', '001,002,003'
  UNION ALL SELECT 'ORS Approver', '2', '004,005,006'
  UNION ALL SELECT 'SAOB Officer', '3', '001,002,003'
  UNION ALL SELECT 'User Administrator', '4', '001'
  UNION ALL SELECT 'Payee Maintenance Officer', '5', '001,002,003,004'
  UNION ALL SELECT 'Project Maintenance Officer', '6', '001'
  UNION ALL SELECT 'PPMP Officer', '8', '001'
  UNION ALL SELECT 'UACS Maintenance Officer', '9', '001'
  UNION ALL SELECT 'Supplies Officer', '10', '001,002,003'
  UNION ALL SELECT 'BURS Officer', '11', '001,002,003'
  UNION ALL SELECT 'Abstract Officer', '12', '001'
  UNION ALL SELECT 'Disbursement Officer', '13', '001'
  UNION ALL SELECT 'Cashier - LDDAP-ADA', '14', '001'
  UNION ALL SELECT 'Cashier - RCI', '15', '001'
) fam
  ON SUBSTRING(m.access_code, 1, LENGTH(m.access_code) - 3) = fam.family
 AND FIND_IN_SET(RIGHT(m.access_code, 3), fam.suffixes)
JOIN `tbl_roles` r ON r.role_name = fam.role_name
WHERE NOT EXISTS (SELECT 1 FROM `tbl_role_access` ra WHERE ra.role_id = r.recid AND ra.access_code = m.access_code);

-- ---------------------------------------------------------------------
-- 2.2 Old codes -> new series (20 Payee ... 35 RCI). Every user and
--     role keeps exactly what the old code let them do.
-- ---------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_code_map;
CREATE TEMPORARY TABLE tmp_code_map (
  old_code varchar(10) NOT NULL,
  new_code varchar(10) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tmp_code_map (old_code, new_code) VALUES
('5001','20001'), ('5002','20002'), ('5003','20003'), ('5004','20004'),
('10001','21001'), ('10002','21002'), ('10003','21003'),
('6001','22001'), ('6001','22002'), ('6001','22003'),
('9001','23001'), ('9001','23002'), ('9001','23003'),
('4001','24001'), ('4001','24002'), ('4001','24003'), ('4001','24004'),
('1001','25001'), ('1002','25002'), ('1003','25003'),
('1004','25005'), ('1005','25005'),
('2001','26001'), ('2002','26002'), ('2003','26003'),
('2005','26005'), ('2005','27005'),
('2006','26006'), ('2006','27006'), ('2006','32006'),
('11001','27001'), ('11002','27002'), ('11003','27003'),
('3001','28001'), ('3002','28002'), ('3003','28003'),
('8001','29001'), ('8001','29002'), ('8001','29003'),
('8001','30001'), ('8001','30002'), ('8001','30003'),
('12001','31001'), ('12001','31002'), ('12001','31003'),
('13001','32001'), ('13001','32002'), ('13001','32003'), ('13001','32005'),
('13001','34001'), ('13001','34002'), ('13001','34003'),
('14001','33001'), ('14001','33002'), ('14001','33003'),
('15001','35001'), ('15001','35002'), ('15001','35003');

INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT DISTINCT ra.role_id, m.new_code, 'SYSTEM'
FROM tbl_role_access ra
JOIN tmp_code_map m ON m.old_code = ra.access_code;

DELETE ra FROM tbl_role_access ra
WHERE ra.access_code IN (SELECT old_code FROM tmp_code_map);

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, m.new_code, 1, 'SYSTEM'
FROM tbl_user_access ua
JOIN tmp_code_map m ON m.old_code = ua.access_code
WHERE ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = m.new_code);

DELETE FROM tbl_user_access
WHERE access_code IN (SELECT old_code FROM tmp_code_map)
   OR is_active = 0;

-- New module catalog
DROP TEMPORARY TABLE IF EXISTS tmp_catalog;
CREATE TEMPORARY TABLE tmp_catalog (
  access_code varchar(10) NOT NULL,
  access_name varchar(100) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tmp_catalog (access_code, access_name) VALUES
('20001','Payee - View'), ('20002','Payee - Add'), ('20003','Payee - Update'), ('20004','Payee - Delete'),
('21001','Supplies - View'), ('21002','Supplies - Add'), ('21003','Supplies - Update'), ('21004','Supplies - Delete'),
('22001','Projects - View'), ('22002','Projects - Add'), ('22003','Projects - Update'), ('22004','Projects - Delete'),
('23001','UACS - View'), ('23002','UACS - Add'), ('23003','UACS - Update'), ('23004','UACS - Delete'),
('24001','User Management - View'), ('24002','User Management - Add'), ('24003','User Management - Update'), ('24004','User Management - Delete'),
('25001','Budget Allotment - View'), ('25002','Budget Allotment - Add'), ('25003','Budget Allotment - Update'), ('25004','Budget Allotment - Delete'), ('25005','Budget Allotment - Approve'),
('26001','ORS - View'), ('26002','ORS - Add'), ('26003','ORS - Update'), ('26004','ORS - Delete'), ('26005','ORS - Certify A'), ('26006','ORS - Certify B'),
('27001','BURS - View'), ('27002','BURS - Add'), ('27003','BURS - Update'), ('27004','BURS - Delete'), ('27005','BURS - Certify A'), ('27006','BURS - Certify B'),
('28001','SAOB - View'), ('28002','SAOB - Add'), ('28003','SAOB - Update'), ('28004','SAOB - Delete'),
('29001','PPMP - View'), ('29002','PPMP - Add'), ('29003','PPMP - Update'), ('29004','PPMP - Delete'),
('30001','Purchase Request - View'), ('30002','Purchase Request - Add'), ('30003','Purchase Request - Update'), ('30004','Purchase Request - Delete'),
('31001','Abstract - View'), ('31002','Abstract - Add'), ('31003','Abstract - Update'), ('31004','Abstract - Delete'),
('32001','Disbursement - View'), ('32002','Disbursement - Add'), ('32003','Disbursement - Update'), ('32004','Disbursement - Delete'), ('32005','Disbursement - Certify A'), ('32006','Disbursement - Certify B'),
('33001','LDDAP-ADA - View'), ('33002','LDDAP-ADA - Add'), ('33003','LDDAP-ADA - Update'), ('33004','LDDAP-ADA - Delete'),
('34001','ADA - View'), ('34002','ADA - Add'), ('34003','ADA - Update'), ('34004','ADA - Delete'),
('35001','RCI - View'), ('35002','RCI - Add'), ('35003','RCI - Update'), ('35004','RCI - Delete'),
-- codes added by the later migrations; listed here so a re-run of this
-- step does not delete them (they are remapped/retired again below)
('33005','LDDAP-ADA - Approve'), ('34005','ADA - Approve'),
('36001','Budget Approval - View'), ('36005','Budget Approval - Approve'),
('37001','Support Tickets - View'), ('37003','Support Tickets - Update'),
('38001','ORS Approval - View'), ('38005','ORS Approval - Approve'),
('39001','BURS Approval - View'), ('39005','BURS Approval - Approve'),
('40001','Disbursement Approval - View'), ('40005','Disbursement Approval - Approve'),
('41001','LDDAP-ADA Approval - View'), ('41005','LDDAP-ADA Approval - Approve'),
('42001','ADA Approval - View'), ('42005','ADA Approval - Approve');

-- drop old codes from the catalog (rows only; new codes are inserted per step)
DELETE FROM tbl_access_modules
WHERE access_code NOT IN (SELECT access_code FROM tmp_catalog);

UPDATE tbl_access_modules am
JOIN tmp_catalog c ON c.access_code = am.access_code
SET am.access_name = c.access_name;

INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM tmp_catalog c
WHERE c.access_code BETWEEN '20001' AND '35004'
  AND c.access_code NOT IN ('33005','34005')
  AND NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

DELETE FROM tbl_user_access
WHERE access_code NOT IN (SELECT access_code FROM tbl_access_modules);

DELETE FROM tbl_role_access
WHERE access_code NOT IN (SELECT access_code FROM tbl_access_modules);

DROP TEMPORARY TABLE IF EXISTS tmp_code_map;
DROP TEMPORARY TABLE IF EXISTS tmp_catalog;

-- ---------------------------------------------------------------------
-- 2.3 Budget Approval becomes its own module: 25005 -> 36001 + 36005
-- ---------------------------------------------------------------------
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '36001' access_code, 'Budget Approval - View' access_name
  UNION ALL SELECT '36005', 'Budget Approval - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT ra.role_id, n.code, 'SYSTEM'
FROM tbl_role_access ra
CROSS JOIN (SELECT '36001' code UNION ALL SELECT '36005') n
WHERE ra.access_code = '25005';

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, n.code, 1, 'SYSTEM'
FROM tbl_user_access ua
CROSS JOIN (SELECT '36001' code UNION ALL SELECT '36005') n
WHERE ua.access_code = '25005' AND ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = n.code);

DELETE FROM tbl_role_access WHERE access_code = '25005';
DELETE FROM tbl_user_access WHERE access_code = '25005';
DELETE FROM tbl_access_modules WHERE access_code = '25005';

-- ---------------------------------------------------------------------
-- 2.4 Disbursement: single approval, Certify B (32006) retired
-- ---------------------------------------------------------------------
UPDATE tbl_access_modules SET access_name = 'Disbursement - Approve' WHERE access_code = '32005';
DELETE FROM tbl_role_access WHERE access_code = '32006';
DELETE FROM tbl_user_access WHERE access_code = '32006';
DELETE FROM tbl_access_modules WHERE access_code = '32006';

-- ---------------------------------------------------------------------
-- 2.5 LDDAP-ADA / ADA approve codes (33005, 34005) for the Administrator
--     role; step 2.6 moves them to their approval modules (41, 42)
-- ---------------------------------------------------------------------
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '33005' access_code, 'LDDAP-ADA - Approve' access_name
  UNION ALL SELECT '34005', 'ADA - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code)
  AND NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code IN ('41005','42005'));

INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '33005' code UNION ALL SELECT '34005') c
WHERE r.role_name = 'Administrator'
  AND EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.code);

-- ---------------------------------------------------------------------
-- 2.6 Every approval page is its own module (View + Approve)
--   38 ORS Approval (26005, 26006)   39 BURS Approval (27005, 27006)
--   40 Disbursement Approval (32005) 41 LDDAP-ADA Approval (33005)
--   42 ADA Approval (34005)
-- ---------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_approval_map;
CREATE TEMPORARY TABLE tmp_approval_map (
  old_code varchar(10) NOT NULL,
  family varchar(2) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO tmp_approval_map VALUES
  ('26005', '38'), ('26006', '38'),
  ('27005', '39'), ('27006', '39'),
  ('32005', '40'),
  ('33005', '41'),
  ('34005', '42');

DROP TEMPORARY TABLE IF EXISTS tmp_approval_codes;
CREATE TEMPORARY TABLE tmp_approval_codes (
  family varchar(2) NOT NULL,
  access_code varchar(10) NOT NULL,
  access_name varchar(100) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO tmp_approval_codes VALUES
  ('38', '38001', 'ORS Approval - View'),          ('38', '38005', 'ORS Approval - Approve'),
  ('39', '39001', 'BURS Approval - View'),         ('39', '39005', 'BURS Approval - Approve'),
  ('40', '40001', 'Disbursement Approval - View'), ('40', '40005', 'Disbursement Approval - Approve'),
  ('41', '41001', 'LDDAP-ADA Approval - View'),    ('41', '41005', 'LDDAP-ADA Approval - Approve'),
  ('42', '42001', 'ADA Approval - View'),          ('42', '42005', 'ADA Approval - Approve');

INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM tmp_approval_codes c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT DISTINCT ra.role_id, c.access_code, 'SYSTEM'
FROM tbl_role_access ra
JOIN tmp_approval_map m ON m.old_code = ra.access_code
JOIN tmp_approval_codes c ON c.family = m.family
WHERE NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = ra.role_id AND x.access_code = c.access_code);

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, c.access_code, 1, 'SYSTEM'
FROM tbl_user_access ua
JOIN tmp_approval_map m ON m.old_code = ua.access_code
JOIN tmp_approval_codes c ON c.family = m.family
WHERE ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = c.access_code);

DELETE FROM tbl_role_access WHERE access_code IN (SELECT old_code FROM tmp_approval_map);
DELETE FROM tbl_user_access WHERE access_code IN (SELECT old_code FROM tmp_approval_map);
DELETE FROM tbl_access_modules WHERE access_code IN (SELECT old_code FROM tmp_approval_map);

DROP TEMPORARY TABLE IF EXISTS tmp_approval_map;
DROP TEMPORARY TABLE IF EXISTS tmp_approval_codes;

-- ---------------------------------------------------------------------
-- 2.7 Support Tickets (37001 View = Support desk, 37003 Update) for the
--     Administrator role; 37002 is not used
-- ---------------------------------------------------------------------
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '37001' access_code, 'Support Tickets - View' access_name
  UNION ALL SELECT '37003', 'Support Tickets - Update'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '37001' code UNION ALL SELECT '37003') c
WHERE r.role_name = 'Administrator';

DELETE FROM tbl_role_access WHERE access_code = '37002';
DELETE FROM tbl_user_access WHERE access_code = '37002';
DELETE FROM tbl_access_modules WHERE access_code = '37002';

-- ---------------------------------------------------------------------
-- 2.8 Users who already have a role: rebuild their access cache from it.
--     (After this script nobody has a role yet - existing users keep
--     their remapped per-user grants until a role is assigned to them.)
-- ---------------------------------------------------------------------
DELETE ua FROM tbl_user_access ua
JOIN myua_user u ON u.username = ua.username
WHERE u.role_id IS NOT NULL;

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, ra.access_code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.is_active = 1
JOIN tbl_role_access ra ON ra.role_id = r.recid;


-- =====================================================================
-- OPTIONAL - existing LDDAP-ADAs and ADAs start as PENDING approval.
-- If the ones already on staging are final, uncomment to mark them
-- approved instead (run once, right after this script).
-- =====================================================================
-- UPDATE tbl_lddapada_hd SET is_pending = 0, is_approved = 1, approver = 'SYSTEM' WHERE is_pending = 1 AND approver = '';
-- UPDATE tbl_ada         SET is_pending = 0, is_approved = 1, approver = 'SYSTEM' WHERE is_pending = 1 AND approver = '';
