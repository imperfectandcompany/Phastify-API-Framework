<?php
    // Fetch all users for the admin dashboard
    
    // For each user...
    // Fetch roles associated to the user if any, also log the count
    // fetch any integraitons associated to the user if any, also log the count
    // fetch any devices associated to the user if any, also log the count
    // fetch any logs associated to the user if any, also log the count
    // fetch all ips associated to the user, also log the count
    // fetch any accounts with any of the same ip addresses as the user if any
    // fetch the followers of the user, also separately log the count
    // fetch the following of the user, also separately log the count
    // fetch the last time user logged in
    // fetch the last time user posted
    // fetch the last time user commented
    // fetch the last time user liked
    // fetch the last time user followed
    // fetch the last time user archived
    // fetch the contacts of the user, also separately log the count
    // fetch the users the user if a contact of, also separately log the count
    // fetch is the user deactivated, or when last deactivation was
    // fetch how many likes the user gotten
    // fetch whether the user posted to the public feed, and count
    // fetch whether the user posted to the private feed, and count
    // fetch whether the user user has posts in archived feed, and count   
    // fetch how many comments the user gotten 
    // fetch the amount of comments the user got on his public feed
    // fetch the amount of comments the user got on his private feed
    // fetch the amount of comments the user got on his archived feed
    // fetch the amount of likes the user got on his public feed
    // fetch the amount of likes the user got on his private feed
    // fetch the amount of likes the user got on his archived feed
    // fetch the amount of comments the user got on his public feed
    // fetch the amount of comments the user got on his private feed
    // fetch the amount of comments the user got on his archived feed
    // fetch the amount of comments the user got on his deleted posts
    // fetch the amount of likes the user got on his deleted posts


    function getUsersList($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $users = $this->dbConnection->viewData('users', '*', 'LIMIT :limit OFFSET :offset', array(':limit' => $perPage, ':offset' => $offset));
        return $users;
    }

    function searchUsers($query, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $users = $this->dbConnection->viewData('users', '*', 'WHERE username LIKE :query LIMIT :limit OFFSET :offset', array(':query' => '%' . $query . '%', ':limit' => $perPage, ':offset' => $offset));
        return $users;
    }


    function getAllUsers() {
        // Using the DatabaseConnector class to fetch all users from the `users` table
        $result = $this->dbConnection->viewData('users');
        return $result;
    }

    // Fetch details for a specific user
    function getUserDetails($userId) {
        // Using the DatabaseConnector class to fetch details of a specific user from the `users` table
        $result = $this->dbConnection->viewSingleData('users', '*', 'WHERE id = :id', array(':id' => array($userId, PDO::PARAM_INT)));
        return $result;
    }
    

    // Update user details
    function updateUser($userId, $userData) {
        // Using the DatabaseConnector class to update details of a specific user in the `users` table
        $this->dbConnection = new DatabaseConnector('localhost', 3306, 'igfastdl_postogon', 'root', '', 'utf8mb4');  // dummy connection details
        
        // Constructing the SET clause and parameters for the update query
        $set_clause = implode(', ', array_map(function($key) {
            return "{$key} = :{$key}";
        }, array_keys($userData)));
        
        $params = array_map(function($value) {
            return $value;
        }, $userData);
        $params[':id'] = array($userId, PDO::PARAM_INT);
        
        $result = $this->dbConnection->query("UPDATE users SET {$set_clause} WHERE id = :id", $params);
        return $result->rowCount();  // Return the number of rows affected
    }
    

    // Delete a user
    function deleteUser($userId) {
        $result = $this->dbConnection->query('DELETE FROM users WHERE id = :id', array(':id' => array($userId, PDO::PARAM_INT)));
        return $result->rowCount();  // Return the number of rows affected
    }
    
    
    // Assign a role to a user
    function assignRole($userId, $roleId) {
        // Using the DatabaseConnector class to assign a role to a specific user in the `user_roles` table
        // Check if the user already has the role assigned
        $existing_role = $this->dbConnection->viewSingleData('user_roles', '*', 'WHERE user_id = :user_id AND role_id = :role_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT),
            ':role_id' => array($roleId, PDO::PARAM_INT)
        ));
        
        // If the role is not already assigned, then assign it
        if (!$existing_role) {
            $result = $this->dbConnection->insertData('user_roles', array('user_id', 'role_id'), array($userId, $roleId));
            return $result;  // Return the result of the insertion
        }
    
        return false;  // Role was already assigned
    }
    
    
    // Fetch user activity logs
    function getUserLogs($userId) {
        // Using the DatabaseConnector class to fetch user activity logs from the `activity_log` table        
        $logs = $this->dbConnection->viewData('activity_log', '*', 'WHERE user_id = :user_id ORDER BY created_at DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the logs and their count
        return array('logs' => $logs, 'count' => count($logs));
    }

    function getLastLoginTime($userId) {
        // Using the DatabaseConnector class to fetch the last login time of a user from the `activity_log` table
        $db = new DatabaseConnector('localhost', 3306, 'igfastdl_postogon', 'root', '', 'utf8mb4');  // dummy connection details
        
        $last_login = $this->dbConnection->viewSingleData('activity_log', 'created_at', 'WHERE user_id = :user_id AND action = "login" ORDER BY created_at DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the last login time, if found
        return $last_login ? $last_login['created_at'] : null;
    }
    
    
    
    // Fetch roles associated with a user
    function getUserRoles($userId) {
        // Fetching roles associated with a user from the `user_roles` table using `$this->dbConnection`
        $roles = $this->dbConnection->viewData('user_roles', '*', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the roles and their count
        return array('roles' => $roles, 'count' => count($roles));
    }
    
    
    // Fetch integrations associated with a user
    function getUserIntegrations($userId) {
        // Fetching integrations associated with a user from the `integrations` table using `$this->dbConnection`
        $integrations = $this->dbConnection->viewData('integrations', 'service_id', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the integrations and their count
        return array('integrations' => $integrations, 'count' => count($integrations));
    }
    
    
    // Fetch devices associated with a user
    function getUserDevices($userId) {
        // Fetching devices associated with a user from the `devices` table using `$this->dbConnection`
        $devices = $this->dbConnection->viewData('devices', '*', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the devices and their count
        return array('devices' => $devices, 'count' => count($devices));
    }
    
    
    // Fetch IPs associated with a user
    function getUserIPs($userId) {
        // Fetching IP addresses associated with a user from the `device_ips` table using `$this->dbConnection`
        $ips = $this->dbConnection->viewData('device_ips', 'ip', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the IPs and their count
        return array('ips' => $ips, 'count' => count($ips));
    }
    
    // Fetch accounts with the same IPs as a user
    function getAccountsWithSameIPs($userId) {
        // Implement the logic to fetch accounts with the same IPs as the user
        // Return the accounts and their count
    }
    
    // Fetch followers of a user
    function getUserFollowers($userId) {
        // Fetching followers of a user from the `followers` table using `$this->dbConnection`
        $followers = $this->dbConnection->viewData('followers', 'follower_id', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the followers and their count
        return array('followers' => $followers, 'count' => count($followers));
    }
    
    
    // Fetch following of a user
    function getUserFollowing($userId) {
        // Fetching users that the given user is following from the `followers` table using `$this->dbConnection`
        $following = $this->dbConnection->viewData('followers', 'user_id', 'WHERE follower_id = :follower_id', array(
            ':follower_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the users that the given user is following and their count
        return array('following' => $following, 'count' => count($following));
    }
    
    // Implement similar functions for other user-related data you mentioned
    
    // Fetch user login history
    function getUserLoginHistory($userId) {
        // Fetching the login history for a user from the `login_history` table using `$this->dbConnection`
        $login_history = $this->dbConnection->viewData('login_history', '*', 'WHERE user_id = :user_id ORDER BY login_time DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the login history and their count
        return array('login_history' => $login_history, 'count' => count($login_history));
    }
    
    
    // Fetch user posting history
    function getUserPostingHistory($userId) {
        // Fetching the posting history for a user from the assumed `posts` table using `$this->dbConnection`
        $posting_history = $this->dbConnection->viewData('posts', '*', 'WHERE user_id = :user_id ORDER BY post_time DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the posting history and their count
        return array('posting_history' => $posting_history, 'count' => count($posting_history));
    }
    
    
    // Fetch user comment history
    function getUserCommentHistory($userId) {
        // Fetching the comment history for a user from the assumed `comments` table using `$this->dbConnection`
        $comment_history = $this->dbConnection->viewData('comments', '*', 'WHERE user_id = :user_id ORDER BY comment_time DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the comment history and their count
        return array('comment_history' => $comment_history, 'count' => count($comment_history));
    }
    
    
    // Fetch user like history
    function getUserLikeHistory($userId) {
        // Fetching the like history for a user from the assumed `likes` table using `$this->dbConnection`
        $like_history = $this->dbConnection->viewData('likes', '*', 'WHERE user_id = :user_id ORDER BY like_time DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the like history and their count
        return array('like_history' => $like_history, 'count' => count($like_history));
    }
    
    
    // Fetch user follow history
    function getUserFollowHistory($userId) {
        // Fetching the follow history for a user from the `followers` table using `$this->dbConnection`
        $follow_history = $this->dbConnection->viewData('followers', '*', 'WHERE follower_id = :follower_id ORDER BY follow_time DESC', array(
            ':follower_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the follow history and their count
        return array('follow_history' => $follow_history, 'count' => count($follow_history));
    }
    
    
    // Fetch user archive history
    function getUserArchiveHistory($userId) {
        // Fetching the archive history for a user from the assumed `archives` table using `$this->dbConnection`
        $archive_history = $this->dbConnection->viewData('archives', '*', 'WHERE user_id = :user_id ORDER BY archive_time DESC', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the archive history and their count
        return array('archive_history' => $archive_history, 'count' => count($archive_history));
    }
    
    
    // Fetch user contacts
    function getUserContacts($userId) {
        // Fetching contacts of a user from the `contacts` table using `$this->dbConnection`
        $contacts = $this->dbConnection->viewData('contacts', 'contact_id', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the contacts and their count
        return array('contacts' => $contacts, 'count' => count($contacts));
    }
    
    
    // Fetch users who have the user as a contact
    function getUsersWithUserAsContact($userId) {
        // Fetching users who have the given user as a contact from the `contacts` table using `$this->dbConnection`
        $users_with_user_as_contact = $this->dbConnection->viewData('contacts', 'user_id', 'WHERE contact_id = :contact_id', array(
            ':contact_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the users who have the given user as a contact and their count
        return array('users_with_user_as_contact' => $users_with_user_as_contact, 'count' => count($users_with_user_as_contact));
    }
    
    
    // Check if the user is deactivated and fetch deactivation date
    function isUserDeactivated($userId) {
        // Checking if the user is deactivated and fetching the deactivation date from the `users` table using `$this->dbConnection`
        $user_status = $this->dbConnection->viewData('users', 'is_deactivated, deactivation_date', 'WHERE user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the deactivation status and date if deactivated
        if ($user_status && isset($user_status[0])) {
            return array(
                'is_deactivated' => $user_status[0]['is_deactivated'],
                'deactivation_date' => $user_status[0]['deactivation_date']
            );
        }
        return array('is_deactivated' => false);
    }
    
    
    // Fetch the number of likes received by the user
    function getLikesReceivedByUser($userId) {
        // Fetching the number of likes received by a user from the `post_likes` table using `$this->dbConnection`
        $likes_received = $this->dbConnection->viewData('post_likes', 'COUNT(*) as total_likes', 'WHERE post_owner_id = :post_owner_id', array(
            ':post_owner_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of likes received
        return $likes_received[0]['total_likes'] ?? 0;
    }
    
    
    // Check if the user posted to the public feed and fetch count
    function didUserPostToPublicFeed($userId) {
        // Checking if the user posted to the public feed from the `posts` table using `$this->dbConnection`
        $public_posts_count = $this->dbConnection->viewData('posts', 'COUNT(*) as total_public_posts', 'WHERE user_id = :user_id AND to_whom = 1', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the posting status (true if they posted) and count if posted
        $has_posted = $public_posts_count[0]['total_public_posts'] > 0;
        return array('has_posted' => $has_posted, 'count' => $public_posts_count[0]['total_public_posts']);
    }
    
    
    // Check if the user posted to the private feed and fetch count
    function didUserPostToPrivateFeed($userId) {
        // Checking if the user posted to the private feed from the `posts` table using `$this->dbConnection`
        $private_posts_count = $this->dbConnection->viewData('posts', 'COUNT(*) as total_private_posts', 'WHERE user_id = :user_id AND to_whom = 2', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the posting status (true if they posted) and count if posted
        $has_posted = $private_posts_count[0]['total_private_posts'] > 0;
        return array('has_posted' => $has_posted, 'count' => $private_posts_count[0]['total_private_posts']);
    }
    
    
    // Check if the user has posts in the archived feed and fetch count
    function doesUserHavePostsInArchivedFeed($userId) {
        // Checking if the user has posts in the archived feed from the `posts` table using `$this->dbConnection`
        $archived_posts_count = $this->dbConnection->viewData('posts', 'COUNT(*) as total_archived_posts', 'WHERE user_id = :user_id AND (to_whom = 3 OR to_whom = 4)', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the posting status (true if they have archived posts) and count if archived
        $has_archived = $archived_posts_count[0]['total_archived_posts'] > 0;
        return array('has_archived' => $has_archived, 'count' => $archived_posts_count[0]['total_archived_posts']);
    }
    
    
    // Fetch the number of comments received by the user
    function getCommentsReceivedByUser($userId) {
        // Fetching the number of comments received by a user from the `comments` table using `$this->dbConnection`
        $comments_received = $this->dbConnection->viewData('comments', 'COUNT(*) as total_comments_received', 'WHERE post_owner_id = :post_owner_id', array(
            ':post_owner_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments received
        return $comments_received[0]['total_comments_received'] ?? 0;
    }
    
    
    // Fetch the number of comments received by the user on their public feed
    function getCommentsReceivedOnPublicFeed($userId) {
        // Fetching the number of comments received by a user on their public feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_on_public_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_on_public', 'WHERE posts.to_whom = 1 AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments received on public feed
        return $comments_on_public_feed[0]['total_comments_on_public'] ?? 0;
    }
    
    
    // Fetch the number of comments received by the user on their private feed
    function getCommentsReceivedOnPrivateFeed($userId) {
        // Fetching the number of comments received by a user on their private feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_on_private_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_on_private', 'WHERE posts.to_whom = 2 AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments received on private feed
        return $comments_on_private_feed[0]['total_comments_on_private'] ?? 0;
    }
    
    
    // Fetch the number of comments received by the user on their archived feed
    function getCommentsReceivedOnArchivedFeed($userId) {
        // Fetching the number of comments received by a user on their archived feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_on_archived_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_on_archived', 'WHERE (posts.to_whom = 3 OR posts.to_whom = 4) AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments received on archived feed
        return $comments_on_archived_feed[0]['total_comments_on_archived'] ?? 0;
    }
    
    
    // Fetch the number of likes received by the user on their public feed
    function getLikesReceivedOnPublicFeed($userId) {
        // Fetching the number of likes received by a user on their public feed from the `post_likes` and `posts` tables using `$this->dbConnection`
        $likes_on_public_feed = $this->dbConnection->viewData('post_likes JOIN posts ON post_likes.post_id = posts.post_id', 'COUNT(*) as total_likes_on_public', 'WHERE posts.to_whom = 1 AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of likes received on public feed
        return $likes_on_public_feed[0]['total_likes_on_public'] ?? 0;
    }
    
    
    // Fetch the number of likes received by the user on their private feed
    function getLikesReceivedOnPrivateFeed($userId) {
        // Fetching the number of likes received by a user on their private feed from the `post_likes` and `posts` tables using `$this->dbConnection`
        $likes_on_private_feed = $this->dbConnection->viewData('post_likes JOIN posts ON post_likes.post_id = posts.post_id', 'COUNT(*) as total_likes_on_private', 'WHERE posts.to_whom = 2 AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of likes received on private feed
        return $likes_on_private_feed[0]['total_likes_on_private'] ?? 0;
    }
    
    
    // Fetch the number of likes received by the user on their archived feed
    function getLikesReceivedOnArchivedFeed($userId) {
        // Fetching the number of likes received by a user on their archived feed from the `post_likes` and `posts` tables using `$this->dbConnection`
        $likes_on_archived_feed = $this->dbConnection->viewData('post_likes JOIN posts ON post_likes.post_id = posts.post_id', 'COUNT(*) as total_likes_on_archived', 'WHERE (posts.to_whom = 3 OR posts.to_whom = 4) AND posts.user_id = :user_id', array(
            ':user_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of likes received on archived feed
        return $likes_on_archived_feed[0]['total_likes_on_archived'] ?? 0;
    }
    
    
    
    // Fetch the number of comments made by the user on their public feed
    function getCommentsMadeOnPublicFeed($userId) {
        // Fetching the number of comments made by a user on their public feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_made_on_public_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_made_on_public', 'WHERE posts.to_whom = 1 AND comments.commenter_id = :commenter_id', array(
            ':commenter_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments made on public feed
        return $comments_made_on_public_feed[0]['total_comments_made_on_public'] ?? 0;
    }
    
    
    // Fetch the number of comments made by the user on their private feed
    function getCommentsMadeOnPrivateFeed($userId) {
        // Fetching the number of comments made by a user on their private feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_made_on_private_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_made_on_private', 'WHERE posts.to_whom = 2 AND comments.commenter_id = :commenter_id', array(
            ':commenter_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments made on private feed
        return $comments_made_on_private_feed[0]['total_comments_made_on_private'] ?? 0;
    }
    
    
    // Fetch the number of comments made by the user on their archived feed
    function getCommentsMadeOnArchivedFeed($userId) {
        // Fetching the number of comments made by a user on their archived feed from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_made_on_archived_feed = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_made_on_archived', 'WHERE (posts.to_whom = 3 OR posts.to_whom = 4) AND comments.commenter_id = :commenter_id', array(
            ':commenter_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments made on archived feed
        return $comments_made_on_archived_feed[0]['total_comments_made_on_archived'] ?? 0;
    }
    
    
    // Fetch the number of comments made by the user on their deleted posts
    function getCommentsMadeOnDeletedPosts($userId) {
        // Fetching the number of comments made by a user on their soft-deleted posts from the `comments` and `posts` tables using `$this->dbConnection`
        $comments_made_on_deleted_posts = $this->dbConnection->viewData('comments JOIN posts ON comments.post_id = posts.post_id', 'COUNT(*) as total_comments_made_on_deleted', 'WHERE posts.to_whom = 5 AND comments.commenter_id = :commenter_id', array(
            ':commenter_id' => array($userId, PDO::PARAM_INT)
        ));
        
        // Return the count of comments made on soft-deleted posts
        return $comments_made_on_deleted_posts[0]['total_comments_made_on_deleted'] ?? 0;
    }
    
    // Fetch the number of likes received by the user on their deleted posts
    function getLikesReceivedOnDeletedPosts($userId) {
        // Implement the logic to fetch the number of likes received by the user on their deleted posts
        // Return the count of likes received on deleted posts
    }
?>
