<?php
    include($GLOBALS['config']['private_folder'].'/classes/class.integration.php');

    class ProfileController {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
        }
        
        public function showAvatar($user_id = null){
            // Check if we passed a specific user ID before using the global user ID / avatar
            if($user_id == null){ 
                if($GLOBALS['user_data']['avatar']){ sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return; }
                if($GLOBALS['user_id'] == null){ sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return; }
                $user_id = $GLOBALS['user_id'];
             }

            $GLOBALS['user_data']['avatar'] = $this->dbConnection->viewSingleData("users", "avatar", "WHERE id = ?", array(array("value" => $user_id, "type" => PDO::PARAM_INT)))['result']['avatar'];
            if($GLOBALS['user_data']['avatar'] == ""){ $GLOBALS['user_data']['avatar'] = $GLOBALS['config']['default_avatar']; }
            sendResponse('success', ['avatar' => $GLOBALS['user_data']['avatar']], SUCCESS_OK); return;
        }


    }
    ?>
