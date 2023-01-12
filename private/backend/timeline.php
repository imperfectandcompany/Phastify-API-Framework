<?php

//Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');
//Variables

//Overwrite our existing DB, but through the timeline class
$dbConnection = new timeline($GLOBALS['db_conf']['db_host'], $GLOBALS['db_conf']['port'], $GLOBALS['db_conf']['db_db'], $GLOBALS['db_conf']['db_user'], $GLOBALS['db_conf']['db_pass'], $GLOBALS['db_conf']['db_charset']);

$timeline = $dbConnection->fetchPublicTimeline();


?>
