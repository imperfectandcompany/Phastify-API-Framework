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
        // Retrieve logs for a specific user with a specific action
        $query = "SELECT * FROM activity_log WHERE user_id = ? AND action = ?";
        $params = [$userId, $action];
        return $this->dbObject->queryAll($query, $params);
    }

    /**
     * Log an activity with custom data.
     *
     * @param int $userId The ID of the user performing the activity.
     * @param string $action The action being logged.
     * @param array $customData Custom data specific to the activity.
     */
    public function logWithCustomData($userId, $action, $customData) {
        // Create a log entry in the database with custom data
        $query = "INSERT INTO activity_log (user_id, action, activity_data, custom_data) VALUES (?, ?, ?, ?)";
        $params = [$userId, $action, json_encode($customData), json_encode($customData)];
        $this->dbObject->query($query, $params);
    }

    /**
     * Get all logs for a specific user.
     *
     * @param int $userId The ID of the user for whom logs are retrieved.
     *
     * @return array An array of log entries for the user.
     */
    public function getUserLogs($userId) {
        // Retrieve logs for a specific user
        $query = "SELECT * FROM activity_log WHERE user_id = ?";
        $params = [$userId];
        return $this->dbObject->queryAll($query, $params);
    }

}
