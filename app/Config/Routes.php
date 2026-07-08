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

//SAOB - SAOB module
$routes->get('mysaobrpt', 'MySaobReport::index',['filter' => 'myauthuser']);
$routes->post('mysaobrpt', 'MySaobReport::index',['filter' => 'myauthuser']);

//PR - PR module
$routes->get('mypr', 'MyPR::index',['filter' => 'myauthuser']);
$routes->post('mypr', 'MyPR::index',['filter' => 'myauthuser']);

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

//Cashier
//LDDAP-ADA - LDDAP-ADA module
$routes->get('mylddapada', 'MyLDDAPADA::index',['filter' => 'myauthuser']);
$routes->post('mylddapada', 'MyLDDAPADA::index',['filter' => 'myauthuser']);

//RCI - RCI module
$routes->get('myrci', 'MyRCI::index',['filter' => 'myauthuser']);
$routes->post('myrci', 'MyRCI::index',['filter' => 'myauthuser']);
