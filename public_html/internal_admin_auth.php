<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.roles.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.device.php');

function checkAdmin() {
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

        if (isset($_COOKIE['POSTOGONADMINID'])) {
            //db check to see if the token is valid
            if ($prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))) {
                //grab and return user id
                $userid = $prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))[0]['user_id'];
                if (isset($_COOKIE['POSTOGONADMINID_'])) {
                    // @role guard - only admins can create a service (future update, create decorators for functions)
                    if (!$roles->isUserAdmin($userid)) {
                        $isLoggedIn = true;
                        $isAuthorized = false;
                        return false;
                    } else {
                        $isLoggedIn = true;
                        $isAuthorized = true;
                        return $userid;
                    }
                } else {
                    // save device since expire cookie isn't present
                    $deviceId = $device->saveDevice($userid);
                    if ($deviceId) {
                        throwSuccess('Device saved');
                        $cstrong = True;
                        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                        if (!$roles->isUserAdmin($userid)) {
                            $isLoggedIn = true;
                            $isAuthorized = false;
                            return false;
                        } else {
                            $prodDbConnection->query('INSERT INTO login_tokens (token, user_id) VALUES (:token, :user_id)', array(':token' => sha1($token), ':user_id' => $userid));
                            $prodDbConnection->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])));
                            // 3 day expiry time
                            setcookie("POSTOGONADMINID", $token, time() + 60 * 60 * 24 * 3, '/', 'admin.postogon.com', TRUE, TRUE);
                            // create a second cookie to force the first cookie to expire without logging the user out, this way the user won't even know they've been given a new login toke
                            setcookie("POSTOGONADMINID_", '1', time() + 60 * 60 * 24 * 1, '/', 'admin.postogon.com', TRUE, TRUE);
                            $isLoggedIn = true;
                            $isAuthorized = true;
                            return $userid;
                        }
                    }
                }
            }
        }
    }
?>
