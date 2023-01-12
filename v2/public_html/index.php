<?php
include("../../private/config.php");
if(!ob_start("ob_gzhandler")) ob_start();
session_start();

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


//includes
include($GLOBALS['config']['private_folder'].'/classes/class.database.php'); //Our database script
//variables
$dbConnection = new DatabaseConnector($GLOBALS['db_conf']['db_host'], $GLOBALS['db_conf']['port'], $GLOBALS['db_conf']['db_db'], $GLOBALS['db_conf']['db_user'], $GLOBALS['db_conf']['db_pass'], $GLOBALS['db_conf']['db_charset']);

?>
