<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.roles.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.device.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardUsers.php');


class AdminController
{

    protected $dbConnection;
    protected $router;
    protected $logger;

    protected $prodDbConnection;

    public function __construct($dbConnection, $router)
    {
        $this->dbConnection = $dbConnection;
        $this->router = $router;
        // connect to prod db
        $prodDbConnection = new DatabaseConnector(
            $GLOBALS['db_conf']['db_host'],
            $GLOBALS['db_conf']['port'],
            $GLOBALS['db_conf']['db_db_prod'],
            $GLOBALS['db_conf']['db_user'],
            $GLOBALS['db_conf']['db_pass'],
            $GLOBALS['db_conf']['db_charset']
        );
        $this->prodDbConnection = $prodDbConnection;
    }

    public function fetchUsersList(int $page = 1, int $perPage = 20){
    $dashboardUsers = new dashboardUsers($this->prodDbConnection);
    // disable devmode for the call
    $GLOBALS['config']['devmode'] = 0;
    $usersList = $dashboardUsers->getUsersList($page, $perPage);
    return sendResponse('success', ['data' => $usersList], SUCCESS_OK);
    }

    public function countUsers(?string $searchQuery = null){
        $dashboardUsers = new dashboardUsers($this->prodDbConnection);
        // disable devmode for the call
        $GLOBALS['config']['devmode'] = 0;
        $usersCount = $dashboardUsers->getUsersCount($searchQuery);
        return sendResponse('success', ['data' => $usersCount], SUCCESS_OK);
    }

    public function searchUsers(string $query, int $page = 1, int $perPage = 20) {
    $dashboardUsers = new dashboardUsers($this->prodDbConnection);
    $GLOBALS['config']['devmode'] = 0;
    $userSearchResults = $dashboardUsers->searchUsers($query, $page, $perPage);
    return sendResponse('success', ['data' => $userSearchResults], SUCCESS_OK);
    }    

    private function isAdminLoggedIn()
    {
        $roles = new Roles($this->prodDbConnection);
        $prodLogger = new Logger($this->prodDbConnection);
        $device = new Device($this->prodDbConnection, $prodLogger);

        $isLoggedIn = false;
        $isAuthorized = false;

        if (isset($_COOKIE['POSTOGONADMINID'])) {
            //db check to see if the token is valid
            if ($this->prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))) {
                //grab and return user id
                $userid = $this->prodDbConnection->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))[0]['user_id'];
                if (isset($_COOKIE['POSTOGONADMINID_'])) {
                    // @role guard - only admins can create a service (future update, create decorators for functions)
                    if (!$roles->isUserAdmin($userid)) {
                        sendResponse('error', ['message' => 'Unauthorized to access admin panel.'], ERROR_FORBIDDEN);
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
                            sendResponse('error', ['message' => 'Unauthorized to access admin panel.'], ERROR_FORBIDDEN);
                            $isLoggedIn = true;
                            $isAuthorized = false;
                            return false;
                        } else {
                            $this->prodDbConnection->query('INSERT INTO login_tokens (token, user_id) VALUES (:token, :user_id)', array(':token' => sha1($token), ':user_id' => $userid));
                            $this->prodDbConnection->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])));
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

    public function loadAdminSearch()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Search";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminLogs()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Logs";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminTests()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Tests";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminRoles()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Roles";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminDevices()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Devices";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminServices()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Services";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminUsers()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Users";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminIntegrations()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Integrations";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }



    public function loadAdminDashboard()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Dashboard";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminService()
    {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');
        
        $uidIsLoggedInAuthorized = $this->isAdminLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Service";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
    }

    public function loadAdminLogin()
    {
        if($this->isAdminLoggedIn()){
            header("Location: https://admin.postogon.com/admin/dashboard");
        }        


        $roles = new Roles($this->prodDbConnection);
        
        
        $prodLogger = new Logger($this->prodDbConnection);
        
        
        $device = new Device($this->prodDbConnection, $prodLogger);
        $isLoggedIn = false;
        $isAuthorized = false;

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
                        $error = "You did not provide an email or username!";
                        http_response_code(ERROR_NOT_FOUND);
                        throwError($error);
                        throw new Exception($error);
                    }
                    if (!isset($_POST['login_password']) || !$_POST['login_password']) {
                        $error = "No password provided.";
                        http_response_code(ERROR_NOT_FOUND);
                        throwError($error);
                        throw new Exception($error);
                    }

                    // Extract the username and password from the request body
                    $identifier = $_POST['login_emailoruser'];
                    $password = $_POST['login_password'];

                    // Query the database for the user with the given username
                    $user = new User($this->prodDbConnection);

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
                            $error = "User not found";
                            // Return an error if the user cannot be found
                            http_response_code(ERROR_NOT_FOUND);
                            throwError($error);
                            throw new Exception($error);
                        }
                    }

                    // Check if the password is correct
                    if (password_verify($password, $dbPassword)) {
                        $prodLogger->log($uid, 'admin_login_password_success', '{ip: ' . $_SERVER['REMOTE_ADDR'] . '}');
                        // @role guard - only admins can create a service (future update, create decorators for functions)
                        if (!$roles->isUserAdmin($uid)) {
                            $prodLogger->log($uid, 'admin_unauthorized_login_attempt', '{ip: ' . $_SERVER['REMOTE_ADDR'] . '}');
                            $isLoggedIn = true;
                            $error = "Unauthorized to access admin.";
                            http_response_code(ERROR_NOT_FOUND);
                            throwError($error);
                            throw new Exception($error);
                        } else {
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
                                        throwError("Token could not be saved.");
                                    }
                                    // Return the token to the client
                                    //pass cookie name, token itself, expiry date = current time + amount valid for which we picked for one 3 days, then location of the server the cookie is valid for.. / for everywhere, domain cookie is valid on... admin.postogon.com, and ssl is true, and http only which means http only meaning js cant access which prevents XSS ATTACKS.
                                    setcookie("POSTOGONADMINID", $token, time() + 60 * 60 * 24 * 3, '/', 'admin.postogon.com', TRUE, TRUE);
                                    setcookie("POSTOGONADMINID_", '1', time() + 60 * 60 * 24 * 1, '/', 'admin.postogon.com', TRUE, TRUE);
                                    $prodLogger->log($uid, 'admin_token_save_success', $token);
                                    $prodLogger->log($uid, 'admin_authentication_end', 'User authenticated as admin successfully');
                                    $isLoggedIn = true;
                                    $isAuthorized = true;
                                } else {
                                    throwError('Device not associated with login');
                                    sendResponse('error', ['message' => "Device of user could not be associated with login."], ERROR_INTERNAL_SERVER);
                                    $error = "Device of user could not be associated with login.";
                                    throw new Exception($error);
                                }
                            } else {
                                throwError('Device not saved');
                                sendResponse('error', ['message' => "Device of user could not be saved."], ERROR_INTERNAL_SERVER);
                                $prodLogger->log($uid, 'device_login_save_fail', $device->getDeviceInfo());
                                $error = "Device not saved";
                                throw new Exception($error);
                            }
                        }
                    } else {
                        throwError('Provided password was incorrect');
                        // use later once logging becomes really serious
                        //$identifierKey = $email === true ? "email" : "username";

                        // Log a failed login attempt
                        $prodLogger->log(0, 'admin_login_failed', ['user_id' => $uid, 'ip' => $_SERVER['REMOTE_ADDR'], 'identifier' => $identifier]);

                        // It was an invalid password but we don't want to confirm or deny info just in case it was an opp
                        $error = "Invalid Username or Password.";
                        throwError($error);
                        throw new Exception($error);
                    }
                } catch (Exception $e) {
                    // Handle unexpected exceptions and log them
                    $prodLogger->log(0, 'admin_authentication_error', ['error_message' => $e->getMessage()]);
                    // Return an error response
                    throwError($e->getMessage());
                    http_response_code(ERROR_INTERNAL_SERVER);
                    
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        include($GLOBALS['config']['private_folder'] . '/frontend/admin_login.php');
    }

    public function getDevMode()
    {
        $devMode = new Dev($this->dbConnection);
        $devModeStatus = $devMode->getDevModeStatus();
        sendResponse('success', ['devmode' => $devModeStatus], SUCCESS_OK);
    }

    public function toggleDevMode()
    {
        $devMode = new Dev($this->dbConnection);

        $result = $devMode->toggleDevMode();
        if ($result) {
            sendResponse('success', ['message' => 'Devmode toggled'], SUCCESS_OK);
        } else {
            sendResponse('error', ['message' => 'Failed to toggle devmode'], ERROR_INTERNAL_SERVER);
        }
    }


    public function listRoutes()
    {
        if (!$GLOBALS['config']['devmode']) {
            // This route should only be accessible in devmode.
            http_response_code(ERROR_NOT_FOUND);
            echo '404 - Not Found';
            return;
        }

        // Get the list of routes from the router.
        $routes = $this->router;
        // Start building the HTML content.
        $html = '<html><head><title>Available Routes</title></head><body>';
        $html .= '<h1>Available Routes</h1>';
        $html .= '<table border="1">';
        $html .= '<tr><th>URI</th><th>Parameters</th><th>Methods</th><th>Required Parameters</th><th>Documentation</th></tr>';

        foreach ($routes as $uri => $routeData) {
            $parameters = implode(', ', $routeData['params']);
            $methods = implode(', ', array_keys($routeData['methods']));

            $documentation = '';
            $requiredParams = '';

            foreach ($routeData['methods'] as $requestMethod => $methodData) {
                if (isset($methodData['documentation'])) {
                    $documentation .= "$requestMethod: " . $methodData['documentation'] . '<br>';
                }
                if (isset($methodData['required_params'])) {
                    $requiredParamStrings = [];
                    foreach ($methodData['required_params'] as $paramName => $source) {
                        $requiredParamStrings[] = "{$requestMethod}->{$source}->{$paramName}";
                    }
                    $requiredParams .= implode(', ', $requiredParamStrings) . '<br>';
                }
            }

            $html .= "<tr><td>$uri</td><td>$parameters</td><td>$methods</td><td>$requiredParams</td><td>$documentation</td></tr>";
        }

        $html .= '</table>';
        $html .= '</body></html>';

        // Output the HTML page.
        echo $html;
    }



    public function toggleDevModeValue(string $value)
    {
        $devMode = new Devmode($this->dbConnection);
        if ($value != null) {
            if ($value == 'true' || $value == 'false' || $value == '1' || $value == '0') {
                $bool = $value == 'true' || $value == '1' ? true : false;
                $result = $devMode->toggleDevModeFromValue($bool);
                if ($result) {
                    sendResponse('success', ['message' => 'Devmode status updated'], SUCCESS_OK);
                } else {
                    sendResponse('error', ['message' => 'Unable to update devmode status'], ERROR_INTERNAL_SERVER);
                }
            } else {
                sendResponse('error', ['message' => $value . ' is not a true or false value'], ERROR_INTERNAL_SERVER);
            }
        } else {
            sendResponse('error', ['message' => 'Value for toggle cannot be null'], ERROR_INTERNAL_SERVER);
        }
    }
}
?>