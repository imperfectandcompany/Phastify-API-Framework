<?php
include_once($GLOBALS['config']['private_folder'].'/controllers/IntegrationController.php');

    class IntegrationControllerTestDouble extends IntegrationController {
        protected static $inputStream;
        protected $dbConnection;

        public function __construct($dbConnection) {
            parent::__construct($dbConnection);
            $this->dbConnection = $dbConnection;
        }
    

        public static function setInputStream($input = 'php://input')
        {
            static::$inputStream = $input;
        }
    
        protected static function getInputStream()
        {
            return static::$inputStream;
        }

        public function getNewlyCreatedIntegrationId() {
            return $this->getLastInsertedId();
        }

        protected function getLastInsertedId() {
            // You should implement this method based on your specific database connector class.
            $query = "SELECT LAST_INSERT_ID() as last_id";
            $result = $this->dbConnection->query($query);
            
            if ($result) {
                $row = $result->fetch(PDO::FETCH_ASSOC);
                return $row['last_id'];
            } else {
                return null;
            }
        }
    }
?>
