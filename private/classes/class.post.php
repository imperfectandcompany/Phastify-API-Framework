<?php

class Post {
    
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    //For showing a private timeline
    //UserID of the person viewing it
    //User ID of the person's timeline optional
    public function fetchPrivateTimeline($user_id, $view_id = NULL){

        //If timeline post is set to 2, it means they're for contacts only. This means that we can only see it if we're a contact of this person. We need to be mutual followers, they need to set me as a contact.
        //if id = 3 and we are contact of this person, show.
        $query = 'LIMIT '.$GLOBALS['config']['max_timeline_lookup'];
        $filter_params = array();
        $filter_params[] = array("value" => $user_id, "type" => PDO::PARAM_INT);
        $query = "WHERE id = ?";


        return $this->dbObject->viewData($GLOBALS['db_conf']['db_db'].".timeline", '*', $query, $filter_params);

    }

    // function to see the to_whom of a given post id
    public function getToWhom($post_id) {
        $query = 'WHERE id = ?';
        $table = "posts";
        $select = 'to_whom';
        $paramValues = array($post_id);
        $filter_params = makeFilterParams($paramValues);
        $result = $this->dbConnection->viewSingleData($table, $select, $query, $filter_params)['result']['to_whom'];
        return $result;
    }

    public function getPosts(int $userid, int $to_whom) {
        // Check if we passed a specific user ID before showing users' feed
            $query = 'WHERE to_whom = 1 AND user_id = ?';
            $select = 'id, body, to_whom, user_id, expire_time, posted_on, last_edited, likes';
            $paramValues = array($userid);
            // If we're viewing our own feed, show the original content, flagged_content, and to_whom_original
            if($userid == $GLOBALS['user_id']){
                $select .= ', original_content, flagged_content, to_whom_original';
            }
            $filter_params = makeFilterParams($paramValues);
            $result = $this->dbConnection->viewData("posts", $select, $query, $filter_params);
            return $result;
    }

    public function updateAvatar($filter_params)
    {   
        //include avatar_ts = UNIX_TIMESTAMP() in the future
        return $this->dbObject->updateData("users", "avatar = ?", "id = ?", $filter_params);
    }
}
