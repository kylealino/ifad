-- =====================================================================
-- Every approval page becomes its own module, like Budget Approval (36):
-- a "<Module> Approval" card with View (opens the approval page) and
-- Approve (approve / disapprove). The approve/certify codes leave the
-- entry modules, and Certify A / Certify B are gone - one Approve covers
-- whichever step an ORS/BURS is waiting at.
--
--   38 ORS Approval           38001 View, 38005 Approve  (replaces 26005 Certify A, 26006 Certify B)
--   39 BURS Approval          39001 View, 39005 Approve  (replaces 27005, 27006)
--   40 Disbursement Approval  40001 View, 40005 Approve  (replaces 32005)
--   41 LDDAP-ADA Approval     41001 View, 41005 Approve  (replaces 33005)
--   42 ADA Approval           42001 View, 42005 Approve  (replaces 34005)
--
-- Everyone who had an old code - in a role or as a per-user grant - gets
-- both new codes of that module, so nobody loses the ability to approve.
-- Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local (pre-approval-modules-09302026).sql
-- =====================================================================

-- old code -> new module family
DROP TEMPORARY TABLE IF EXISTS tmp_approval_map;
CREATE TEMPORARY TABLE tmp_approval_map (old_code varchar(10) NOT NULL, family varchar(2) NOT NULL);
INSERT INTO tmp_approval_map VALUES
  ('26005', '38'), ('26006', '38'),
  ('27005', '39'), ('27006', '39'),
  ('32005', '40'),
  ('33005', '41'),
  ('34005', '42');

DROP TEMPORARY TABLE IF EXISTS tmp_approval_codes;
CREATE TEMPORARY TABLE tmp_approval_codes (family varchar(2) NOT NULL, access_code varchar(10) NOT NULL, access_name varchar(100) NOT NULL);
INSERT INTO tmp_approval_codes VALUES
  ('38', '38001', 'ORS Approval - View'),          ('38', '38005', 'ORS Approval - Approve'),
  ('39', '39001', 'BURS Approval - View'),         ('39', '39005', 'BURS Approval - Approve'),
  ('40', '40001', 'Disbursement Approval - View'), ('40', '40005', 'Disbursement Approval - Approve'),
  ('41', '41001', 'LDDAP-ADA Approval - View'),    ('41', '41005', 'LDDAP-ADA Approval - Approve'),
  ('42', '42001', 'ADA Approval - View'),          ('42', '42005', 'ADA Approval - Approve');

-- 1. New catalog entries
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM tmp_approval_codes c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 2. Roles that could approve keep that ability under the new module
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT DISTINCT ra.role_id, c.access_code, 'SYSTEM'
FROM tbl_role_access ra
JOIN tmp_approval_map m ON m.old_code = ra.access_code
JOIN tmp_approval_codes c ON c.family = m.family
WHERE NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = ra.role_id AND x.access_code = c.access_code);

-- 3. Same for per-user grants (also the cache of role access)
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, c.access_code, 1, 'SYSTEM'
FROM tbl_user_access ua
JOIN tmp_approval_map m ON m.old_code = ua.access_code
JOIN tmp_approval_codes c ON c.family = m.family
WHERE ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = c.access_code);

-- 4. Retire the old codes everywhere
DELETE FROM tbl_role_access WHERE access_code IN (SELECT old_code FROM tmp_approval_map);
DELETE FROM tbl_user_access WHERE access_code IN (SELECT old_code FROM tmp_approval_map);
DELETE FROM tbl_access_modules WHERE access_code IN (SELECT old_code FROM tmp_approval_map);

DROP TEMPORARY TABLE IF EXISTS tmp_approval_map;
DROP TEMPORARY TABLE IF EXISTS tmp_approval_codes;
