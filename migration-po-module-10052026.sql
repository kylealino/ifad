-- =====================================================================
-- Purchase Order module (mypo): a PO is prepared from a numbered PR.
--
--   1. tbl_po_hd (one PO) and tbl_po_dt (its items)
--   2. Access codes, family 43:
--        43001 Purchase Order - View
--        43002 Purchase Order - Add
--        43003 Purchase Order - Update
--        43004 Purchase Order - Delete
--   3. Who gets them:
--        - the Administrator role (all four)
--        - every role and user that already has the matching Purchase
--          Request code (30001 -> 43001, 30002 -> 43002, ...), so the
--          people who prepare PRs can prepare their POs
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with Config\AccessCodes, Routes and Navigation.
-- Backup: __db_backup/fods-local access tables (pre-po-module-10052026).sql
-- =====================================================================

-- Staging's default collation is utf8mb4_0900_ai_ci while every FODS table
-- is utf8mb4_general_ci; pin the connection so literals compare cleanly.
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

-- The grants below filter on non-key columns; safe-update mode (on by
-- default in MySQL Workbench) would block them.
SET SQL_SAFE_UPDATES = 0;

-- 1. Tables
CREATE TABLE IF NOT EXISTS `tbl_po_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `pono` varchar(100) NOT NULL DEFAULT '',
  `po_date` date DEFAULT NULL,
  `pr_id` int(11) NOT NULL DEFAULT 0,
  `prno` varchar(100) NOT NULL DEFAULT '',
  `pr_date` date DEFAULT NULL,
  `supplier_name` varchar(250) NOT NULL DEFAULT '',
  `supplier_address` varchar(250) NOT NULL DEFAULT '',
  `supplier_tel` varchar(100) NOT NULL DEFAULT '',
  `supplier_tin` varchar(100) NOT NULL DEFAULT '',
  `mode_of_procurement` varchar(100) NOT NULL DEFAULT '',
  `place_of_delivery` varchar(250) NOT NULL DEFAULT '',
  `date_of_delivery` varchar(250) NOT NULL DEFAULT '',
  `delivery_term` varchar(250) NOT NULL DEFAULT '',
  `payment_term` varchar(100) NOT NULL DEFAULT '',
  `charge_to` varchar(250) NOT NULL DEFAULT '',
  `end_user` varchar(100) NOT NULL DEFAULT '',
  `fund_cluster` varchar(50) NOT NULL DEFAULT '',
  `funds_available` decimal(25,2) NOT NULL DEFAULT 0.00,
  `ors_burs_no` varchar(100) NOT NULL DEFAULT '',
  `ors_burs_date` date DEFAULT NULL,
  `ors_burs_amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  `approved_by` varchar(100) NOT NULL DEFAULT '',
  `approved_position` varchar(100) NOT NULL DEFAULT '',
  `accountant` varchar(100) NOT NULL DEFAULT '',
  `accountant_position` varchar(100) NOT NULL DEFAULT '',
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_po_hd_pono` (`pono`),
  KEY `idx_tbl_po_hd_prno` (`prno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_po_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `pono` varchar(100) NOT NULL DEFAULT '',
  `stock_no` varchar(100) NOT NULL DEFAULT '',
  `unit` varchar(50) NOT NULL DEFAULT '',
  `item_desc` varchar(500) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(25,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_po_dt_po_id` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '43001' access_code, 'Purchase Order - View' access_name
  UNION ALL SELECT '43002', 'Purchase Order - Add'
  UNION ALL SELECT '43003', 'Purchase Order - Update'
  UNION ALL SELECT '43004', 'Purchase Order - Delete'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 3a. The Administrator role gets all four
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '43001' code UNION ALL SELECT '43002' UNION ALL SELECT '43003' UNION ALL SELECT '43004') c
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = r.recid AND x.access_code = c.code);

-- 3b. Roles with a Purchase Request code get the matching Purchase Order code
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT ra.role_id, CONCAT('43', SUBSTRING(ra.access_code, 3)), 'SYSTEM'
FROM tbl_role_access ra
WHERE ra.access_code IN ('30001', '30002', '30003', '30004')
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = ra.role_id AND x.access_code = CONCAT('43', SUBSTRING(ra.access_code, 3)));

-- 3c. Keep the per-user cache (tbl_user_access) in step: users on the
--     Administrator role, and users who already hold the matching PR code
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.role_name = 'Administrator'
CROSS JOIN (SELECT '43001' code UNION ALL SELECT '43002' UNION ALL SELECT '43003' UNION ALL SELECT '43004') c
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, CONCAT('43', SUBSTRING(ua.access_code, 3)), 1, 'SYSTEM'
FROM tbl_user_access ua
WHERE ua.access_code IN ('30001', '30002', '30003', '30004')
  AND ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = CONCAT('43', SUBSTRING(ua.access_code, 3)));
