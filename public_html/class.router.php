class Router {
  private $url;
  private $method;

  public function __construct($url, $method) {
    $this->url = $url;
    $this->method = $method;
  }

  public function route() {
    // Parse the URL and extract the endpoint and any URL parameters
    $url_parts = parse_url($this->url);
    $endpoint = $url_parts['path'];
    parse_str($url_parts['query'], $params);

    // If the endpoint is empty, default to the home page
    if (!$endpoint || $endpoint == '/') {
      $endpoint = '/home';
    }

    // Extract the controller and action from the endpoint
    $parts = explode('/', trim($endpoint, '/'));
    $controller_name = ucfirst($parts[0]) . 'Controller';
    $action_name = count($parts) > 1 ? $parts[1] : 'index';

    // Check that the controller and action exist
    $controller_file = __DIR__ . '/controllers/' . $controller_name . '.php';
    if (!file_exists($controller_file)) {
      http_response_code(ERROR_NOT_FOUND);
      echo '404 - Not Found';
      return;
    }

    include($controller_file);

    if (!class_exists($controller_name)) {
      http_response_code(ERROR_NOT_FOUND);
      echo '404 - Not Found';
      return;
    }

    $controller = new $controller_name($this->method, $params);

    if (!method_exists($controller, $action_name)) {
      http_response_code(ERROR_NOT_FOUND);
      echo '404 - Not Found';
      return;
    }

    // Call the controller action with the parameters
    $controller->$action_name();
  }
