<?php
class TokenService
{
    private $dbObject;
    private $logger;

    public function __construct($dbObject, $logger)
    {
        $this->dbObject = $dbObject;
        $this->logger = $logger;
    }

    /**
     * Get tokens by user ID.
     *
     * @param int $userId The unique identifier of the user.
     *
     * @return array An array of tokens associated with the user.
     */
    public function getTokensByUserId($userId)
    {
        try {
            $query = "WHERE user_id = ?";
            $params = makeFilterParams($userId);
            // Use DatabaseConnector's viewData method to execute the query
            $results = $this->dbObject->viewData('login_tokens', '*', $query, $params);

            // Return the retrieved tokens
            return $results['results'];
        } catch (Exception $e) {
            // Handle any exceptions and log them
            $this->logger->log(0, 'get_tokens_by_user_id_error', ['error_message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get tokens associated with an IP address.
     *
     * @param string $ip The IP address to retrieve tokens for.
     *
     * @return array An array of tokens associated with the IP address.
     */
    public function getTokensByIp($ip)
    {
        try {
            // Step 1: Get device IDs associated with the IP address
            $query = "WHERE ip_address = ?";
            $params = makeFilterParams($ip);
            $deviceIpData = $this->dbObject->viewData('device_ips', '*', $query, $params);
    
            // Step 2: Get tokens associated with each device ID
            $tokens = [];
            foreach ($deviceIpData as $ipData) {
                if (isset($ipData['device_id'])) {
                    $deviceId = $ipData['device_id']; // Extract the device_id
                    $query = "WHERE device_id = ?";
                    $params = makeFilterParams($deviceId);
                    $results = $this->dbObject->viewData('login_tokens', 'token', $query, $params);
                    
                    foreach ($results as $result) {
                        // Ensure that 'token' key exists in the $result array
                        if (isset($result['token'])) {
                            $tokens[] = $result['token'];
                        }                    
                    }
                }
            }
    
            return $tokens;
        } catch (Exception $e) {
            $this->logger->log(0, 'get_tokens_by_ip_error', ['error_message' => $e->getMessage()]);
            return [];
        }
    }
    
}