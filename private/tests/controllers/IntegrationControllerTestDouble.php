<?php
include_once($GLOBALS['config']['private_folder'] . '/controllers/IntegrationController.php');
include_once($GLOBALS['config']['private_folder'] . '/tests/classes/class.serviceTestDouble.php');

class IntegrationControllerTestDouble extends IntegrationController
{
    protected static $inputStream;

    public function __construct($dbConnection, $logger)
    {
        parent::__construct($dbConnection, $logger);
        // Create an instance of ServiceTestDouble and pass the database connection
        $service = new ServiceTestDouble($dbConnection);

        $this->service = $service;
    }

    public static function setInputStream($input = 'php://input')
    {
        static::$inputStream = $input;
    }

    protected static function getInputStream()
    {
        return static::$inputStream;
    }

    public function getNewlyCreatedIntegrationId()
    {
        return $this->getLastInsertedId();
    }

    protected function getLastInsertedId()
    {
        $query = "SELECT LAST_INSERT_ID() as last_id";
        $result = $this->dbConnection->query($query);

        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row['last_id'];
        } else {
            return null;
        }
    }

    public function getNewlyCreatedIntegrationIdTest()
    {
        return $this->dbConnection->getConnection()->lastInsertId();
    }

    public function getIntegration($id) {
        $integration = $this->integration;

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

        return $integration;
    }

}
?>
