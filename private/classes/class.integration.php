<?php 

class Integration {

    private $dbConnection;
    
    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }
    
    // Get all integrations by user ID
    public function getIntegrationsByUserId($userId) {
        $table = 'integrations';
        $select = '*';
        $whereClause = 'WHERE user_id = :user_id';
        $filterParams = makeFilterParams($userId);

        return $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
    }

    // Get all integrations by user ID
    public function getIntegrationsById($id) {
        $table = 'integrations';
        $select = '*';
        $whereClause = 'WHERE id = :id';
        $filterParams = makeFilterParams($id);

        return $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
    }
    
    // Create a new integration for the user
    public function createIntegrationForUser($userId, $data) {
        $data = (array)$data;

        // Add user_id to data
        $data['user_id'] = $userId;
        
        // Define columns that can be inserted into
        $allowedColumns = ['user_id', 'service_id', 'client_id', 'client_secret', 'access_token', 'token_type', 'refresh_token', 'token_expiration', 'status', 'data'];
        
        // Check if unknown columns are present
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns)) {
                // Handle unknown column error
                throw new Exception("Unknown column {$key} provided.", ERROR_BAD_REQUEST);
            }
        }

        // Filter data to contain only allowed columns and ensure values are not empty
        $filteredData = array_filter($data, function($value, $key) use ($allowedColumns) {
            return in_array($key, $allowedColumns) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        // Create dynamic rows and values for SQL statement
        $rows = implode(", ", array_keys($filteredData));
        $values = ':' . implode(", :", array_keys($filteredData));
        
        $filterParams = makeFilterParams($filteredData); // make sure this function is accessible here
        
        // This assumes the insertData method returns true/false based on success. 
        // Modify as needed if it returns something different.

        if (!$this->dbConnection->insertData('integrations', $rows, $values, $filterParams)) {
            throw new Exception('Failed to insert data into the database.', ERROR_INTERNAL_SERVER);
        }
        
        return true;
    }

    
    // Update an integration by ID
    public function updateIntegrationById($id, $data) {
        $table = 'integrations';

        $data = (array)$data;
        // Add id to data
        $data['id'] = $id;

        // Define columns that can be inserted into
        $allowedColumns = ['id', 'user_id', 'service_id', 'client_id', 'client_secret', 'access_token', 'token_type', 'refresh_token', 'token_expiration', 'status', 'data'];

        // Check if unknown columns are present
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns)) {
                // Handle unknown column error
                throw new Exception("Unknown column {$key} provided.", ERROR_BAD_REQUEST);
            }
        }

        // Ensure ID exists. This is required for the WHERE clause.
        if (!array_key_exists("id", $data)) {
            // Handle unknown column error
            throw new Exception("Required column id missing.", ERROR_BAD_REQUEST);
        }

        // Filter data to contain only allowed columns and ensure values are not empty
        $filteredData = array_filter($data, function($value, $key) use ($allowedColumns) {
            return in_array($key, $allowedColumns) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        $filterParams = makeFilterParams($filteredData);

        $setParts = [];
        foreach ($filteredData as $column => $value) {
            if ($column != 'id') {  // Skip the id column for the SET clause
                $setParts[] = $column . ' = :' . $column;
            }
        }

        $setClause = implode(", ", $setParts);

        $whereClause = 'id = :id';

        return $this->dbConnection->updateData($table, $setClause, $whereClause, $filterParams);
    }
    
    // Delete an integration by ID
    public function deleteIntegrationById($id) {
        
        $whereClause = 'WHERE id = :id';
        $filterParams = makeFilterParams($id);

        return $this->dbConnection->deleteData('integrations', $whereClause, $filterParams);
    }

    // Refresh data for an integration (Stub function for now)
    public function refreshDataForIntegration($id) {
        // This method will potentially call an external API and then update the integration data.
        // For now, this is just a placeholder.
        return true;
    }
    
    // Check if an integration belongs to a specific user
    public function doesIntegrationBelongToUser($integrationId, $userId) {
        $table = 'integrations';
        $select = 'id';
        $whereClause = 'WHERE id = :integration_id AND user_id = :user_id';
        $params = [
            'integration_id' => $integrationId,
            'user_id' => $userId
        ];
        $filterParams = makeFilterParams($params);

        $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
        return !empty($result);
    }

        // Check if an integration exists
        public function doesIntegrationExist($integrationId) {
            $table = 'integrations';
            $select = 'id';
            $whereClause = 'WHERE id = :integration_id';
            $params = [
                'integration_id' => $integrationId,
            ];
            $filterParams = makeFilterParams($params);
    
            $result = $this->dbConnection->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
            return !empty($result);
        }

        // function to update integration visibility by id
        public function updateIntegrationVisibility($integrationId, $data){
            // Turn data into an array
            $data = (array)$data;

            // Add id to data
            $data['id'] = $integrationId;

            // Define columns that can be inserted into
            $allowedColumns = ['show_to_followers', 'show_to_contacts'];
            
            // Check if unknown columns are present
            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns)) {
                    // Handle unknown column error
                    throw new Exception("Unknown column {$key} provided.", ERROR_BAD_REQUEST);
                }
            }

            // Ensure ID exists. This is required for the WHERE clause.
            if (!array_key_exists("id", $data)) {
                // Handle unknown column error
                throw new Exception("Required column id missing.", ERROR_BAD_REQUEST);
            }

            // Filter data to contain only allowed columns and ensure values are not empty
            $filteredData = array_filter($data, function($value, $key) use ($allowedColumns) {
                return in_array($key, $allowedColumns) && !empty($value);
            }, ARRAY_FILTER_USE_BOTH);

            $filterParams = makeFilterParams($filteredData);

            $setParts = [];

            foreach ($filteredData as $column => $value) {
                if ($column != 'id') {  // Skip the id column for the SET clause
                    $setParts[] = $column . ' = :' . $column;
                }
            }

            $setClause = implode(", ", $setParts);

            $whereClause = 'id = :id';

            return $this->dbConnection->updateData('integrations', $setClause, $whereClause, $filterParams);
        }

}



?>
