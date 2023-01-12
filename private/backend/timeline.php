<?php
//Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');
//Variables

$timeline = new timeline($dbConnection);

//$timeline = $timeline->fetchPublicTimeline();

?>
