<?php
class DashboardUsers
{
    private $dbConnection;

    public function __construct(DatabaseConnector $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    function getUsersList($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [
            ':limit' => ['value' => $perPage, 'type' => PDO::PARAM_INT],
            ':offset' => ['value' => $offset, 'type' => PDO::PARAM_INT]
        ];
        // using named placeholder binding
        $users = $this->dbConnection->viewDataNP('users', '*', 'LIMIT :limit OFFSET :offset', $params);
        return $users;
    }
    
function getUsersCount($query = null) {
    if ($query) {
        $params = [['value' => '%' . $query . '%', 'type' => PDO::PARAM_STR]];
        $count = $this->dbConnection->viewData('users', 'COUNT(*) as total', 'WHERE username LIKE ?', $params);
    } else {
        $count = $this->dbConnection->viewData('users', 'COUNT(*) as total');
    }
    return $count;
}


    function searchUsers($query, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [
            ['value' => '%' . $query . '%', 'type' => PDO::PARAM_STR],
            ['value' => $perPage, 'type' => PDO::PARAM_INT],
            ['value' => $offset, 'type' => PDO::PARAM_INT]
        ];
        // using placeholder binding
        $users = $this->dbConnection->viewData('users', '*', 'WHERE username LIKE ? LIMIT ? OFFSET ?', $params);
        return $users;
    }
}
?>