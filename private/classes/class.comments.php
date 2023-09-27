<?php
include_once($GLOBALS['config']['private_folder'].'/classes/class.post.php');


class Comments
{

    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }


    // Retrieve Comments for a Post
    public function getComments(int $id) {
        $query = 'WHERE post_id = ?';
        $select = 'id, comment, user_id, post_id, posted_on';
        $paramValues = array($id);
        $filter_params = makeFilterParams($paramValues);
        return $this->dbConnection->viewData("comments", $select, $query, $filter_params)["results"];
    }

    // Helper function to validate data against allowed and required columns
    private function validateData(array $allowedColumns, array $requiredColumns, array $data) {
        // Check if unknown columns are present
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns)) {
                // Handle unknown column error
                throw new Exception("Unknown column {$key} provided.", ERROR_BAD_REQUEST);
            }
        }

        // Ensure required columns exist
        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $data)) {
                // Handle missing required column error
                throw new Exception("Required column {$column} missing.", ERROR_BAD_REQUEST);
            }
        }

        // Filter data to contain only allowed columns and ensure values are not empty
        $filteredData = array_filter($data, function ($value, $key) use ($allowedColumns) {
            return in_array($key, $allowedColumns) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        // Create dynamic rows and values for SQL statement
        $rows = implode(", ", array_keys($filteredData));
        $values = ':' . implode(", :", array_keys($filteredData));

        $filterParams = makeFilterParams($filteredData);

        return [
            "rows" => $rows,
            "values" => $values,
            "filterParams" => $filterParams,
        ];
    }

    // Helper function to add specific column-value pairs to the validated data
    private function addToValidatedData(array &$validatedData, $columnName, $value) {
        // Add the column name to the list of rows
        $validatedData["rows"] .= ", " . $columnName;

        // Add the value to the list of values
        $validatedData["values"] .= ", " . $value;
    }

    // Create a Comment on a Post
    // TO DO: Make sure post isn't archived.
    public function postComment(int $id, object $data) {
        // Turn object to array
        $data = (array)$data;

        // Add id to data
        $data['user_id'] = $GLOBALS['user_id'];
        
        // Add post_id to data
        $data['post_id'] = $id;

        $allowedColumns = ['comment', 'user_id', 'post_id', 'posted_on'];
        $requiredColumns = ['comment', 'user_id', 'post_id'];

        // Validate data and get validated data
        $validatedData = $this->validateData($allowedColumns, $requiredColumns, $data);

        // Add 'posted_on' column with the value 'UNIX_TIMESTAMP()' to validated data
        $this->addToValidatedData($validatedData, 'posted_on', 'UNIX_TIMESTAMP()');

        if (!$this->dbConnection->insertData('comments', $validatedData["rows"], $validatedData["values"], $validatedData["filterParams"])) {
            throw new Exception('Failed to insert data into the database.', ERROR_INTERNAL_SERVER);
        }

        return true;
    }
    

}