-- =====================================================================
-- Index on tbl_user_access (username, access_code).
--
-- Every page's access check reads tbl_user_access by username, and saving
-- permissions in User Management deletes a user's rows by username. With
-- only the recid primary key both scanned (and on save, locked) the whole
-- table each time, which made saving permissions slow on staging.
--
-- No data changes. Runs on both MariaDB (local) and MySQL 8 (staging).
-- Safe to re-run. Deploy together with the MyUserManagementModel change.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_user_access' AND INDEX_NAME = 'idx_tbl_user_access_username') = 0,
  "ALTER TABLE `tbl_user_access` ADD INDEX `idx_tbl_user_access_username` (`username`, `access_code`)", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
