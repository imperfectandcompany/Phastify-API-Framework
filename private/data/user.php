<?php
if($dbConnection && $GLOBALS['logged_in']){
    //We need to make sure all of our global variables are lined up right.
    if($GLOBALS['user_id']){
        $GLOBALS['user_data'] = $dbConnection->viewSingleData("users", "username,createdAt,admin,verified,avatar,display_name", "WHERE id = ?", array(array("value" => $GLOBALS['user_id'], "type" => PDO::PARAM_INT)))['result'];
        if($GLOBALS['user_data']['avatar'] == ""){ $GLOBALS['logged_in']['user_data']['avatar'] = $GLOBALS['config']['default_avatar']; }
    }else{
        //We don't have a user ID? Uh...
    }
}