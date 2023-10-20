<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.roles.php');
include_once($GLOBALS['config']['private_folder'] . '/classes/class.device.php');

class DevController
{

    protected $dbConnection;
    protected $router;
    protected $logger;

    protected $prodDbConnection;

    public function __construct($dbConnection, $router)
    {
        echo "f;dwe";
        echo "f;dwe";

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
    
    public function loadAdminLogin(){
        echo "f;dwe";
    }

    private function isLoggedIn()
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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

        $uidIsLoggedInAuthorized = $this->isLoggedIn();
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
        
        $uidIsLoggedInAuthorized = $this->isLoggedIn();
        if(!$uidIsLoggedInAuthorized){
            header("Location: https://admin.postogon.com/admin/login");
        }
        $prodLogger = new Logger($this->prodDbConnection);
        $prodDbConnection = $this->prodDbConnection;
        $page = "Service";
        include($GLOBALS['config']['private_folder'] . '/frontend/admin.php');
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
