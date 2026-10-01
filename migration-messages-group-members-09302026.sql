-- =====================================================================
-- Messages: group chat members can be removed by the person who created
-- the group. The removal shows in the chat as a note, so chat messages
-- get the same is_system flag ticket posts have.
--
--   tbl_conversation_msg.is_system - 1 = a note ("removed X from the group")
--
-- No new access codes. Safe to re-run.
-- Backup: __db_backup/fods-local (pre-group-members-09302026).sql
-- =====================================================================

ALTER TABLE tbl_conversation_msg
  ADD COLUMN IF NOT EXISTS `is_system` int(11) NOT NULL DEFAULT 0;
