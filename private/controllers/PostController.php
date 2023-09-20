<?php
    include_once($GLOBALS['config']['private_folder'].'/classes/class.post.php');
    include_once($GLOBALS['config']['private_folder'].'/classes/class.security.php');
    

    class PostController {
        
        protected $dbConnection;
        protected $post;
        protected $security;
        
        public function __construct($dbConnection)
        {
            $this->dbConnection = $dbConnection;
            $post = new Post($this->dbConnection);
            $security = new Security($this->dbConnection);
            $this->post = $post;
            $this->security = $security;
        }

        /**
         * Create a new post or update an existing post.
         *
         * @return Response
         */
        public function createPost() {
            $postBody = json_decode(file_get_contents("php://input"));

            $postId = isset($postBody->postId) ? $postBody->postId : null;

            // Extract data from the request body
            $content = $postBody->content;
            $originalContent = isset($postBody->original_content) ? $postBody->original_content : $content;
            $toWhom = $postBody->to_whom;
            $flaggedContent = isset($postBody->flagged_content) ? $postBody->flagged_content : null;
            $toWhomOriginal = isset($postBody->to_whom_original) ? $postBody->to_whom_original : null;
            $expirationTime = isset($postBody->expiration_time) ? $postBody->expiration_time : null;
            $postedOn = isset($postBody->posted_on) ? $postBody->posted_on : time();
            $lastEdited = isset($postBody->last_edited) ? $postBody->last_edited : time();
            $likes = isset($postBody->likes) ? $postBody->likes : 0;

            // Insert or update the post in the database based on the presence of postId
            if ($postId !== null) {
                // Update the existing post
                // Use $postId to identify the post to update
                // Update content, to_whom, expiration_time, last_edited, and likes
            } else {
                // Create a new post
                // Insert a new row in the posts table with the provided data
            }

            // Return the newly created or updated post object
            // Include post ID, posted_on, last_edited, and other relevant data
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

    /**
     * Retrieves a single post by its ID.
     *
     * @param int $postId The ID of the post to retrieve.
     * @return void
     */
    public function getSinglePost(int $postId)
    {
        $userId = $GLOBALS['user_id'];
        $postOwner = $this->post->getPostOwner($postId);
        $toWhom = $this->post->getToWhom($postId);

        if ($postOwner === false) {
            sendResponse('error', ['message' => 'Post not found'], ERROR_NOT_FOUND);
            return;
        }

        if ($postOwner === $userId || $toWhom === TO_WHOM_PUBLIC) {
            $post = $this->post->getPost($postId, $userId);
            if ($post) {
                sendResponse('success', $post, SUCCESS_OK);
                return;
            }
        } elseif ($toWhom === TO_WHOM_PRIVATE && $this->security->checkContact($userId, $postOwner)) {
            $post = $this->post->getPost($postId);
            if ($post) {
                sendResponse('success', $post, SUCCESS_OK);
                return;
            }
        }

        sendResponse('error', ['message' => 'Unauthorized to view this post'], ERROR_FORBIDDEN);
    }

        // Retrieve Posts in a User's Public Feed
        public function getPublicFeedPosts(int $userid = null) {
                // Check if we passed a specific user ID before showing users' public feed
                if ($userid == null) {
                    $userid = $GLOBALS['user_id'];
                }
                $result = $this->post->getPosts($userid, 1);
                sendResponse('success', $result, SUCCESS_OK);
        }

        /**
         * For displaying a private feed of either the user or a specific user.
         *
         * @return void
         */
        public function getPrivateFeedPosts($userid = null) {
                // Check if we passed a specific user ID before showing the private feed
                if ($userid === null) {
                    $userid = $GLOBALS['user_id'];
                } else {
                    // Check if the private feed we are trying to view belongs to the user
                    if ($userid !== $GLOBALS['user_id']) {
                        // Check to see if the user is a contact of the user whose private feed they are trying to view
                        if(!$this->security->checkContact($GLOBALS['user_id'], $userid)){
                            sendResponse('error', ['message' => 'Unauthorized to view this private feed'], ERROR_FORBIDDEN);
                            return;
                        }  
                    } else {$userid = $GLOBALS['user_id'];}
                }

                $result = $this->post->getPosts($userid, 2);
                sendResponse('success', $result, SUCCESS_OK);
                return;
    }


}
?>
