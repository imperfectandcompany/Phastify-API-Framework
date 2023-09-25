<?php

    class ProfileController {
        
        protected $dbConnection;
        protected $logger;

        public function __construct($dbConnection, $logger)
        {
            $this->dbConnection = $dbConnection;
            $this->logger = $logger;
        }
        
        public function showAvatar(int $user_id = null){
            if($user_id == null){ 
                if($GLOBALS['user_data']['avatar']){ sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return; }
                if($GLOBALS['user_id'] == null){ sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return; }
                $user_id = $GLOBALS['user_id'];
             }

            $GLOBALS['user_data']['avatar'] = $this->dbConnection->viewSingleData("users", "avatar", "WHERE id = ?", array(array("value" => $user_id, "type" => PDO::PARAM_INT)))['result']['avatar'] ?? $GLOBALS['config']['default_avatar'];
            if($GLOBALS['user_data']['avatar'] == ""){ $GLOBALS['user_data']['avatar'] = $GLOBALS['config']['default_avatar']; }
            sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return;
        }
    }
?>
