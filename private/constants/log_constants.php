<?php

define('LOG_USER_LOGIN_START', 'User Login');
define('LOG_USER_LOGOUT_END', 'User Logout');
define('LOG_USER_REGISTER', 'User Registration');
define('LOG_POST_CREATED_END', 'Post Created');
define('LOG_COMMENT_ADDED_END', 'Comment Added');

define('LOG_ACTIVITY_USER_LOGIN', 'LOG-001');
define('LOG_ACTIVITY_USER_LOGOUT', 'LOG-002');
define('LOG_ACTIVITY_POST_CREATED', 'LOG-003');

$logMessages = [
    'LOG-001' => 'User logged in successfully.',
    'LOG-002' => 'User logged out.',
    'LOG-003' => 'New post created by user.',
];
