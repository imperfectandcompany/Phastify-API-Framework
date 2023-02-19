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
    
    /**
     * Queries the database for a user with the given email and returns their password (hashed and salted).
     *
     * @param string $email The email of the user to query
     *
     * @return string|false Returns the user's password if the query is successful, or false if the user cannot be found
     */
    public function getPasswordFromEmail($email) {
        $result = $this->dbObject->viewSingleData('users', 'password', 'WHERE email = :email', array(':email' => strtolower($email)));
        if ($result && count($result) > 0) {
            return $result['password'];
        } else {
            return false;
        }
    }

    /**
     * Queries the database for a user with the given email and returns their unique identifier.
     *
     * @param string $email The email of the user to query
     *
     * @return int|false Returns the user's unique identifier if the query is successful, or false if the user cannot be found
     */
    public function getUidFromEmail($email) {
        $result = $this->dbObject->viewSingleData('users', 'id', 'WHERE email = :email', array(':email' => strtolower($email)));
        if ($result && count($result) > 0) {
            return $result['id'];
        } else {
            return false;
        }
    }

    /**
     * Queries the database for a user with the given username and returns their password (hashed and salted).
     *
     * @param string $username The username of the user to query
     *
     * @return string|false Returns the user's password if the query is successful, or false if the user cannot be found
     */
    public function getPasswordFromUsername($username) {
        $result = $this->dbObject->viewSingleData('users', 'password', 'WHERE username = :username', array(':username' => strtolower($username)));
        if ($result && count($result) > 0) {
            return $result['password'];
        } else {
            return false;
        }
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
     * Updates the database to set the token for the user with the given unique identifier.
     *
     * @param int $uid The unique identifier of the user
     * @param string $token The token to set
     *
     * @return bool Returns true if the token was set successfully, false otherwise
     */
    public function setToken($uid, $token) {
        // Hash the token for security
        $token_hash = sha1($token);
        
        // Prepare the SQL statement to update the token
        $sql = "UPDATE login_tokens SET token = ? WHERE user_id = ?";
        
        // Execute the SQL statement with the user ID and hashed token as parameters
        $result = $this->dbObject->query($sql, array($token_hash, $uid));
        
        // Check if the update was successful
        return $result !== false;
    }
}
