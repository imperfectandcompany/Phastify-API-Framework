<?php
include_once($GLOBALS['config']['private_folder'].'/classes/class.comments.php');
include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
include_once($GLOBALS['config']['private_folder'].'/classes/class.post.php');

    class CommentController {
        
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
                return false;
            }

            if ($postOwner === $userId || $this->postController->canViewPost($id, $userId)) {
                $result = $this->comments->getComments($id);
                if ($result) {
                    sendResponse('success', $result, SUCCESS_OK);
                } else {
                    sendResponse('error', ['message' => 'Post comments not found'], ERROR_NOT_FOUND);
                    return false;
                }
            } else {
            sendResponse('error', ['message' => 'Unauthorized to view the post comments.'], ERROR_FORBIDDEN);
            return false;
            }
        }
       
        public function createPostComment(int $id) {
            $userId = $GLOBALS['user_id'];
            $post = new Post($this->dbConnection);
            $postOwner = $post->getPostOwner($id);
            
            if ($postOwner === false) {
                sendResponse('error', ['message' => 'Post not found'], ERROR_NOT_FOUND);
                return false;
            }

            if ($this->postController->canViewPost($id, $userId)) {

                $postBody = json_decode(static::getInputStream());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_last_error_msg();
                }

                if ($postBody === null || !isset($postBody->comment) || empty(trim($postBody->comment))) {
                    sendResponse('error', ['message' => 'Comment cannot be empty'], ERROR_BAD_REQUEST);
                    return;
                }
                
                try {
                    // I've assumed that CheckInputFields throws an exception if 'comment' isn't set
                    CheckInputFields(['comment'], $postBody);
        
                    $result = $this->comments->postComment($id, $postBody);
                    if ($result) {
                        sendResponse('success', ['message' => 'Comment created'], SUCCESS_CREATED);
                        return true;
                    } else {
                        throw new Exception('Unable to create comment', ERROR_INTERNAL_SERVER);
                    }
                } catch (Exception $e) {
                    sendResponse('error', ['message' => 'Comment created'], ERROR_BAD_REQUEST);
                    return false;
                }
            } else {
                sendResponse('error', ['message' => 'Unauthorized to create comment on the post'], ERROR_FORBIDDEN);
                return false;
            }
        }
        
        
        protected static function getInputStream()
        {
            return file_get_contents('php://input');
        }

        // Delete a comment
        public function deleteComment(int $id) {
            // implementation here
        }

    }
?>
