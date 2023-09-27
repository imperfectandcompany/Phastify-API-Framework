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
            $results = $this->dbObject->viewData('login_tokens', 'token, expiration_time', $query, $params);

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
     * @return bool true if tokens were found, false otherwise.
     */
    public function logTokensByIp($ip)
    {
        try {
            // Step 1: Get device IDs associated with the IP address
            $query = "WHERE ip_address = ?";
            $params = makeFilterParams($ip);
            $deviceIpData = $this->dbObject->viewData('device_ips', '*', $query, $params);

            // Step 2: Get tokens associated with each device ID
            $deviceIds = [];
            $foundTokenFromDeviceId = false;
            if ($deviceIpData['count'] > 0 && !empty($deviceIpData['results'])) {
                foreach ($deviceIpData["results"] as $ipData) {
                    if (!empty($ipData['device_id'])) {
                        $deviceId = $ipData['device_id']; // Extract the device_id
                        $query = "WHERE device_id = ?";
                        $params = makeFilterParams($deviceId);
                        // look for login tokens associated with the ip address that is paired with the device id
                        $results = $this->dbObject->viewData('login_tokens', 'token, user_id', $query, $params);
                        if ($results['count'] > 0 && !empty($results['results'])) {
                            foreach ($results["results"] as $result) {
                                // Ensure that 'token' key exists in the $result array
                                if (!empty($result["token"] && !empty($result["user_id"]))) {
                                    if ($foundTokenFromDeviceId == false) {
                                        $foundTokenFromDeviceId = true;
                                    }
                                    $tokenPairs[] = [
                                        'device_id' => $deviceId,
                                        'user_id' => $result["user_id"],
                                        'token' => $result["token"]
                                    ];
                                }
                            }
                        }
                    }
                }
                if ($foundTokenFromDeviceId && !empty($tokenPairs)) {
                    $tokenPairsCount = count($tokenPairs);
                    $this->logger->log(0, 'tokens_found_for_paired_ip_device_id', ['PairsFound' => $tokenPairsCount, 'TokenPairs' => $tokenPairs, 'IP' => $ip]);
                    return true;
                } else {
                    //No tokens found for device ID
                    $this->logger->log(0, 'no_tokens_found_for_paired_ip_device_id', ['IP' => $ip, 'Device ID' => $deviceIds]);
                    return false;
                }
            } else {
                $this->logger->log(0, 'no_device_ids_found_from_ip', ['error_message' => 'No device IDs found for IP address.', 'IP' => $ip]);
                return false;
            }
        } catch (Exception $e) {
            $this->logger->log(0, 'get_tokens_by_ip_error', ['error_message' => $e->getMessage()]);
            return false; // Return false to indicate an error occurred
        }
    }

}