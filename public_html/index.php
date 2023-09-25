<?php

include '../private/config.php';

// set timezone
date_default_timezone_set($GLOBALS['config']['timezone']);



// start output buffering
if(!ob_start("ob_gzhandler")) ob_start();
// start session
session_start();

// set error reporting level
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// includes
include($GLOBALS['config']['private_folder'].'/functions/functions.general.php');
include($GLOBALS['config']['private_folder'].'/functions/functions.json.php');
include($GLOBALS['config']['private_folder'].'/functions/functions.database.php');
include($GLOBALS['config']['private_folder'].'/constants.php');
include($GLOBALS['config']['private_folder'].'/constants/localization_manager.php');


// include the necessary files and create a database connection object
require_once $GLOBALS['config']['private_folder'].'/classes/class.database.php';
require_once $GLOBALS['config']['private_folder'].'/classes/class.user.php';
require_once $GLOBALS['config']['private_folder'].'/classes/class.dev.php';
require_once($GLOBALS['config']['private_folder'] . '/classes/class.localizationCache.php');

// Create a cache instance
$cache = new LocalizationCache();

// Initialize LocalizationManager with the cache
$localizationManager = new LocalizationManager(
    $GLOBALS['config']['private_folder'] . "/constants",
    $GLOBALS['config']['devmode'] ? 'dev' : 'prod',
    'en_US',
    $cache
);

$localizationManager->initialize();

//include($GLOBALS['config']['private_folder'].'/structures/create_constants_structure.php');

// set up database connection
$dbConnection = new DatabaseConnector(
    $GLOBALS['db_conf']['db_host'],
    $GLOBALS['db_conf']['port'],
    $GLOBALS['db_conf']['db_db'],
    $GLOBALS['db_conf']['db_user'],
    $GLOBALS['db_conf']['db_pass'],
    $GLOBALS['db_conf']['db_charset']
);


require_once $GLOBALS['config']['private_folder'].'/classes/class.router.php';

require("./auth.php");
// check if token is provided in the request header or query parameter or default to dev_mode_token if dev mode is enabled
$token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
if (empty($token)) {
    $token = isset($_GET['token']) ? $_GET['token'] : '';
}

// authenticate the user
$result = authenticate_user($token, $dbConnection);

    // get an instance of the Devmode class
    $devMode = new Dev($dbConnection);
    $GLOBALS['config']['devmode'] = $devMode->getDevModeStatus();



    // // Create a cache instance
    // $cache = new LocalizationCache();

    // // Initialize LocalizationManager with the cache
    // $localizationManager = new LocalizationManager(
    //     $GLOBALS['config']['private_folder'] . "/constants",
    //     $GLOBALS['config']['devmode'] ? 'dev' : 'prod',
    //     'en_US',
    //     $cache
    // );
    
    // $message = $localizationManager->getLocalizedString('ERROR_LOGIN_FAILED');
    
    // echo $message;
    // echo "afwefw;";
    


// handle case where user is not authenticated
if ($result['status'] === 'error') {
    
    // create a new instance for unauthenticated routes from router class
    $notAuthenticatedRouter = new Router();

    if($GLOBALS['config']['devmode'] == 1){
        include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');  
    }


    // add the non-authenticated routes to the router
    $notAuthenticatedRouter->add('/register', 'UserController@register', 'POST');
    $notAuthenticatedRouter->add('/auth', 'UserController@authenticate', 'POST');
    $notAuthenticatedRouter->add('/devmode', 'DevController@getDevMode', 'GET');
    $notAuthenticatedRouter->add('/devmode/toggle', 'DevController@toggleDevMode', 'GET');
    $notAuthenticatedRouter->add('/devmode/toggle/:value', 'DevController@toggleDevModeValue', 'GET');

    // get all the routes that have been added to the router
    $routes = $notAuthenticatedRouter->getRoutes();
    
    //check if the requested route does not match one of the non-authenticated routes
    if(!$notAuthenticatedRouter->routeExists($GLOBALS['url_loc'], $routes)){
        http_response_code(ERROR_UNAUTHORIZED);
        echo $result['message'];
        exit();
    }
    
    // dispatch the request to the appropriate controller
    $notAuthenticatedRouter->dispatch($GLOBALS['url_loc'], $dbConnection, 1);
    exit();
}

// set user ID and token in global variable
$GLOBALS['user_id'] = $result['user_id'];

$GLOBALS['token'] = $result['token'];
$GLOBALS['logged_in'] = true;

// at this point we have our user_id and can set global data
include_once($GLOBALS['config']['private_folder'].'/data/user.php');

// if the user is authenticated, create a new instance of the Router class and dispatch the incoming request
$router = new Router();

// get an instance of the Devmode class
$devMode = new Dev($dbConnection);
$GLOBALS['config']['devmode'] = $devMode->getDevModeStatus();



// Fetch the public timeline (POST request)
$router->add('/timeline/publicTimeline', 'TimelineController@fetchPublicTimeline', 'POST');
$router->addDocumentation('/timeline/publicTimeline', 'POST', 'Fetches the public timeline.');

// Fetch the public timeline with a parameter (GET request)
$router->add('/timeline/:publicTimeline', 'TimelineController@fetchPublicTimelineParamTest', 'GET');
$router->addDocumentation('/timeline/:publicTimeline', 'GET', 'Fetches the public timeline with a parameter.');

// Get the development mode page (GET request)
$router->add('/devmode', 'DevController@getDevMode', 'GET');
$router->addDocumentation('/devmode', 'GET', 'Gets the development mode status.');

// Toggle development mode (GET request)
$router->add('/devmode/toggle', 'DevController@toggleDevMode', 'GET');
$router->addDocumentation('/devmode/toggle', 'GET', 'Toggles development mode.');

// Toggle development mode with a value (GET request)
$router->add('/devmode/toggle/:value', 'DevController@toggleDevModeValue', 'GET');
$router->addDocumentation('/devmode/toggle/:value', 'GET', 'Toggles development mode with a specific value.');

// Adjust user avatar (POST request)
$router->add('/settings/adjustAvatar', 'SettingsController@adjustAvatar', 'POST');
$router->addDocumentation('/settings/adjustAvatar', 'POST', 'Adjusts the user avatar settings.');

$router->add('/profile/getAvatar', 'ProfileController@showAvatar', 'GET');
$router->addDocumentation('/profile/getAvatar', 'GET', 'Fetches the avatar for the currently authenticated user.');

$router->add('/profile/getAvatar/:user_id', 'ProfileController@showAvatar', 'GET');
$router->addDocumentation('/profile/getAvatar/:user_id', 'GET', 'Fetches the avatar for the specified user by their user ID.');

// Return a list of all integrations for the authenticated user
$router->add('/integrations', 'IntegrationController@getAllIntegrations', 'GET');
$router->addDocumentation('/integrations', 'GET', 'Returns a list of all integrations for the authenticated user.');

// Returns integration details based on the provided ID
$router->add('/integrations/:id', 'IntegrationController@getIntegration', 'GET');
$router->addDocumentation('/integrations/:id', 'GET', 'Returns integration details based on the provided ID.');

// Create a new integration for the authenticated user
$router->add('/integrations', 'IntegrationController@createIntegration', 'POST');
$router->addDocumentation('/integrations', 'POST', 'Creates a new integration for the authenticated user.');

// Update an existing integration for the authenticated user
$router->add('/integrations/:id', 'IntegrationController@updateIntegration', 'PUT', [
    'service' => 'body',   // Service comes from the request body
]);

// Delete an existing integration for the authenticated user
$router->add('/integrations/:id', 'IntegrationController@deleteIntegration', 'DELETE');
$router->addDocumentation('/integrations/:id', 'DELETE', 'Deletes an existing integration for the authenticated user.');

// Refresh the data for an integration for the authenticated user
$router->add('/integrations/:id/refresh', 'IntegrationController@refreshIntegrationData', 'POST');
$router->addDocumentation('/integrations/:id/refresh', 'POST', 'Refreshes the data for an integration for the authenticated user.');

// Add documentation to route
$router->addDocumentation('/integrations/:id', 'PUT', 'Updates an existing integration for the authenticated user.');
// Require a 'service' to be present in the request body
$router->enforceParameters('/integrations/:id', 'PUT', [
    'service' => 'body',   // Service comes from the request body
]);

// Create a New Post with Optional Expiration Time
$router->add('/post', 'PostController@createPost', 'POST');
$router->addDocumentation('/post', 'POST', 'Creates a new post with an optional expiration time.');
$router->enforceParameters('/post', 'POST', [
    'body' => 'body',            // Content of the post in the request body
    'to_whom' => 'body',         // Public (1) or private (2) post in the request body
    // 'expiration_time' => 'body' Optional expiration time in the request body
]);

// Retrieve a Single Post by ID
$router->add('/post/:postId', 'PostController@getSinglePost', 'GET');
$router->addDocumentation('/post/:postId', 'GET', 'Retrieves a single post by its Post ID.');

// Delete a post
$router->add('/post/:id', 'PostController@deletePost', 'DELETE');
$router->addDocumentation('/post/:id', 'DELETE', 'Deletes a post.');


// Update a Post with Optional Expiration Time
$router->add('/post/:id', 'PostController@updatePost', 'PUT');
$router->addDocumentation('/post/:id', 'PUT', 'Updates an existing post with an optional expiration time.');
$router->enforceParameters('/post/:id', 'PUT', [
    'body' => 'body',            // Content of the post in the request body
    'to_whom' => 'body',         // Public (1) or private (2) post in the request body
    // 'expiration_time' => 'body' Optional expiration time in the request body
]);

// Archive a Post by ID
$router->add('/post/:postId/archive', 'PostController@archivePost', 'POST');
$router->addDocumentation('/post/:postId/archive', 'POST', 'Archives a post by its ID.');

// Unarchive a Post by ID
$router->add('/post/:postId/unarchive', 'PostController@unarchivePost', 'POST');
$router->addDocumentation('/post/:postId/unarchive', 'POST', 'Unarchives a post by its ID.');

// View Archived Posts for User
$router->add('/users/archived-posts', 'PostController@viewArchivedPosts', 'GET');
$router->addDocumentation('/users/archived-posts', 'GET', 'Allows the user to view their archived posts.');

// View Archived Public Posts for User
$router->add('/users/archived-posts/public', 'PostController@viewArchivedPostsPublic', 'GET');
$router->addDocumentation('/users/archived-posts/public', 'GET', 'Allows the user to view their archived public posts.');

// View Archived Private Posts for User
$router->add('/users/archived-posts/private', 'PostController@viewArchivedPostsPrivate', 'GET');
$router->addDocumentation('/users/archived-posts/private', 'GET', 'Allows the user to view their archived private posts.');

// Retrieve Posts in a User's Public Feed
$router->add('/posts/feed/public', 'PostController@getPublicFeedPosts', 'GET');
$router->addDocumentation('/posts/feed/public', 'GET', 'Retrieves posts in the user\'s public feed.');

// Retrieve Posts in Current User's Private Feed
$router->add('/posts/feed/private', 'PostController@getPrivateFeedPosts', 'GET');
$router->addDocumentation('/posts/feed/private', 'GET', 'Retrieves posts in the user\'s private feed.');

// Retrieve Posts in another User's Public Feed
$router->add('/posts/feed/:userid/public', 'PostController@getPublicFeedPosts', 'GET');
$router->addDocumentation('/posts/feed/:userid/public', 'GET', 'Retrieves posts in another user\'s public feed.');

// Retrieve Posts in another User's Private Feed
$router->add('/posts/feed/:userid/private', 'PostController@getPrivateFeedPosts', 'GET');
$router->addDocumentation('/posts/feed/:userid/private', 'GET', 'Retrieves posts in another user\'s private feed.');

// Retrieve Comments for a Post
$router->add('/post/:id/comments', 'CommentController@getPostComments', 'GET');
$router->addDocumentation('/post/:id/comments', 'GET', 'Retrieves comments for a specific post.');

// Create a Comment on a Post
$router->add('/post/:id/comment', 'CommentController@createPostComment', 'POST');
$router->addDocumentation('/post/:id/comment', 'POST', 'Creates a new comment on a post.');
$router->enforceParameters('/post/:id/comment', 'POST', [
    'comment' => 'body',       // Content of the comment in the request body
]);

// Delete a comment
$router->add('/comment/:id', 'CommentController@deleteComment', 'DELETE');
$router->addDocumentation('/comment/:id', 'DELETE', 'Deletes a comment.');


//POST /logout
//Description: Logs out the user from the current device and invalidates the token unless all_devices is passed as true, in which case, the user is logged out from all devices, and all tokens are invalidated.
//Request Body: {
//"token": "string",
//"all_devices": "boolean"
//}
//token (string): The token passed for auth interceptor as header, also identifier for the user's current device, used to fetch the user_id.
//all_devices (boolean): If true, logs out the user from all devices, passed as a boolean value in the request body.
    $router->add('/logout', 'UserController@logoutAll', 'GET');

//POST /logout/:id
//Description: Logs out the user from a specific device and invalidates the token associated with that device.
//Request Body: {
//"token": "string",
//"token_to_logout": "string"
//}
//token (string): The token passed for auth interceptor as header, also identifier for the user's current device, used to fetch the user_id.
//token_to_logout (string): The token of the device to log out from. This is passed as a string value in the request body.

$router->add('/logout', 'UserController@logout', 'POST');
$router->add('/logout/:deviceToken', 'UserController@logoutAllParam', 'GET');
//$router->add('/logout/:deviceToken/:param2/:optionalParam?', 'UserController@logoutMultipleParams', 'GET');

//implement next..
$router->add('/logout/:deviceToken/:param2/:optionalParam', 'UserController@theOnewokring', 'GET');

if($GLOBALS['config']['devmode'] == 1){
    $router->add('/list-routes', 'DevController@listRoutes', 'GET');

    // TODO: LIST CONSTANTS ENDPOINT
}
$GLOBALS['config']['testmode'] = 0; //This disables testing

//dispatch router since authentication and global variables are set!
$router->dispatch($GLOBALS['url_loc'], $dbConnection, $GLOBALS['config']['devmode']);
$GLOBALS['config']['testmode'] = 1; //This enables testing
if($GLOBALS['config']['devmode'] == 1){
    include($GLOBALS['config']['private_folder'].'/frontend/devmode.php');  
}
// Check if we're in devmode
if ($GLOBALS['config']['devmode'] && $GLOBALS['config']['testmode']) {
    // Run testing script
    include_once($GLOBALS['config']['private_folder'].'/tests/tests.php');
    $GLOBALS['config']['testmode'] = 0; //This disables testing
}


//// router
//switch($GLOBALS['url_loc'][1]) {
//    case "/":
//        break;
//    case "timeline":
//        include($GLOBALS['config']['private_folder'].'/backend/timeline.php');
//        include($GLOBALS['config']['private_folder'].'/frontend/timeline.php');
//        break;
//    default:
//        break;
//}

// unset token to prevent accidental use
unset($token);
ob_end_flush();

exit();
?>
