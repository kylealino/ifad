-- =====================================================================
-- Budget Approval becomes its own module (run AFTER
-- migration-access-codes-09292026.sql)
--
--   25005 Budget Allotment - Approve   (removed)
--   36001 Budget Approval - View       (opens the Budget Approval page)
--   36005 Budget Approval - Approve    (approve / disapprove allotments)
--
-- Everyone who had 25005 - in a role or as a per-user grant - gets both
-- new codes, so nobody loses the ability to approve. Safe to re-run.
-- Deploy together with the matching PHP (Config\AccessCodes).
-- =====================================================================

-- 1. New catalog entries
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '36001' access_code, 'Budget Approval - View' access_name
  UNION ALL SELECT '36005', 'Budget Approval - Approve'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 2. Roles that could approve keep that ability under the new module
INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT ra.role_id, n.code, 'SYSTEM'
FROM tbl_role_access ra
CROSS JOIN (SELECT '36001' code UNION ALL SELECT '36005') n
WHERE ra.access_code = '25005';

-- 3. Same for per-user grants
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, n.code, 1, 'SYSTEM'
FROM tbl_user_access ua
CROSS JOIN (SELECT '36001' code UNION ALL SELECT '36005') n
WHERE ua.access_code = '25005' AND ua.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = ua.username AND x.access_code = n.code);

-- 4. Retire the old code everywhere
DELETE FROM tbl_role_access WHERE access_code = '25005';
DELETE FROM tbl_user_access WHERE access_code = '25005';
DELETE FROM tbl_access_modules WHERE access_code = '25005';
