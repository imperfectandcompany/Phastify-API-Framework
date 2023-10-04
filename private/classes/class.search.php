<?php
class Search
{
    protected $dbConnection;
    protected $logger;

    /**
     * Constructor for the Search  class.
     *
     * @param DatabaseConnector $dbConnector A database connection object
     */
    public function __construct($dbConnection, $logger)
    {
        $this->dbConnection = $dbConnection;
        $this->logger = $logger;
    }

    function performSearch($query, $category = "all")
    {
        $resultsPerPage = 10; // Change this to your desired number of results per page
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Get the current page from the query parameter



        // Validate and sanitize input
        $query = trim($query);
        $category = trim($category);

        // Check for valid category values (you can define a list of valid categories)
        $validCategories = ['post', 'comment', 'user', 'integration', 'reply', 'all'];
        if (!in_array($category, $validCategories)) {
            // Handle invalid category value, e.g., set it to 'all' as a default
            $category = 'all';
        }

        // Debugging statements
        echo 'Received query: ' . $_GET['query'] . '<br>';
        echo 'Received category: ' . $_GET['category'] . '<br>';

        // Construct SQL query with UNION to combine results from different tables
        $sql = "SELECT 'post' AS type, id, body AS content, body AS post, NULL AS comment, NULL AS username, NULL AS service_name, NULL AS reply FROM posts WHERE body LIKE :query1
        UNION ALL
        SELECT 'comment' AS type, id, comment AS content, NULL AS post, comment AS comment, NULL AS username, NULL AS service_name, NULL AS reply FROM comments WHERE comment LIKE :query2
        UNION ALL
        SELECT 'user' AS type, id, username AS content, NULL AS post, NULL AS comment, username AS username, NULL AS service_name, NULL AS reply FROM users WHERE username LIKE :query3
        UNION ALL
        SELECT 'integration' AS type, integrations.id, IFNULL(services.name, '') AS content, NULL AS post, NULL AS comment, NULL AS username, services.name AS service_name, NULL AS reply
        FROM integrations
        LEFT JOIN services ON integrations.service_id = services.id
        WHERE services.name LIKE :query4
        UNION ALL
        SELECT 'reply' AS type, id, reply AS content, NULL AS post, NULL AS comment, NULL AS username, NULL AS service_name, reply AS reply FROM replies WHERE reply LIKE :query5
        LIMIT :limit OFFSET :offset"; // Add LIMIT and OFFSET for pagination
        $offset = ($page - 1) * $resultsPerPage;

        try {
            // Build the parameter array
            $queryParam = "%$query%";

            $params = array(
                'query1' => $queryParam,
                'query2' => $queryParam,
                'query3' => $queryParam,
                'query4' => $queryParam,
                'query5' => $queryParam,
                ':limit' => $resultsPerPage,
                ':offset' => $offset,
            );

            if ($category !== 'all') {
                $sql = "SELECT * FROM ($sql) AS subquery WHERE subquery.type = :category";
                $params[':category'] = $category;
            }

            // Prepare the statement
            $statement = $this->dbConnection->getConnection()->prepare($sql);


            // Execute the query
            $statement->execute($params);

            // Fetch results
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the database query error (e.g., log or show an error message)
            echo 'Error executing query: ' . $e->getMessage();
        }
    }

}