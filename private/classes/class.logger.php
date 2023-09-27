<?php
/**
 * Class Logger
 * Handles logging of user activities in the database.
 */
class Logger {
    private $dbObject;

    /**
     * Logger constructor.
     *
     * @param DatabaseConnector $dbObject A database connection object.
     */
    public function __construct($dbObject) {
        $this->dbObject = $dbObject;
    }

    /**
     * Log an activity.
     *
     * @param int $userId The ID of the user performing the activity.
     * @param string $action The action being logged.
     * @param array|null $data Additional data related to the activity.
     */
    public function log($userId, $action, $data = null) {
        if($userId == 0) {
            // if user is not logged in lets use uid 19 which is reserved for guest
            $userId = 19;
        }        
        // Create a log entry in the database
        $query = "INSERT INTO activity_log (user_id, action, activity_data) VALUES (?, ?, ?)";
        $params = [$userId, $action, json_encode($data)];
        $this->dbObject->query($query, $params);
    }

    /**
     * Get logs with a specific action for a specific user.
     *
     * @param int $userId The ID of the user for whom logs are retrieved.
     * @param string $action The action for which logs are retrieved.
     *
     * @return array An array of log entries matching the criteria.
     */
    public function getUserLogsByAction($userId, $action) {
        if($userId == 0) {
            // if user is not logged in lets use uid 19 which is reserved for guest
            $userId = 19;
        }
        // Retrieve logs for a specific user with a specific action
        $table = 'activity_log';
        $select = '*';
        $whereClause = "WHERE user_id = ? AND action = ?";
        $filter_params = makeFilterParams(array($userId, $action));
        return $this->dbObject->viewData($table, $select, $whereClause, $filter_params); 
    }

    /**
     * Log an activity with custom data.
     *
     * @param int $userId The ID of the user performing the activity.
     * @param string $action The action being logged.
     * @param array $customData Custom data specific to the activity.
     */
    public function logWithCustomData($userId, $action, $customData) {
        if($userId == 0) {
            // if user is not logged in lets use uid 19 which is reserved for guest
            $userId = 19;
        }
        // Create a log entry in the database with custom data
        $query = "INSERT INTO activity_log (user_id, action, activity_data, custom_data) VALUES (?, ?, ?, ?)";
        $params = [$userId, $action, json_encode($customData), json_encode($customData)];
        $this->dbObject->query($query, $params);
    }

}
