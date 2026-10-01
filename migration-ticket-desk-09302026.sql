-- =====================================================================
-- Tickets: everyone files and follows their own tickets (as a conversation);
-- support staff get the Support desk (dashboard of every ticket).
--
--   37001 Support Tickets - View    opens the Support desk (everyone's tickets)
--   37002 Support Tickets - Add     (removed - everyone can file a ticket)
--   37003 Support Tickets - Update  reply as support and change status
--
-- Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local (pre-ticket-desk-09302026).sql
-- =====================================================================

DELETE FROM tbl_role_access WHERE access_code = '37002';
DELETE FROM tbl_user_access WHERE access_code = '37002';
DELETE FROM tbl_access_modules WHERE access_code = '37002';
