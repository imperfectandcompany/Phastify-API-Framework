<?php
// Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');
include($GLOBALS['config']['private_folder'].'/classes/class.device.php');

/**
 * UserController handles user authentication.
 */
class UserController {
    
    protected $dbConnection;
    protected $logger;

    public function __construct($dbConnection, $logger)
    {
        $this->dbConnection = $dbConnection;
        $this->logger = $logger;
    }

    /**
     * Checks if the given input fields are set and not empty.
     * Returns an error message if any of the fields are missing.
     */
    private function checkInputFields($inputFields, $postBody) {
        foreach ($inputFields as $field) {
            if (!isset($postBody->{$field}) || empty($postBody->{$field})) {
                $error = "Error: " . ucfirst($field) . " field is required";
                echo json_encode(array('status' => 'error', 'message' => $error));
                http_response_code(ERROR_BAD_REQUEST);
                exit;
            }
        }
    }
    
    //TODO: implement device check for logs. See if ip has registered before. Also save device on register, without UID (unclaimed). No login_tokens association until logged in.
    public function register() {
        //header('Content-Type: application/json');
        // Retrieve the post body from the request
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        // Validate email and password fields
        $this->checkInputFields(['email', 'password'], $postBody);

        $email = strtolower($postBody->email);
        $password = $postBody->password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Error: Invalid email format";
            echo json_encode(array('status' => 'error', 'message' => $error));
            http_response_code(ERROR_INVALID_INPUT);
            exit;
        }

        if (strlen($password) < 6) {
            $error = "Error: Password must be at least 6 characters";
            echo json_encode(array('status' => 'error', 'message' => $error));
            http_response_code(ERROR_INVALID_INPUT);
            exit;
        }

        // check if the email already exists in the database
        $user = new User($this->dbConnection);
        $result = $user->getUserByEmail($email);
        if ($result) {
            $error = "Error: Email already exists";
            echo json_encode(array('status' => 'error', 'message' => $error));
            http_response_code(ERROR_USER_ALREADY_EXISTS);
            exit;
        }

        // create the new user
        $newUser = $user->createUser($email, $password);

        if ($newUser) {
            echo json_encode(array('status' => 'success', 'message' => 'Account registered'));
            http_response_code(SUCCESS_OK);
        } else {
            $error = "Error: Unable to register account";
            echo json_encode(array('status' => 'error', 'message' => $error));
            http_response_code(ERROR_INTERNAL_SERVER);
        }
    }
    
    /**
     * Authenticate a user.
     *
     * @throws Exception If an unexpected error occurs.
     */    
    public function authenticate() {
        // Log the start of the authentication process
        $this->logger->log(0, 'authentication_start', $_SERVER);
        $username = false;
        $email = false;
        try {
        // Parse the request body
        $postBody = json_decode(file_get_contents("php://input"));

        // Check that the required fields are present and not empty
        $this->checkInputFields(['username', 'password'], $postBody);

        // Extract the username and password from the request body
        $identifier = $postBody->username;
        $password = $postBody->password;
        
        // Query the database for the user with the given username
        $user = new User($this->dbConnection);
        
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
                // Return an error if the user cannot be found
                echo json_encode(array('status' => 'error', 'message' => 'User not found'));
                http_response_code(ERROR_NOT_FOUND);
                return false; 
            }
        }

        // Check if the password is correct
        if (password_verify($password, $dbPassword)) {
            throwSuccess('Provided password was correct');
            // Log a successful login
            $this->logger->log($uid, 'login_success');

            // Save Device of user logging in
            $device = new Device($this->dbConnection, $this->logger);

            $deviceId = $device->saveDevice($uid);
            if($deviceId){
            throwSuccess('Device saved');
            // Save the token in the database
                if(($device->associateDeviceIdWithLogin($uid, $deviceId, $device->getDevice(), $_SERVER['REMOTE_ADDR']))){
                    $token = $user->setToken($uid, $deviceId);
                    if(!$token){
                        // Return an error if the password is incorrect
                        sendResponse('error', ['message' => "Token could not be saved."], ERROR_INTERNAL_SERVER);
                        http_response_code(ERROR_UNAUTHORIZED);
                        return false;
                    }
                    // Return the token to the client
                    sendResponse('success', ['token' => $token], SUCCESS_OK);
                    return true;
                } else {
                    throwError('Device not associated with login');
                    sendResponse('error', ['message' => "Device of user could not be associated with login."], ERROR_INTERNAL_SERVER);
                    return false;
                }
            } else {
                throwError('Device not saved');
                sendResponse('error', ['message' => "Device of user could not be saved."], ERROR_INTERNAL_SERVER);
                return false;
            }
        } else {
            throwError('Provided password was incorrect');
            // use later once logging becomes really serious
            //$identifierKey = $email === true ? "email" : "username";

            // Log a failed login attempt
            $this->logger->log(0, 'login_failed', ['user_id' => $uid, 'ip' => $_SERVER['REMOTE_ADDR']]);
            
            // Return an error if the password is incorrect
            echo json_encode(array('status' => 'error', 'message' => 'Invalid password'));

            // It was an invalid password but we don't want to confirm or deny info just in case it was an opp
            sendResponse('error', ['message' => "Invalid Username or Password."], ERROR_UNAUTHORIZED);
            return false;
        }
    } catch (Exception $e) {
        // Handle unexpected exceptions and log them
        $this->logger->log(0, 'authentication_error', ['error_message' => $e->getMessage()]);
        // Return an error response
        echo json_encode(array('status' => 'error', 'message' => 'An unexpected error occurred.'));
        http_response_code(ERROR_INTERNAL_SERVER);
        return false;
    }
}
    
    public function removeToken($uid, $token = null) {
        if ($token) {
            // Delete the specified token
            $result = $this->dbObject->deleteData('login_tokens', 'WHERE user_id = ? AND token = ?', array(array('value' => $uid, 'type' => PDO::PARAM_INT), array('value' => sha1($token), 'type' => PDO::PARAM_STR)));
        } else {
            // Delete all tokens for the user
            $result = $this->dbObject->deleteData('login_tokens', 'WHERE user_id = ?', array(array('value' => $uid, 'type' => PDO::PARAM_INT)));
        }

        return $result;
    }
    
    public function logout() {
        
        // Return an error if the password is incorrect
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout'));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;

    }

    public function logoutAll() {
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout all'));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;
    }
    
    public function logoutAllParam(string $deviceToken) {
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout all', 'token' => $deviceToken));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;
    }
    
    public function logoutMultipleParams(string $deviceToken, int $param2, ?string $optionalParam = "jcas") {
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout all', 'token' => $deviceToken, 'param' => $param2, 'Optional' => $optionalParam));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;
    }

    public function theOnewokring(string $deviceToken, int $toggle, string $optionalParam) {
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout all', 'token' => $deviceToken, 'param' => $toggle, 'Optional' => $optionalParam));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;
    }

    //implement next..
    public function logoutMultOptional(string $deviceToken, int $param2, ?string $optionalParam = "default fr") {
        echo json_encode(array('status' => 'testing', 'message' => 'cant logout all', 'token' => $deviceToken, 'param' => $param2, 'optional' => $optionalParam));
        http_response_code(ERROR_UNAUTHORIZED);
        exit;
    }
    
    
    
    
}
 ?>
