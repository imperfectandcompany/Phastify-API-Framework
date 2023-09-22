<?php
include_once($GLOBALS['config']['private_folder'].'/classes/class.comments.php');
include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
include_once($GLOBALS['config']['private_folder'].'/classes/class.post.php');


    class Commentcontroller {
        
        protected $dbConnection;

        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
            $comments = new Comments($this->dbConnection);
            $this->comments = $comments;
            $PostController = new PostController($this->dbConnection);
            $this->postController = $PostController;
        }
        
        // Retrieve Comments for a Post
        public function getPostComments(int $id) {        
            $userId = $GLOBALS['user_id'];
            $post = new Post($this->dbConnection);
            $postOwner = $post->getPostOwner($id);
    
            if ($postOwner === false) {
                sendResponse('error', ['message' => 'Post not found'], ERROR_NOT_FOUND);
                return;
            }

            if ($postOwner === $userId || $this->postController->canViewPost($id, $userId)) {
                $result = $this->comments->getComments($id);
                if ($result) {
                    sendResponse('success', $result, SUCCESS_OK);
                } else {
                    sendResponse('error', ['message' => 'Post comments not found'], ERROR_NOT_FOUND);
                }
            } else {
            sendResponse('error', ['message' => 'Unauthorized to view the post comments.'], ERROR_FORBIDDEN);
            return;
            }
        }
       
        // Create a Comment on a Post
        public function createPostComment(int $id) {
            $userId = $GLOBALS['user_id'];
            $post = new Post($this->dbConnection);
            $postOwner = $post->getPostOwner($id);

            // Check if the post was not found
            if ($postOwner === false) {
                sendResponse('error', ['message' => 'Post not found'], ERROR_NOT_FOUND);
                return;
            }

            // Check if the user is the post owner or has permission to view the post
            if ($postOwner === $userId || $this->postController->canViewPost($id, $userId)) {
                // Parse JSON input from the request body
                $postBody = json_decode(file_get_contents("php://input"));

                try {
                    // Check if the 'comment' field is present in the request body
                    CheckInputFields(['comment'], $postBody);

                    // Post the comment
                    $result = $this->comments->postComment($id, $postBody);

                    if ($result) {
                        // Return success response
                        echo json_encode(['status' => 'success', 'message' => 'Comment created']);
                        http_response_code(SUCCESS_CREATED);
                    } else {
                        throw new Exception('Unable to create comment', ERROR_INTERNAL_SERVER);
                    }
                } catch (Exception $e) {
                    // Handle and report exceptions
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    http_response_code($e->getCode() ?: ERROR_BAD_REQUEST);
                }
            } else {
                // User is not authorized to view the post
                sendResponse('error', ['message' => 'Unauthorized to view the post comments'], ERROR_FORBIDDEN);
            }
        }


        // Delete a comment
        public function deleteComment(int $id) {
            // implementation here
        }

    }
?>
