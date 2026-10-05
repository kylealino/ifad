-- =====================================================================
-- Purchase Request approval, like ORS / PO: two steps in order.
--
--   Box A  requesting office: "Requested by", the head of the requesting
--          office picked on the PR (tbl_pr_hd.requested_by)
--   Box B  approval: follows the PR estimated cost (tbl_pr_hd.approved_by,
--          set on save): below 5,000,000.00 ALEXIS M. ORTIZ, 5,000,000.00
--          and above ATTY. LUCIEDEN G. RAZ
--
-- Each step is decided by the signatory named on it (or an Administrator)
-- on the new PR Approval page, family 45:
--        45001 PR Approval - View
--        45005 PR Approval - Approve
--
--   1. Signatory and approval columns on tbl_pr_hd (same names as ORS / PO)
--   2. Box B for the PRs already saved, from their estimated cost
--   3. Access codes
--   4. Who gets them: the Administrator role, and the people who can be
--      named on a PR step - active office heads / supervisors (cert_tag = 1
--      or PPMP signatories) and the two approving officers
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- Backup: __db_backup/fods-local pr approval (pre-pr-approval-10052026).sql
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

-- 1. Signatory and approval columns
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'requested_by') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `requested_by` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'requested_position') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `requested_position` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'approved_by') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `approved_by` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'approved_position') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `approved_position` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'is_pending') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `is_pending` int(11) NOT NULL DEFAULT 1", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'is_approved_certa') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `is_approved_certa` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'is_disapproved_certa') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `is_disapproved_certa` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'certa_remarks') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `certa_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'certa_approver') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `certa_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'is_approved_certb') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `is_approved_certb` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'is_disapproved_certb') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `is_disapproved_certb` int(11) NOT NULL DEFAULT 0", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'certb_remarks') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `certb_remarks` varchar(100) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_pr_hd' AND COLUMN_NAME = 'certb_approver') = 0,
  "ALTER TABLE `tbl_pr_hd` ADD COLUMN `certb_approver` varchar(50) NOT NULL DEFAULT ''", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Box B for PRs saved before this migration (same rule as MyPRModel::approver_for).
--    Their Box A (requested_by) stays blank until someone edits the PR.
UPDATE tbl_pr_hd
SET
  `approved_by` = IF(`estimated_cost` >= 5000000, 'ATTY. LUCIEDEN G. RAZ', 'ALEXIS M. ORTIZ'),
  `approved_position` = IF(`estimated_cost` >= 5000000, 'Director III', 'Chief Administrative Officer, FAD')
WHERE `approved_by` = '';

-- 3. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '45001' access_code, 'PR Approval - View' access_name
  UNION ALL SELECT '45005', 'PR Approval - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 4a. The Administrator role gets both
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '45001' code UNION ALL SELECT '45005') c
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = r.recid AND x.access_code = c.code);

-- 4b. Per-user cache (tbl_user_access): users on the Administrator role, and
--     everyone who can be named on a PR step
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
CROSS JOIN (SELECT '45001' code UNION ALL SELECT '45005') c
WHERE u.is_active = 1
  AND (
    u.role_id IN (SELECT recid FROM tbl_roles WHERE role_name = 'Administrator')
    OR u.cert_tag = 1
    OR u.is_ppmp_signatory = 1
    OR UPPER(TRIM(u.full_name)) IN ('ALEXIS M. ORTIZ', 'ATTY. LUCIEDEN G. RAZ')
  )
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);
