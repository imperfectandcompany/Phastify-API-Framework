<?php            
            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/test_comment.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/test_integration.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/controllers/CommentControllerTestDouble.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/controllers/IntegrationControllerTestDouble.php');
            include_once($GLOBALS['config']['private_folder'].'/classes/class.testRunner.php');

            // Initialize the Controller object once
            $controllers = [
                'post' => new PostController($dbConnection),
                'comments' => new CommentControllerTestDouble($dbConnection),
                'integrations' => new IntegrationControllerTestDouble($dbConnection)
            ];
            
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

            $testIntegration = [
                "testUserCanUpdateIntegrationAfterAddingService"
            ];

            $tests = [
                "Can View Own Posts" => ['controller' => 'post', 'tests' => $testCanViewOwnPosts],
                "Cannot View Own Posts" => ['controller' => 'post', 'tests' => $testCannotViewOwnPosts],
                "Can View Others' Posts" => ['controller' => 'post', 'tests' => $testCanViewOthersPosts],
                "Cannot View Others' Posts" => ['controller' => 'post', 'tests' => $testCannotViewOthersPosts],
                "Post Helper Functions" => ['controller' => 'post', 'tests' => $testDeveloperTestPosts],
                "Can User comment" => ['controller' => 'comments', 'tests' => $testUserComment],
                "Integration" => ['controller' => 'integrations', 'tests' => $testIntegration]
            ];
            
            $runner = new TestRunner($controllers);
            $runner->runTests($tests);

            unset($post);
            unset($comments);
?>