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

        // Loop through the routes to find a match
        foreach ($this->routes as $route => $config) {
            // Replace parameter values in URL with parameter names
            $pattern = preg_replace('/:[^\/]+/', '([^\/]+)', $route);
            
            // Check if URL matches route pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                // Extract parameter names and values
                $routeParams = array_combine($config['params'], array_slice($matches, 1));
                
                // Replace parameter values in URL with parameter names
                $routeUrl = str_replace(array_values($routeParams), array_keys($routeParams), $route);
                                
                // Check if request method is allowed for this route
                if (!isset($config['methods'][$_SERVER['REQUEST_METHOD']])) {
                    http_response_code(ERROR_NOT_FOUND);
                    $GLOBALS['config']['devmode'] ? '404 - Not Found' : '404 - Not Found';
                    return;
                }
                
                // Extract controller and method from route
                list($controller, $method) = explode('@', $config['methods'][$_SERVER['REQUEST_METHOD']]['controller']);
                
                // Include the controller class
                require_once $GLOBALS['config']['private_folder'] . "/controllers/{$controller}.php";

                // Check if controller class and method exist
                if (!class_exists($controller) || !method_exists($controller, $method)) {
                    echo (class_exists($controller)) ? "true":"false";
                    http_response_code(ERROR_NOT_FOUND);
                    echo $GLOBALS['config']['devmode'] ? '404 - Not Found' : '404 - Not Found';
                    return;
                }

                // Set content type as json if its not in dev mode
                if(!$GLOBALS['config']['devmode']){header('Content-Type: application/json');}
                
                // Create controller instance and call method
                $controllerInstance = new $controller($dbConnection);
                $controllerInstance->{$method}(...array_values($routeParams));
                
                return;
            }
        }

        // No route matched the URL
        http_response_code(ERROR_NOT_FOUND);
        echo $GLOBALS['config']['devmode'] ? '404 - Not Found' : '404 - Not Found';
        return;
    }

    
    

}

?>

