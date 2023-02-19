<?php
// Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');

class UserController {
    
    protected $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
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
    
    public function register() {
//        header('Content-Type: application/json');
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
    
    public function authenticate() {
        // Set the response content type to JSON
        header('Content-Type: application/json');
        
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
            $dbPassword = $emailPassword;
            $uid = $user->getUidFromEmail($identifier);
        } else {
            $userPassword = $user->getPasswordFromUsername($identifier);
            if ($userPassword) {
                $dbPassword = $userPassword;
                $uid = $user->getUidFromUsername($identifier);
            } else {
                // Return an error if the user cannot be found
                echo json_encode(array('status' => 'error', 'message' => 'User not found'));
                http_response_code(ERROR_NOT_FOUND);
                exit;
            }
        }

        // Check if the password is correct
        if (password_verify($password, $dbPassword)) {
            // Generate a token
            $cstrong = True;
            $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
            // Save the token in the database
            $user->setToken($uid, $token);
            // Return the token to the client
            echo json_encode(array('status' => 'success', 'token' => $token));
            http_response_code(SUCCESS_OK);
            exit;
        } else {
            // Return an error if the password is incorrect
            echo json_encode(array('status' => 'error', 'message' => 'Invalid password'));
            http_response_code(ERROR_UNAUTHORIZED);
            exit;
        }
    }
}
 ?>