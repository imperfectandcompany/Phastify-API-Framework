<?php

//dbconf includes database details i can't pass to github, config stores all global data and soon will do more... we will decouple
//this ultimately!
require($GLOBALS['config']['private_folder'] . "/dbconfig.php");
//Database variables
$GLOBALS['db_conf']['db_host']  =    $domain;
$GLOBALS['db_conf']['db_user']  =    $user;
$GLOBALS['db_conf']['db_pass']  =    $pass;
$GLOBALS['db_conf']['db_db']    =    $table;
$GLOBALS['db_conf']['port']     =    '3306';
$GLOBALS['db_conf']['db_charset']  = 'utf8mb4';