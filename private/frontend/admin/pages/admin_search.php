<form method="get" class="mb-8">
    <div class="flex items-center">
        <input type="text" name="query" id="query" placeholder="Search..."
            class="w-full p-2 border border-gray-300 rounded-l focus:outline-none"
            value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" />
        <select name="category" id="category" class="py-2 px-4 border border-gray-300 rounded-r focus:outline-none">
            <option value="all" <?php echo isset($_GET['category']) && $_GET['category'] === 'all' ? 'selected' : ''; ?>>
                All</option>
            <option value="post" <?php echo isset($_GET['category']) && $_GET['category'] === 'post' ? 'selected' : ''; ?>>Posts</option>
            <option value="comment" <?php echo isset($_GET['category']) && $_GET['category'] === 'comment' ? 'selected' : ''; ?>>Comments</option>
            <option value="user" <?php echo isset($_GET['category']) && $_GET['category'] === 'user' ? 'selected' : ''; ?>>Users</option>
            <option value="integration" <?php echo isset($_GET['category']) && $_GET['category'] === 'integration' ? 'selected' : ''; ?>>Integrations</option>
            <option value="reply" <?php echo isset($_GET['category']) && $_GET['category'] === 'reply' ? 'selected' : ''; ?>>Replies</option>
        </select>
        <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Search</button>
    </div>
</form>

<!-- Search Results Section -->
<div class="mt-8">
    <?php
    // Check if the form has been submitted
    if (isset($_GET['query'])) {
        include_once($GLOBALS['config']['private_folder'] . '/classes/class.search.php');
        $search = new Search($this->prodDbConnection, $prodLogger);
        $found = false;
        try {
            // Perform the search and get the results
            $searchResults = $search->performSearch($_GET['query'], $_GET['category']);

            // Display search results
            if (is_array($searchResults)) {
                $found = true;
                echo '<h2 class="text-xl mb-4">Search Results</h2>';
                echo '<ul>';
                foreach ($searchResults as $result) {
                    // Display 'Type'
                    echo '<li>Type: ' . $result['type'] . ', ';

                    // Switch content based on type
                    switch ($result['type']) {
                        case 'post':
                            echo 'Post content: ' . ($result['content'] ?? '');
                            break;
                        case 'comment':
                            echo 'Comment content: ' . ($result['content'] ?? '');
                            break;
                        case 'user':
                            echo 'Username: ' . ($result['content'] ?? '');
                            break;
                        case 'integration':
                            echo 'Service Name: ' . ($result['content'] ?? '');
                            break;
                        case 'reply':
                            echo 'Reply content: ' . ($result['content'] ?? '');
                            break;
                        default:
                            echo 'Unknown Type';
                    }

                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No results found.</p>';
            }
        } catch (PDOException $e) {
            // Handle the database query error (e.g., log or show an error message)
            echo 'Database Error: ' . $e->getMessage();
        }
    }
     if($found){
        printPagination($startPage, $endPage, $totalPages, $searchPage, $query, $category);
     }
     ?>
</div>

<script>
    document.getElementById("searchBtn").addEventListener("click", function() {
    const query = document.getElementById("searchInput").value;
    // Make an AJAX call to the /admin/search endpoint with the query
    // Display the search results in the "searchResults" div
});
</script>

