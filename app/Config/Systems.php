<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * The systems of the IFAD Portal (Integrated Finance and Administrative
 * Division ERP), in the order of the FAD ERP functional modules. The portal
 * page shows one tile each, the header's system switcher lists them, and
 * ifad_can_open() decides who may open which.
 *
 * Keys per system:
 *   no     - the functional module number on the FAD ERP diagram
 *   area   - the functional area (tile heading)
 *   name   - the system's full name
 *   short  - short name for the sidebar, tiles and page titles
 *   tab    - (optional) label on the topbar system tab, when short is long
 *   icon   - Tabler icon class
 *   tone   - fods.css tone (teal, indigo, amber, green, slate, red)
 *   status - 'live' (can be opened) or 'soon' (not live yet; opens its preview page)
 *   stage  - (soon only) 'development' (default: being built here) or
 *            'integration' (an existing system the portal is connecting to,
 *            e.g. through its API); see ifad_stage() for the wording
 *   steps  - what the system covers, as on the diagram
 *   tip    - what the portal guide (Ifa) says about it
 *
 * Adding a system: an entry here, its access rule in ifad_can_open() and its
 * start page in ifad_home() (app/Helpers/ifad_helper.php), and - for its
 * sidebar - a menu in Config\Navigation with ifad_nav() reading it.
 */
class Systems extends BaseConfig
{
    public array $systems = [
        'fods' => [
            'no' => 1,
            'area' => 'Financial Management',
            'name' => 'Financial Obligation & Disbursement System',
            'short' => 'FODS',
            'icon' => 'ti-coin',
            'tone' => 'tone-green',
            'status' => 'live',
            'steps' => ['Create Obligation / Disbursement Request', 'Review and Approval', 'Budget Checking and Validation', 'Processing and Payment', 'Status Tracking and Financial Records'],
            'tip' => 'FODS handles the money side: budget allotments, ORS and BURS obligations, disbursement vouchers, LDDAP-ADA and procurement (PPMP, PR, PO).',
        ],
        'sorf' => [
            'no' => 2,
            'area' => 'Document & Records Management',
            'name' => 'Document Request System',
            'short' => 'SORF',
            'icon' => 'ti-file-description',
            'tone' => 'tone-indigo',
            'status' => 'live',
            'steps' => ['Submit Document Request', 'Review and Approval', 'Processing and Routing', 'Release and Notification', 'Status Tracking and Records Management'],
            'tip' => 'SORF is for special order and travel order requests: file one, follow its approval, and get the signed document.',
        ],
        'rfms' => [
            'no' => 3,
            'area' => 'Resource & Facilities Management',
            'name' => 'Vehicle Request Portal and Job Order / Repair Request System',
            'short' => 'Resource & Facilities',
            'tab' => 'Facilities',
            'icon' => 'ti-car',
            'tone' => 'tone-amber',
            'status' => 'soon',
            'steps' => ['Submit Request (Vehicle / Job Order)', 'Review and Approval', 'Scheduling / Assignment', 'Utilization / Service Implementation', 'Completion and Feedback', 'Status Tracking and Asset / Maintenance Records'],
            'tip' => 'Coming soon: book a vehicle or file a job order or repair request, then follow it until the work is done.',
        ],
        'qmis' => [
            'no' => 4,
            'area' => 'Quality Management',
            'name' => 'Quality Management Information System (QMIS)',
            'short' => 'QMIS',
            'icon' => 'ti-certificate',
            'tone' => 'tone-slate',
            'status' => 'soon',
            'steps' => ['Manage QMS Documents (Policies, Procedures, Forms)', 'Process and Compliance Monitoring', 'Internal Audit Management', 'Corrective and Preventive Action (CAPA)', 'Management Review', 'Quality Reports and Dashboards'],
            'tip' => 'Coming soon: QMIS for QMS documents, internal audits, corrective actions and management reviews.',
        ],
        'ihrmis' => [
            'no' => 5,
            'area' => 'Human Resource Management',
            'name' => 'Integrated Human Resource Management Information System (IHRMIS)',
            'short' => 'IHRMIS',
            'stage' => 'integration', // an existing HR system, reached through its API
            'icon' => 'ti-id-badge-2',
            'tone' => 'tone-red',
            'status' => 'soon',
            'steps' => ['Personnel Records and 201 Files', 'Recruitment, Selection and Plantilla', 'Attendance, Leave and Time Records', 'Payroll, Benefits and Deductions', 'Learning, Development and Performance (SPMS)', 'HR Reports and Certifications'],
            'tip' => 'IHRMIS, the HR system, is being connected to the portal, so personnel records, leave, payroll and performance will open here with this same sign-in.',
        ],
        'erp' => [
            'no' => 6,
            'area' => 'Integration & Management',
            'name' => 'ERP Integration Layer and Management Portal',
            // plain words for users; area and name stay as on the ERP diagram
            'short' => 'Admin & Oversight',
            'tab' => 'Admin',
            'icon' => 'ti-shield-check',
            'tone' => 'tone-teal',
            'status' => 'soon',
            'steps' => ['User Access and Role Management', 'Workflow Integration Across Modules', 'Centralized Data and Document Repository', 'Notifications and Alerts', 'Management Dashboards and Reports', 'Audit Trails and Monitoring'],
            'tip' => 'Coming soon: Admin & Oversight, where administrators manage access and workflows for every system, and chiefs see dashboards and audit trails across them.',
        ],
    ];

    // Pages every portal user shares (not part of one system): they keep the
    // sidebar of the system the user came from
    public array $shared = ['mymessages', 'myhelpdesk', 'myaccount', 'myassistant', 'mynotifications'];
}
