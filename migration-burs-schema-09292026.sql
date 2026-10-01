-- =====================================================================
-- BURS tables made identical to the ORS tables (only the date column
-- keeps its own name: burs_date <-> ors_date). Column types, defaults
-- and positions copied from tbl_ors_*. Run once; the BURS tables were
-- empty when this was written.
-- Backup: __db_backup/fods-local burs tables (pre-burs-schema-09292026).sql
-- =====================================================================

-- Header: tax/deduction fields, amounts, fund cluster, DV no.
ALTER TABLE tbl_burs_hd
  ADD COLUMN `ip_address` varchar(100) NOT NULL AFTER `burs_date`,
  ADD COLUMN `is_vatable` int(11) NOT NULL DEFAULT 0 AFTER `ip_address`,
  ADD COLUMN `vat_percent` int(11) NOT NULL DEFAULT 0 AFTER `is_vatable`,
  ADD COLUMN `ewt_percent` int(11) NOT NULL DEFAULT 0 AFTER `vat_percent`,
  ADD COLUMN `pt_percent` int(11) NOT NULL DEFAULT 0 AFTER `ewt_percent`,
  ADD COLUMN `other_deductions` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pt_percent`,
  ADD COLUMN `deduction_name` varchar(100) NOT NULL DEFAULT '' AFTER `other_deductions`,
  ADD COLUMN `sss` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `deduction_name`,
  ADD COLUMN `hdmf` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `sss`,
  ADD COLUMN `pagibig_mp2` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `hdmf`,
  ADD COLUMN `pagibig_loan` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pagibig_mp2`,
  ADD COLUMN `hmo` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `pagibig_loan`,
  ADD COLUMN `wtax` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `hmo`,
  ADD COLUMN `income_tax` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `wtax`,
  ADD COLUMN `category` varchar(50) NOT NULL DEFAULT '' AFTER `income_tax`,
  ADD COLUMN `gross_amount` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `category`,
  ADD COLUMN `total_deduction` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `gross_amount`,
  ADD COLUMN `net_amount` decimal(25,2) NOT NULL DEFAULT 0.00 AFTER `total_deduction`,
  ADD COLUMN `fund_cluster_code` varchar(50) NOT NULL AFTER `net_amount`,
  ADD COLUMN `dvno` varchar(100) NOT NULL AFTER `fund_cluster_code`,
  ADD UNIQUE KEY `idx1` (`recid`);

-- Line items: program title, in the same position as the ORS tables
ALTER TABLE tbl_burs_direct_ps_dt     ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_id`;
ALTER TABLE tbl_burs_indirect_ps_dt   ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_title`;
ALTER TABLE tbl_burs_direct_mooe_dt   ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_title`;
ALTER TABLE tbl_burs_indirect_mooe_dt ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_title`;
ALTER TABLE tbl_burs_direct_co_dt     ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_title`;
ALTER TABLE tbl_burs_indirect_co_dt   ADD COLUMN `program_title` varchar(250) NOT NULL AFTER `project_title`;
