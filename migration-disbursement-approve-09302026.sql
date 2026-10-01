-- =====================================================================
-- Disbursement Approval becomes a single accounting approval.
--
--   32005 Disbursement - Certify A  ->  Disbursement - Approve
--         (the accounting approver; the same people keep it)
--   32006 Disbursement - Certify B      (retired: no second step)
--
-- The approval keeps using the is_approved_certa / is_disapproved_certa /
-- certa_* columns of tbl_disbursement_hd, so DVs already approved stay
-- approved. Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local access tables (pre-disbursement-approve-09302026).sql
-- =====================================================================

UPDATE tbl_access_modules SET access_name = 'Disbursement - Approve' WHERE access_code = '32005';

DELETE FROM tbl_role_access WHERE access_code = '32006';
DELETE FROM tbl_user_access WHERE access_code = '32006';
DELETE FROM tbl_access_modules WHERE access_code = '32006';
