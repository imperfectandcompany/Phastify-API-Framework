<?php
include '../private/config.php';
// includes
include($GLOBALS['config']['private_folder'].'/functions/functions.general.php');
include($GLOBALS['config']['private_folder'].'/functions/functions.json.php');
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

// check the authentication result
if ($result['status'] === 'error') {
    http_response_code(ERROR_UNAUTHORIZED);
    echo $result['message'];
    exit;
}

// set user ID and token in global variable
$GLOBALS['user_id'] = $result['user_id'];
$GLOBALS['token'] = $result['token'];

if($GLOBALS['config']['devmode'] == 1){
    include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');
}

// create a new instance of the Router class and dispatch the incoming request
$router = new Router();

$router->add('/timeline/publicTimeline', 'TimelineController@fetchPublicTimeline', 'GET');
$router->add('/timeline/createPost', 'TimelineController@createPost', 'POST');

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
