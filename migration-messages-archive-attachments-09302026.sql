-- =====================================================================
-- Messages: archived chats + chat attachments (images and files).
--
--   tbl_conversation_member.is_archived - per person: archiving a chat
--       hides it from your chat list (it moves to the Archived section)
--       without touching anyone else's list. A new message in the chat
--       brings it back for everyone.
--   tbl_conversation_attachment - files sent in a chat, stored under
--       writable/uploads/chats/<year>/ and served only to members.
--
-- No new access codes (chatting is open to everyone).
-- Safe to re-run.
-- Backup: __db_backup/fods-local (pre-messages-archive-attachments-09302026).sql
-- =====================================================================

-- 1. Archive flag
ALTER TABLE tbl_conversation_member
  ADD COLUMN IF NOT EXISTS `is_archived` int(11) NOT NULL DEFAULT 0;

-- 2. Attachments
CREATE TABLE IF NOT EXISTS `tbl_conversation_attachment` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `msg_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `orig_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(100) NOT NULL DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT 0,
  `is_image` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `msg_id` (`msg_id`),
  KEY `conv_id` (`conv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
