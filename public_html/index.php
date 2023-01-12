<?php
include("../private/config.php");
date_default_timezone_set($GLOBALS['config']['timezone']);
if(!ob_start("ob_gzhandler")) ob_start();

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//includes
include($GLOBALS['config']['private_folder'].'/functions/functions.general.php');
include($GLOBALS['config']['private_folder'].'/classes/class.database.php'); //Our database script
//variables
$dbConnection = new DatabaseConnector($GLOBALS['db_conf']['db_host'], $GLOBALS['db_conf']['port'], $GLOBALS['db_conf']['db_db'], $GLOBALS['db_conf']['db_user'], $GLOBALS['db_conf']['db_pass'], $GLOBALS['db_conf']['db_charset']);

//router!
switch($GLOBALS['url_loc'][1]){
    case "/":
    break;
    case "timeline":
        include($GLOBALS['config']['private_folder'].'/backend/timeline.php');
        include($GLOBALS['config']['private_folder'].'/frontend/timeline.php');
    break;
    default:
    break;
}
?>


<?php /*Call our error handling*/ include($GLOBALS['config']['private_folder'].'/frontend/apinotif.php'); ?>



<?php
    if($GLOBALS['config']['devmode'] == 1){
        include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');
    }
?>
