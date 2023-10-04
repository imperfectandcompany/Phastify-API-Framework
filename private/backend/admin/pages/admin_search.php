<?php
    $resultsPerPage = 10; // Change this to your desired number of results per page
    $searchPage = isset($_GET['page']) ? intval($_GET['page']) : 1; // Get the current page from the query parameter
    
    
    $category = isset($_GET['category']) ? trim($_GET['category']) : "all"; // Get the current category from the query parameter
    
    // Check for valid category values (you can define a list of valid categories)
    $validCategories = ['post', 'comment', 'user', 'integration', 'reply', 'all'];
    if (!in_array($category, $validCategories)) {
        // Handle invalid category value, e.g., set it to 'all' as a default
        $category = 'all';
    }
    
    $query = trim($_GET['query']);
    
    $queryParam = "%$query%";
    
    
    // Construct a new SQL query to count the total number of rows without pagination
    $countSql = "SELECT COUNT(*) AS total FROM (
                    SELECT 'post' AS type FROM posts WHERE body LIKE :query1
                    UNION ALL
                    SELECT 'comment' AS type FROM comments WHERE comment LIKE :query2
                    UNION ALL
                    SELECT 'user' AS type FROM users WHERE username LIKE :query3
                    UNION ALL
                    SELECT 'integration' AS type FROM integrations
                    LEFT JOIN services ON integrations.service_id = services.id
                    WHERE services.name LIKE :query4
                    UNION ALL
                    SELECT 'reply' AS type FROM replies WHERE reply LIKE :query5
                ) AS subquery";
    
    $params = array(
        'query1' => $queryParam,
        'query2' => $queryParam,
        'query3' => $queryParam,
        'query4' => $queryParam,
        'query5' => $queryParam,
    );
    
    if ($category !== 'all') {
        $countSql .= " WHERE subquery.type = :category";
        $params[':category'] = $category;
    }
    
    // Prepare and execute the count query
    $countStatement = $this->dbConnection->getConnection()->prepare($countSql);
    $countStatement->execute($params);
    
    // Fetch the total number of results
    $countResult = $countStatement->fetch(PDO::FETCH_ASSOC);
    $totalResults = $countResult['total'];
    
    
    // Calculate the total number of pages
    $totalPages = ceil($totalResults / $resultsPerPage);
    
    // Limit the number of pages shown in pagination
    $maxPagesToShow = 5;
    
    $startPage = max(1, $searchPage - floor($maxPagesToShow / 2));
    $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

    function printPagination($startPage, $endPage, $totalPages, $searchPage, $query, $category){
            // Pagination code here
    echo '<ul class="flex justify-center space-x-2">';
    if ($startPage > 1) {
        // Add link to the first page
        echo '<li><a href="?page=1&query=' . urlencode($query) . '&category=' . urlencode($category) . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">1</a></li>';
        // Add an ellipsis if there are more pages before the start
        if ($startPage > 2) {
            echo '<li><span class="px-2">...</span></li>';
        }
    }
    for ($i = $startPage; $i <= $endPage; $i++) {
        // Create an array to hold the query parameters
        $queryParams = array(
            'query' => $query,
            // Preserve the 'query' parameter
            'category' => $category,
            // Preserve the 'category' parameter
            'page' => $i,
            // Set the 'page' parameter to the current page number                    
        );
    
        // Build the query string
        $queryString = http_build_query($queryParams);
        // Generate the pagination link
        $isActive = ($i == $searchPage) ? 'bg-blue-700' : 'bg-blue-500 hover:bg-blue-700';
        echo '<li><a href="?' . $queryString . '" class="text-white font-bold py-2 px-4 rounded transition ' . $isActive . '">' . $i . '</a></li>';
    }
    if ($endPage < $totalPages) {
        // Add an ellipsis if there are more pages after the end
        if ($endPage < $totalPages - 1) {
            echo '<li><span class="px-2">...</span></li>';
        }
        // Add link to the last page
        echo '<li><a href="?page=' . $totalPages . '&query=' . urlencode($query) . '&category=' . urlencode($category) . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">' . $totalPages . '</a></li>';
    }
    echo '</ul>';
    }
?>