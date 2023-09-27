<?php
class DeviceService
{
    private $dbConnector;
    private $logger;

    public function __construct($dbConnector, $logger)
    {
        $this->dbConnector = $dbConnector;
        $this->logger = $logger;
    }

    public function getAssociatedIPsByDeviceId($deviceId)
    {
        try {
            $query = "WHERE device_id = ?";
            $params = makeFilterParams($deviceId);
            $results = $this->dbConnector->viewData('device_ips', '*', $query, $params);

            $ipAddresses = [];
            if ($results) {
                foreach ($results as $result) {
                    // Ensure that 'ip_address' key exists in the $result array
                    if (isset($result['ip_address'])) {
                        $ipAddresses[] = $result['ip_address'];
                    }
                }
            }

            return $ipAddresses;
        } catch (Exception $e) {
            $this->logger->log(0, 'get_associated_ips_by_device_error', ['error_message' => $e->getMessage()]);
            return [];
        }
    }

    //  Need to review because there can be multiple devices by user
    public function getDeviceIdByUserId($userId)
    {
        try {
            $table = 'devices';
            $select = 'id';
            $whereClause = "WHERE user_id = ?";
            $params = makeFilterParams($userId);
            $result = $this->dbConnector->viewSingleData($table, $select, $whereClause, $params);
            if ($result) {
                return $result["result"]["id"];
            }

            return null;
        } catch (Exception $e) {
            $this->logger->log(0, 'get_device_id_error', ['error_message' => $e->getMessage()]);
            return null;
        }
    }

    public function getDevicesByUserId($userId)
    {
        try {
            $table = 'devices';
            $select = '*';
            $whereClause = "WHERE user_id = ?";
            $params = makeFilterParams($userId);
            $result = $this->dbConnector->viewData($table, $select, $whereClause, $params);
            if ($result) {
                return $result["results"];
            }
            return null;
        } catch (Exception $e) {
            $this->logger->log(0, 'get_device_id_error', ['error_message' => $e->getMessage()]);
            return null;
        }
    }

    public function getAssociatedIPsByUserId($userId)
    {
        try {
            $params = makeFilterParams($userId);
            $results = $this->dbConnector->viewData("device_ips", "*", "WHERE user_id = ?", $params);
            if ($results) {
                $ipAddresses = [];
                foreach ($results as $result) {
                    $ipAddresses[] = $result['results']['ip_address'];
                }
                return $ipAddresses;
            }
            $this->logger->log($userId, 'get_associated_ips_error', ['error_message' => 'No results found']);
            return [];
        } catch (Exception $e) {
            $this->logger->log($userId, 'get_associated_ips_error', ['error_message' => $e->getMessage()]);
            return [];
        }

    }


    /**
     * Check if there are previous login activity logs, associated devices, or login tokens for the user.
     *
     * @param int $uid The unique identifier of the user.
     *
     * @return bool True if there is evidence of a previous login, false otherwise.
     */
    private function hasPreviousLoginLogs($uid)
    {
        // Check if there are previous login activity logs
        $userLogs = $this->logger->getUserLogsByAction($uid, 'login_success');

        // We can't get devices, just a single device because you can only have one device
        // Check if there are associated devices
        $associatedDevices = $this->getDevicesByUserId($uid);

        // Check if there are login tokens for the user
        $token = new Token($this->dbObject);
        $loginTokens = $token->getTokensByUserId($uid);

        // If there are previous login logs, associated devices, or login tokens, return true
        if (!empty($userLogs) || !empty($associatedDevices) || !empty($loginTokens)) {
            // Log that evidence of a previous login was found
            $this->logger->log($uid, 'previous_login_evidence', [
                'user_logs_count' => count($userLogs),
                'associated_devices_count' => count($associatedDevices),
                'login_tokens_count' => count($loginTokens),
            ]);
            return true;
        }

        return false;
    }

}