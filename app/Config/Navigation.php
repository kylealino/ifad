<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Sidebar menu, in display order. The header renders only the items the
 * signed-in user may open, so adding a module = add one entry here.
 *
 * Item keys:
 *   label  - menu text
 *   icon   - Tabler icon class
 *   route  - first URI segment (also used to highlight the active item)
 *   query  - query string appended to the link ('' for none)
 *   access - null (everyone) or one/several AccessCodes; any one grants it
 *   badge  - optional element id for a live counter
 */
class Navigation extends BaseConfig
{
    public array $groups = [
        [
            'label' => 'Home',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'route' => 'mydashboard', 'query' => '', 'access' => null],
            ],
        ],
        [
            'label' => 'Budget',
            'items' => [
                ['label' => 'Budget Allotment', 'icon' => 'ti-wallet', 'route' => 'mybudgetallotment', 'query' => 'meaction=MAIN', 'access' => AccessCodes::BUDGET_VIEW],
                ['label' => 'Budget Approval', 'icon' => 'ti-checklist', 'route' => 'mybudgetapproval', 'query' => '', 'access' => AccessCodes::BUDGETAPPROVAL_VIEW],
            ],
        ],
        [
            'label' => 'ORS',
            'items' => [
                ['label' => 'ORS Entry', 'icon' => 'ti-file-invoice', 'route' => 'myors', 'query' => 'meaction=MAIN', 'access' => AccessCodes::ORS_VIEW],
                ['label' => 'ORS Approval', 'icon' => 'ti-file-check', 'route' => 'myorsapproval', 'query' => '', 'access' => AccessCodes::ORSAPPROVAL_VIEW],
            ],
        ],
        [
            'label' => 'BURS',
            'items' => [
                ['label' => 'BURS Entry', 'icon' => 'ti-receipt', 'route' => 'myburs', 'query' => 'meaction=MAIN', 'access' => AccessCodes::BURS_VIEW],
                ['label' => 'BURS Approval', 'icon' => 'ti-receipt-2', 'route' => 'mybursapproval', 'query' => '', 'access' => AccessCodes::BURSAPPROVAL_VIEW],
            ],
        ],
        [
            'label' => 'Reports',
            'items' => [
                ['label' => 'SAOB', 'icon' => 'ti-report-analytics', 'route' => 'mysaobrpt', 'query' => 'meaction=MAIN', 'access' => AccessCodes::SAOB_VIEW],
            ],
        ],
        [
            'label' => 'Procurement',
            'items' => [
                ['label' => 'PPMP', 'icon' => 'ti-calendar-stats', 'route' => 'myppmp', 'query' => 'meaction=MAIN', 'access' => AccessCodes::PPMP_VIEW],
                ['label' => 'Purchase Request', 'icon' => 'ti-shopping-cart', 'route' => 'mypr', 'query' => 'meaction=PR-MAIN', 'access' => AccessCodes::PR_VIEW],
                ['label' => 'Abstract', 'icon' => 'ti-scale', 'route' => 'myabstract', 'query' => 'meaction=MAIN', 'access' => AccessCodes::ABSTRACT_VIEW],
            ],
        ],
        [
            'label' => 'Accounting',
            'items' => [
                ['label' => 'Disbursement', 'icon' => 'ti-file-dollar', 'route' => 'mydisbursement', 'query' => 'meaction=MAIN', 'access' => AccessCodes::DISBURSEMENT_VIEW],
                ['label' => 'Disbursement Approval', 'icon' => 'ti-file-check', 'route' => 'mydisbursementapproval', 'query' => '', 'access' => AccessCodes::DISBURSEMENTAPPROVAL_VIEW],
            ],
        ],
        [
            'label' => 'Cashier',
            'items' => [
                ['label' => 'LDDAP-ADA', 'icon' => 'ti-building-bank', 'route' => 'mylddapada', 'query' => 'meaction=MAIN', 'access' => AccessCodes::LDDAPADA_VIEW],
                ['label' => 'LDDAP-ADA Approval', 'icon' => 'ti-file-certificate', 'route' => 'mylddapadaapproval', 'query' => '', 'access' => AccessCodes::LDDAPADAAPPROVAL_VIEW],
                ['label' => 'ADA', 'icon' => 'ti-credit-card', 'route' => 'myada', 'query' => 'meaction=MAIN', 'access' => AccessCodes::ADA_VIEW],
                ['label' => 'ADA Approval', 'icon' => 'ti-checks', 'route' => 'myadaapproval', 'query' => '', 'access' => AccessCodes::ADAAPPROVAL_VIEW],
                ['label' => 'RCI', 'icon' => 'ti-receipt-2', 'route' => 'myrci', 'query' => 'meaction=MAIN', 'access' => AccessCodes::RCI_VIEW],
            ],
        ],
        [
            'label' => 'Maintenance',
            'items' => [
                ['label' => 'Payee', 'icon' => 'ti-user-check', 'route' => 'mypayee', 'query' => 'meaction=MAIN', 'access' => AccessCodes::PAYEE_VIEW],
                ['label' => 'Supplies', 'icon' => 'ti-package', 'route' => 'myproducts', 'query' => 'meaction=MAIN', 'access' => AccessCodes::SUPPLIES_VIEW],
                ['label' => 'Projects', 'icon' => 'ti-folders', 'route' => 'myproject', 'query' => 'meaction=MAIN', 'access' => AccessCodes::PROJECT_VIEW],
                ['label' => 'UACS', 'icon' => 'ti-list-numbers', 'route' => 'myuacs', 'query' => 'meaction=MAIN', 'access' => AccessCodes::UACS_VIEW],
            ],
        ],
        [
            'label' => 'Administration',
            'items' => [
                ['label' => 'User Management', 'icon' => 'ti-users', 'route' => 'myua', 'query' => 'meaction=MAIN', 'access' => AccessCodes::USERMGMT_VIEW],
            ],
        ],
    ];
}
