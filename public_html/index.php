<?php
include '../private/config.php';
// includes
include($GLOBALS['config']['private_folder'].'/functions/functions.general.php');
include($GLOBALS['config']['private_folder'].'/functions/functions.json.php');
include($GLOBALS['config']['private_folder'].'/functions/functions.database.php');
include($GLOBALS['config']['private_folder'].'/constants.php');

// include the necessary files and create a database connection object
require_once $GLOBALS['config']['private_folder'].'/classes/class.database.php';
require_once $GLOBALS['config']['private_folder'].'/classes/class.user.php';

// set timezone
date_default_timezone_set($GLOBALS['config']['timezone']);

// start output buffering
if(!ob_start("ob_gzhandler")) ob_start();

// start session
session_start();

// set error reporting level
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// set up database connection
$dbConnection = new DatabaseConnector(
    $GLOBALS['db_conf']['db_host'],
    $GLOBALS['db_conf']['port'],
    $GLOBALS['db_conf']['db_db'],
    $GLOBALS['db_conf']['db_user'],
    $GLOBALS['db_conf']['db_pass'],
    $GLOBALS['db_conf']['db_charset']
);
require_once $GLOBALS['config']['private_folder'].'/classes/class.router.php';

require("./auth.php");
// check if token is provided in the request header or query parameter or default to dev_mode_token if dev mode is enabled
$token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
if (empty($token)) {
    $token = isset($_GET['token']) ? $_GET['token'] : '';
}

// authenticate the user
$result = authenticate_user($token, $dbConnection);

// handle case where user is not authenticated
if ($result['status'] === 'error') {
    
    // create a new instance for unauthenticated routes from router class
    $notAuthenticatedRouter = new Router();
    
    // add the non-authenticated routes to the router
    $notAuthenticatedRouter->add('/register', 'UserController@register', 'POST');
    $notAuthenticatedRouter->add('/auth', 'UserController@authenticate', 'POST');

    // get all the routes that have been added to the router
    $routes = $notAuthenticatedRouter->getRoutes();
    
    //check if the requested route does not match one of the non-authenticated routes
    if(!$notAuthenticatedRouter->routeExists($GLOBALS['url_loc'], $routes)){
        http_response_code(ERROR_UNAUTHORIZED);
        echo $result['message'];
        exit();
    }
    
    if($GLOBALS['config']['devmode'] == 1){
        include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');
    }
    // dispatch the request to the appropriate controller
    $notAuthenticatedRouter->dispatch($GLOBALS['url_loc'], $dbConnection);
    exit();
}


// set user ID and token in global variable
$GLOBALS['user_id'] = $result['user_id'];
$GLOBALS['token'] = $result['token'];

if($GLOBALS['config']['devmode'] == 1){
    include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');
}


// if the user is authenticated, create a new instance of the Router class and dispatch the incoming request
$router = new Router();
$router->add('/timeline/publicTimeline', 'TimelineController@fetchPublicTimeline', 'POST');
$router->add('/timeline/:publicTimeline', 'TimelineController@fetchPublicTimelineParamTest', 'GET');


//POST /logout
//Description: Logs out the user from the current device and invalidates the token unless all_devices is passed as true, in which case, the user is logged out from all devices, and all tokens are invalidated.
//Request Body: {
//"token": "string",
//"all_devices": "boolean"
//}
//token (string): The token passed for auth interceptor as header, also identifier for the user's current device, used to fetch the user_id.
//all_devices (boolean): If true, logs out the user from all devices, passed as a boolean value in the request body.
    $router->add('/logout', 'UserController@logoutAll', 'GET');

//POST /logout/:id
//Description: Logs out the user from a specific device and invalidates the token associated with that device.
//Request Body: {
//"token": "string",
//"token_to_logout": "string"
//}
//token (string): The token passed for auth interceptor as header, also identifier for the user's current device, used to fetch the user_id.
//token_to_logout (string): The token of the device to log out from. This is passed as a string value in the request body.

$router->add('/logout', 'UserController@logout', 'POST');
$router->add('/logout/:deviceToken', 'UserController@logoutAllParam', 'GET');
//implement next..
$router->add('/logout/:deviceToken/:param2', 'UserController@logoutMultipleParams', 'GET');


//dispatch router since authentication and global variables are set!
$router->dispatch($GLOBALS['url_loc'], $dbConnection);


//// router
//switch($GLOBALS['url_loc'][1]) {
//    case "/":
//        break;
//    case "timeline":
//        include($GLOBALS['config']['private_folder'].'/backend/timeline.php');
//        include($GLOBALS['config']['private_folder'].'/frontend/timeline.php');
//        break;
//    default:
//        break;
//}


// unset token to prevent accidental use
unset($token);
ob_end_flush();

exit();
?>
