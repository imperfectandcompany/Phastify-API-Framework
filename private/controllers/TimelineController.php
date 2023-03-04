<?php
//Includes
include($GLOBALS['config']['private_folder'].'/classes/class.timeline.php');

class TimelineController {
    
    protected $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function fetchPublicTimeline()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(ERROR_METHOD_NOT_ALLOWED);
            echo '405 - Method Not Allowed';
            return;
        }
        $timeline = new Timeline($this->dbConnection);
        // Fetch the public timeline data using $this->dbConnection
        $endpointResponse = $timeline->fetchPublicTimeline()['results'];
        //prepare array for json
        $result = array();
        foreach ($endpointResponse as $row) {
            $temp = array(
                "PostId" => $row["id"],
                "PostBody" => $row["body"],
                "PostedBy" => $row["user_id"],
                "Likes" => $row["likes"]
            );
            array_push($result, $temp);
        }
        // Return the data as JSON
        json_response($result);
    }
    
    
    public function fetchPublicTimelineParamTest($param)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(ERROR_METHOD_NOT_ALLOWED);
            echo '405 - Method Not Allowed';
            return;
        }
        
        echo $param;
        
        $timeline = new Timeline($this->dbConnection);
        // Fetch the public timeline data using $this->dbConnection
        $endpointResponse = $timeline->fetchPublicTimeline()['results'];
        //prepare array for json
        $result = array();
        foreach ($endpointResponse as $row) {
            $temp = array(
                "PostId" => $row["id"],
                "PostBody" => $row["body"],
                "PostedBy" => $row["user_id"],
                "Likes" => $row["likes"]
            );
            array_push($result, $temp);
        }
        // Return the data as JSON
        json_response($result);
    }
    
}
 ?>
