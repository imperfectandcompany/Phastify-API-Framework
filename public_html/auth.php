<?php
function authenticate_user($token, $dbConnection) {
    // check if token is provided in the request header or query parameter or default to dev_mode_token if dev mode is enabled
    if (empty($token) && $GLOBALS['config']['devmode'] == 1) {
        $token = DEV_MODE_TOKEN;
    }

    // if token is not provided, return an error response
    if (empty($token)) {
        return array('status' => 'error', 'message' => 'Unauthorized - No Token Provided', 'token' => '');
    }

    // get an instance of the User class
    $user = new User($dbConnection);

    // verify the token
    $user_id = $user->verifyToken($token);

    // if token is not valid, return an error response
    if (!$user_id) {
        return array('status' => 'error', 'message' => 'Unauthorized - Invalid Token', 'token' => '');
    }

    // try to 'destroy' user instance
    $user = null;
    unset($user);

    // return user ID and token
    return array('status' => 'success', 'user_id' => $user_id, 'token' => $token);
}
?>
