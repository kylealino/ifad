-- =====================================================================
-- PPMP approval: one step, like LDDAP-ADA / ADA.
--
--   For approval -> Approved, or Returned (disapproved, with a reason).
--   Decided in the PPMP editor by anyone whose role has PPMP Approval -
--   Approve. An approved PPMP is locked (its documents can still be
--   added); saving a returned one sends it back for approval.
--
--   Family 47:
--        47001 PPMP Approval - View
--        47005 PPMP Approval - Approve
--
--   1. Approval columns on tbl_ppmp_hd (same names as tbl_lddapada_hd).
--      PPMPs saved before this start as For approval.
--   2. Access codes
--   3. The Administrator role (and its users' access cache) gets both
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

-- 1. Approval columns
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ppmp_hd' AND COLUMN_NAME = 'is_pending') = 0,
  "ALTER TABLE `tbl_ppmp_hd` ADD COLUMN `is_pending` int(11) NOT NULL DEFAULT 1", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ppmp_hd' AND COLUMN_NAME = 'is_approved') = 0,
  "ALTER TABLE `tbl_ppmp_hd` ADD COLUMN `is_approved` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ppmp_hd' AND COLUMN_NAME = 'is_disapproved') = 0,
  "ALTER TABLE `tbl_ppmp_hd` ADD COLUMN `is_disapproved` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ppmp_hd' AND COLUMN_NAME = 'approver') = 0,
  "ALTER TABLE `tbl_ppmp_hd` ADD COLUMN `approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ppmp_hd' AND COLUMN_NAME = 'remarks') = 0,
  "ALTER TABLE `tbl_ppmp_hd` ADD COLUMN `remarks` varchar(250) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '47001' access_code, 'PPMP Approval - View' access_name
  UNION ALL SELECT '47005', 'PPMP Approval - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 3a. The Administrator role gets both
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '47001' code UNION ALL SELECT '47005') c
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = r.recid AND x.access_code = c.code);

-- 3b. Per-user cache (tbl_user_access) for users on the Administrator role.
--     Other roles: tick PPMP Approval in User Management > Roles.
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
CROSS JOIN (SELECT '47001' code UNION ALL SELECT '47005') c
WHERE u.is_active = 1
  AND u.role_id IN (SELECT recid FROM tbl_roles WHERE role_name = 'Administrator')
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);
