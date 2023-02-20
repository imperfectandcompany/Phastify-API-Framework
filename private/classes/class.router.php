<?php
class Router {
    
    protected $routes = [];

    public function add($uri, $controller, $requestMethod)
    {
        // Check if the URI is empty
        if (empty($uri)) {
            throw new Exception("Route URI cannot be empty.");
        }
        
        // Add slash to the beginning of the URI if it is missing
        if (substr($uri, 0, 1) !== '/') {
            $uri = '/' . $uri;
        }

//        if (substr($uri, -1) === '/') {
//            throw new Exception("Route cannot end with '/'.");
//        }
        
        // Check if the URI ends with a slash and remove it if it does
        if (substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        if (strpos($controller, '@') === false) {
            throw new Exception("Controller and method should be separated by '@'.");
        }
        
        $controllerParts = explode('@', $controller);
        $controllerName = $controllerParts[0];
        $methodName = $controllerParts[1];

        if (empty($methodName)) {
            throw new Exception("Method not provided after @ for '$controllerName'.");
        }
        
        // Check if a route with the same URI already exists
        if (isset($this->routes[$uri])) {
            if(isset($this->routes[$uri]['methods'][$requestMethod])){
                throw new Exception("Route with URI '$uri' already exists.");
            }
        }
        
        // checks if the endpoint matches, if so then checks if it has the same url location of any that exist, if so we can access the endpoint twice so throw error
        //works for parameters and segments alike muahaha ezpz
        foreach ($this->routes as $existingUri => $existingRoute) {
            //Let endpoint exist since the request method is different...
            $existingSegments = explode('/', $existingUri);
            $newSegments = explode('/', $uri);
            
            if ($existingSegments[1] == $newSegments[1] && count($existingSegments) == count($newSegments)) {
                if (isset($existingRoute['methods'][$requestMethod])) {
                    throw new Exception("Route with URI '$uri' conflicts with existing route '$existingUri'.");
                }
            }
        }
        
        // Get the list of files in the controllers directory
        $controllerDir = '../private/controllers/';
        $files = scandir($controllerDir);

        // Loop through the files and check if a file with the expected name exists
        $found = false;
        foreach ($files as $file) {
            if (strtolower($file) === strtolower("$controllerName") . '.php') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Controller '$controllerName' does not exist.");
        }

        $controllerPath = $GLOBALS['config']['private_folder'] . '/controllers/' . $controllerName . '.php';

        $controllerContents = file_get_contents($controllerPath);

        if (strpos($controllerContents, "function $methodName") === false) {
            throw new Exception("Method '$methodName' does not exist in controller '$controllerName'.");
        }

        if (!in_array($requestMethod, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new Exception("Invalid request method.");
        }
        
        
        $params = array();
        $uriSegments = explode('/', $uri);
        
        foreach ($uriSegments as $segment) {
            if (substr($segment, 0, 1) == ':') {
                $params[] = substr($segment, 1);
            }
        }
        if (!isset($this->routes[$uri])) {
            $this->routes[$uri] = [
                'params' => $params,
                'methods' => [
                    $requestMethod => [
                        'controller' => $controller
                    ]
                ]
            ];
        } else {
            // Check if the request method is already registered for the URI
            if (isset($this->routes[$uri]['methods'][$requestMethod])) {
                // If it is, throw an error or handle it however you want
            } else {
                // If it's not, add it to the list of available methods for this URI
                $this->routes[$uri]['methods'][$requestMethod] = [
                    'controller' => $controller
                ];
            }
        }
    }

    
    public function getRoutes() {
        return $this->routes;
    }

    public function routeExists($url, $route) {
        $url = implode('/', $url);
        return array_key_exists($url, $route);
    }
    
    private function checkParams($controllerClass, $controllerMethod, $params) {
        // Get the method signature of the controller method
        $reflectionMethod = new ReflectionMethod($controllerClass, $controllerMethod);
        $parameters = $reflectionMethod->getParameters();

        // Check if the number of parameters match
        if (count($params) !== count($parameters)) {
            return false;
        }

        // Check if the types of the parameters match
        foreach ($parameters as $index => $parameter) {
            $expectedType = $parameter->getType();
            $actualType = gettype($params[$index]);
            if ($expectedType !== null && $actualType !== $expectedType->getName()) {
                return false;
            }
        }

        // All checks passed, params are valid
        return true;
    }
    
    
    public function dispatch($url, $dbConnection)
    {
        $url = implode('/', $url);
        
        // Check if the url has parameters
        $params = array();
        $urlSegments = explode('/', $url);
        $routeParams = array_filter(explode('/', array_keys($this->routes)[0]), function ($val) {
            return strpos($val, ':') !== false;
        });

        if (count($urlSegments) > count($routeParams)) {
            foreach ($routeParams as $index => $paramName) {
                $pos = array_search($paramName, $routeParams);
                if (isset($urlSegments[$pos])) {
                    $params[] = $urlSegments[$pos];
                } else {
                    $params[] = null;
                }
            }
        }
        
        //check if url accessed is a route that exists
        if (!array_key_exists($url[0], $this->routes)) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '404 - Not Found: Endpoint is not defined as route' : '404 - Not Found';
            if($GLOBALS['config']['devmode']){
                echo "<h3>Given url</h3>";
                echo "<pre>";
                echo $url;
                echo "</pre>";
                echo "<h3>Routes</h3>";
                echo "<pre>";
                var_dump($this->routes);
                echo "</pre>";
            }
            return;
        }
        

        
        if (!isset($this->routes[$url])) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '404 - Endpoint not found: '.$url : '404 - Endpoint not found';
            return;
        }
        
        // Get the request method to match expected method
        $request_method = $_SERVER['REQUEST_METHOD'];
        // Check if the requested method is available for this URI
        if (!in_array($request_method, array_keys($this->routes[$url]['methods']))) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '405 - Request Not Allowed: Request method not allowed for this URI' : '405 - Request Not Allowed';
            return;
        }
        
        // extract the controller and method from the route, we can use $request_method since we cleared the possibility of it not being accepted
        list($controllerName, $controllerMethod) = explode('@', $this->routes[$url]['methods'][$request_method]['controller']);
        // include the controller class and create an instance
        require_once $GLOBALS['config']['private_folder'] . "/controllers/{$controllerName}.php";
        // Check if the controller class exists
        if (!class_exists($controllerName)) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '404 - Not Found: Controller class does not exist ' : '404 - Not Found' ;
            return;
        }
        
        //create instance since class exists (no cap)
        $controllerClass = new $controllerName($dbConnection);

        // Check if the method exists in the controller class
        if (!method_exists($controllerClass, $controllerMethod)) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '404 - Not Found: Controller method does not exist ' : '404 - Not Found' ;
            return;
        }
        
        // Check if the parameters are valid
        $params = array_values($params);
        if (!$this->checkParams($controllerClass, $controllerMethod, $params)) {
            http_response_code(ERROR_BAD_REQUEST);
            echo $GLOBALS['config']['devmode'] ? '400 - Bad Request: Invalid parameters ' : '400 - Bad Request';
            return;
        }
        
        //set content type as json if its not in dev mode
        if(!$GLOBALS['config']['devmode']){header('Content-Type: application/json');}
        
        $controllerClass->{$controllerMethod}(...$params);

        return;
    }
}

?>

