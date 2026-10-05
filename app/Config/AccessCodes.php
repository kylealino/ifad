<?php

namespace Config;

/**
 * Module access codes - the single source of truth for every permission
 * checked in the app. Must stay in sync with tbl_access_modules.
 *
 * Series: <family><action>
 *   family = 2-digit module number (20 Payee ... 35 RCI in sidebar order;
 *            36 Budget Approval, 37 Support Tickets and 38-42 the other
 *            approval pages were added later, so they're out of sequence —
 *            the permissions editor orders groups by the sidebar, not the number)
 *   action = 001 View, 002 Add, 003 Update, 004 Delete, 005 Approve
 *
 * Every approval page is its own module ("<Module> Approval": View opens
 * the page, Approve approves/disapproves), separate from the entry module.
 */
class AccessCodes
{
    // 20 - Payee
    public const PAYEE_VIEW   = '20001';
    public const PAYEE_ADD    = '20002';
    public const PAYEE_UPDATE = '20003';
    public const PAYEE_DELETE = '20004';

    // 21 - Supplies
    public const SUPPLIES_VIEW   = '21001';
    public const SUPPLIES_ADD    = '21002';
    public const SUPPLIES_UPDATE = '21003';
    public const SUPPLIES_DELETE = '21004';

    // 22 - Projects
    public const PROJECT_VIEW   = '22001';
    public const PROJECT_ADD    = '22002';
    public const PROJECT_UPDATE = '22003';
    public const PROJECT_DELETE = '22004';

    // 23 - UACS
    public const UACS_VIEW   = '23001';
    public const UACS_ADD    = '23002';
    public const UACS_UPDATE = '23003';
    public const UACS_DELETE = '23004';

    // 24 - User Management
    public const USERMGMT_VIEW   = '24001';
    public const USERMGMT_ADD    = '24002';
    public const USERMGMT_UPDATE = '24003';
    public const USERMGMT_DELETE = '24004';

    // 25 - Budget Allotment
    public const BUDGET_VIEW    = '25001';
    public const BUDGET_ADD     = '25002';
    public const BUDGET_UPDATE  = '25003';
    public const BUDGET_DELETE  = '25004';

    // 26 - ORS
    public const ORS_VIEW      = '26001';
    public const ORS_ADD       = '26002';
    public const ORS_UPDATE    = '26003';
    public const ORS_DELETE    = '26004';

    // 27 - BURS
    public const BURS_VIEW      = '27001';
    public const BURS_ADD       = '27002';
    public const BURS_UPDATE    = '27003';
    public const BURS_DELETE    = '27004';

    // 28 - SAOB
    public const SAOB_VIEW   = '28001';
    public const SAOB_ADD    = '28002';
    public const SAOB_UPDATE = '28003';
    public const SAOB_DELETE = '28004';

    // 29 - PPMP
    public const PPMP_VIEW   = '29001';
    public const PPMP_ADD    = '29002';
    public const PPMP_UPDATE = '29003';
    public const PPMP_DELETE = '29004';

    // 30 - Purchase Request
    public const PR_VIEW   = '30001';
    public const PR_ADD    = '30002';
    public const PR_UPDATE = '30003';
    public const PR_DELETE = '30004';

    // 31 - Abstract
    public const ABSTRACT_VIEW   = '31001';
    public const ABSTRACT_ADD    = '31002';
    public const ABSTRACT_UPDATE = '31003';
    public const ABSTRACT_DELETE = '31004';

    // 32 - Disbursement
    public const DISBURSEMENT_VIEW      = '32001';
    public const DISBURSEMENT_ADD       = '32002';
    public const DISBURSEMENT_UPDATE    = '32003';
    public const DISBURSEMENT_DELETE    = '32004';

    // 33 - LDDAP-ADA
    public const LDDAPADA_VIEW   = '33001';
    public const LDDAPADA_ADD    = '33002';
    public const LDDAPADA_UPDATE = '33003';
    public const LDDAPADA_DELETE = '33004';

    // 34 - ADA
    public const ADA_VIEW   = '34001';
    public const ADA_ADD    = '34002';
    public const ADA_UPDATE = '34003';
    public const ADA_DELETE = '34004';

    // 35 - RCI
    public const RCI_VIEW   = '35001';
    public const RCI_ADD    = '35002';
    public const RCI_UPDATE = '35003';
    public const RCI_DELETE = '35004';

    // 36 - Budget Approval (reviewing and approving/disapproving allotments)
    public const BUDGETAPPROVAL_VIEW    = '36001';
    public const BUDGETAPPROVAL_APPROVE = '36005';

    // 37 - Support Tickets (everyone can chat and file/follow their own tickets;
    //      these are for support staff)
    public const TICKET_VIEW   = '37001';   // open the Support desk: everyone's tickets
    public const TICKET_UPDATE = '37003';   // reply as support, change status

    // 38 - ORS Approval (one Approve covers Box A and Box B)
    public const ORSAPPROVAL_VIEW    = '38001';
    public const ORSAPPROVAL_APPROVE = '38005';

    // 39 - BURS Approval (one Approve covers Box A and Box B)
    public const BURSAPPROVAL_VIEW    = '39001';
    public const BURSAPPROVAL_APPROVE = '39005';

    // 40 - Disbursement Approval
    public const DISBURSEMENTAPPROVAL_VIEW    = '40001';
    public const DISBURSEMENTAPPROVAL_APPROVE = '40005';

    // 41 - LDDAP-ADA Approval
    public const LDDAPADAAPPROVAL_VIEW    = '41001';
    public const LDDAPADAAPPROVAL_APPROVE = '41005';

    // 42 - ADA Approval
    public const ADAAPPROVAL_VIEW    = '42001';
    public const ADAAPPROVAL_APPROVE = '42005';

    // 43 - Purchase Order (prepared from a PR)
    public const PO_VIEW   = '43001';
    public const PO_ADD    = '43002';
    public const PO_UPDATE = '43003';
    public const PO_DELETE = '43004';

    // 44 - PO Approval (Box A accounting, then Box B approval; each step is
    //      decided by the signatory named on it, or an Administrator)
    public const POAPPROVAL_VIEW    = '44001';
    public const POAPPROVAL_APPROVE = '44005';

    // 45 - PR Approval (Box A requesting office, then Box B approval; each
    //      step is decided by the signatory named on it, or an Administrator)
    public const PRAPPROVAL_VIEW    = '45001';
    public const PRAPPROVAL_APPROVE = '45005';
}
