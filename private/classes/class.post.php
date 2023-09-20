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

    /**
     * Get the 'to_whom' value of a given post ID.
     *
     * @param int $post_id The ID of the post.
     * @return int|false The 'to_whom' value or false if not found.
     */
    public function getToWhom($post_id) {
        $query = 'WHERE id = ?';
        $table = "posts";
        $select = 'to_whom';
        $paramValues = array($post_id);
        $filter_params = makeFilterParams($paramValues);
        $result = $this->dbConnection->viewSingleData($table, $select, $query, $filter_params)['result'];
        if ($result && isset($result['to_whom'])) {
            return (int)$result['to_whom'];
        } else {
            return false;
        }
    }
    
    /**
     * Get the owner of a given post ID.
     *
     * @param int $post_id The ID of the post.
     * @return int|false The user ID of the post owner or false if not found.
     */
    public function getPostOwner($post_id) {
        $query = 'WHERE id = ?';
        $table = "posts";
        $select = 'user_id';
        $paramValues = array($post_id);
        $filter_params = makeFilterParams($paramValues);
        $result = $this->dbConnection->viewSingleData($table, $select, $query, $filter_params)['result'];
        if ($result && isset($result['user_id'])) {
            return (int)$result['user_id'];
        } else {
            return false;
        }
    }

    /**
     * Get a post by its ID.
     *
     * @param int $postId The ID of the post to retrieve.
     * @param int|null $userid The user ID if specified.
     * @return array|null The post data or null if not found.
     */
    public function getPost(int $postId, int $userid = null) {
        $query = 'WHERE id = ?';
        $select = 'id, body, to_whom, user_id, expire_time, posted_on, last_edited, likes';
        $paramValues = array($postId);
        $filter_params = null;
        // If we're viewing our own post, show the original content, flagged_content, and to_whom_original
        if ($userid === $GLOBALS['user_id']) {
            $select .= ', original_content, flagged_content, to_whom_original';
        }
        $filter_params = makeFilterParams($paramValues);
        $result = $this->dbConnection->viewSingleData("posts", $select, $query, $filter_params)['result'];
        return $result;
    }

    /**
     * Get posts for a user by 'to_whom' value.
     *
     * @param int $userid The user ID.
     * @param int $to_whom The 'to_whom' value.
     * @return array|null The posts data or null if not found.
     */
    public function getPosts(int $userid, int $to_whom) {
        $query = 'WHERE to_whom = ? AND user_id = ?';
        $select = 'id, body, to_whom, user_id, expire_time, posted_on, last_edited, likes';
        $paramValues = array($to_whom, $userid);
        // If we're viewing our own feed, show the original content, flagged_content, and to_whom_original
        if ($userid === $GLOBALS['user_id']) {
            $select .= ', original_content, flagged_content, to_whom_original';
        }
        $filter_params = makeFilterParams($paramValues);
        $result = $this->dbConnection->viewData("posts", $select, $query, $filter_params);
        return $result;
    }
}
