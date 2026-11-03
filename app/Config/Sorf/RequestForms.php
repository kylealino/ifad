<?php

namespace Config\Sorf;

use CodeIgniter\Config\BaseConfig;

/**
 * The request form behind each document type (tbl_document_type.document_id).
 * One definition drives the entry form, the request page and the printed PDF.
 *
 * fields: key   = tbl_request_dt column
 *         type  = staff (name with suggestions from tbl_employee, fills the
 *                 position beside it) | text | textarea | money
 *         w     = column width on the printed form, in % of the table
 *         merge = printed inside the column before it (no column of its own)
 */
class RequestForms extends BaseConfig
{
    // A request is signed by the requester, then by the "Noted by" signatory.
    // That second signature is what submits it to HR:
    //   For Approval -> (Returned ->) Pending -> For Compliance / Approved / Denied
    public const STATUS_FOR_APPROVAL = 'For Approval';   // waiting for the "Noted by" signature
    public const STATUS_RETURNED = 'Returned';           // sent back to the requester by the signatory
    public const STATUS_PENDING = 'Pending';             // both signatures in: with HR
    public const STATUS_COMPLIANCE = 'For Compliance';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_DENIED = 'Denied';

    public const STATUSES = [
        self::STATUS_FOR_APPROVAL, self::STATUS_RETURNED,
        self::STATUS_PENDING, self::STATUS_COMPLIANCE, self::STATUS_APPROVED, self::STATUS_DENIED,
    ];

    // What HR sees and sets: a request only reaches HR once its signatory approves it
    public const HR_STATUSES = [self::STATUS_PENDING, self::STATUS_COMPLIANCE, self::STATUS_APPROVED, self::STATUS_DENIED];

    // HR's decision is final; until then the requester may change the request.
    // A change clears the "Noted by" signature and sends it back for approval.
    public const LOCKED = [self::STATUS_APPROVED, self::STATUS_DENIED];

    public array $forms = [
        'SO01' => [
            'title' => 'Request for Authority to Receive Incentive',
            'layout' => 'table',
            'noted_label' => 'Noted by:',
            'note' => '* Please notify the HRMS of any changes in the request.',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 28],
                ['key' => 'position', 'label' => 'Designation', 'type' => 'text', 'w' => 16],
                ['key' => 'duration', 'label' => 'Duration', 'type' => 'text', 'w' => 19],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'w' => 14],
                ['key' => 'source_of_fund', 'label' => 'Source of Fund', 'type' => 'text', 'w' => 23],
            ],
        ],
        'SO02' => [
            'title' => 'Request for General Special Order',
            'layout' => 'table',
            'noted_label' => 'Noted by:',
            'note' => '* Please notify the HRMS of any changes in the request.',
            'fields' => [
                ['key' => 'subject', 'label' => 'Subject', 'type' => 'textarea', 'w' => 34],
                ['key' => 'staff_name', 'label' => 'Staff Involved', 'print_label' => "Staff Involved/\nPosition", 'type' => 'staff', 'w' => 24],
                ['key' => 'position', 'label' => 'Position', 'type' => 'text', 'merge' => true],
                ['key' => 'purpose', 'label' => 'Purpose', 'type' => 'textarea', 'w' => 23],
                ['key' => 'remarks', 'label' => 'Remarks', 'type' => 'text', 'w' => 19],
            ],
        ],
        'SO03' => [
            'title' => 'Request for Authority (Special Disbursing Officer)',
            'layout' => 'table',
            'noted_label' => 'Noted by:',
            'note' => '* Please notify the HRMS of any changes in the request.',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 32],
                ['key' => 'position', 'label' => 'Position', 'type' => 'text', 'w' => 13],
                ['key' => 'duration', 'label' => 'Duration', 'type' => 'text', 'w' => 17],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'w' => 16],
                ['key' => 'source_of_fund', 'label' => 'Source of Fund', 'type' => 'text', 'w' => 22],
            ],
        ],
        'SO04' => [
            'title' => 'Request for Authority (Petty Cash Fund Custodian)',
            'layout' => 'table',
            'noted_label' => 'Noted by:',
            'note' => '* Please notify the HRMS of any changes in the request.',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 37],
                ['key' => 'position', 'label' => 'Position', 'type' => 'text', 'w' => 21],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'w' => 19],
                ['key' => 'source_of_fund', 'label' => 'Source of Fund', 'type' => 'text', 'w' => 23],
            ],
        ],
        'SO05' => [
            'title' => 'Request for Special Order* (Attendance in the Seminar/Training/Conference/Etc.)',
            'layout' => 'table',
            'noted_label' => 'Noted by:',
            'note' => '* Please immediately notify the HRMS if there are changes in the S.O.',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 22],
                ['key' => 'position', 'label' => 'Designation', 'type' => 'text', 'w' => 22],
                ['key' => 'purpose', 'label' => 'Purpose', 'type' => 'textarea', 'w' => 18],
                ['key' => 'registration_fee', 'label' => 'Registration Fee', 'type' => 'money', 'w' => 16],
                ['key' => 'source_of_fund', 'label' => 'Source of Fund', 'type' => 'text', 'w' => 22],
            ],
        ],
        'SO06' => [
            'title' => 'Request for Local Travel Order',
            'layout' => 'lto',
            'noted_label' => 'Noted by: (Section/Division Chief)',
            'note' => '* Please, immediately notify the HRMS if there are changes in the LTO',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 34],
                ['key' => 'position', 'label' => 'Position', 'type' => 'text', 'w' => 33],
                ['key' => 'division_agency', 'label' => 'Division/Agency', 'type' => 'text', 'w' => 33],
            ],
        ],
        'SO07' => [
            'title' => 'Request for Special Order (For Honorarium/Incentive/Etc.)',
            'layout' => 'table',
            'noted_label' => 'Noted by: (Division Chief)',
            'note' => '* Please: Immediately notify the HRMS if there are changes in the S.O.',
            'fields' => [
                ['key' => 'staff_name', 'label' => 'Name', 'type' => 'staff', 'w' => 28],
                ['key' => 'position', 'label' => 'Designation', 'type' => 'text', 'w' => 16],
                ['key' => 'duration', 'label' => 'Duration', 'type' => 'text', 'w' => 14],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'w' => 13],
                ['key' => 'total_amount', 'label' => 'Total Amount', 'type' => 'money', 'w' => 15],
                ['key' => 'source_of_fund', 'label' => 'Source of Fund', 'type' => 'text', 'w' => 14],
            ],
        ],
    ];

    /**
     * Local Travel Order: "Travel Expenses to be Incurred", in the order the
     * form prints them. A group with a code has its own line; items are the
     * indented lines under it. Codes are tbl_request_lto_expense.expense_code.
     */
    public array $ltoExpenses = [
        ['label' => 'Actual', 'code' => 'actual', 'items' => []],
        ['label' => 'Per Diem', 'code' => '', 'items' => [
            'accommodation' => 'Accommodation',
            'meals' => 'Meals/Food',
            'incidental' => 'Incidental',
        ]],
        ['label' => 'Transportation', 'code' => '', 'items' => [
            'official_vehicle' => 'Official Vehicle',
            'public_conveyance' => 'Public conveyance (Airplane, Bus, Taxi)',
        ]],
        ['label' => 'Others', 'code' => 'others', 'items' => []],
    ];

    /**
     * Every expense_code the Local Travel Order accepts.
     *
     * @return list<string>
     */
    public function ltoExpenseCodes(): array
    {
        $codes = [];
        foreach ($this->ltoExpenses as $group) {
            if ($group['code'] !== '') {
                $codes[] = $group['code'];
            }
            foreach (array_keys($group['items']) as $code) {
                $codes[] = $code;
            }
        }
        return $codes;
    }
}
