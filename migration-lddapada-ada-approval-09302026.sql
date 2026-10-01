-- =====================================================================
-- LDDAP-ADA and ADA get an approval step (single approver, like Budget
-- Approval) and their own "Approve" access codes.
--
--   33005 LDDAP-ADA - Approve
--   34005 ADA - Approve
--
-- Existing LDDAP-ADAs and ADAs start as pending. tbl_ada also gets the
-- standard added_by / added_at columns (existing rows: added_at = ADA date).
-- Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local lddapada ada access (pre-lddapada-ada-approval-09302026).sql
-- =====================================================================

-- 1. Approval columns
ALTER TABLE tbl_lddapada_hd
  ADD COLUMN IF NOT EXISTS `is_pending` int(11) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `is_approved` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `is_disapproved` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `approver` varchar(50) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `remarks` varchar(250) NOT NULL DEFAULT '';

ALTER TABLE tbl_ada
  ADD COLUMN IF NOT EXISTS `is_pending` int(11) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `is_approved` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `is_disapproved` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `approver` varchar(50) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `remarks` varchar(250) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `added_by` varchar(50) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `added_at` datetime NOT NULL DEFAULT current_timestamp();

-- existing ADAs had no encoded date: use their ADA date (only rows not stamped yet)
UPDATE tbl_ada SET added_at = ada_date WHERE added_by = '' AND ada_date IS NOT NULL AND DATE(added_at) <> ada_date;

-- 2. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '33005' access_code, 'LDDAP-ADA - Approve' access_name
  UNION ALL SELECT '34005', 'ADA - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 3. The Administrator role gets both; assign them to other roles in User Management
INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '33005' code UNION ALL SELECT '34005') c
WHERE r.role_name = 'Administrator';

-- keep the per-user cache (tbl_user_access) in step for users with that role
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.role_name = 'Administrator'
CROSS JOIN (SELECT '33005' code UNION ALL SELECT '34005') c
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);

-- 4. tbl_ada used utf8mb4_unicode_ci while every other table uses
--    utf8mb4_general_ci, so comparing ADA and DV numbers failed
--    ("Illegal mix of collations"). Align it with the rest.
ALTER TABLE tbl_ada CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
