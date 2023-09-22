<?php
class TestRunner
{
    private $objectUnderTest;
    private $failed = false;
    private $currentFailed = false;

    private $metrics = [
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0,
        'errors' => 0,
        'successes' => 0
    ];

    public function __construct($objectUnderTest)
    {
        $this->objectUnderTest = $objectUnderTest;
        $this->addCSS();
    }

    public function runTests($categories)
    {
        $totalCategories = count($categories);
        $totalTests = array_sum(array_map('count', $categories)); // Get the total number of tests
        $totalTestsRun = 0;
    
        foreach ($categories as $category => $tests) {
            // Initialize category metrics
            $categoryMetrics = [
                'warnings' => 0,
                'errors' => 0,
                'successes' => 0
            ];
    
            echo "<div class='category'>Category: {$category}</div>";
    
            foreach ($tests as $test) {
                $count = count($tests);
                $index = array_search($test, $tests) + 1;
                $remaining = $totalTests - $totalTestsRun;
                echo "Initiating test {$index} out of {$count} in this category ({$remaining} total remaining)\n<br><br>";
                $totalTestsRun++;
    
                $this->runTest($test);
    
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
        $GLOBALS['config']['testmode'] = false;
        $GLOBALS['logs'][] = [];
        $GLOBALS['currentTest'] = null;
    }

    private function runTest($testName)
    {
        $GLOBALS['currentTest'] = $testName; // Set the currently running test name
        echo "<div class='test'>Running: {$testName}... ";
        try {
            $this->currentFailed = false;
            $testName($this->objectUnderTest);
            echo "<span class='passed'>PASSED</span>";
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
        echo "</div>"; // End of the test div
    }

    private function addCSS()
    {
        // Add this CSS to your page (either inline or in a separate CSS file)
        echo "
        <style>
            .category {
                font-weight: bold;
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