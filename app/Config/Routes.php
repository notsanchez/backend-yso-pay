<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->group("api", ["namespace" => "App\Controllers\Api"], function ($routes) {

    $routes->group("auth", function ($routes) {

        $routes->post("open-new-account", "GeneralUserController::openNewAccount");
        $routes->post("login", "GeneralUserController::userAccountLogin");

    });

    $routes->group("account", function ($routes) {

        $routes->get("account-details", "GeneralUserController::userAccountDetails");
        $routes->get("dashboard-account-details", "GeneralUserController::dashboardAccountDetails");
        $routes->get("search-user/(:any)", "GeneralUserController::searchUser/$1");

    });

    $routes->group("transactions", function ($routes) {

        $routes->post("transact-to-user", "GeneralTransactionController::transactToUser");
        $routes->get("all-transactions", "GeneralTransactionController::userTransactions");
        $routes->get("transaction-details/(:any)", "GeneralTransactionController::transactionDetails/$1");

    });

    $routes->group("cards", function ($routes) {

        $routes->post("request-new-card", "GeneralCardsController::requestNewCard");
        $routes->get("show-user-cards", "GeneralCardsController::getAllCardsForUser");
        $routes->get("card-details/(:any)", "GeneralCardsController::cardDetails/$1");

    });

    $routes->group("friends", function ($routes) {

        $routes->post("send-friend-request", "GeneralFriendListController::sendFriendRequest");
        $routes->post("accept-friend-request/(:any)", "GeneralFriendListController::accepFriendRequest/$1");
        $routes->get("show-invites", "GeneralFriendListController::showInvites");
        $routes->get("show-friends", "GeneralFriendListController::showFriends");

    });

    $routes->group("payment", function ($routes) {

        $routes->post("request-payment", "GeneralPaymentsController::requestPayment");
        $routes->get("payment-details/(:any)", "GeneralPaymentsController::paymentDetails/$1");
        $routes->get("pay-payment-link/(:any)", "GeneralPaymentsController::payPaymentLink/$1");
        $routes->post("refound-payment/(:any)", "GeneralPaymentsController::refoundPayment/$1");

    });

    $routes->group("investiments", function ($routes) {

        $routes->post("create-investiment", "GeneralInvestimentsController::createInvestiment");
        $routes->get("show-all-investiments-type", "GeneralInvestimentsController::showInvestimentsType");
        $routes->get("investiment-type/(:any)", "GeneralInvestimentsController::investimentType/$1");
        $routes->post("create-investiment-order/(:any)", "GeneralInvestimentsController::createInvestimentOrder/$1");
        $routes->get("investiment-orders", "GeneralInvestimentsController::investimentOrders");
        $routes->get("investiment-order-details/(:any)", "GeneralInvestimentsController::singleInvestimentOrderDetails/$1");
        $routes->get("withdrawal-order/(:any)", "GeneralInvestimentsController::withdrawalOrder/$1");

    });

});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
