-- =====================================================================
-- Access code series refactor (run AFTER migration-rbac-09292026.sql)
--
-- New series, one family per module in sidebar order:
--   <family><action>   action: 001 View, 002 Add, 003 Update, 004 Delete,
--                              005 Approve / Certify A, 006 Certify B
--   20 Payee        24 User Mgmt   28 SAOB      32 Disbursement
--   21 Supplies     25 Budget      29 PPMP      33 LDDAP-ADA
--   22 Projects     26 ORS         30 PR        34 ADA
--   23 UACS         27 BURS        31 Abstract  35 RCI
-- Families start at 20 so no new code collides with an old one.
--
-- Existing grants (per-user tbl_user_access rows AND role permissions in
-- tbl_role_access) are remapped so every user keeps exactly what the
-- code let them do before. Where one old code covered several things
-- (e.g. 8001 opened PPMP *and* PR, 13001 opened Disbursement *and* ADA,
-- 2005/2006 approved in several modules), it maps to all of them.
--
-- Deploy together with the matching PHP changes (Config\AccessCodes).
-- Safe to re-run: remapping only happens while old codes still exist.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Old -> new mapping
-- ---------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_code_map;
CREATE TEMPORARY TABLE tmp_code_map (
  old_code varchar(10) NOT NULL,
  new_code varchar(10) NOT NULL
);

INSERT INTO tmp_code_map (old_code, new_code) VALUES
-- Payee
('5001','20001'), ('5002','20002'), ('5003','20003'), ('5004','20004'),
-- Supplies
('10001','21001'), ('10002','21002'), ('10003','21003'),
-- Projects (saving was never checked - view implied add/update)
('6001','22001'), ('6001','22002'), ('6001','22003'),
-- UACS (same)
('9001','23001'), ('9001','23002'), ('9001','23003'),
-- User Management (actions were never checked - view implied all)
('4001','24001'), ('4001','24002'), ('4001','24003'), ('4001','24004'),
-- Budget Allotment (1004 approval screen + 1005 approve -> one Approve)
('1001','25001'), ('1002','25002'), ('1003','25003'),
('1004','25005'), ('1005','25005'),
-- ORS (2004 approval screen is now opened by Certify A/B)
('2001','26001'), ('2002','26002'), ('2003','26003'),
-- ORS Certify A/B were also the approval codes for BURS, and B for Disbursement
('2005','26005'), ('2005','27005'),
('2006','26006'), ('2006','27006'), ('2006','32006'),
-- BURS
('11001','27001'), ('11002','27002'), ('11003','27003'),
-- SAOB
('3001','28001'), ('3002','28002'), ('3003','28003'),
-- 8001 opened both PPMP and PR (saving in both was never checked)
('8001','29001'), ('8001','29002'), ('8001','29003'),
('8001','30001'), ('8001','30002'), ('8001','30003'),
-- Abstract (saving was never checked)
('12001','31001'), ('12001','31002'), ('12001','31003'),
-- 13001 opened both Disbursement and ADA; saving and Disbursement
-- Certify A were never checked
('13001','32001'), ('13001','32002'), ('13001','32003'), ('13001','32005'),
('13001','34001'), ('13001','34002'), ('13001','34003'),
-- LDDAP-ADA
('14001','33001'), ('14001','33002'), ('14001','33003'),
-- RCI
('15001','35001'), ('15001','35002'), ('15001','35003');

-- ---------------------------------------------------------------------
-- 2. Remap role permissions
-- ---------------------------------------------------------------------
INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT DISTINCT ra.role_id, m.new_code, 'SYSTEM'
FROM tbl_role_access ra
JOIN tmp_code_map m ON m.old_code = ra.access_code;

DELETE ra FROM tbl_role_access ra
WHERE ra.access_code IN (SELECT old_code FROM tmp_code_map);

-- ---------------------------------------------------------------------
-- 3. Remap per-user grants (only active rows ever granted anything)
-- ---------------------------------------------------------------------
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT DISTINCT ua.username, m.new_code, 1, 'SYSTEM'
FROM tbl_user_access ua
JOIN tmp_code_map m ON m.old_code = ua.access_code
WHERE ua.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM tbl_user_access x
    WHERE x.username = ua.username AND x.access_code = m.new_code
  );

DELETE FROM tbl_user_access
WHERE access_code IN (SELECT old_code FROM tmp_code_map)
   OR is_active = 0;

-- ---------------------------------------------------------------------
-- 4. Replace the module catalog
-- ---------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_catalog;
CREATE TEMPORARY TABLE tmp_catalog (
  access_code varchar(10) NOT NULL,
  access_name varchar(100) NOT NULL
);

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
('35001','RCI - View'), ('35002','RCI - Add'), ('35003','RCI - Update'), ('35004','RCI - Delete');

DELETE FROM tbl_access_modules
WHERE access_code NOT IN (SELECT access_code FROM tmp_catalog);

UPDATE tbl_access_modules am
JOIN tmp_catalog c ON c.access_code = am.access_code
SET am.access_name = c.access_name;

INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM tmp_catalog c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- Drop any grant for a code that no longer exists (e.g. 2004, the old
-- ORS approval screen code - that screen now opens for Certify A/B)
DELETE FROM tbl_user_access
WHERE access_code NOT IN (SELECT access_code FROM tbl_access_modules);

DELETE FROM tbl_role_access
WHERE access_code NOT IN (SELECT access_code FROM tbl_access_modules);

-- ---------------------------------------------------------------------
-- 5. Re-sync users who have a role from that role's (remapped) codes,
--    so tbl_user_access matches exactly what the role grants
-- ---------------------------------------------------------------------
DELETE ua FROM tbl_user_access ua
JOIN myua_user u ON u.username = ua.username
WHERE u.role_id IS NOT NULL;

INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, ra.access_code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.is_active = 1
JOIN tbl_role_access ra ON ra.role_id = r.recid;

DROP TEMPORARY TABLE IF EXISTS tmp_code_map;
DROP TEMPORARY TABLE IF EXISTS tmp_catalog;
