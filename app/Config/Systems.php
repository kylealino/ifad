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
 *   short  - short name for the switcher, sidebar and page titles
 *   icon   - Tabler icon class
 *   tone   - fods.css tone (teal, indigo, amber, green, slate, red)
 *   status - 'live' (can be opened) or 'soon' (shown as coming soon)
 *   steps  - what the system covers, as on the diagram
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
        ],
        'rfms' => [
            'no' => 3,
            'area' => 'Resource & Facilities Management',
            'name' => 'Vehicle Request Portal and Job Order / Repair Request System',
            'short' => 'Resource & Facilities',
            'icon' => 'ti-car',
            'tone' => 'tone-amber',
            'status' => 'soon',
            'steps' => ['Submit Request (Vehicle / Job Order)', 'Review and Approval', 'Scheduling / Assignment', 'Utilization / Service Implementation', 'Completion and Feedback', 'Status Tracking and Asset / Maintenance Records'],
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
        ],
        'erp' => [
            'no' => 5,
            'area' => 'Integration & Management',
            'name' => 'ERP Integration Layer and Management Portal',
            'short' => 'Integration & Management',
            'icon' => 'ti-affiliate',
            'tone' => 'tone-teal',
            'status' => 'soon',
            'steps' => ['User Access and Role Management', 'Workflow Integration Across Modules', 'Centralized Data and Document Repository', 'Notifications and Alerts', 'Management Dashboards and Reports', 'Audit Trails and Monitoring'],
        ],
    ];

    // Pages every portal user shares (not part of one system): they keep the
    // sidebar of the system the user came from
    public array $shared = ['mymessages', 'myhelpdesk', 'myaccount', 'myassistant'];
}
