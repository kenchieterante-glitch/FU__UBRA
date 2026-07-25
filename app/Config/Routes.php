<?php
/**
 * FU-UBRA Routes
 * File: app/Config/Routes.php
 *
 * PASTE these route definitions inside your existing Routes.php
 * inside the section where you define application routes.
 * (Below the default CodeIgniter route boilerplate.)
 *
 * Example structure in your Routes.php:
 *   $routes->get('/', 'Home::index');
 *   // ---- PASTE BELOW ----
 */

// ============================================================
// DASHBOARD
// ============================================================
$routes->get('dashboard', 'DashboardController::index');

// ============================================================
// PERSONNEL
// ============================================================
$routes->get ('personnel',          'PersonnelController::index');
$routes->get ('personnel/drivers',   'PersonnelController::drivers');
$routes->get ('personnel/janitors',  'PersonnelController::janitors');
$routes->get ('personnel/carpentries','PersonnelController::carpentries');
$routes->get ('personnel/maintenance','PersonnelController::maintenance');
$routes->post('personnel/add',       'PersonnelController::add');
$routes->post('personnel/add/',      'PersonnelController::add');
$routes->post('personnel/edit/(:num)','PersonnelController::edit/$1');
$routes->post('personnel/edit/(:num)/','PersonnelController::edit/$1');
$routes->get ('personnel/delete/(:num)','PersonnelController::delete/$1');

// ============================================================
// TOOLS & EQUIPMENT
// ============================================================
$routes->get ('tools',                  'ToolsController::index');
$routes->post('tools/add',              'ToolsController::add');
$routes->post('tools/edit/(:num)',      'ToolsController::edit/$1');
$routes->get ('tools/delete/(:num)',    'ToolsController::delete/$1');
$routes->post('tools/borrow/(:num)',    'ToolsController::borrow/$1');
$routes->post('tools/returnTool/(:num)','ToolsController::returnTool/$1');

// ============================================================
// VEHICLE MANAGEMENT
// ============================================================
$routes->get ('vehicles',              'VehicleController::index');
$routes->post('vehicles/add',          'VehicleController::add');
$routes->post('vehicles/edit/(:num)',  'VehicleController::edit/$1');
$routes->get ('vehicles/delete/(:num)','VehicleController::delete/$1');

// ============================================================
// TRAVEL MANAGEMENT + GPS (COMBINED)
// GPS Tracker remains available from the Travel section.
// ============================================================
$routes->get ('travel',                  'TravelController::index');
$routes->post('travel/add',              'TravelController::add');
$routes->post('travel/approve/(:num)',   'TravelController::approve/$1');
$routes->post('travel/reject/(:num)',    'TravelController::reject/$1');
$routes->post('travel/complete/(:num)',  'TravelController::complete/$1');
$routes->post('travel/edit/(:num)',      'TravelController::edit/$1');
$routes->get ('travel/delete/(:num)',    'TravelController::delete/$1');
$routes->get ('travel/getTrip/(:num)',   'TravelController::getTrip/$1');
$routes->post('travel/checkIn/(:num)',   'TravelController::checkIn/$1');
$routes->post('travel/checkOut/(:num)',  'TravelController::checkOut/$1');

$routes->get ('gps',                     'GPSController::index');
$routes->get ('gps/getVehicle/(:num)',   'GPSController::getVehicle/$1');
$routes->get ('gps/sync/(:num)',         'GPSController::sync/$1');
$routes->post('gps/logPing',             'GPSController::logPing');

// ============================================================
// GUARD DASHBOARD
// ============================================================
$routes->get ('guard', 'GuardController::index');
$routes->get ('guard/check/(:num)', 'GuardController::check/$1');

// ============================================================
// SAFETY MONITORING (Separated from Janitorial)
// ============================================================
$routes->get ('safety',                        'SafetyController::index');
$routes->get ('safety/scan-borrow',            'SafetyController::scanBorrow');
$routes->get ('safety/scan-return',            'SafetyController::scanReturn');
$routes->get ('safety/work-orders/create',     'SafetyController::createWorkOrder');
$routes->post('safety/work-orders/store',      'SafetyController::storeWorkOrder');
$routes->get ('safety/work-orders/edit/(:num)','SafetyController::editWorkOrder/$1');
$routes->get ('safety/equipment/create',       'SafetyController::createEquipment');
$routes->post('safety/equipment/store',        'SafetyController::storeEquipment');

// ============================================================
// JANITORIAL MONITORING (Separated from Safety)
// ============================================================
$routes->get('janitorial',            'JanitorialController::index');
$routes->get('janitorial/checklists', 'JanitorialController::checklists');

// ============================================================
// CALENDAR
// ============================================================
$routes->get('calendar', 'CalendarController::index');

// ============================================================
// NOTIFICATIONS
// ============================================================
$routes->get ('notifications',                 'NotificationController::index');
$routes->get ('notifications/mark-all-read',   'NotificationController::markAllRead');
$routes->get ('notifications/export',          'NotificationController::export');
$routes->post('notifications/markRead/(:num)', 'NotificationController::markRead/$1');
$routes->post('notifications/dismiss/(:num)',  'NotificationController::dismiss/$1');
$routes->post('notifications/action/(:num)',   'NotificationController::action/$1');
$routes->get ('notifications/unreadCount',     'NotificationController::unreadCount');

// ============================================================
// RECORDS, ARCHIVING & REPORTS
// ============================================================
$routes->get ('reports',                    'ReportController::index');
$routes->post('reports/generate',           'ReportController::generate');
$routes->post('reports/update',             'ReportController::update');
$routes->post('reports/add',                'ReportController::add');
$routes->post('reports/update/(:num)',      'ReportController::update/$1');
$routes->get ('reports/view/(:num)',        'ReportController::view/$1');
$routes->get ('reports/download/(:num)',    'ReportController::download/$1');
$routes->get ('reports/delete/(:num)',      'ReportController::delete/$1');
$routes->get ('reports/activate/(:num)',    'ReportController::activate/$1');
$routes->get ('reports/deactivate/(:num)',  'ReportController::deactivate/$1');
$routes->get ('reports/export/(:any)',      'ReportController::export/$1');

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
$routes->get ('settings/deleteUser/(:num)','SettingsController::deleteUser/$1');
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
// AUTH
// ============================================================
$routes->get ('login',        'AuthController::login');
$routes->post('login',        'AuthController::attemptLogin');
$routes->get ('auth/login',   'AuthController::login');
$routes->post('auth/login',   'AuthController::attemptLogin');
$routes->get ('logout',       'AuthController::logout');
$routes->get ('auth/logout',  'AuthController::logout');

// Default redirect to dashboard
$routes->get('/', function() {
    return redirect()->to(base_url('dashboard'));
});