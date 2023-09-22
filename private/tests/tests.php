<?php            
            include_once($GLOBALS['config']['private_folder'].'/tests/test_post.php');
            include_once($GLOBALS['config']['private_folder'].'/controllers/PostController.php');

            // Initialize the PostController object once
            $post = new PostController($dbConnection);
            
            function customAssert($condition, $message) {
                global $currentTest;
                
                if (!$condition) {
                    $GLOBALS['logs'][$currentTest]["test"][] = $message;  // Store the message with the test name
                    throw new Exception($message);
                }
            }
            /*Each test function calls the canViewPost method of the PostController 
            class to determine whether a user can view a specific post based on various criteria
            (like post ownership, privacy settings, and archival status). After calling this method, 
            each function uses the customAssert to check the result against the expected outcome.*/


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
            "testCanViewOwnPrivatePost"
            ];

            foreach ($tests as $test) {
                $count = count($tests);
                $index = array_search($test, $tests) +1;
                $remaining = $count - $index;
                echo "Running test {$index} out of {$count} ({$remaining} remaining)\n";
                echo "</br>";

                $GLOBALS['currentTest'] = $test; // Set the currently running test name
                try {
                    $test($post);
                    echo "{$test}: PASSED\n";
                } catch (Exception $e) {
                    echo "{$test}: FAILED</br>{$e->getMessage()}\n";
                    echo "</br>";

                    if (isset($GLOBALS['logs'][$test]) && $GLOBALS['config']['testmode'] = true) {
                        display_feedback($GLOBALS['logs'][$test]);
                    }
                }
                echo "</br>";
            } 

            unset($post);
?>