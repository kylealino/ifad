-- =====================================================================
-- Record history for every module (Disbursement keeps its own
-- tbl_disbursement_history): who created, updated, resubmitted,
-- approved, returned or deleted a record, when, with remarks and the
-- header fields that changed. Written by MyHistoryModel::log, read through
-- myhistory?module=&recid= (MyHistory), which checks the module's View code.
--
--   module        ors, burs, budget, saob, ppmp, pr, po, abstract, lddapada,
--                 ada, rci, payee, supplies, project, uacs, user, role,
--                 approvermap (MyHistoryModel::MODULES)
--   record_id     the record's recid (approvermap: the person's name hash)
--   ref_no        its number at the time (serial no., PR no., ...)
--   action_type   CREATED, UPDATED, RESUBMITTED, APPROVED[_BOX_X],
--                 DISAPPROVED[_BOX_X], DELETED, or a module action (RFQ_ADDED, ...)
--   field_changes JSON [{field, old, new}] for updates
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- Backup: __db_backup/fods-local (pre-record-history-11092026).sql
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_record_history` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(30) NOT NULL DEFAULT '',
  `record_id` varchar(64) NOT NULL DEFAULT '',
  `ref_no` varchar(150) NOT NULL DEFAULT '',
  `action_type` varchar(50) NOT NULL DEFAULT '',
  `remarks` text DEFAULT NULL,
  `field_changes` text DEFAULT NULL,
  `action_by` varchar(50) NOT NULL DEFAULT '',
  `action_by_name` varchar(150) NOT NULL DEFAULT '',
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_record_history_record` (`module`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
