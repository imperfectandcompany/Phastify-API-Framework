<?php
include($GLOBALS['config']['private_folder'].'/classes/class.logger.php');

class Router {
    
    protected $routes = [];

    public function add($uri, $controller, $requestMethod, $documentation = null)
    {

        // Check if the URI is valid
        if (empty($uri) || substr($uri, 0, 1) !== '/') {
            throw new Exception("Invalid route URI: '$uri'.");
        }
        
        // Remove trailing slash from the URI
        $uri = rtrim($uri, '/');
        
        // Check if the controller and method names are valid
        if (strpos($controller, '@') === false) {
            throw new Exception("Invalid controller and method name: '$controller'.");
        }
        
        // Split the controller and method names
        list($controllerName, $methodName) = explode('@', $controller);

        // Check if the method name is valid
        if (empty($methodName)) {
            throw new Exception("Method name not provided for controller: '$controllerName'.");
        }
        
        
        // Check if a route with the same URI and request method already exists
        if (isset($this->routes[$uri]['methods'][$requestMethod])) {
            throw new Exception("Route with URI '$uri' and request method '$requestMethod' already exists.");
        }
        
        $newSegments = explode('/', $uri);

        // Check if the endpoint matches any existing routes
        foreach ($this->routes as $existingUri => $existingRoute) {
            if ($existingUri !== $uri && count($existingRoute['params']) === count($newSegments)) {
                $existingSegments = explode('/', $existingUri);
                
                $same = true;
                $params = array();

                
                for ($i = 0; $i < count($existingSegments); $i++) {
                    if ($existingSegments[$i] !== $newSegments[$i] && substr($existingSegments[$i], 0, 1) !== ':') {
                        $same = false;
                        break;
                    } elseif (substr($existingSegments[$i], 0, 1) === ':') {
                        $params[] = substr($existingSegments[$i], 1);
                    }
                }
                
                if ($same && !empty($params)) {
                    throw new Exception("Route with URI '$uri' conflicts with existing route '$existingUri'.");
                }
            }
        }
        
        // Check if the controller file exists
        $controllerDir = '../private/controllers/';
        $controllerPath = $controllerDir . $controllerName . '.php';
        
        if (!file_exists($controllerPath)) {
            throw new Exception("Controller file '$controllerName.php' not found.");
        }

        // Check if the method exists in the controller file
        $controllerContents = file_get_contents($controllerPath);

        if (strpos($controllerContents, "function $methodName") === false) {
            throw new Exception("Method '$methodName' does not exist in controller '$controllerName'.");
        }

        // Check if the request method is valid
        if (!in_array($requestMethod, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new Exception("Invalid request method: '$requestMethod'.");
        }
        
        // Extract parameters from the URI
        $params = array_filter(explode('/', $uri), function($segment) {
            return substr($segment, 0, 1) == ':';
        });

        // Check if any parameters are optional
        $optionalParams = array_filter($params, function($param) {
            return substr($param, -1) === '?';
        });

        // Remove the '?' character from optional parameters
        $optionalParams = array_map(function($param) {
            return rtrim($param, '?~');
        }, $optionalParams);

        if (!isset($this->routes[$uri])) {
            $this->routes[$uri] = [
                'params' => $params,
                'optional_params' => $optionalParams,
                'methods' => []
            ];
        }
    
        $this->routes[$uri]['methods'][$requestMethod] = [
            'controller' => $controller,
        ];

        $this->routes[$uri]['methods'][$requestMethod]['documentation'] = $documentation;
    }

    public function addDocumentation($uri, $requestMethod, $documentation)
    {
        // Check if the route exists
        if (isset($this->routes[$uri]['methods'][$requestMethod])) {
            // Add or update the documentation comment
            $this->routes[$uri]['methods'][$requestMethod]['documentation'] = $documentation;
        } else {
            throw new Exception("Route with URI '$uri' and request method '$requestMethod' does not exist.");
        }
    }

    public function enforceParameters($uri, $requestMethod, $params = [])
    {
        // Check if the route exists
        if (isset($this->routes[$uri]['methods'][$requestMethod])) {
            // Add or update enforced parameters
            $this->routes[$uri]['methods'][$requestMethod]['required_params'] = $params;
        } else {
            throw new Exception("Route with URI '$uri' and request method '$requestMethod' does not exist.");
        }
    }


    public function getRoutes() {
        return $this->routes;
    }
    
    public function routeExists($urlParts, $routes) {
        foreach ($routes as $route => $routeDetails) {
            $routeParts = explode('/', $route);
            
            // Skip the route if the number of parts doesn't match
            if (count($urlParts) != count($routeParts)) {
                continue;
            }
    
            $matches = true; // Assume it's a match until proven otherwise
            for ($i = 0; $i < count($urlParts); $i++) {
                // If a route part is a parameter, e.g. starts with ':', it's considered a match
                // Otherwise, the exact route part and URL part must match
                if (!(strpos($routeParts[$i], ':') === 0 || $routeParts[$i] == $urlParts[$i])) {
                    $matches = false;
                    break;  // Break out of the for loop if any part doesn't match
                }
            }
            
            // If we found a match, return true immediately
            if ($matches) {
                return true;
            }
        }
    
        // If we've checked all routes and found no matches, return false
        return false;
    }
    


    public function dispatch($url, $dbConnection, $devMode)
    {
        $url = implode('/', $url);
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $routeMatched = false; // Flag to keep track if a route was dispatched

        // Loop through the routes to find a match
        foreach ($this->routes as $route => $config) {
            // Replace parameter values in URL with parameter names
            $pattern = preg_replace('/:[^\/~]+/', '([^\/~]+)', $route);

            // Check if URL matches route pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {

                // Check if request method is allowed for this route
                if (!isset($config['methods'][$httpMethod])) {
                    continue; // Just skip this route and check the next one
                }

                // Extract parameter names and values
                $routeParams = array_combine($config['params'], array_slice($matches, 1));
                $optionalParams = $config['optional_params'] ?? [];

                // Replace parameter values in URL with parameter names
                $routeUrl = str_replace(array_values($routeParams), array_keys($routeParams), $route);
                foreach ($optionalParams as $param) {
                    if (!isset($routeParams[$param])) {
                        $routeParams[$param] = null;
                    }
                }

                // Check if request method is allowed for this route
                if (!isset($config['methods'][$_SERVER['REQUEST_METHOD']])) {
                    $this->handleError("Route with URI '$url' and request method '{$_SERVER['REQUEST_METHOD']}' not found.");
                    return;
                }

                // Extract controller and method from route
                list($controller, $method) = explode('@', $config['methods'][$_SERVER['REQUEST_METHOD']]['controller']);
                
                // Include the controller class
                require_once $GLOBALS['config']['private_folder'] . "/controllers/{$controller}.php";

                // Check if controller and method exist
                if (!class_exists($controller) || !method_exists($controller, $method)) {
                    $this->handleError("Controller or method not found for route with URI '$url'.");
                    return;
                }

                 // Combine parameter names and values into associative array, ignoring optional parameters with no value
                 $params = array_intersect_key($routeParams, array_flip(array_filter($config['params'], function($param) use ($routeParams) {
                    return substr($param, -1) !== '?' || array_key_exists(rtrim($param, '?'), $routeParams);
                })));
                
                // Validate the parameters
                $validatedParams = $this->validateParams($controller, $method, $params);
                if ($validatedParams === false) {
                    //9-10-23 add specific error handling, missing parameter or wrong data type etc.
                    $this->handleError("Invalid parameters for route with URI '$url'.");
                    return;
                }

                foreach ($config['methods'] as $requestMethod => $methodData) {
                    if (isset($methodData['required_params']) && $requestMethod === $httpMethod) {
                        $routeParams = $methodData['required_params'];
                        break;
                    }
                }

                // Extract parameters from different sources (e.g., URL, body) (MOVE IN FROM 217)
                $routeBodyParams = [];

                // Check if the request method matches the current method being processed
                if ($requestMethod === $httpMethod) {
                    // Extract parameters from different sources (e.g., URL, body)
                    $routeBodyParams = [];
                    foreach ($routeParams as $paramName => $source) {
                        switch ($source) {
                            case 'body':
                                // Extract parameters from the request body (e.g., JSON)
                                $postBody = json_decode(file_get_contents("php://input"));
                                if (property_exists($postBody, $paramName)) {
                                    $routeBodyParams[$paramName] = $postBody->$paramName;
                                } else {
                                    // Handle missing mandatory parameter in the request body
                                    $this->handleError("Missing mandatory parameter: $paramName in request body");
                                    return;
                                }
                                break;
                        }
                    }
                }
                $logger = new Logger($dbConnection);
                // Call the controller method with the parameters
                // TODO: Implement newly injected logger functionality throughout entire application
                $controllerInstance = $controller !== 'DevController' ? new $controller($dbConnection, $logger) : new $controller($dbConnection, $logger, $this->routes);
                
                $controllerInstance->{$method}(...$validatedParams);
                $routeMatched = true; // Set the flag to true as a route was dispatched
                return;
            }
        }
        if (!$routeMatched) {
        // if not main page, don't return anything
        if($url == '/'){
            return;
        }

        // If no route is found, handle the error
        $this->handleError("Route with URI '$url' not found.");
        }

    }

    // Handle errors in development mode by displaying a message and error code
    private function handleError($message) {
        http_response_code(ERROR_NOT_FOUND);
        if ($GLOBALS['config']['devmode']) {
            echo "Error: $message";
        } else {
            echo "Error: Route not found.";
        }
    }

    private function validateParams($controller, $method, $params) {
        $methodParams = new ReflectionMethod($controller, $method);
        $paramTypes = array_map(function($param) {
            return [
                'param' => $param,
                'type' => $param->getType(),
                'isOptional' => $param->isOptional(),
            ];
        }, $methodParams->getParameters());

    
        // Validate the number and types of parameters
        if (count($params) < count(array_filter($paramTypes, function($param) {
            return !$param['isOptional'];
        }))) {
            return false;
        }
    
        $validatedParams = array();
        
        
        foreach ($paramTypes as $param) {
            $paramName = ':'.$param['param']->getName();
            $paramType = $param['type'] ? $param['type']->getName() : 'string';


            //checks to see if the parameter within the function matches the one in route
            if (!array_key_exists($paramName, $params)) {
                if ($param['isOptional']) {
                    $validatedParams[] = null;
                    continue;
                } else {
                    return false;
                }
            }

            $value = $params[$paramName];
            
    
            switch ($paramType) {
                case 'int':
                    $validatedValue = filter_var($value, FILTER_VALIDATE_INT);
                    break;
                case 'float':
                    $validatedValue = filter_var($value, FILTER_VALIDATE_FLOAT);
                    break;
                case 'bool':
                    $validatedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                default:
                    $validatedValue = $value;
            }
    
            if ($validatedValue === false) {
                return false;
            }
    
            $validatedParams[] = $validatedValue;
        }
    
        return $validatedParams;
    }
    

}

