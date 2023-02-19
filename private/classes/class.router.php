<?php
class Router {
    protected $routes = [];

    public function add($uri, $controller, $requestMethod)
    {
        $this->routes[$uri] = [
            'controller' => $controller,
            'requestmethod' => $requestMethod
        ];
    }
    
    public function getRoutes() {
        return $this->routes;
    }

    public function routeExists($url, $route) {
        $url = implode('/', $url);
        return array_key_exists($url, $route);
    }
    
    public function dispatch($url, $dbConnection)
    {
        $url = implode('/', $url);
        
        //check if url accessed is a route that exists
        if (!array_key_exists($url, $this->routes)) {
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
        
        // Get the request method to match expected method
        $request_method = $_SERVER['REQUEST_METHOD'];
        
        // Check if request_method matches expected method
        if ($request_method !== $this->routes[$url]['requestmethod']) {
            http_response_code(ERROR_NOT_FOUND);
            echo $GLOBALS['config']['devmode'] ? '405 - Request Not Allowed: Request method does not matched expected request' : '405 - Request Not Allowed' ;
            return;
        }
        
        // extract the controller and method from the route
        list($controllerName, $controllerMethod) = explode('@', $this->routes[$url]['controller']);
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
        //set content type as json if its not in dev mode
        if(!$GLOBALS['config']['devmode']){header('Content-Type: application/json');}
        
        $controllerClass->{$controllerMethod}();
        return;
    }
}

?>

