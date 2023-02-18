<?php

function handle_route($url, $dbConnection) {
    $routes = [
        '/' => $GLOBALS['config']['private_folder'] . '/frontend/home.php',
        '/timeline/fetchPublicTimeline' => $GLOBALS['config']['private_folder'] . '/backend/timeline.php',
        '/profile' => $GLOBALS['config']['private_folder'] . '/frontend/profile.php',
        // add more routes as needed
    ];

    $route = isset($routes[$url]) ? $routes[$url] : null;

    if (!$route) {
        http_response_code(ERROR_NOT_FOUND);
        echo '404 - Not Found';
        return;
    }

    // execute the script for the route
    include($route);
}

?>
