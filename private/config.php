<?php

//Global config variables
$GLOBALS['config']['url'] = "https://postogon.com";
$GLOBALS['config']['avatar_url'] = "https://cdn.postogon.com/assets/img/profile_pictures";
$GLOBALS['config']['avatar_folder'] = "/usr/www/igfastdl/postogon-cdn/assets/img/profile_pictures";
$GLOBALS['config']['private_folder'] = "/usr/www/igfastdl/postogon-api/private";
$GLOBALS['config']['timezone'] = "America/New_York";
//If the site is not in a root folder, how many values in the url_loc array will we be ignoring so we think we're in a root folder?
$GLOBALS['config']['url_offset'] = 0;

//dbconf includes database details i can't pass to github, config stores all global data and soon will do more... we will decouple
//this ultimately!
require('dbconfig.php');
//Database variables
$GLOBALS['db_conf']['db_host']  =    $domain;
$GLOBALS['db_conf']['db_user']  =    $user;
$GLOBALS['db_conf']['db_pass']  =    $pass;
$GLOBALS['db_conf']['db_db']    =    $table;
$GLOBALS['db_conf']['port']     =    '3306';
$GLOBALS['db_conf']['db_charset']  = 'utf8mb4';

$GLOBALS['config']['devmode'] = 1; //This enables dev mode to print out dev information -- DO NOT USE IN PRODUCTION!
$GLOBALS['config']['testmode'] = 1; //This enables testing

//General settings
$GLOBALS['config']['max_username_length'] = '32';
$GLOBALS['config']['max_password_length'] = '32';
$GLOBALS['config']['max_timeline_lookup'] = '30';
$GLOBALS['config']['avatar_max_size'] = '156';
$GLOBALS['config']['default_avatar'] = 'default.png';

//Group settings
$GLOBALS['config']['groups'] = array(
    1 => "Admin",
    2 => "Employee",
    2 => "Staff",
    3 => "InvestorDemo",
    4 => "Educator",
    5 => "Influencer",
    6 => "User",
    7 => "Unverified",
    8 => "Banned",
);

$GLOBALS['config']['tags'] = array(
    1 => "#Debunked",
    2 => "#Verified",
    3 => "#Educational"
);

//This is how we get what page we should be on based on URL.
$GLOBALS['url_loc'] = explode('/', htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'), ENT_QUOTES));

if($GLOBALS['config']['url_offset'] > 0){
    $x = 0; while($x < ($GLOBALS['config']['url_offset'])){ unset($GLOBALS['url_loc'][$x]); $x++; }
    $GLOBALS['url_loc'] = array_values($GLOBALS['url_loc']);
}

//When we're looking up a user's profile, we use this query to easily not accidentally call their password when we don't need it.
$GLOBALS['config']['profile_lookup'] = "id,username,email,admin,verified,createdAt,avatar,display_name";

//Do not touch -- These are settings we should define or set, but not adjust unless we absolutely need to.
$GLOBALS['errors'] = array();
$GLOBALS['logs'] = array();



$GLOBALS['messages'] = array(); //Main array for all status messages
$GLOBALS['messages']['error'] = array(); //Main array for all status messages
$GLOBALS['messages']['warning'] = array(); //Main array for all status messages
$GLOBALS['messages']['success'] = array(); //Main array for all status messages
$GLOBALS['messages']['test'] = array(); //Main array for all status messages

?>
