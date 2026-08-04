<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = service('routes');

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
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

// The Auto Routing (Legacy) is very dangerous. It is easy to accidentally
// create routes that match the directories and controller names, so the
// legacy *auto* Routing is globally disabled by default.
// $routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// ============================================================
// DASHBOARD
// ============================================================
$routes->get('dashboard', 'Dashboard::index');

// ============================================================
// PERSONNEL
// ============================================================
$routes->get ('personnel',          'PersonnelController::index');
$routes->get ('personnel/drivers',   'PersonnelController::drivers');
$routes->get ('personnel/janitors',  'PersonnelController::janitors');
$routes->get ('personnel/carpentries','PersonnelController::carpentries');
$routes->get ('personnel/maintenance','PersonnelController::maintenance');
$routes->get ('personnel/construction-workers','PersonnelController::constructionWorkers');
$routes->post('personnel/add',       'PersonnelController::add');
$routes->post('personnel/add/',      'PersonnelController::add');
$routes->post('personnel/edit/(:num)','PersonnelController::edit/$1');
$routes->post('personnel/edit/(:num)/','PersonnelController::edit/$1');
$routes->post('personnel/delete/(:num)','PersonnelController::delete/$1');

// ============================================================
// TOOLS & EQUIPMENT
// ============================================================
$routes->get ('tools',                  'ToolsController::index');
$routes->get ('tools/electronic-devices','ToolsController::electronicDevices');
$routes->get ('tools/accessories',      'ToolsController::accessories');
$routes->get ('tools/equipment',        'ToolsController::toolsEquipment');
$routes->get ('tools/consumable',       'ToolsController::consumable');
$routes->post('tools/add',              'ToolsController::add');
$routes->post('tools/edit/(:num)',      'ToolsController::edit/$1');
$routes->post('tools/delete/(:num)',    'ToolsController::delete/$1');
$routes->post('tools/borrow/(:num)',    'ToolsController::borrow/$1');
$routes->post('tools/returnTool/(:num)','ToolsController::returnTool/$1');
$routes->post('tools/refillStock/(:num)','ToolsController::refillStock/$1');

// ============================================================
// VEHICLE MANAGEMENT
// ============================================================
$routes->get ('vehicles',              'VehicleController::index');
$routes->post('vehicles/add',          'VehicleController::add');
$routes->post('vehicles/edit/(:num)',  'VehicleController::edit/$1');
$routes->post('vehicles/delete/(:num)','VehicleController::delete/$1');

// ============================================================
// DRIVER'S TRIP TICKET
// ============================================================
$routes->get ('travel',                 'TravelController::index');
$routes->get ('travel/getTrip/(:num)',  'TravelController::getTrip/$1');
$routes->post('travel/add',             'TravelController::add');
$routes->post('travel/approve/(:num)',  'TravelController::approve/$1');
$routes->post('travel/reject/(:num)',   'TravelController::reject/$1');
$routes->post('travel/complete/(:num)', 'TravelController::complete/$1');
$routes->post('travel/delete/(:num)',   'TravelController::delete/$1');
$routes->post('travel/checkin/(:num)',  'TravelController::checkIn/$1');
$routes->post('travel/checkout/(:num)', 'TravelController::checkOut/$1');


// ============================================================
// GPS
// ============================================================
$routes->get ('gps',                     'GPSController::index');
$routes->get ('gps/getVehicle/(:num)',   'GPSController::getVehicle/$1');
$routes->get ('gps/sync/(:num)',         'GPSController::sync/$1');
$routes->post('gps/logPing',             'GPSController::logPing');

// ============================================================
// SAFETY MONITORING (Separated from Janitorial)
// ============================================================
$routes->get ('safety',                        'SafetyController::index');
$routes->get ('safety/guard-dashboard',        'SafetyController::guardDashboard');
$routes->get ('safety/keylogs',                'SafetyController::keylogs');
$routes->get ('safety/keylogs/lookup/(:any)',  'SafetyController::lookupBorrower/$1');
$routes->post('safety/keylogs/scan-borrow',    'SafetyController::scanBorrow');
$routes->post('safety/keylogs/scan-return',    'SafetyController::scanReturn');

// ============================================================
// JANITORIAL MONITORING (Separated from Safety)
// ============================================================
$routes->get('janitorial',            'JanitorialController::index');
$routes->get('janitorial/checklists', 'JanitorialController::checklists');
$routes->post('janitorial/refillInventory/(:num)', 'JanitorialController::refillInventory/$1');
$routes->post('janitorial/addInventoryItem',       'JanitorialController::addInventoryItem');

// ============================================================
// CALENDAR
// ============================================================
$routes->get ('calendar',                   'CalendarController::index');
$routes->post('calendar/scheduleCleaning',  'CalendarController::scheduleCleaning');
$routes->post('calendar/scheduleMaintenance','CalendarController::scheduleMaintenance');

// ============================================================
// NOTIFICATIONS
// ============================================================
$routes->get ('notifications',                 'NotificationController::index');
$routes->post('notifications/markAllRead',     'NotificationController::markAllRead');
$routes->get ('notifications/export',          'NotificationController::export');
$routes->post('notifications/markRead/(:num)', 'NotificationController::markRead/$1');
$routes->post('notifications/dismiss/(:num)',  'NotificationController::dismiss/$1');
$routes->post('notifications/action/(:num)',   'NotificationController::action/$1');
$routes->get ('notifications/unreadCount',     'NotificationController::unreadCount');

// ============================================================
// RECORDS, ARCHIVING & REPORTS
// ============================================================
$routes->get ('reports',                    'RecordsController::index');
$routes->post('reports/update',             'ReportController::update');
$routes->post('reports/update/(:num)',      'ReportController::update/$1');
$routes->get ('reports/chartData',          'ReportController::chartData');

// ============================================================
// MR. UBRA AI
// ============================================================
$routes->get ('ubra', 'UbraController::index');
$routes->post('ubra/chat', 'UbraController::chat');
$routes->post('ubra/quickAction', 'UbraController::quickAction');

// ============================================================
// SETTINGS
// ============================================================
$routes->get ('settings',                 'SettingsController::index');
$routes->post('settings/saveGeneral',     'SettingsController::saveGeneral');
$routes->post('settings/saveEmail',       'SettingsController::saveEmail');
$routes->post('settings/saveNotifications','SettingsController::saveNotifications');
$routes->post('settings/saveRoles',       'SettingsController::saveRoles');
$routes->post('settings/addUser',         'SettingsController::addUser');
$routes->post('settings/deleteUser/(:num)','SettingsController::deleteUser/$1');
$routes->get ('settings/getSetting/(:any)','SettingsController::getSetting/$1');

// ============================================================
// RECORDS, ARCHIVING & DISPOSAL
// ============================================================
$routes->get ('records', 'RecordsController::index');
$routes->post('records/markForDisposal/(:any)/(:num)', 'RecordsController::markForDisposal/$1/$2');
$routes->post('records/authorizeDisposal', 'RecordsController::authorizeDisposal');
$routes->get ('records/export/(:any)', 'RecordsController::exportReport/$1');

// ============================================================
// PROFILE
// ============================================================
$routes->get ('profile', 'ProfileController::index');
$routes->post('profile', 'ProfileController::index');
$routes->post('profile/updateProfile',    'ProfileController::updateProfile');
$routes->post('profile/changePassword',   'ProfileController::changePassword');
$routes->post('profile/change-password',  'ProfileController::changePassword');
$routes->post('profile/updateSettings',   'ProfileController::updateSettings');

// ============================================================
// MOBILE JSON API (FU-UBRA Expo app)
// ============================================================
$routes->group('api', function ($routes) {
    $routes->post('auth/login', 'Api::login');
    $routes->post('auth/scan-login', 'Api::scanLogin');

    $routes->get('tools', 'Api::tools');
    $routes->get('tools/categories', 'Api::toolCategories');
    $routes->get('tools/lookup/(:any)', 'Api::toolLookup/$1');
    $routes->post('tools/scan-borrow', 'Api::toolsScanBorrow');
    $routes->post('tools/scan-return', 'Api::toolsScanReturn');

    $routes->get('vehicles', 'Api::vehicles');
    $routes->get('vehicles/meta', 'Api::vehiclesMeta');
    $routes->post('vehicles', 'Api::addVehicle');
    $routes->get('trip-tickets/next', 'Api::nextTripTicket');
    $routes->post('trip-tickets/(:num)/scan-in', 'Api::tripScanIn/$1');
    $routes->post('trip-tickets/(:num)/scan-out', 'Api::tripScanOut/$1');

    $routes->get('safety/buildings', 'Api::safetyBuildings');
    $routes->get('safety/aircon', 'Api::safetyAircon');
    $routes->post('safety/aircon', 'Api::saveAirconUnit');
    $routes->post('safety/aircon/checklist/(:num)', 'Api::saveAirconChecklist/$1');
    $routes->post('safety/fire-extinguishers', 'Api::saveExtinguisher');

    $routes->get('janitorial/zones', 'Api::janitorialZones');
    $routes->get('janitorial/checklist/(:num)', 'Api::janitorialChecklist/$1');
    $routes->post('janitorial/checklist/(:num)', 'Api::saveJanitorialChecklist/$1');

    $routes->get('guard/keylog', 'Api::guardKeylog');
    $routes->post('guard/keylog/scan-borrow', 'Api::guardScanBorrow');
    $routes->post('guard/keylog/scan-return', 'Api::guardScanReturn');
    $routes->get('guard/trip-tickets/today', 'Api::guardTripTicketsToday');

    $routes->get('notifications', 'Api::notifications');
    $routes->get('notifications/unread-count', 'Api::notificationsUnreadCount');
    $routes->post('notifications/(:num)/mark-read', 'Api::markNotificationRead/$1');
});

// ============================================================
// AUTH
// ============================================================
$routes->get ('login',        'AuthController::login');
$routes->post('login',        'AuthController::attemptLogin');
$routes->post('auth/login',   'AuthController::attemptLogin');
$routes->get ('logout',       'AuthController::logout');
$routes->get ('auth/logout',  'AuthController::logout');

// Default redirect to dashboard
$routes->get('/', function() {
    return redirect()->to(base_url('dashboard'));
});

// ============================================================
// JSON API (for the Expo mobile app)
// ============================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('login', 'AuthController::login');

  $routes->group('', ['filter' => 'apiauth'], function ($routes) {
    $routes->post('logout', 'AuthController::logout');
    $routes->get('me', 'AuthController::me');

    $routes->get('dashboard', 'DashboardController::index');

    $routes->get('personnel', 'PersonnelController::index');
    $routes->get('personnel/lookup/(:any)', 'PersonnelController::lookup/$1');
    $routes->get('personnel/drivers', 'PersonnelController::drivers');
    $routes->get('personnel/janitors', 'PersonnelController::janitors');
    $routes->get('personnel/carpentries', 'PersonnelController::carpentries');
    $routes->get('personnel/construction-workers', 'PersonnelController::constructionWorkers');
    $routes->get('personnel/maintenance', 'PersonnelController::maintenance');
    $routes->post('personnel/add', 'PersonnelController::add');
    $routes->post('personnel/edit/(:num)', 'PersonnelController::edit/$1');
    $routes->post('personnel/delete/(:num)', 'PersonnelController::delete/$1');

    $routes->get('tools', 'ToolsController::index');
    $routes->post('tools/add', 'ToolsController::add');
    $routes->post('tools/edit/(:num)', 'ToolsController::edit/$1');
    $routes->post('tools/delete/(:num)', 'ToolsController::delete/$1');
    $routes->post('tools/borrow/(:num)', 'ToolsController::borrow/$1');
    $routes->post('tools/returnTool/(:num)', 'ToolsController::returnTool/$1');

    $routes->get('vehicles', 'VehicleController::index');
    $routes->post('vehicles/add', 'VehicleController::add');
    $routes->post('vehicles/edit/(:num)', 'VehicleController::edit/$1');
    $routes->post('vehicles/delete/(:num)', 'VehicleController::delete/$1');

    $routes->get('janitorial', 'JanitorialController::index');
    $routes->get('janitorial/checklists', 'JanitorialController::checklists');
    $routes->get('janitorial/my', 'JanitorialController::my');
    $routes->post('janitorial/tasks/(:num)/toggle', 'JanitorialController::toggleTask/$1');
    $routes->post('janitorial/refillInventory/(:num)', 'JanitorialController::refillInventory/$1');
    $routes->post('janitorial/addInventoryItem', 'JanitorialController::addInventoryItem');

    $routes->get('travel', 'TravelController::index');
    $routes->post('travel/add', 'TravelController::add');
    $routes->get('travel/today', 'TravelController::today');
    $routes->post('travel/(:num)/scanout', 'TravelController::scanOut/$1');
    $routes->post('travel/(:num)/scanin', 'TravelController::scanIn/$1');

    $routes->get('safety', 'SafetyController::index');
    $routes->post('safety/addExtinguisher', 'SafetyController::addExtinguisher');
    $routes->get('safety/guard-dashboard', 'SafetyController::guardDashboard');
    $routes->get('safety/keylogs', 'SafetyController::keylogs');

    $routes->get('aircon', 'AirconController::index');
    $routes->post('aircon/add', 'AirconController::add');

    $routes->get('guard/keylogs', 'GuardController::keylogs');
    $routes->post('guard/keyborrow', 'GuardController::keyBorrow');
    $routes->post('guard/keyreturn/(:num)', 'GuardController::keyReturn/$1');

    $routes->get('notifications', 'NotificationController::index');
    $routes->post('notifications/markAllRead', 'NotificationController::markAllRead');
    $routes->post('notifications/markRead/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/dismiss/(:num)', 'NotificationController::dismiss/$1');
    $routes->post('notifications/action/(:num)', 'NotificationController::action/$1');
    $routes->get('notifications/unreadCount', 'NotificationController::unreadCount');

    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/updateProfile', 'ProfileController::updateProfile');
    $routes->post('profile/changePassword', 'ProfileController::changePassword');
  });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Nothing
 * prevents you from adding additional route files or just adding
 * tables here, logically, the Routes class does not care.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}