<!-- Tests Tab -->
<div class="tab-content" id="testsTab">
    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">Run Tests</h2>
        
        <!-- Trigger tests button -->
        <button id="runTestsBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Run Tests
        </button>
        
        <!-- Display test results -->
        <div id="testResults" class="mt-4">
            <!-- Test results will be appended here dynamically -->
        </div>
    </div>
</div>

<script>
    document.getElementById("runTestsBtn").addEventListener("click", function() {
        // TODO: Make an AJAX call to the backend to run tests
        // Display results in the "testResults" div
    });
</script>