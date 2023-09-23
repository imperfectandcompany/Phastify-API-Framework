<?php            
            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/test_comment.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/CommentController.php');
            include_once($GLOBALS['config']['private_folder'].'/classes/class.testRunner.php');
            include_once('MockInputStreamsWrapper.php');


            // Initialize the PostController object once
            $controllers = [
                'post' => new PostController($dbConnection),
                'comments' => new CommentController($dbConnection)
            ];

            stream_wrapper_unregister("php");
            stream_wrapper_register("php", "MockInputStreamsWrapper")
                or die("Failed to register protocol");


            
            function customAssert($condition, $message) {
                global $currentTest;
                if (!$condition) {
                    throw new Exception($message);
                }
            }

            $testCanViewOwnPosts = [
                "testCanViewOwnPublicPost",
                "testCanViewOwnPrivatePost",
                "testCanViewOwnArchivedPublicPost",
                "testCanViewOwnArchivedPrivatePost"
            ];
            $testCannotViewOwnPosts = [
                "testCannotViewOwnUnauthorizedPost",
            ];
            $testCanViewOthersPosts = [
                "testCanViewPublicPostAsContact",
                "testCanViewPublicPostAsNoContact",
                "testCanViewPrivatePostAsContact"
            ];
            $testCannotViewOthersPosts = [
                "testCannotViewUnauthorizedPost",
                "testCannotViewUnauthorizedPostAsContact",
                "testCannotViewArchivedPublicPost",
                "testCannotViewArchivedPrivatePost",
                "testCannotViewArchivedPublicPostAsContact",
                "testCannotViewArchivedPrivatePostAsContact",
                "testCannotViewPrivatePostAsNoContact"
            ];
            $testDeveloperTestPosts = [
                "testGetPostOwner",
                "testFailedGetPostOwner"
            ];
            $testUserComment = [
                "testValidCommentCreation",
                "testCommentCreationWithMissingField",
                "testCommentCreationWithUnknownColumn",
                "testOwnerCanCommentOnTheirPost",

                "testCanCommentOwnPublicPost",
                "testCanCommentOwnPrivatePost",
                "testCannotCommentOwnUnauthorizedPost",
                "testCanCommentPublicPostAsContact",
                "testCanCommentPublicPostAsNoContact",
                "testCannotCommentPrivatePostAsNoContact",
                "testCanCommentPrivatePostAsContact"




                


            ];

            $tests = [
                "Can View Own Posts" => ['controller' => 'post', 'tests' => $testCanViewOwnPosts],
                "Cannot View Own Posts" => ['controller' => 'post', 'tests' => $testCannotViewOwnPosts],
                "Can View Others' Posts" => ['controller' => 'post', 'tests' => $testCanViewOthersPosts],
                "Cannot View Others' Posts" => ['controller' => 'post', 'tests' => $testCannotViewOthersPosts],
                "Post Helper Functions" => ['controller' => 'post', 'tests' => $testDeveloperTestPosts],
                "Can User comment" => ['controller' => 'comments', 'tests' => $testUserComment]
            ];

            
            $runner = new TestRunner($controllers);
            $runner->runTests($tests);
            stream_wrapper_restore('php');
            $GLOBALS['user_id'] = 12;

            unset($post);
            unset($comments);
?>