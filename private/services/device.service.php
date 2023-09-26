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
}