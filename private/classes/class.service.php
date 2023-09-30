<?php
include($GLOBALS['config']['private_folder'] . '/classes/class.roles.php');


class Service
{
    private $dbConnection;
    private $roles;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $roles = new Roles($dbConnection);
        $this->roles = $roles;
    }

    // Create a new service
    public function createService()
    {
        $postBody = json_decode(static::getInputStream());
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_last_error_msg();
        }

        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to create a service'], ERROR_FORBIDDEN);
            return;
        }

        try {
            $postBody = json_decode(file_get_contents("php://input"));
            CheckInputFields(['name', 'description'], $postBody);

            // Check if the service with the same name already exists
            if ($this->isServiceNameExists($postBody->name)) {
                throw new Exception('Service with the same name already exists', ERROR_BAD_REQUEST);
            }

            // Check if the service with the same URL already exists
            if ($this->isServiceUrlExists($postBody->url)) {
                throw new Exception('Service with the same URL already exists', ERROR_BAD_REQUEST);
            }

            $result = $this->insertServiceData($postBody);

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Service Added'));
                http_response_code(SUCCESS_CREATED);
            } else {
                throw new Exception('Unable to create service', ERROR_INTERNAL_SERVER);
            }
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
            http_response_code($e->getCode() ?: ERROR_BAD_REQUEST);
        }
    }

    // Insert service data into the database
    private function insertServiceData($data)
    {
        // Define columns that can be inserted into
        $allowedColumns = ['name', 'description', 'logo', 'url', 'available', 'client_id'];

        // Filter data to contain only allowed columns and ensure values are not empty
        $filteredData = array_filter($data, function ($value, $key) use ($allowedColumns) {
            return in_array($key, $allowedColumns) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        // Create dynamic rows and values for SQL statement
        $rows = implode(", ", array_keys($filteredData));
        $values = ':' . implode(", :", array_keys($filteredData));

        $filterParams = makeFilterParams($filteredData); // Make sure this function is accessible here

        // This assumes the insertData method returns true/false based on success. 
        // Modify as needed if it returns something different.

        if (!$this->dbConnection->insertData('services', $rows, $values, $filterParams)) {
            throw new Exception('Failed to insert data into the database.', ERROR_INTERNAL_SERVER);
        }
        return true;
    }

    // Get a service by name and client ID
    private function getServiceByNameAndClientId($name, $client_id)
    {

        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to get a service'], ERROR_FORBIDDEN);
            return;
        }

        $table = 'services';
        $select = 'id';
        $whereClause = 'WHERE name = :name AND client_id = :client_id';
        $params = [
            'name' => $name,
            'client_id' => $client_id
        ];
        $filterParams = makeFilterParams($params);

        $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return $result;
    }

    // Check if a service with the same URL exists
    private function doesServiceUrlExist($url)
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to see if a service url exists'], ERROR_FORBIDDEN);
            return;
        }
        $table = 'services';
        $select = 'id';
        $whereClause = 'WHERE url = :url';
        $params = ['url' => $url];
        $filterParams = makeFilterParams($params);

        $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return !empty($result);
    }

    public function getNewlyCreatedServiceId()
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to get newly created service id'], ERROR_FORBIDDEN);
            return;
        }
        return $this->getLastInsertedId();
    }

    protected function getLastInsertedId()
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to get lsat inserted id'], ERROR_FORBIDDEN);
            return;
        }

        $query = "SELECT LAST_INSERT_ID() as last_id";
        $result = $this->dbConnection->query($query);

        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row['last_id'];
        } else {
            return null;
        }
    }

    protected static function getInputStream()
    {
        return file_get_contents('php://input');
    }

}

?>