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
    
    
}
