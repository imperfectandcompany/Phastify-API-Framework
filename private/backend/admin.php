<?php
include($GLOBALS['config']['private_folder'] . '/classes/class.device.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.user.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.roles.php');

// connect to prod db
$prodDbConnection = new DatabaseConnector(
    $GLOBALS['db_conf']['db_host'],
    $GLOBALS['db_conf']['port'],
    $GLOBALS['db_conf']['db_db_prod'],
    $GLOBALS['db_conf']['db_user'],
    $GLOBALS['db_conf']['db_pass'],
    $GLOBALS['db_conf']['db_charset']
);

$roles = new Roles($prodDbConnection);


$prodLogger = new Logger($prodDbConnection);


$device = new Device($prodDbConnection, $prodLogger);

$isLoggedIn = false;
$isAuthorized = false;
//looks for cookie that is stored if admin is logged in
if (isset($_COOKIE['POSTOGONADMINID'])) {
    //db check to see if the token is valid
    if ($prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))) {
        //grab and return user id
        $userid = $prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))[0]['user_id'];
        if (isset($_COOKIE['POSTOGONADMINID_'])) {
            // @role guard - only admins can create a service (future update, create decorators for functions)
            if (!$this->roles->isUserAdmin($userid)) {
                sendResponse('error', ['message' => 'Unauthorized to access admin panel.'], ERROR_FORBIDDEN);
                $isLoggedIn = true;
                $isAuthorized = false;
                return;
            } else {
                $isLoggedIn = true;
                $isAuthorized = true;
            }        
        } else {
            // save device since expire cookie isn't present
            $deviceId = $device->saveDevice($uid);
            if ($deviceId) {
                throwSuccess('Device saved');
                $cstrong = True;
                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                if (!$this->roles->isUserAdmin($userid)) {
                    sendResponse('error', ['message' => 'Unauthorized to access admin panel.'], ERROR_FORBIDDEN);
                    $isLoggedIn = true;
                    $isAuthorized = false;
                    return;
                } else {
                    $prodDbConnection->query('INSERT INTO login_tokens (token, user_id) VALUES (:token, :user_id)', array(':token' => sha1($token), ':user_id' => $userid));
                    $prodDbConnection->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])));
                    // 3 day expiry time
                    setcookie("POSTOGONADMINID", $token, time() + 60 * 60 * 24 * 3, '/', 'admin.postogon.com', TRUE, TRUE);
                    // create a second cookie to force the first cookie to expire without logging the user out, this way the user won't even know they've been given a new login toke
                    setcookie("POSTOGONADMINID_", '1', time() + 60 * 60 * 24 * 1, '/', 'admin.postogon.com', TRUE, TRUE);
                    $isLoggedIn = true;                    
                    $isAuthorized = true;
                }                 
            }
        }
    }
} else {
    if ($isLoggedIn !== true) {
        if (isset($_POST['login'])) {
            try {
                $emailoruser = $_POST['login_emailoruser'];
                $password = $_POST['login_password'];

                // Log the start of the authentication process
                $prodLogger->log(0, 'authentication_start', $_SERVER);
                $username = false;
                $email = false;
                try {

                    // Check that the required fields are present and not empty
                    if (!isset($_POST['login_emailoruser']) || !$_POST['login_emailoruser']) {
                        throw new Exception('Error: You did not provide an email or username!');
                    }
                    if (!isset($_POST['login_password']) || !$_POST['login_password']) {
                        throw new Exception('Error: You did not provide a password!');
                    }

                    // Extract the username and password from the request body
                    $identifier = $_POST['login_emailoruser'];
                    $password = $_POST['login_password'];

                    // Query the database for the user with the given username
                    $user = new User($prodDbConnection);

                    // Determine whether the identifier is an email or a username
                    $emailPassword = $user->getPasswordFromEmail($identifier);
                    if ($emailPassword) {
                        throwSuccess('User email found');
                        $email = true;
                        $dbPassword = $emailPassword;
                        $uid = $user->getUidFromEmail($identifier);
                    } else {
                        throwWarning('User email not found');
                        $userPassword = $user->getPasswordFromUsername($identifier);
                        if ($userPassword) {
                            $username = true;
                            throwSuccess('Username found');
                            $dbPassword = $userPassword;
                            $uid = $user->getUidFromUsername($identifier);
                        } else {
                            throwWarning('Username not found');
                            $prodLogger->log(0, 'authentication_failed_admin', 'User not found');
                            // Return an error if the user cannot be found
                            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
                            http_response_code(ERROR_NOT_FOUND);
                            return false;
                        }
                    }

                    // Check if the password is correct
                    if (password_verify($password, $dbPassword)) {
                        throwSuccess('Provided password was correct');

                        // @role guard - only admins can create a service (future update, create decorators for functions)
                        if (!$this->roles->isUserAdmin($uid)) {
                            sendResponse('error', ['message' => 'Unauthorized to access admin.'], ERROR_FORBIDDEN);
                            $prodLogger->log($uid, 'admin_unauthorized_login_attempt', '{ip: ' . $_SERVER['REMOTE_ADDR'] . '}');
                            return;
                        }

                        $deviceId = $device->saveDevice($uid);
                        if ($deviceId) {
                            throwSuccess('Device saved');
                            $prodLogger->log($uid, 'admin_device_login_save_success', '{device_id: ' . $deviceId . '}');
                            // Save the token in the database
                            if (($device->associateDeviceIdWithLogin($uid, $deviceId, $device->getDevice(), $_SERVER['REMOTE_ADDR']))) {
                                $prodLogger->log($uid, 'token_save_initiated', '{device_id: ' . $deviceId . '}');
                                $token = $user->setToken($uid, $deviceId);
                                if (!$token) {
                                    // Return an error if the password is incorrect
                                    sendResponse('error', ['message' => "Token could not be saved."], ERROR_INTERNAL_SERVER);
                                    $prodLogger->log($uid, 'admin_token_save_fail', $token);
                                    http_response_code(ERROR_UNAUTHORIZED);
                                    return false;
                                }
                                // Return the token to the client
                                sendResponse('success', ['token' => $token], SUCCESS_OK);
                                //pass cookie name, token itself, expiry date = current time + amount valid for which we picked for one 3 days, then location of the server the cookie is valid for.. / for everywhere, domain cookie is valid on... admin.postogon.com, and ssl is true, and http only which means http only meaning js cant access which prevents XSS ATTACKS.
                                setcookie("POSTOGONADMINID", $token, time() + 60 * 60 * 24 * 3, '/', 'admin.postogon.com', TRUE, TRUE);
                                setcookie("POSTOGONADMINID_", '1', time() + 60 * 60 * 24 * 1, '/', 'admin.postogon.com', TRUE, TRUE);
                                $prodLogger->log($uid, 'admin_token_save_success', $token);
                                $prodLogger->log($uid, 'admin_authentication_end', 'User authenticated as admin successfully');
                                $isLoggedIn = true;
                                return;
                            } else {
                                throwError('Device not associated with login');
                                sendResponse('error', ['message' => "Device of user could not be associated with login."], ERROR_INTERNAL_SERVER);
                                return false;
                            }
                        } else {
                            throwError('Device not saved');
                            sendResponse('error', ['message' => "Device of user could not be saved."], ERROR_INTERNAL_SERVER);
                            $prodLogger->log($uid, 'device_login_save_fail', $device->getDeviceInfo());
                            return false;
                        }
                    } else {
                        throwError('Provided password was incorrect');
                        // use later once logging becomes really serious
                        //$identifierKey = $email === true ? "email" : "username";

                        // Log a failed login attempt
                        $prodLogger->log(0, 'admin_login_failed', ['user_id' => $uid, 'ip' => $_SERVER['REMOTE_ADDR'], 'identifier' => $identifier]);

                        // Return an error if the password is incorrect
                        echo json_encode(array('status' => 'error', 'message' => 'Invalid password'));

                        // It was an invalid password but we don't want to confirm or deny info just in case it was an opp
                        sendResponse('error', ['message' => "Invalid Username or Password."], ERROR_UNAUTHORIZED);
                        return false;
                    }
                } catch (Exception $e) {
                    // Handle unexpected exceptions and log them
                    $prodLogger->log(0, 'authentication_error', ['error_message' => $e->getMessage()]);
                    // Return an error response
                    echo json_encode(array('status' => 'error', 'message' => 'An unexpected error occurred.'));
                    http_response_code(ERROR_INTERNAL_SERVER);
                    return false;
                }
            } catch (Exception $e) {
                echo "error";
            }
        }
    }
}



?>