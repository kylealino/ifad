-- =====================================================================
-- Purchase Order approval, like ORS: two steps in order.
--
--   Box A  accounting: funds available certified by the accountant picked
--          on the PO (tbl_po_hd.accountant)
--   Box B  approval: follows the PO total (tbl_po_hd.approved_by, set on
--          save): below 5,000,000.00 ALEXIS M. ORTIZ, 5,000,000.00 and
--          above ATTY. LUCIEDEN G. RAZ
--
-- Each step is decided by the signatory named on it (or an Administrator)
-- on the new PO Approval page, family 44:
--        44001 PO Approval - View
--        44005 PO Approval - Approve
--
--   1. Approval columns on tbl_po_hd (same names as ORS / BURS)
--   2. Access codes
--   3. Who gets them: the Administrator role, and the people who can be
--      named on a PO step - active accounting signatories (cert_tag = 3)
--      and the two approving officers
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Run after migration-po-module-10052026.sql. Deploy together with the code.
-- Backup: __db_backup/fods-local po approval (pre-po-approval-10052026).sql
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

-- 1. Approval columns
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'is_pending') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `is_pending` int(11) NOT NULL DEFAULT 1", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'is_approved_certa') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `is_approved_certa` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'is_disapproved_certa') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `is_disapproved_certa` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'certa_remarks') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `certa_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'certa_approver') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `certa_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'is_approved_certb') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `is_approved_certb` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'is_disapproved_certb') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `is_disapproved_certb` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'certb_remarks') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `certb_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_po_hd' AND COLUMN_NAME = 'certb_approver') = 0,
  "ALTER TABLE `tbl_po_hd` ADD COLUMN `certb_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '44001' access_code, 'PO Approval - View' access_name
  UNION ALL SELECT '44005', 'PO Approval - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 3a. The Administrator role gets both
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '44001' code UNION ALL SELECT '44005') c
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = r.recid AND x.access_code = c.code);

-- 3b. Per-user cache (tbl_user_access): users on the Administrator role, and
--     everyone who can be named on a PO step
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
CROSS JOIN (SELECT '44001' code UNION ALL SELECT '44005') c
WHERE u.is_active = 1
  AND (
    u.role_id IN (SELECT recid FROM tbl_roles WHERE role_name = 'Administrator')
    OR u.cert_tag = 3
    OR UPPER(TRIM(u.full_name)) IN ('ALEXIS M. ORTIZ', 'ATTY. LUCIEDEN G. RAZ')
  )
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);
