    <?php
    include($GLOBALS['config']['private_folder'].'/classes/class.integration.php');

    class IntegrationController {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
        }
        
        public function getAllIntegrations() {
            $integration = new Integration($this->dbConnection);
            $integrations = $integration->getIntegrationsByUserId($GLOBALS['user_id']);
            sendResponse('success', ['integrations' => $integrations], SUCCESS_OK);
        }
        
        public function getIntegration($id) {
            $integration = new Integration($this->dbConnection);

            // Check if integration exists before authorization check.
            if (!$integration->doesIntegrationExist($id)) {
                sendResponse('error', ['message' => 'Integration ID does not exist'], ERROR_NOT_FOUND);
                return;
            }
        
            if (!$integration->doesIntegrationBelongToUser($id, $GLOBALS['user_id'])) {
                sendResponse('error', ['message' => 'Unauthorized to view this integration'], ERROR_FORBIDDEN);
                return;
            }

            $integration = $integration->getIntegrationsById($id);


            sendResponse('success', ['integration' => $integration], SUCCESS_OK);
        }

        public function createIntegration() {
            try {
                $postBody = json_decode(file_get_contents("php://input"));
                CheckInputFields(['service', 'client_id', 'client_secret', 'access_token', 'token_type'], $postBody);

        
                $integration = new Integration($this->dbConnection);
                $result = $integration->createIntegrationForUser($GLOBALS['user_id'], $postBody);
        
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Integration created'));
                    http_response_code(SUCCESS_CREATED);
                } else {
                    throw new Exception('Unable to create integration', ERROR_INTERNAL_SERVER);
                }
            } catch (Exception $e) {
                echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
                http_response_code($e->getCode() ?: ERROR_BAD_REQUEST);
            }
        }
        
        public function updateIntegration($id) {
            $postBody = json_decode(file_get_contents("php://input"));

            $integration = new Integration($this->dbConnection);

            // Check if integration exists before authorization check.
            if (!$integration->doesIntegrationExist($id)) {
                sendResponse('error', ['message' => 'Integration ID does not exist'], ERROR_NOT_FOUND);
                return;
            }
        
            if (!$integration->doesIntegrationBelongToUser($id, $GLOBALS['user_id'])) {
                sendResponse('error', ['message' => 'Unauthorized to update this integration'], ERROR_FORBIDDEN);
                return;
            }
        
            $result = $integration->updateIntegrationById($id, $postBody);
        
            if ($result) {
                sendResponse('success', ['message' => 'Integration updated'], SUCCESS_OK);
            } else {
                sendResponse('error', ['message' => 'Unable to update integration'], ERROR_INTERNAL_SERVER);
            }
        }
        
        public function deleteIntegration($id) {
            $integration = new Integration($this->dbConnection);

            // Check if integration exists before authorization check.
            if (!$integration->doesIntegrationExist($id)) {
                sendResponse('error', ['message' => 'Integration ID does not exist'], ERROR_NOT_FOUND);
                return;
            }

            // Check if integration belongs to the authenticated user before deletion.
            if (!$integration->doesIntegrationBelongToUser($id, $GLOBALS['user_id'])) {
                sendResponse('error', ['message' => 'Unauthorized to delete this integration'], ERROR_FORBIDDEN);
                return;
            }

            $result = $integration->deleteIntegrationById($id);

            if ($result) {
                sendResponse('success', ['message' => 'Integration deleted'], SUCCESS_OK);
            } else {
                sendResponse('error', ['message' => 'Unable to delete integration'], ERROR_INTERNAL_SERVER);
            }
        }
        
        public function refreshIntegrationData($id) {
            $integration = new Integration($this->dbConnection);

            // The logic for refreshing data might be complex, involving third-party APIs. This is WIP.

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
