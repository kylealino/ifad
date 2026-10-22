-- LDDAP-ADA: the four printed signatories become choices on the LDDAP-ADA entry.
--   Certified Correct  -> certified_by / certified_position
--   Approved           -> approved_by / approved_position
--   Agency Authorized Signatory 1 -> ada_approver (existing) / ada_approver_position
--   Agency Authorized Signatory 2 -> ada_approver2 / ada_approver2_position
-- Records saved before this keep blank values and print the old fixed names.
-- Re-runnable; works on MariaDB and MySQL 8 (no ADD COLUMN IF NOT EXISTS).

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'certified_by') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `certified_by` varchar(150) NOT NULL DEFAULT '' AFTER `ada_approver`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'certified_position') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `certified_position` varchar(150) NOT NULL DEFAULT '' AFTER `certified_by`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'approved_by') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `approved_by` varchar(150) NOT NULL DEFAULT '' AFTER `certified_position`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'approved_position') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `approved_position` varchar(150) NOT NULL DEFAULT '' AFTER `approved_by`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'ada_approver_position') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `ada_approver_position` varchar(150) NOT NULL DEFAULT '' AFTER `approved_position`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'ada_approver2') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `ada_approver2` varchar(150) NOT NULL DEFAULT '' AFTER `ada_approver_position`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_lddapada_hd' AND COLUMN_NAME = 'ada_approver2_position') = 0,
  "ALTER TABLE `tbl_lddapada_hd` ADD COLUMN `ada_approver2_position` varchar(150) NOT NULL DEFAULT '' AFTER `ada_approver2`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

