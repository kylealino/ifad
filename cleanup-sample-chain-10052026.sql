-- Removes the sample PR -> RFQ -> PO -> ORS -> DV chain created on 2026-10-05.
-- Matches on the SAMPLE-* numbers only, so it is safe to re-run.
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;

DELETE FROM tbl_disbursement_history WHERE serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_disbursement_direct_ps_dt dt     JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE dt FROM tbl_disbursement_direct_mooe_dt dt   JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE dt FROM tbl_disbursement_direct_co_dt dt     JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE dt FROM tbl_disbursement_indirect_ps_dt dt   JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE dt FROM tbl_disbursement_indirect_mooe_dt dt JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE dt FROM tbl_disbursement_indirect_co_dt dt   JOIN tbl_disbursement_hd hd ON hd.recid = dt.project_id WHERE hd.dvno = 'SAMPLE-DV-2026-0001';
DELETE FROM tbl_disbursement_hd WHERE dvno = 'SAMPLE-DV-2026-0001';

DELETE dt FROM tbl_ors_direct_ps_dt dt     JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_ors_direct_mooe_dt dt   JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_ors_direct_co_dt dt     JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_ors_indirect_ps_dt dt   JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_ors_indirect_mooe_dt dt JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE dt FROM tbl_ors_indirect_co_dt dt   JOIN tbl_ors_hd hd ON hd.recid = dt.project_id WHERE hd.serialno = 'SAMPLE-ORS-2026-10-0001';
DELETE FROM tbl_ors_hd WHERE serialno = 'SAMPLE-ORS-2026-10-0001';

DELETE FROM tbl_po_dt WHERE pono = 'SAMPLE-PO-2026-0001';
DELETE FROM tbl_po_hd WHERE pono = 'SAMPLE-PO-2026-0001';

DELETE FROM tbl_pr_rfq WHERE prno = 'SAMPLE-PR-2026-0001';
DELETE dt FROM tbl_pr_dt dt JOIN tbl_pr_hd hd ON hd.recid = dt.pr_id WHERE hd.prno = 'SAMPLE-PR-2026-0001';
DELETE pp FROM tbl_pr_ppmp pp JOIN tbl_pr_hd hd ON hd.recid = pp.pr_id WHERE hd.prno = 'SAMPLE-PR-2026-0001';
DELETE FROM tbl_pr_hd WHERE prno = 'SAMPLE-PR-2026-0001';

DELETE FROM tbl_payee WHERE payee_name = 'SAMPLE OFFICE SUPPLIES TRADING';
