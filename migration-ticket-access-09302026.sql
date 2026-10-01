-- =====================================================================
-- Support Tickets follow the module permissions like every other page.
-- Without any Support Tickets access the Tickets tab is hidden.
--
--   37001 Support Tickets - View    open the Tickets tab, see and answer your own tickets
--   37002 Support Tickets - Add     file a new ticket (new)
--   37003 Support Tickets - Update  support staff: see everyone's tickets, reply as support, change status
--
-- (Before this, everyone could file tickets and 37001 meant "see everyone's".)
-- No grants here - tick the codes per role in User Management.
-- Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local (pre-ticket-access-09302026).sql
-- =====================================================================

INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT '37002', 'Support Tickets - Add', 'SYSTEM'
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules WHERE access_code = '37002');
