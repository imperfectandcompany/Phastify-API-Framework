<?php
// tests/test.post.php

require_once '../controllers/PostController.php';  // Include the class you want to test
// Initialize the PostController object once

$post = new PostController($dbConnection);
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

// User should be able to view their own public post
function testCanViewOwnPublicPost($post) {
    assert($post->canViewPost(248, 12) === true, 'User should be able to view their own public post');
}

// User should be able to view their own private post
function testCanViewOwnPrivatePost($post) {
    assert($post->canViewPost(249, 12) === true, 'User should be able to view their own private post');
}

// User should be able to view their own archived public post
function testCanViewOwnArchivedPublicPost($post) {
    assert($post->canViewPost(250, 12) === true, 'User should be able to view their own archived public post');
}

// User should be able to view their own archived private post
function testCanViewOwnArchivedPrivatePost($post) {
    assert($post->canViewPost(251, 12) === true, 'User should be able to view their own archived private post');
}

// User should not be able to view a post they own that is soft deleted
function testCannotViewOwnUnauthorizedPost($post) {
    // Post 256's to_whom column is 5 (soft delete) and belongs to user ID 12
    assert($post->canViewPost(256, 12) === false, 'User should not be able to view this post despite being the owner');
}

// User should not be able to view another person's post that is soft deleted
function testCannotViewUnauthorizedPost($post) {
    // Post 256's to_whom column is 5 (soft delete) and does not belong to user ID 15
    assert($post->canViewPost(256, 15) === false, 'User should not be able to view this unauthorized post');
}

// User should not be able to view another person's post that is soft deleted as a contact
function testCannotViewUnauthorizedPostAsContact($post) {
    // Post 256's to_whom column is 5 (soft delete) and does not belong to user ID 42
    assert($post->canViewPost(256, 42) === false, 'User should not be able to view this unauthorized post');
}

// User should not be able to view another person's archived public post
function testCannotViewArchivedPublicPost($post) {
    // Post 250 is archived public and does not belong to user ID 13
    $result = $post->canViewPost(250, 13); // Assuming user 13 is not a contact of the post owner (user 12)
    unset($post);
    assert($result === false, 'User should not be able to view another person\'s archived public post');
}

// User should not be able to view another person's archived private post
function testCannotViewArchivedPrivatePost($post) {
    // Post 251 is archived private and does not belong to user ID 13
    assert($post->canViewPost(251, 13) === false, 'User should not be able to view another person\'s archived private post');
}

// User should not be able to view another person's archived public post as a contact
function testCannotViewArchivedPublicPostAsContact($post) {
    // Post 250 is archived public and does not belong to user ID 42
    assert($post->canViewPost(250, 42) === false, 'User should not be able to view another person\'s archived public post despite being a contact');
}

// User should not be able to view another person's archived private post as a contact
function testCannotViewArchivedPrivatePostAsContact($post) {
    // Post 251 is archived private and does not belong to user ID 42
    assert($post->canViewPost(251, 42) === false, 'User should not be able to view another person\'s archived private post despite being a contact');
}

// User should be able to view another person's public post as a contact
function testCanViewPublicPostAsContact($post) {
    // Post 192 is public and belongs to user ID 42, and user ID 12 is a contact of user ID 42
    assert($post->canViewPost(192, 12) === true, 'User should be able to view the public post as a contact');
}

// User should be able to view another person's public post without being a contact
function testCanViewPublicPostAsNoContact($post) {
    // Post 192 is public and belongs to user ID 42, and user ID 13 is not a contact of user ID 42
    assert($post->canViewPost(192, 13) === true, 'User should be able to view the public post without being a contact');
}

// User should not be able to view another person's private post without being a contact
function testCannotViewPrivatePostAsNoContact($post) {
    // Post 193 is private and belongs to user ID 42, and user ID 13 is not a contact of user ID 42
    assert($post->canViewPost(193, 13) === false, 'User should not be able to view the private post without being a contact');
}

// User should be able to view another person's private post as a contact
function testCanViewPrivatePostAsContact($post) {
    // Post 193 is private and belongs to user ID 42, and user ID 12 is a contact of user ID 42
    assert($post->canViewPost(193, 12) === true, 'User should be able to view the private post as a contact');
}

// Run all the test functions
testCanViewOwnPublicPost($post);
testCanViewOwnPrivatePost($post);
testCanViewOwnArchivedPublicPost($post);
testCanViewOwnArchivedPrivatePost($post);
testCannotViewOwnUnauthorizedPost($post);
testCannotViewUnauthorizedPost($post);
testCannotViewUnauthorizedPostAsContact($post);
testCannotViewArchivedPublicPost($post);
testCannotViewArchivedPrivatePost($post);
testCannotViewArchivedPublicPostAsContact($post);
testCannotViewArchivedPrivatePostAsContact($post);
testCanViewPublicPostAsContact($post);
testCanViewPublicPostAsNoContact($post);
testCannotViewPrivatePostAsNoContact($post);
testCanViewPrivatePostAsContact($post);

// If all tests pass, print a success message
echo "All tests passed!";
unset($post);
?>