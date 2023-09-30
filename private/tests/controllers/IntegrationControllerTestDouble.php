<?php
include_once($GLOBALS['config']['private_folder'].'/controllers/IntegrationController.php');

    class IntegrationControllerTestDouble extends IntegrationController {
        protected static $inputStream;

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
