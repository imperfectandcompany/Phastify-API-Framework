<?php            
            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');
            include_once($GLOBALS['config']['private_folder'].'/classes/class.testRunner.php');

            // Initialize the PostController object once
            $post = new PostController($dbConnection);
            
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

            $tests = [
                "Can View Own Posts" => $testCanViewOwnPosts,
                "Cannot View Own Posts" => $testCannotViewOwnPosts,
                "Can View Others' Posts" => $testCanViewOthersPosts,
                "Cannot View Others' Posts" => $testCannotViewOthersPosts
            ];


            
            $runner = new TestRunner($post);
            $runner->runTests($tests);

            unset($post);
?>