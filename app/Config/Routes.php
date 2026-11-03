<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
$routes->add('mylogin-auth', 'MyLogIn::auth');
$routes->add('mylogout', 'MyLogIn::logout');
$routes->get('mydashboard', 'MyDashboard::index',['filter' => 'myauthuser']);
$routes->get('dashboard/saob-session-data', 'MyDashboard::getSaobSessionData');
$routes->get('myaccount', 'MyAccount::index',['filter' => 'myauthuser']);
$routes->get('mydashboarddev', 'MyDashboardDev::index',['filter' => 'myauthuser']);

//User Management - User module
$routes->get('myua', 'MyUserManagement::index',['filter' => 'myauthuser']);
$routes->post('myua', 'MyUserManagement::index',['filter' => 'myauthuser']);

//User Management - User module
$routes->get('myaccount', 'MyUserAccount::index',['filter' => 'myauthuser']);
$routes->post('myaccount', 'MyUserAccount::index',['filter' => 'myauthuser']);

//Data-entity - Autocomplete data hovers
$routes->post('mydata-entity', 'MyDataEntity::index',['filter' => 'myauthuser']);
$routes->get('mydata-entity', 'MyDataEntity::index',['filter' => 'myauthuser']);

//Maintenance - Payee module
$routes->get('mypayee', 'MyPayee::index',['filter' => 'myauthuser']);
$routes->post('mypayee', 'MyPayee::index',['filter' => 'myauthuser']);

//Maintenance - Projects module
$routes->get('myproject', 'MyProject::index',['filter' => 'myauthuser']);
$routes->post('myproject', 'MyProject::index',['filter' => 'myauthuser']);

//Maintenance - UACS module
$routes->get('myuacs', 'MyUACS::index',['filter' => 'myauthuser']);
$routes->post('myuacs', 'MyUACS::index',['filter' => 'myauthuser']);

//Budget - Budget Allotment module
$routes->get('mybudgetallotment', 'MyBudgetAllotment::index',['filter' => 'myauthuser']);
$routes->post('mybudgetallotment', 'MyBudgetAllotment::index',['filter' => 'myauthuser']);

$routes->get('mybudgetapproval', 'MyBudgetApproval::index',['filter' => 'myauthuser']);
$routes->post('mybudgetapproval', 'MyBudgetApproval::index',['filter' => 'myauthuser']);

//File uploading
$routes->post('uploadFile', 'Upload::index',['filter' => 'myauthuser']);

//ORS - ORS module
$routes->get('myors', 'MyOrs::index',['filter' => 'myauthuser']);
$routes->post('myors', 'MyOrs::index',['filter' => 'myauthuser']);

//ORS - ORS APPROVAL module
$routes->get('myorsapproval', 'MyOrsApproval::index',['filter' => 'myauthuser']);
$routes->post('myorsapproval', 'MyOrsApproval::index',['filter' => 'myauthuser']);

//BURS - BURS module
$routes->get('myburs', 'MyBurs::index',['filter' => 'myauthuser']);
$routes->post('myburs', 'MyBurs::index',['filter' => 'myauthuser']);

//BURS APPROVAL - BURS certification (Box A / Box B)
$routes->get('mybursapproval', 'MyBursApproval::index',['filter' => 'myauthuser']);
$routes->post('mybursapproval', 'MyBursApproval::index',['filter' => 'myauthuser']);

//SAOB - SAOB module
$routes->get('mysaobrpt', 'MySaobReport::index',['filter' => 'myauthuser']);
$routes->post('mysaobrpt', 'MySaobReport::index',['filter' => 'myauthuser']);

//PR - PR module
$routes->get('mypr', 'MyPR::index',['filter' => 'myauthuser']);
$routes->post('mypr', 'MyPR::index',['filter' => 'myauthuser']);

//PR APPROVAL - Box A requesting office / Box B approval
$routes->get('myprapproval', 'MyPRApproval::index',['filter' => 'myauthuser']);
$routes->post('myprapproval', 'MyPRApproval::index',['filter' => 'myauthuser']);

//PO - Purchase Order module (prepared from a PR)
$routes->get('mypo', 'MyPO::index',['filter' => 'myauthuser']);
$routes->post('mypo', 'MyPO::index',['filter' => 'myauthuser']);

//PO APPROVAL - Box A accounting / Box B approval
$routes->get('mypoapproval', 'MyPOApproval::index',['filter' => 'myauthuser']);
$routes->post('mypoapproval', 'MyPOApproval::index',['filter' => 'myauthuser']);

//PRODUCTS - PRODUCTS module
$routes->get('myproducts', 'MyProducts::index',['filter' => 'myauthuser']);
$routes->post('myproducts', 'MyProducts::index',['filter' => 'myauthuser']);

//ABSTRACT - ABSTRACT module
$routes->get('myabstract', 'MyAbstract::index',['filter' => 'myauthuser']);
$routes->post('myabstract', 'MyAbstract::index',['filter' => 'myauthuser']);

//PPMP - PPMP module
$routes->get('myppmp', 'MyPPMP::index',['filter' => 'myauthuser']);
$routes->post('myppmp', 'MyPPMP::index',['filter' => 'myauthuser']);

//REPORTS
$routes->get('export-csv', 'MySaobReport::exportCsv');
$routes->get('monthly-export-csv', 'MySaobReport::monthlyExportCsv');
$routes->get('saob-export-csv', 'MySaobReport::saobExportCsv');

//ACCOUNTING
//DISBURSEMENT - DISBURSEMENT module
$routes->get('mydisbursement', 'MyDisbursement::index',['filter' => 'myauthuser']);
$routes->post('mydisbursement', 'MyDisbursement::index',['filter' => 'myauthuser']);

//DISBURSEMENT APPROVAL - DV certification (Box A / accounting)
$routes->get('mydisbursementapproval', 'MyDisbursementApproval::index',['filter' => 'myauthuser']);
$routes->post('mydisbursementapproval', 'MyDisbursementApproval::index',['filter' => 'myauthuser']);

//Cashier
//LDDAP-ADA - LDDAP-ADA module
$routes->get('mylddapada', 'MyLDDAPADA::index',['filter' => 'myauthuser']);
$routes->post('mylddapada', 'MyLDDAPADA::index',['filter' => 'myauthuser']);

//LDDAP-ADA APPROVAL
$routes->get('mylddapadaapproval', 'MyLDDAPADAApproval::index',['filter' => 'myauthuser']);
$routes->post('mylddapadaapproval', 'MyLDDAPADAApproval::index',['filter' => 'myauthuser']);

//RCI - RCI module
$routes->get('myrci', 'MyRCI::index',['filter' => 'myauthuser']);
$routes->post('myrci', 'MyRCI::index',['filter' => 'myauthuser']);

//MESSAGES - chats between users (topbar messages icon)
$routes->get('mymessages', 'MyMessages::index',['filter' => 'myauthuser']);
$routes->post('mymessages', 'MyMessages::index',['filter' => 'myauthuser']);

//ASSISTANT - FODS Assistant: AI chat over FODS data (read-only, follows the user's access)
$routes->get('myassistant', 'MyAssistant::index',['filter' => 'myauthuser']);
$routes->post('myassistant', 'MyAssistant::index',['filter' => 'myauthuser']);

//HELPDESK - support tickets (topbar helpdesk icon, beside messages)
$routes->get('myhelpdesk', 'MyHelpdesk::index',['filter' => 'myauthuser']);
$routes->post('myhelpdesk', 'MyHelpdesk::index',['filter' => 'myauthuser']);

// SUPPORT - Support Chat System (old, replaced by mymessages)
$routes->get('support', 'Support::index', ['filter' => 'myauthuser']);
$routes->post('support', 'Support::index', ['filter' => 'myauthuser']);

//Cashier
//ADA - ADA module
$routes->get('myada', 'MyADA::index',['filter' => 'myauthuser']);
$routes->post('myada', 'MyADA::index',['filter' => 'myauthuser']);

//ADA APPROVAL
$routes->get('myadaapproval', 'MyADAApproval::index',['filter' => 'myauthuser']);
$routes->post('myadaapproval', 'MyADAApproval::index',['filter' => 'myauthuser']);
//IFAD PORTAL - the systems a user may open (FODS, SORF, more to come)
$routes->get('portal', 'Portal::index',['filter' => 'myauthuser']);

//IFAD PORTAL - SYSTEM 2: DOCUMENT REQUEST SYSTEM (SORF). Its pages live under
// sorf/, need a SORF role (sorfaccess) and are CSRF-protected (Config\Filters)
$routes->group('sorf', ['namespace' => 'App\Controllers\Sorf', 'filter' => ['myauthuser', 'sorfaccess']], static function ($routes) {
    $routes->get('/', 'SorfHome::index');
    foreach (['myrequest' => 'MyRequest', 'myapproval' => 'MyApproval', 'myhrrequest' => 'MyHRRequest', 'myhrdoc' => 'MyHRDocument', 'myroles' => 'MyRoles'] as $path => $controller) {
        $routes->get($path, $controller . '::index');
        $routes->post($path, $controller . '::index');
    }
});
