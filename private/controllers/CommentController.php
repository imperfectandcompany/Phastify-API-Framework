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
    
            if ($postOwner === false) {
                sendResponse('error', ['message' => 'Post not found'], ERROR_NOT_FOUND);
                return;
            }

            if ($postOwner === $userId || $this->postController->canViewPost($id, $userId)) {
                $postBody = json_decode(file_get_contents("php://input"));
                $postBodyArray = (array)$postBody;
                try {
                    CheckInputFields(['comment'], $postBody);
                    $result = $this->comments->postComment($id, $postBodyArray);

                    if ($result) {
                        echo json_encode(array('status' => 'success', 'message' => 'Comment created'));
                        http_response_code(SUCCESS_CREATED);
                    } else {
                        throw new Exception('Unable to create comment', ERROR_INTERNAL_SERVER);
                    }
                } catch (Exception $e) {
                    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
                    http_response_code($e->getCode() ?: ERROR_BAD_REQUEST);
                }
            } else {
            sendResponse('error', ['message' => 'Unauthorized to view the post comments'], ERROR_FORBIDDEN);
            return;
            }
        }

        // Delete a comment
        public function deleteComment(int $id) {
            // implementation here
        }

    }
?>
