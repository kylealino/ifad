-- Disbursement Box D (Approved for payment) becomes a choice on the DV, like Box A and C.
--   * tbl_disbursement_hd gets certified_d / position_d.
--   * When the columns are first added, every existing DV is filled with the Box D it
--     shows today (the active Approver Mapping dv/d row with the highest "from amount"
--     at or below its net amount), so older DVs keep the signatory they already have.
--     DVs no tier covered stay blank (as they print today). JACUS S. NACIS was never
--     an old tier, so he is left out even if he was mapped to Box D before this runs.
--   * JACUS S. NACIS is added to Approver Mapping dv/d. The box offers its active
--     people: ALEXIS M. ORTIZ, ATTY. LUCIEDEN G. RAZ and JACUS S. NACIS.
-- Re-runnable; works on MariaDB and MySQL 8 (no ADD COLUMN IF NOT EXISTS).
-- Take a backup dump first.

SET @new := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'certified_d') = 0;

SET @s := IF(@new,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `certified_d` varchar(100) NOT NULL DEFAULT '' AFTER `position_c`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_disbursement_hd' AND COLUMN_NAME = 'position_d') = 0,
  "ALTER TABLE `tbl_disbursement_hd` ADD COLUMN `position_d` varchar(100) NOT NULL DEFAULT '' AFTER `certified_d`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- existing DVs keep the Box D they show today (only on the run that adds the column,
-- before JACUS is mapped, so a re-run never changes them)
SET @s := IF(@new,
  "UPDATE `tbl_disbursement_hd` h
      SET h.`certified_d` = COALESCE((SELECT m.`full_name` FROM `tbl_approver_map` m
                                       WHERE m.`module` = 'dv' AND m.`box` = 'd' AND m.`is_active` = 1 AND m.`min_amount` <= h.`net_amount` AND LOWER(TRIM(m.`full_name`)) <> 'jacus s. nacis'
                                       ORDER BY m.`min_amount` DESC LIMIT 1), ''),
          h.`position_d`  = COALESCE((SELECT m.`position` FROM `tbl_approver_map` m
                                       WHERE m.`module` = 'dv' AND m.`box` = 'd' AND m.`is_active` = 1 AND m.`min_amount` <= h.`net_amount` AND LOWER(TRIM(m.`full_name`)) <> 'jacus s. nacis'
                                       ORDER BY m.`min_amount` DESC LIMIT 1), '')", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Box D choices: JACUS S. NACIS joins ORTIZ and RAZ (already mapped)
INSERT INTO `tbl_approver_map` (`module`, `box`, `full_name`, `position`, `min_amount`, `is_default`, `is_active`, `added_by`)
SELECT 'dv', 'd', 'JACUS S. NACIS', 'Director IV', 0, 0, 1, 'migration'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_approver_map` WHERE `module` = 'dv' AND `box` = 'd' AND LOWER(TRIM(`full_name`)) = 'jacus s. nacis');

-- Box D amount range: each person approves DVs whose net amount is from `min_amount`
-- up to `max_amount` (NULL = no upper limit). Set per person in Approver Mapping.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_approver_map' AND COLUMN_NAME = 'max_amount') = 0,
  "ALTER TABLE `tbl_approver_map` ADD COLUMN `max_amount` decimal(25,2) NULL DEFAULT NULL AFTER `min_amount`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
