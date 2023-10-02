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
        $postBody = json_decode(static::getInputStream(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON Decode Error: ' . json_last_error_msg();
        }

        // @role guard - only admins can create a service (TODO: future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to create a service'], ERROR_FORBIDDEN);
            return;
        }

        try {
            $missingFields = missingFields(['name', 'description'], $postBody);

            if (!empty($missingFields)) {
                $error = 'Error: ' . implode(', ', $missingFields) . ' field(s) is/are required';
                sendResponse('error', ['message' => $error], ERROR_INVALID_INPUT);
                return;
            }
    
            // Check if the service with the same name already exists
            if ($this->doesServiceNameExist($postBody["name"])) {
                throwError("Service with the same name already exists");
            }
    
            // Check if the service with the same URL already exists
            if ($this->doesServiceUrlExist($postBody["url"])) {
                throwError('Service with the same URL already exists');
            }
    
            $result = $this->insertServiceData($postBody);
    
            if ($result) {
                sendResponse('success', ['message' => 'Service Added'], SUCCESS_CREATED);
                return true;
            } else {
                sendResponse('error', ['message' => 'Unable to create service'], ERROR_INTERNAL_SERVER);
                return;
            }

        } catch (Exception $e) {
            sendResponse('error', ['message' => $e->getMessage()], ERROR_BAD_REQUEST);
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

    // Check if a service with the same URL exists
    private function doesServiceNameExist($name)
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to see if a service name exists'], ERROR_FORBIDDEN);
            return;
        }
        $table = 'services';
        $select = 'id';
        $whereClause = 'WHERE name = :name';
        $params = ['url' => $name];
        $filterParams = makeFilterParams($params);

        $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return !empty($result);
    }    

    public function getNewlyCreatedServiceId()
    {
        $lastInserted = $this->dbConnection->getConnection()->lastInsertId();
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if(!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to get newly created service id'], ERROR_FORBIDDEN);
            return;
        }
        return $lastInserted;
    }

    // Check if a service with the same URL exists
    private function doesServiceIdExist($id)
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to see if a service id exists'], ERROR_FORBIDDEN);
            return;
        }
        $table = 'services';
        $select = 'id';
        $whereClause = 'WHERE id = :id';
        $params = ['id' => $id];
        $filterParams = makeFilterParams($params);

        $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return !empty($result);
    }    

    public function deleteService($id) {
        // Check if service exists before authorization check.
        if (!$this->doesServiceIdExist($id)) {
            sendResponse('error', ['message' => 'service ID does not exist'], ERROR_NOT_FOUND);
            return;
        }

        // @role guard - only admins can create a service (future update, create decorators for functions)
        if(!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to delete service id'], ERROR_FORBIDDEN);
            return false;
        }
        $result = $this->deleteServiceById($id);
        if ($result) {
            sendResponse('success', ['message' => 'Service deleted'], SUCCESS_OK);
            return true;
        } else {
            sendResponse('error', ['message' => 'Unable to delete service'], ERROR_INTERNAL_SERVER);
            return false;
        }
    }

        // Delete an integration by ID
        public function deleteServiceById($id) {
            $whereClause = 'WHERE id = :id';
            $filterParams = makeFilterParams($id);
    
            return $this->dbConnection->deleteData('services', $whereClause, $filterParams);
        }

    protected function getLastInsertedId()
    {
        // @role guard - only admins can create a service (future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to get last inserted id'], ERROR_FORBIDDEN);
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