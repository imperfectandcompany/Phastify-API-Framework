<?php
define ('loggedIn', true);
define('devmode', true);
define('baseDirectory', '/usr/www/igfastdl/postogon-api');
define('region', 'en_US');
if(loggedIn){
    define('DEV_MODE_TOKEN', '8388357810cfd983a0f0b3c739118fc9b37a43a30f813c12a65e0a06d49fbae7c96466ac49d679f3e0c20be18fa09e3cdcc7063d2c0df32c01cc03d079b616cf');
} else {
// force broken devmode token
define('DEV_MODE_TOKEN', '30s60c53dc356e5e6ad483bc92b1378621c15140da545e64092ddeef3517943c138870b6fb87f790ac663a5b5f174c07e2c2818160fa3d5faeee113b53b446481');
}

define('ERROR_GENERIC', 'An unexpected error occurred.');
// define('ERROR_NOT_FOUND', 'The requested resource was not found.');
// define('ERROR_BAD_REQUEST', 'Bad request. Please check your request data.');
