-- =====================================================================
-- Role-Based Access Control (RBAC) migration
-- Adds tbl_roles / tbl_role_access and wires myua_user to a role.
-- tbl_user_access is KEPT and now acts as a synced cache: every existing
-- module's access check (SELECT ... FROM tbl_user_access WHERE username=
-- ... AND access_code=...) keeps working unchanged. The app regenerates
-- a user's tbl_user_access rows from their role whenever the role (or the
-- user's role assignment) changes.
--
-- Safe to re-run: existing users/roles/permissions are untouched if this
-- has already been applied (CREATE TABLE IF NOT EXISTS, guarded ALTERs).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Roles
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `is_active` int(11) NOT NULL DEFAULT 1,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  UNIQUE KEY `uq_tbl_roles_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 2. Role -> module-access mapping (replaces the old per-user checkbox
--    grid; a role is checked once, all its users inherit it)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_role_access` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `access_code` varchar(50) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  UNIQUE KEY `uq_tbl_role_access` (`role_id`,`access_code`),
  KEY `idx_tbl_role_access_role_id` (`role_id`),
  CONSTRAINT `fk_tbl_role_access_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`recid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 3. myua_user: add role_id, drop the plaintext password column
--    (hash_password, the SHA-512 hash, is untouched and keeps working)
-- ---------------------------------------------------------------------
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'role_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `myua_user` ADD COLUMN `role_id` int(11) NULL DEFAULT NULL AFTER `position`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND CONSTRAINT_NAME = 'fk_myua_user_role'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `myua_user` ADD CONSTRAINT `fk_myua_user_role` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`recid`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'hash_value'
);
SET @sql := IF(@col_exists > 0,
  'ALTER TABLE `myua_user` DROP COLUMN `hash_value`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 4. Starter roles, seeded from the existing tbl_access_modules catalog.
--    Fully editable afterwards from the User Management > Roles tab -
--    this just gives the admin a sensible starting point instead of an
--    empty list.
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
  AND NOT EXISTS (
    SELECT 1 FROM `tbl_role_access` ra WHERE ra.role_id = r.recid AND ra.access_code = m.access_code
  );

-- Family-scoped starter roles: map by the access_code's family prefix
-- (all codes are <family><3-digit suffix>, e.g. 1001-1005 = family "1")
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
WHERE NOT EXISTS (
  SELECT 1 FROM `tbl_role_access` ra WHERE ra.role_id = r.recid AND ra.access_code = m.access_code
);

-- Existing users keep logging in and keep whatever access they already
-- had in tbl_user_access (untouched). role_id is left NULL - the admin
-- assigns roles progressively from the new UI, which then syncs
-- tbl_user_access for that user from the role's permissions.
