<?php

class Device {
    
    private $dbObject;
    private $logger;

    
    /**
     * Constructor for the User class.
     *
     * @param DatabaseConnector $dbObject A database connection object
     */
    public function __construct($dbObject, $logger)
    {
        $this->dbObject = $dbObject;
        $this->logger = $logger;
    }

    /**
     * Sets the database to set the device for the user with the given unique identifier.
     *
     * @param int $uid The unique    identifier of the user
     *
     * @return int|false Returns the newly generated token if it was set successfully, or false otherwise
     */
    public function saveDevice($uid) {
        // Get device information (you can implement this)
        $deviceInfo = $this->getDeviceInfo();

        if (!$deviceInfo) {
            // Handle error, device information not available
            return false;
        }

        // Check if this is the user's first login (based on username availability)
        $isFirstLogin = $this->isFirstLogin($uid);
        
        if($isFirstLogin){
            throwSuccess('First Login');
            $this->logger->log($uid, 'first_login', $deviceInfo);
        } else {
            throwWarning('Not First Login');
            $this->logger->log($uid, 'not_first_login', $deviceInfo);
        }

        // Determine whether the token is expired 
        $isTokenExpired = $this->isTokenExpired($uid);
        
        // Log the device login action
        $this->logger->log($uid, 'device_login', $deviceInfo);
        try {

        if ($isFirstLogin || $isTokenExpired) {
            // Generate a new token and associate it with the device
            $token = $this->generateToken($uid, $deviceInfo['device_name']);
            
            if ($token) {
                $this->logger->log($uid, 'token_generated', ['device_name' => $deviceInfo['device_name']]);
            } else {
                // Handle token generation failure
                $this->logger->log($uid, 'token_generation_failed', ['device_name' => $deviceInfo['device_name']]);
                return false;
            }
        }

        // Update the device information in the devices table
        $query = "INSERT INTO devices (user_id, device_name, first_login, last_login, is_logged_in, expired) VALUES (?, ?, NOW(), NOW(), 1, DATE_ADD(NOW(), INTERVAL 7 DAY))";
        $params = [$uid, $deviceInfo['device_name']];
        return $this->dbObject->query($query, $params);
        
        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'device_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }

    private function isTokenExpired($uid) {
        // Implement logic to check if the user's token is expired
    }

    
    private function generateToken($uid, $deviceName) {
        // Generate a new token and associate it with the device
        $token = generateNewToken(); // Implement this function to generate a unique token
        
        if ($token) {
            $query = "INSERT INTO login_tokens (token, user_id, device_id, expiration_time) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";
            $params = [$token, $uid, $deviceName];
            $this->dbObject->query($query, $params);
            return $token;
        }
        
        return false;
    }

    /**
     * Retrieves device information.
     *
     * @return array|false Returns device information if available, or false otherwise.
     */
    private function getDeviceInfo()
    {
        try {
            // You can implement logic to get actual device information here,
            // but for this example, we'll use sample data.
            return [
                'device_name' => 'Sample Device',
                'os' => 'Android',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ];
        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'device_info_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Checks if it's the user's first login.
     *
     * @param int $uid The unique identifier of the user.
     *
     * @return bool|false Returns true if it's the user's first login, false if not, or false on error.
     */
    private function isFirstLogin($uid)
    {
        try {
            // Query the users table to check if the username is empty for the given user.
            $query = "SELECT username FROM users WHERE id = ?";
            $params = [$uid];
            $result = $this->dbObject->querySingleRow($query, $params);

            // If the username is empty, it's the user's first login.
            return empty($result['username']);

        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'first_login_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }



}
