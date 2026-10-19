-- =====================================================================
-- FODS Assistant (AI chat over FODS data, using Groq).
--
-- Everyone signed in can use it: there is no access code for it. What it can
-- look up still follows each user's own module access (ORS, BURS,
-- Disbursement, Payee, PR, PO and the approval pages), and every lookup is
-- read-only.
--
--   1. Removes the "FODS Assistant - View" (46001) permission if an earlier
--      version of this migration added it, so it no longer shows in
--      User Management. Family 46 is free again.
--   2. tbl_assistant_log: an audit trail of who asked what and which data
--      the assistant looked up to answer (the answers are not stored).
--
-- The Groq key and models live in .env (GROQ_API_KEY, GROQ_MODEL,
-- GROQ_FALLBACK_MODEL), never in the code or the database.
--
-- Runs on both MariaDB (local) and MySQL 8 (staging). Safe to re-run.
-- Deploy together with the code.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

-- 1. No permission for the assistant
DELETE FROM tbl_user_access WHERE access_code = '46001';
DELETE FROM tbl_role_access WHERE access_code = '46001';
DELETE FROM tbl_access_modules WHERE access_code = '46001';

-- 2. Audit trail
CREATE TABLE IF NOT EXISTS `tbl_assistant_log` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `question` text NOT NULL,
  `tools_used` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `model` varchar(100) NOT NULL DEFAULT '',
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recid`),
  KEY `idx_tbl_assistant_log_user` (`username`, `added_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
