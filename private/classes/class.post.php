<?php

class Post {
    
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
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
     * Checks if a post is archived.
     *
     * @param int $postId The ID of the post.
     * @return bool True if the post is archived, false otherwise.
     */
    public function isPostArchived(int $postId) {
        // Fetch post information including 'to_whom', 'to_whom_original', and 'expire_time'
        $query = 'WHERE id = ?';
        $table = "posts";
        $select = 'to_whom, to_whom_original, expire_time';
        $paramValues = array($postId);
        $filterParams = makeFilterParams($paramValues);
        $postInfo = $this->dbConnection->viewSingleData($table, $select, $query, $filterParams)['result'];

        // Check if we were able to fetch the post information
        if ($postInfo) {
            // Check if 'to_whom' is set to 3 (archived public) or 4 (archived private)
            if ($postInfo['to_whom'] == 3 || $postInfo['to_whom'] == 4) {
                // The post is archived based on 'to_whom'
                return true;
            } elseif ($postInfo['expire_time'] !== null && $postInfo['expire_time'] < time()) {
                // The post has an expiration time, and it has expired
                return true;
            } else {
                return false;
            }
        }

        return false; // The post is not archived
    }


    /**
     * Archives a post by changing its 'to_whom' value and setting 'expired' timestamp.
     *
     * @param int $postId The ID of the post to archive.
     * @return bool True if the post is successfully archived, false otherwise.
     */
    public function archivePost(int $postId) {
        // Get the current 'to_whom' value of the post
        $currentToWhom = $this->getToWhom($postId);

        // Determine the new 'to_whom' value for archived posts
        $newToWhom = $currentToWhom == 1 ? 3 : ($currentToWhom == 2 ? 4 : $currentToWhom);

        // If the 'to_whom' value is not changing (already archived), return false
        if ($newToWhom == $currentToWhom) {
            return false;
        }

        // Define the SQL update query components
        $table = "posts";
        $setClause = 'to_whom = ?, to_whom_original = ?, expired = UNIX_TIMESTAMP(), last_edited = NOW()';
        $whereClause = 'id = ?';

        // Prepare filter parameters for the query
        $filterParams = makeFilterParams([$newToWhom, $currentToWhom, $postId]);

        // Execute the update query
        return $this->dbConnection->updateData($table, $setClause, $whereClause, $filterParams);
    }


    
    /**
     * Updates the 'to_whom' value of a post.
     *
     * @param int $postId The ID of the post to update.
     * @param int $toWhom The new 'to_whom' value.
     * @return bool True if the post is successfully updated, false otherwise.
     */
    public function updatePostToWhom(int $postId, int $toWhom) {
        // Get the current 'to_whom' value of the post
        $currentToWhom = $this->getToWhom($postId);

        // Determine the new 'to_whom' value
        $newToWhom = $toWhom == 1 ? 3 : ($toWhom == 2 ? 4 : 5);

        // If the 'to_whom' value is not changing, return false
        if ($newToWhom == $currentToWhom) {
            return false;
        }

        // Define the SQL update query components
        $table = "posts";
        $setClause = 'to_whom = ?, to_whom_original = ?, last_edited = NOW()';
        $whereClause = 'id = ?';

        // Prepare filter parameters for the query
        $filterParams = makeFilterParams([$newToWhom, $currentToWhom, $postId]);

        // Execute the update query
        return $this->dbConnection->updateData($table, $setClause, $whereClause, $filterParams);
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
        // TODO: OMIT ARCHIVED POSTS (TO_WHOM = 3 || TO_WHOM = 4 || EXPIRE_TIME < CURRENT_TIMESTAMP)
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
