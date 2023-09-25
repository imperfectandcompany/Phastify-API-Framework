<?php

class DevController {
        
    protected $dbConnection;
    protected $router;
    protected $logger;

    public function __construct($dbConnection, $router, $logger)
    {
        $this->dbConnection = $dbConnection;
        $this->router = $router;
        $this->logger = $logger;
    }
    
    public function getDevMode() {
        $devMode = new Dev($this->dbConnection);
        $devModeStatus = $devMode->getDevModeStatus();
        sendResponse('success', ['devmode' => $devModeStatus], SUCCESS_OK);
    }
    
    public function toggleDevMode() {
        $devMode = new Dev($this->dbConnection);

        $result = $devMode->toggleDevMode();
        if ($result) {
            sendResponse('success', ['message' => 'Devmode toggled'], SUCCESS_OK);
        } else {
            sendResponse('error', ['message' => 'Failed to toggle devmode'], ERROR_INTERNAL_SERVER);
        }
    }

    public function listRoutes() {
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
    
    
        
    public function toggleDevModeValue(string $value) {
        $devMode = new Devmode($this->dbConnection);
        if($value != null){
            if($value == 'true' || $value == 'false' || $value == '1' || $value == '0'){
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