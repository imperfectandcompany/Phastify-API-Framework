<?php

    class PostController {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
        }

        public function createPost() {
            $postBody = json_decode(file_get_contents("php://input"));
        
            // Extract data from the request body
            $postContent = $postBody->body;
            $toWhom = $postBody->to_whom;
            $expirationTime = isset($postBody->expiration_time) ? $postBody->expiration_time : null;
        
            // Insert the post into the database, including the expiration time
            // Use $expirationTime when inserting into the 'expiration_time' column
            // DB Insert

            // Return a success response
        }

        // Delete a post
        public function deletePost(int $id) {
            // implementation here
        }

        // Update a Post with Optional Expiration Time
        public function updatePost(int $id) {
            // implementation here
        }

        // Archive a Post by ID
        public function archivePost(int $postId) {
            // implementation here
        }

        // Unarchive a Post by ID
        public function unarchivePost(int $postId) {
            // implementation here
        }

        // View archived post for user
        public function viewArchivedPosts() {
            // implementation here
        }

        // Retrieve a Single Post by ID
        public function getSinglePost(int $id) {
            // implementation here
        }

        // Retrieve Posts in a User's Public Feed
        public function getPublicFeedPosts(int $userid = null) {
            // implementation here
        }

        // Retrieve Posts in a User's Private Feed
        public function getPrivateFeedPosts($userid = null) {
            // implementation here
        }
    }
?>
