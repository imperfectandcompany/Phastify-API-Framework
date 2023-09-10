    <?php
    include($GLOBALS['config']['private_folder'].'/classes/class.integration.php');

    class IntegrationController {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
        }

        /**
         * Utility function to check if the given input fields are set and not empty.
         * Returns an error message if any of the fields are missing.
         */
        private function checkInputFields($inputFields, $postBody) {
            foreach ($inputFields as $field) {
                if (!isset($postBody->{$field}) || empty($postBody->{$field})) {
                    $error = "Error: " . ucfirst($field) . " field is required";
                    echo json_encode(array('status' => 'error', 'message' => $error));
                    http_response_code(400);  // Bad Request
                    exit;
                }
            }
        }
        
        public function getAllIntegrations() {
            // Assuming an Integration class exists similar to the User class that handles database operations.
            $integration = new Integration($this->dbConnection);
            $integrations = $integration->getIntegrationsByUserId($GLOBALS['user_id']);  // Assuming user ID is stored in a session.

            echo json_encode(array('status' => 'success', 'integrations' => $integrations));
            http_response_code(200);  // OK
        }
        
        public function createIntegration() {
            $postBody = json_decode(file_get_contents("php://input"));
            $this->checkInputFields(['service', 'client_id', 'client_secret', 'access_token', 'token_type'], $postBody);
            
            $integration = new Integration($this->dbConnection);
            $result = $integration->createIntegrationForUser($_SESSION['user_id'], $postBody);

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Integration created'));
                http_response_code(201);  // Created
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Unable to create integration'));
                http_response_code(500);  // Internal Server Error
            }
        }
        
        public function updateIntegration($id) {
            $postBody = json_decode(file_get_contents("php://input"));
            $integration = new Integration($this->dbConnection);
            
            // We might want to check if integration belongs to the authenticated user before updating.

            $result = $integration->updateIntegrationById($id, $postBody);

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Integration updated'));
                http_response_code(200);  // OK
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Unable to update integration'));
                http_response_code(500);  // Internal Server Error
            }
        }
        
        public function deleteIntegration($id) {
            $integration = new Integration($this->dbConnection);

            // We might want to check if integration belongs to the authenticated user before deletion.

            $result = $integration->deleteIntegrationById($id);

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Integration deleted'));
                http_response_code(200);  // OK
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Unable to delete integration'));
                http_response_code(500);  // Internal Server Error
            }
        }
        
        public function refreshIntegrationData($id) {
            $integration = new Integration($this->dbConnection);

            // The logic for refreshing data might be complex, involving third-party APIs. This is just a basic template.

            $result = $integration->refreshDataForIntegration($id);

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Integration data refreshed'));
                http_response_code(200);  // OK
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Unable to refresh integration data'));
                http_response_code(500);  // Internal Server Error
            }
        }
        
    }

    ?>
