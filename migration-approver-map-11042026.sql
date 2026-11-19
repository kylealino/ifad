-- =====================================================================
-- Approver Mapping (myapprovermap): who signs each box of each module,
-- kept in the database instead of in the code and the user flags.
--
--   1. tbl_approver_map: one row per approver of a module's box
--        module/box  e.g. ors/a, dv/d, lddapada/certified_by (the list is
--                    MyApproverMapModel::BOXES)
--        "pick" boxes offer their active rows on the entry form; is_default
--        is filled in on a new record
--        "amount" boxes (DV Box D, PR Box B, PO Box B) are not picked: the
--        active row with the highest min_amount at or below the amount signs
--   2. Access codes, family 46:
--        46001 Approver Mapping - View
--        46002 Approver Mapping - Add
--        46003 Approver Mapping - Update
--        46004 Approver Mapping - Delete
--      granted to the Administrator role
--   3. The mapping as it is today, so nothing changes on deploy:
--        ORS / BURS Box A  active users with cert_tag = 1
--        ORS / BURS Box B  cert_tag = 2
--        DV Box A          cert_tag = 1
--        DV Box C          cert_tag = 3 (default BONJOBIE F. CAJANO, CPA, CTT)
--        DV Box D          below 100,000.00 ALEXIS M. ORTIZ, from 100,000.00 Atty. LUCIEDEN G. RAZ
--        ADA 1 / 2         ada_tag = 1 / 2
--        LDDAP-ADA         the eight names of the old fixed list on each of
--                          the four signatures, with the old defaults
--        PR Box A          cert_tag = 1 or PPMP signatories
--        PR / PO Box B     below 5,000,000.00 ALEXIS M. ORTIZ, from 5,000,000.00 ATTY. LUCIEDEN G. RAZ
--        PO Box A          cert_tag = 3 (default BONJOBIE F. CAJANO, CPA, CTT)
--      Each box is filled only while it has no rows, so re-running never
--      brings back an approver someone removed.
--
-- The cert_tag / ada_tag / is_ppmp_signatory columns stay on myua_user but
-- no longer drive the approver lists.
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- Backup: __db_backup/fods-local (pre-approver-map-11042026).sql
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

-- 1. Table
CREATE TABLE IF NOT EXISTS `tbl_approver_map` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(30) NOT NULL DEFAULT '',
  `box` varchar(30) NOT NULL DEFAULT '',
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `position` varchar(100) NOT NULL DEFAULT '',
  `min_amount` decimal(25,2) NOT NULL DEFAULT 0.00,
  `is_default` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(50) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_approver_map_box` (`module`, `box`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2a. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '46001' access_code, 'Approver Mapping - View' access_name
  UNION ALL SELECT '46002', 'Approver Mapping - Add'
  UNION ALL SELECT '46003', 'Approver Mapping - Update'
  UNION ALL SELECT '46004', 'Approver Mapping - Delete'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 2b. The Administrator role gets all four
INSERT INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '46001' code UNION ALL SELECT '46002' UNION ALL SELECT '46003' UNION ALL SELECT '46004') c
WHERE r.role_name = 'Administrator'
  AND NOT EXISTS (SELECT 1 FROM tbl_role_access x WHERE x.role_id = r.recid AND x.access_code = c.code);

-- 2c. Per-user cache (tbl_user_access) for the users on the Administrator role
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.role_name = 'Administrator'
CROSS JOIN (SELECT '46001' code UNION ALL SELECT '46002' UNION ALL SELECT '46003' UNION ALL SELECT '46004') c
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);

-- 3. Today's approvers. Pick boxes filled from the user flags (one row per name)
INSERT INTO tbl_approver_map (module, box, full_name, position, is_default, added_by)
SELECT s.module, s.box, s.full_name, MAX(s.position), MAX(s.is_default), 'SYSTEM'
FROM (
  SELECT 'ors' module, 'a' box, TRIM(u.full_name) full_name, TRIM(u.position) position, 0 is_default FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 1
  UNION ALL SELECT 'ors', 'b', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 2
  UNION ALL SELECT 'burs', 'a', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 1
  UNION ALL SELECT 'burs', 'b', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 2
  UNION ALL SELECT 'dv', 'a', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 1
  UNION ALL SELECT 'dv', 'c', TRIM(u.full_name), TRIM(u.position), IF(UPPER(TRIM(u.full_name)) = 'BONJOBIE F. CAJANO, CPA, CTT', 1, 0) FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 3
  UNION ALL SELECT 'ada', 'a', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.ada_tag = 1
  UNION ALL SELECT 'ada', 'b', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND u.ada_tag = 2
  UNION ALL SELECT 'pr', 'a', TRIM(u.full_name), TRIM(u.position), 0 FROM myua_user u WHERE u.is_active = 1 AND (u.cert_tag = 1 OR u.is_ppmp_signatory = 1)
  UNION ALL SELECT 'po', 'a', TRIM(u.full_name), TRIM(u.position), IF(UPPER(TRIM(u.full_name)) = 'BONJOBIE F. CAJANO, CPA, CTT', 1, 0) FROM myua_user u WHERE u.is_active = 1 AND u.cert_tag = 3
  -- the PO's default accountant, even without cert_tag = 3
  UNION ALL SELECT 'po', 'a', 'BONJOBIE F. CAJANO, CPA, CTT', 'Accountant III', 1
) s
WHERE s.full_name <> ''
  AND NOT EXISTS (SELECT 1 FROM tbl_approver_map m WHERE m.module = s.module AND m.box = s.box)
GROUP BY s.module, s.box, s.full_name;

-- LDDAP-ADA: the old fixed list on each signature, with the old defaults
INSERT INTO tbl_approver_map (module, box, full_name, position, is_default, added_by)
SELECT 'lddapada', f.box, n.full_name, n.position, IF(n.full_name = f.default_name, 1, 0), 'SYSTEM'
FROM (
  SELECT 'certified_by' box, 'BONJOBIE F. CAJANO, CPA, CTT' default_name
  UNION ALL SELECT 'approved_by', 'ALEXIS M. ORTIZ'
  UNION ALL SELECT 'ada_approver', 'JOVY S. MEDINA'
  UNION ALL SELECT 'ada_approver2', 'ALEXIS M. ORTIZ'
) f
CROSS JOIN (
  SELECT 'JOVY S. MEDINA' full_name, 'Administrative Officer V' position
  UNION ALL SELECT 'JESTER C. VIRIÑA, CPA', 'Supervising Administrative Officer'
  UNION ALL SELECT 'ALEXIS M. ORTIZ', 'Chief Administrative Officer'
  UNION ALL SELECT 'SALVADOR C. SERRANO', 'Chief SRS & Officer-in-Charge'
  UNION ALL SELECT 'BONJOBIE F. CAJANO, CPA, CTT', 'Accountant III'
  UNION ALL SELECT 'ATTY. LUCIEDEN G. RAZ', 'Director III'
  UNION ALL SELECT 'JACUS S. NACIS', 'Director IV'
  UNION ALL SELECT 'JAMES C. LUCENA, CPA', 'Accountant II'
) n
WHERE NOT EXISTS (SELECT 1 FROM tbl_approver_map m WHERE m.module = 'lddapada' AND m.box = f.box);

-- Amount boxes: the limits that were in the code
INSERT INTO tbl_approver_map (module, box, full_name, position, min_amount, added_by)
SELECT t.module, t.box, t.full_name, t.position, t.min_amount, 'SYSTEM'
FROM (
  SELECT 'dv' module, 'd' box, 'ALEXIS M. ORTIZ' full_name, 'Chief Administrative Officer' position, 0.00 min_amount
  UNION ALL SELECT 'dv', 'd', 'Atty. LUCIEDEN G. RAZ', 'Director III', 100000.00
  UNION ALL SELECT 'pr', 'b', 'ALEXIS M. ORTIZ', 'Chief Administrative Officer, FAD', 0.00
  UNION ALL SELECT 'pr', 'b', 'ATTY. LUCIEDEN G. RAZ', 'Director III', 5000000.00
  UNION ALL SELECT 'po', 'b', 'ALEXIS M. ORTIZ', 'Chief AO, FAD', 0.00
  UNION ALL SELECT 'po', 'b', 'ATTY. LUCIEDEN G. RAZ', 'Director III', 5000000.00
) t
WHERE NOT EXISTS (SELECT 1 FROM tbl_approver_map m WHERE m.module = t.module AND m.box = t.box);
