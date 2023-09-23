<?php
// tests/test.post.php

/*
to_whom 1 = public
to_whom 2 = private
to_whom 3 = public archived
to_whom 4 = private archived
to_whom 5 = soft delete (unauthorized)

If the user owns the post, they can view it if to_whom is 1 or 2 or 3 or 4, but not 5.

If the user does not own the post and they are not a contact of the user,
they can only view the post if to_whom is 1.

If the user does not own the post and they are a contact of the user,
they can only view the post if to_whom is 1 or 2.

Here is the data we are working with:

Post 256:
to_whom = 5
user_id = 12

Post 250:
to_whom = 3
user_id = 12

Post 251:
to_whom = 4
user_id = 12

Post 192:
to_whom = 1
user_id = 42

Post 193:
to_whom = 2
user_id = 42

User 12:
Contact of user ID 42
Not contact of user ID 15
Not contact of user ID 13

User 13:
Not a contact of user ID 12
Not a contact of user ID 42
Not a contact of user ID 15

User 15:
Not a contact of user ID 42
Not a contact of user ID 12
Not a contact of user ID 13

User 42:
Contact of user ID 12
Not contact of user ID 15
Not a contact of user ID 13
*/

function setPhpInputStream($data) {
    file_put_contents('php://temp', $data);
    rewind('php://temp');
}

function testValidCommentCreation($commentController) {
    $GLOBALS['user_id'] = 13;
    $data = (object)['comment' => 'This is a valid comment'];
    $result = $commentController->comments->postComment(192, $data);
    customAssert($result === true, "Comment creation should be successful with valid data.");
}

function testCommentCreationWithMissingField($commentController) {
    $GLOBALS['user_id'] = 13;
    $data = (object)[]; 
    try {
        $commentController->comments->postComment(192, $data);
        customAssert(false, "Comment creation should have thrown an exception due to missing 'comment' field.");
    } catch (Exception $e) {
        customAssert($e->getMessage() === "Required column comment missing.", "Comment creation should fail due to missing 'comment' field.");
    }
}

function testCommentCreationWithUnknownColumn($commentController) {
    $GLOBALS['user_id'] = 13;
    $data = (object)['comment' => 'This is a comment', 'unknown_column' => 'Unknown data'];
    try {
        $commentController->comments->postComment(192, $data);
        customAssert(false, "Comment creation should have thrown an exception due to unknown column.");
    } catch (Exception $e) {
        customAssert($e->getMessage() === "Unknown column unknown_column provided.", "Comment creation should fail due to unknown column.");
    }
}

function testOwnerCanCommentOnTheirPost($commentController) {
    $posts = [250, 251, 256];
    foreach ($posts as $postId) {
        $GLOBALS['user_id'] = 12;
        $data = (object)['comment' => "Comment on post {$postId}"];
        try {
            $result = $commentController->comments->postComment($postId, $data);
            customAssert($result === true, "Owner should be able to comment on their post: {$postId}");
        } catch (Exception $e) {
            customAssert(false, "Unexpected exception: " . $e->getMessage());
        }
    }
}

    // Test: A user should be able to comment on their own public post
    function testCanCommentOwnPublicPost($commentController) {
        // Given: Post 192 is public and authored by user ID 42
        $data = (object)array("comment" => "This is a comment");
        $result = $commentController->comments->postComment(192, $data);
        customAssert($result !== false, 'User should be able to comment on their own public post');
    }

    // Test: A user should be able to comment on their own private post
    function testCanCommentOwnPrivatePost($commentController) {
        // Given: Post 193 is private and authored by user ID 42
        $data = (object)array("comment" => "This is another comment");
        $result = $commentController->comments->postComment(193, $data);
        customAssert($result !== false, 'User should be able to comment on their own private post');
    }

    // Test: A user should not be able to comment on a post they own that is soft deleted
    function testCannotCommentOwnUnauthorizedPost($commentController) {
        // Given: Post 256's to_whom column is 5 (soft delete) and belongs to user ID 12
        $data = (object)array("comment" => "This comment should not be added");
        $result = $commentController->comments->postComment(256, $data);
        customAssert($result === false, 'User should not be able to comment on this post despite being the owner');
    }

    // Test: A user should be able to comment on another person's public post as a contact
    function testCanCommentPublicPostAsContact($commentController) {
        // Given: Post 192 is public and belongs to user ID 42
        // Note: User ID 12 is a contact of user ID 42
        $data = (object)array("comment" => "Comment from a contact");
        $result = $commentController->comments->postComment(192, $data);
        customAssert($result !== false, 'User should be able to comment on the public post as a contact');
    }

    // Test: A user should be able to comment on another person's public post without being a contact
    function testCanCommentPublicPostAsNoContact($commentController) {
        // Given: Post 192 is public and belongs to user ID 42
        // Note: User ID 13 is not a contact of user ID 42
        $data = (object)array("comment" => "Comment from a non-contact");
        $result = $commentController->comments->postComment(192, $data);
        customAssert($result !== false, 'User should be able to comment on the public post without being a contact');
    }

    // Test: A user should not be able to comment on another person's private post without being a contact
    function testCannotCommentPrivatePostAsNoContact($commentController) {
        // Given: Post 193 is private and belongs to user ID 42
        // Note: User ID 13 is not a contact of user ID 42
        $GLOBALS['user_id'] = 13;
    
        $testData = json_encode(["comment" => "This comment should fail"]);
        $GLOBALS["php://input"] = new MockInputStreamsWrapper($testData);
    
        // Capture the output
        ob_start();
        $commentController->createPostComment(193);
        $output = ob_get_clean();
    
        $decodedOutput = json_decode($output, true);
        $status = $decodedOutput['status'] ?? null;
    
        customAssert($status === 'error', 'User should not be able to comment on the private post without being a contact');
    }

    // Test: A user should be able to comment on another person's private post as a contact
    function testCanCommentPrivatePostAsContact($commentController) {
        // Given: Post 193 is private and belongs to user ID 42
        // Note: User ID 12 is a contact of user ID 42
        $data = (object)array("comment" => "Comment from a contact on a private post");
        $result = $commentController->comments->postComment(193, $data);
        customAssert($result !== false, 'User should be able to comment on the private post as a contact');
    }






?>