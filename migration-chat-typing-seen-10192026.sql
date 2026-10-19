-- =====================================================================
-- Messages: "is typing..." and "Seen" in chats.
--
-- tbl_conversation_member (one row per person per chat) gets:
--   typing_at     when that person last typed in the chat; they show as
--                 typing to the others for a few seconds after it. Cleared
--                 when they send or clear the box.
--   last_read_at  when their last_read_id last moved forward, i.e. when they
--                 saw the latest message they have read ("Seen 2:31 PM").
--                 Rows that already have reads keep NULL until the next read.
--
-- No data changes. Runs on both MariaDB (local) and MySQL 8 (staging).
-- Safe to re-run. Deploy together with the code.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_conversation_member' AND COLUMN_NAME = 'typing_at') = 0,
  "ALTER TABLE `tbl_conversation_member` ADD COLUMN `typing_at` datetime NULL DEFAULT NULL", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_conversation_member' AND COLUMN_NAME = 'last_read_at') = 0,
  "ALTER TABLE `tbl_conversation_member` ADD COLUMN `last_read_at` datetime NULL DEFAULT NULL", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
