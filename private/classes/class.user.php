<?php

class User {
    
    private $dbObject;
    
    /**
     * Constructor for the User class.
     *
     * @param DatabaseConnector $dbObject A database connection object
     */
    public function __construct($dbObject)
    {
        $this->dbObject = $dbObject;
    }

    /**
     * Verifies the token and returns the associated user ID if the token is valid.
     *
     * @param string $token The token to verify
     *
     * @return int|false Returns the associated user ID if the token is valid, or false if the token is invalid
     */
    public function verifyToken($token) {
        // Query the database for the user ID associated with the token
        $sql = "SELECT user_id FROM login_tokens WHERE token = ?";
        // Hash the token to match the stored token
        $token_hash = sha1($token);
        $result = $this->dbObject->query($sql, array($token_hash));
        // If the query returned a result, return the user ID associated with the token
        if ($result && count($result) > 0) {
            return $result[0]['user_id'];
        } else {
            // If the query did not return a result, the token is invalid
            return false;
        }
    }

    private function generateAndAssociateToken($uid, $deviceInfo) {
        // Implement logic to generate and associate a token with the user and device
        try {
            $token = generateNewToken(); // Implement this function to generate a unique token
            
            if ($token) {
                $query = "INSERT INTO login_tokens (token, user_id, device_name, expiration_time) 
                          VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";
                $params = [$token, $uid, $deviceInfo['device_name']];
                $this->dbObject->query($query, $params);
                return $token;
            }
            
            return false;
        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'token_generation_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }
    
    public function getPasswordFromEmail($email) {
        $table = 'users';
        $select = 'password';
        $whereClause = 'WHERE email = :email';
        $filterParams = makeFilterParams($email);

        $result = $this->dbObject->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return $result ? $result['password'] : null;
    }
    
    public function getPasswordFromUsername($username) {
        $table = 'users';
        $select = 'password';
        $whereClause = 'WHERE username = :username';
        $filterParams = makeFilterParams($username);

        $result = $this->dbObject->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return $result ? $result['password'] : null;
    }

    /**
     * Queries the database for a user with the given email and returns their unique identifier.
     *
     * @param string $email The email of the user to query
     *
     * @return int|false Returns the user's unique identifier if the query is successful, or false if the user cannot be found
     */
    public function getUidFromEmail($email) {
        $table = 'users';
        $select = 'id';
        $whereClause = 'WHERE email = :email';
        $filterParams = makeFilterParams($email);

        $result = $this->dbObject->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return $result ? $result['id'] : null;
    }
    

    
    
    /**
     * Get user by email address
     *
     * @param string $email User email
     * @return array User Id
     */
    public function getUserByEmail($email) {
        $result = $this->dbObject->query('SELECT id from users WHERE email=:email', array(
            ':email' => strtolower($email)
        ));
        if ($result && count($result) > 0) {
            return $result[0]['id'];
        } else {
            return false;
        }
    }
    
    public function createUser($email, $password) {
        $result = $this->dbObject->query('INSERT INTO users (email, password, verified) VALUES (:email, :password, :verified)', array(
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':verified' => 0
        ));
        return $result !== false;
    }
    

    
    /**
         * Queries the database for a user with the given username and returns their unique identifier.
         *
         * @param string $username The username of the user to query
         *
         * @return int|false Returns the unique identifier of the user, or false if the user is not found
         */
        public function getUidFromUsername($username) {
            $sql = "SELECT id FROM users WHERE username = ?";
            $result = $this->dbObject->query($sql, array($username));
            if ($result && count($result) > 0) {
                return $result[0]['id'];
            } else {
                return false;
            }
        }
    
    /**
     * Sets the database to set the token for the user with the given unique identifier.
     *
     * @param int $uid The unique identifier of the user
     *
     * @return string|false Returns the newly generated token if it was set successfully, or false otherwise
     */
    public function setToken($uid, $deviceId) {
        // Generate a token
        $cstrong = True;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));

        // Hash the token for security
        $token_hash = sha1($token);
        // Prepare the SQL statement to insert a new record with the user ID and hashed token
        $rows = 'user_id, token, device_id';
        $values = '?, ?, device_id';
        $paramValues = array($uid, $token_hash);
        $filterParams = makeFilterParams($paramValues);
        $result = $this->dbObject->insertData('login_tokens', $rows, $values, $filterParams);

        // Check if the insert was successful and return the token if so
        return $result !== false ? $token : false;
    }

}
