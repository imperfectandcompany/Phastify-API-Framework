<?php

    class Commentcontroller {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
        }
        
        // Retrieve Comments for a Post
        public function getPostComments(int $id) {
            // implementation here
        }

        // Create a Comment on a Post
        public function createPostComment(int $id) {
            // implementation here
        }

        // Delete a comment
        public function deleteComment(int $id) {
            // implementation here
        }

    }
?>
