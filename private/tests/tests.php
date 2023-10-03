<?php


            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/test_comment.php');
            include_once($GLOBALS['config']['private_folder'].'/tests/test_integration.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
            
            include_once($GLOBALS['config']['private_folder'].'/tests/controllers/CommentControllerTestDouble.php');

            include_once($GLOBALS['config']['private_folder'].'/tests/controllers/IntegrationControllerTestDouble.php');

            include_once($GLOBALS['config']['private_folder'].'/classes/class.testRunner.php');
            include_once($GLOBALS['config']['private_folder'].'/classes/class.logger.php');
            //include($GLOBALS['config']['private_folder'].'/structures/create_constants_structure.php');
            // set up test database connection
            $dbConnection = new DatabaseConnector(
                $GLOBALS['db_conf']['db_host'],
                $GLOBALS['db_conf']['port'],
                $GLOBALS['db_conf']['db_db_test'],
                $GLOBALS['db_conf']['db_user'],
                $GLOBALS['db_conf']['db_pass'],
                $GLOBALS['db_conf']['db_charset']
            );
            $logger = new Logger($dbConnection);
            // Initialize the Controller object once
            $controllers = [
                'post' => new PostController($dbConnection, $logger),
                'comments' => new CommentControllerTestDouble($dbConnection, $logger),
                'integrations' => new IntegrationControllerTestDouble($dbConnection, $logger)
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
                "testUserCanAddService",
                "testUserCanAddIntegration",
                "testUserCanUpdateIntegrationSettings",
                "testUserCanCleanUpScenerio"
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
