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


    // Test: A user should be able to view their own public post
    function testCanViewOwnPublicPost($post) {
        // Given: Post 248 is public and authored by user ID 12
        $canView = $post->canViewPost(248, 12);
        customAssert($canView === true, 'User should be able to view their own public post');
    }

    // Test: A user should be able to view their own private post
    function testCanViewOwnPrivatePost($post) {
        // Given: Post 249 is private and authored by user ID 12
        $canView = $post->canViewPost(249, 12);
        customAssert($canView === true, 'User should be able to view their own private post');
    }

// Test: A user should be able to view their own archived public post
function testCanViewOwnArchivedPublicPost($post) {
    // Given: Post 250 is archived public and authored by user ID 12
    $canView = $post->canViewPost(250, 12);
    customAssert($canView === true, 'User should be able to view their own archived public post');
}

// Test: A user should be able to view their own archived private post
function testCanViewOwnArchivedPrivatePost($post) {
    // Given: Post 251 is archived private and authored by user ID 12
    $canView = $post->canViewPost(251, 12);
    customAssert($canView === true, 'User should be able to view their own archived private post');
}

// Test: A user should not be able to view a post they own that is soft deleted
function testCannotViewOwnUnauthorizedPost($post) {
    // Given: Post 256's to_whom column is 5 (soft delete) and belongs to user ID 12
    $canView = $post->canViewPost(256, 12);
    customAssert($canView === false, 'User should not be able to view this post despite being the owner');
}

// Developer Test: We should be able to grab the correct post owner from the post
function testGetPostOwner($post) {
    // Given: Post 256 belongs to user ID 12
    $postUid = $post->post->getPostOwner(256);
    customAssert($postUid === 12, 'We should be able to grab the correct post owner from the post');
}

// Developer Test: This test of getPostOwner should fail, we match it with the wrong UID
function testFailedGetPostOwner($post) {
    // Given: Post 256 belongs to user ID 12
    $postUid = $post->post->getPostOwner(256);
    $wrongComparison = $postUid == 13;
    customAssert($wrongComparison === false, 'This test should fail, we match it with the wrong UID');
}

// Test: A user should not be able to view another person's post that is soft deleted
function testCannotViewUnauthorizedPost($post) {
    // Given: Post 256's to_whom column is 5 (soft delete) and does not belong to user ID 15
    // Note: User ID 15 is not a contact of user ID 12
    $canView = $post->canViewPost(256, 15);
    customAssert($canView === false, 'User should not be able to view this unauthorized post');
}
// Test: A user should not be able to view another person's post that is soft deleted as a contact
function testCannotViewUnauthorizedPostAsContact($post) {
    // Given: Post 256's to_whom column is 5 (soft delete) and does not belong to user ID 42
    // Note: User ID 42 is a contact of user ID 12
    $canView = $post->canViewPost(256, 42);
    customAssert($canView === false, 'User should not be able to view this unauthorized post despite being a contact');
}

// Test: A user should not be able to view another person's archived public post
function testCannotViewArchivedPublicPost($post) {
    // Given: Post 250 is archived public and does not belong to user ID 13
    // Note: User ID 13 is not a contact of user ID 12
    $canView = $post->canViewPost(250, 13);
    customAssert($canView === false, 'User should not be able to view another person\'s archived public post');
}

// Test: A user should not be able to view another person's archived private post
function testCannotViewArchivedPrivatePost($post) {
    // Given: Post 251 is archived private and does not belong to user ID 13
    // Note: User ID 13 is not a contact of user ID 12
    $canView = $post->canViewPost(251, 13);
    customAssert($canView === false, 'User should not be able to view another person\'s archived private post');
}

// Test: A user should not be able to view another person's archived public post as a contact
function testCannotViewArchivedPublicPostAsContact($post) {
    // Given: Post 250 is archived public and does not belong to user ID 42
    // Note: User ID 42 is a contact of user ID 12
    $canView = $post->canViewPost(250, 42);
    customAssert($canView === false, 'User should not be able to view another person\'s archived public post despite being a contact');
}

// Test: A user should not be able to view another person's archived private post as a contact
function testCannotViewArchivedPrivatePostAsContact($post) {
    // Given: Post 251 is archived private and does not belong to user ID 42
    // Note: User ID 42 is a contact of user ID 12
    $canView = $post->canViewPost(251, 42);
    customAssert($canView === false, 'User should not be able to view another person\'s archived private post despite being a contact');
}

// Test: A user should be able to view another person's public post as a contact
function testCanViewPublicPostAsContact($post) {
    // Given: Post 192 is public and belongs to user ID 42
    // Note: User ID 12 is a contact of user ID 42
    $canView = $post->canViewPost(192, 12);
    customAssert($canView === true, 'User should be able to view the public post as a contact');
}

// Test: A user should be able to view another person's public post without being a contact
function testCanViewPublicPostAsNoContact($post) {
    // Given: Post 192 is public and belongs to user ID 42
    // Note: User ID 13 is not a contact of user ID 42
    $canView = $post->canViewPost(192, 13);
    customAssert($canView === true, 'User should be able to view the public post without being a contact');
}

// Test: A user should not be able to view another person's private post without being a contact
function testCannotViewPrivatePostAsNoContact($post) {
    // Given: Post 193 is private and belongs to user ID 42
    // Note: User ID 13 is not a contact of user ID 42
    $canView = $post->canViewPost(193, 13);
    customAssert($canView === false, 'User should not be able to view the private post without being a contact');
}

// Test: A user should be able to view another person's private post as a contact
function testCanViewPrivatePostAsContact($post) {
    // Given: Post 193 is private and belongs to user ID 42
    // Note: User ID 12 is a contact of user ID 42
    $canView = $post->canViewPost(193, 12);
    customAssert($canView === true, 'User should be able to view the private post as a contact');
}
?>