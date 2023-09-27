<?php
class TestRunner
{
    private $controllers;
    private $failed = false;
    private $currentFailed = false;
    private $metrics = [
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0,
        'errors' => 0,
        'successes' => 0
    ];

    public function __construct($controllers) {
        $this->controllers = $controllers;
        $this->addCSS();
    }

    public function runTestsForController($controller, $tests) {
        // Assuming you iterate through the $tests array inside the runTests() method
        $this->controller = $controller;
        $this->runTests($tests);
    }

    public function runTests($categories)
    {
        $totalCategories = count($categories);
        $totalTests = array_sum(array_map('count', $categories)); // Get the total number of tests
        $totalTestsRun = 0;
    
        foreach ($categories as $category => $testData) {

            // Extract the relevant controller
            $controllerName = $testData['controller'];
            if (!isset($this->controllers[$controllerName])) {
                throw new Exception("Controller $controllerName not provided.");
            }
            $controller = $this->controllers[$controllerName];

            // Initialize category metrics
            $categoryMetrics = [
                'warnings' => 0,
                'errors' => 0,
                'successes' => 0
            ];
    
            echo "<div class='category'>Category: {$category}</div>";
    
            foreach ($testData['tests'] as $test) {
                $count = count($testData['tests']);
                $index = array_search($test, $testData['tests']) + 1;
                $value = $count-$index;
                echo "Initiating test {$index} out of {$count} in this category";
                echo "\n\n</br>Running: {$test}... ";


                $totalTestsRun++;
                $this->runTest($test, $controller);
                echo $value == 0 ? "Category test complete (0 remaining)</br></br>":"({$value} total remaining)\n<br><br>";

    
                // Capture category metrics from $GLOBALS
                if (isset($GLOBALS['logs'][$GLOBALS['currentTest']])) {
                    $currentTest = $GLOBALS['currentTest'];
                    $categoryMetrics['warnings'] += isset($GLOBALS['logs'][$currentTest]["warning"]) && $this->currentFailed ? count($GLOBALS['logs'][$currentTest]["warning"]) : 0;
                    $categoryMetrics['errors'] += isset($GLOBALS['logs'][$currentTest]["error"]) && $this->currentFailed ? count($GLOBALS['logs'][$currentTest]["error"]) : 0;
                    $categoryMetrics['successes'] += isset($GLOBALS['logs'][$currentTest]["success"]) && $this->currentFailed ? count($GLOBALS['logs'][$currentTest]["success"]) : 0;
                }
            }
    
            echo "<div class='category-metrics'>";
            foreach ($categoryMetrics as $metric => $count) {
                echo "<strong>{$metric}</strong>: {$count}<br/>";
                $this->metrics[$metric] += $count; // Update the main metrics
            }
            echo "</div><br>";
    
        }
    
        echo "<div class='metrics'>";
        echo "Total Categories: {$totalCategories}<br>";
        echo "Total Tests Run: {$totalTestsRun}<br>";
        foreach ($this->metrics as $metric => $count) {
            echo "<strong>{$metric}</strong>: {$count}<br/>";
        }
        echo "</div>";
    
        $this->cleanup();
    
        if ($this->failed) {
            die("Stopping due to failed tests.");
        }
    }
    private function cleanup()
    {
        $GLOBALS['config']['testmode'] = 0;
        $GLOBALS['logs'][] = [];
        $GLOBALS['currentTest'] = null;
    }

    private function testCleanup()
    {
        $GLOBALS['user_id'] = 12;
    }

    private function runTest($testName, $controller)
    {
        echo '<div class="test">'; // End of the test div
        $GLOBALS['currentTest'] = $testName; // Set the currently running test name
        try {
                $this->currentFailed = false;
                try{
                    $testName($controller);
                }
                catch(Error $e)
                {
                    throw new Exception($e->getMessage());
                }
                finally{
                    $this->testCleanup();
                }
            echo "<span class='passed'>PASSED</span></br></br>";
            $this->metrics['passed']++;
        } catch (Exception $e) {
            $this->failed = true;
            $this->currentFailed = true;
            echo "<span class='failed'>FAILED</span>";
            echo "<br>{$e->getMessage()}<br>";
            $this->metrics['failed']++;

            // Display additional logs if they exist
            if (isset($GLOBALS['logs'][$testName])) {
                display_feedback($GLOBALS['logs'][$testName]);
            }
        }
        finally {
            $this->testCleanup();
        }
        echo "</div>"; // End of the test div
    }

    private function addCSS()
    {
        // Add this CSS to your page (either inline or in a separate CSS file)
        echo "
        <style>
            .category {
                font-weight: bold;
                margin-top: 20px;
                margin-bottom: 20px;
                background-color: #f3f4f6;
                padding: 10px;
                border-radius: 5px;
            }
            
            .test {
                margin: 10px 0;
            }

            .passed {
                color: green;
            }
            
            .failed {
                color: red;
            }
        </style>
        ";
    }
}