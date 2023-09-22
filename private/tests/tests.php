<?php            
            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');

            // Initialize the PostController object once
            $post = new PostController($dbConnection);
            
            $failures = [];

            function customAssert($condition, $message) {
                global $failures;
            
                if (!$condition) {
                    throw new Exception($message);
                }
            }
            

            // Run all the test functions
            // testCanViewOwnPublicPost($post);
            // testCanViewOwnPrivatePost($post);
            // testCanViewOwnArchivedPublicPost($post);
            // testCanViewOwnArchivedPrivatePost($post);
            // testCannotViewOwnUnauthorizedPost($post);
            // testCannotViewUnauthorizedPost($post);
            // testCannotViewUnauthorizedPostAsContact($post);

            // testCannotViewArchivedPublicPost($post);


            // testCannotViewArchivedPrivatePost($post);
            // testCannotViewArchivedPublicPostAsContact($post);
            // testCannotViewArchivedPrivatePostAsContact($post);
            // testCanViewPublicPostAsContact($post);
            // testCanViewPublicPostAsNoContact($post);
            // testCannotViewPrivatePostAsNoContact($post);
            // testCanViewPrivatePostAsContact($post);
            $tests = [
                "testCanViewOwnPublicPost",
                "testCanViewOwnPrivatePost",
            ];


            foreach ($tests as $test) {
                try {
                    $test($post);
                    echo "{$test}: PASSED\n";
                } catch (Exception $e) {
                    echo "{$test}: FAILED - {$e->getMessage()}\n";
                }
            }

            unset($post);
?>