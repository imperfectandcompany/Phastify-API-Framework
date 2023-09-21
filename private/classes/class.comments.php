<?php
include_once($GLOBALS['config']['private_folder'].'/classes/class.post.php');


class Comments
{

    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $PostController = new PostController($this->dbConnection);
        $this->postController = $PostController;
    }


    // Retrieve Comments for a Post
    public function getComments(int $id) {
        $query = 'WHERE post_id = ?';
        $select = 'id, comment, user_id, post_id, posted_on';
        $paramValues = array($id);
        $filter_params = makeFilterParams($paramValues);
        return $this->dbConnection->viewData("comments", $select, $query, $filter_params)["results"];
    }

    private function insertValidation(array $allowedColumns, array $requiredColumns, array $data){
            // Check if unknown columns are present
            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns)) {
                    // Handle unknown column error
                    throw new Exception("Unknown column {$key} provided.", ERROR_BAD_REQUEST);
                }
            }
            foreach ($requiredColumns as $key => $value) {
                // Ensure required columns exists. This is required for the WHERE clause.
                if (!array_key_exists($value, $data)) {
                    // Handle unknown column error
                    throw new Exception("Required column {$value} missing.", ERROR_BAD_REQUEST);
                }
            }

            // Filter data to contain only allowed columns and ensure values are not empty
            $filteredData = array_filter($data, function($value, $key) use ($allowedColumns) {
                return in_array($key, $allowedColumns) && !empty($value);
            }, ARRAY_FILTER_USE_BOTH);

            // Create dynamic rows and values for SQL statement
            $rows = implode(", ", array_keys($filteredData));
            $values = ':' . implode(", :", array_keys($filteredData));

            $filterParams = makeFilterParams($filteredData); 
            return array("rows" => $rows, "values" => $values, "filterParams" => $filterParams);
    }

    // Retrieve Comments for a Post
    // TO DO : Make sure post isn't archived.
    public function postComment(int $id, array $data) {
        $allowedColumns = ['comment', 'user_id', 'post_id', 'posted_on'];
        $requiredColumns = ['comment', 'user_id', 'post_id'];
        $data['user_id'] = $GLOBALS['user_id'];
        $data['post_id'] = $id;

        $validatedData = $this->insertValidation($allowedColumns, $requiredColumns, $data);
        $validatedData["rows"] = $validatedData["rows"] . ",posted_on";
        $validatedData["values"] = $validatedData["values"] . ",UNIX_TIMESTAMP()";

        if (!$this->dbConnection->insertData('comments', $validatedData["rows"], $validatedData["values"], $validatedData["filterParams"])) {
            throw new Exception('Failed to insert data into the database.', ERROR_INTERNAL_SERVER);
        }
        return true;
    }
    

}