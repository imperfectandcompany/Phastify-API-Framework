<?php
define('DEV_ERROR_INVALID_INPUT', 'DEV-001');
define('DEV_ERROR_DATABASE_CONNECTION', 'DEV-002');
define('DEV_ERROR_UNDEFINED_FUNCTION', 'DEV-003');

$devErrorMessages = [
    'DEV-001' => 'Invalid input data. Please check your input parameters.',
    'DEV-002' => 'Database connection failed. Check your database configuration.',
    'DEV-003' => 'Undefined function called. Ensure the function exists and is loaded.',
];

$constants = [
    'POS-0001' => 'Wrong password.',
    'POS-0002' => '',
];


// $constants = [
//     'ERROR_LOGIN_FAILED' => 'Wrong password.',
//     'ERROR_INVALID_USERNAME' => 'Invalid username.',
//     'device_info_error' => 'Device information not available.',
// ];
