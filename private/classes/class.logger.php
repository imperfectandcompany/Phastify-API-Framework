<?php
class Logger {
    private $dbObject;

    public function __construct($dbObject) {
        $this->dbObject = $dbObject;
    }

    public function log($userId, $action, $data = null) {
        // Create a log entry in the database
        $query = "INSERT INTO activity_log (user_id, action, activity_data) VALUES (?, ?, ?)";
        $params = [$userId, $action, json_encode($data)];
        $this->dbObject->query($query, $params);
    }
}