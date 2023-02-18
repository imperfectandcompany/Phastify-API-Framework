<?php
//Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');

class UserController {
    
    protected $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function register() {
        header('Content-Type: application/json');
        // retrieve the post body from the request
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        // validate email and password fields
        if (!isset($postBody->email) || empty($postBody->email)) {
            echo "Error: Email field is required";
            http_response_code(ERROR_BAD_REQUEST);
            exit;
        }
        if (!isset($postBody->password) || empty($postBody->password)) {
            echo "Error: Password field is required";
            http_response_code(ERROR_BAD_REQUEST);
            exit;
        }
        
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
}
 ?>
