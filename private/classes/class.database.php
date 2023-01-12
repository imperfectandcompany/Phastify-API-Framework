<?php
class DatabaseConnector {

    private $dbConnection = null;

    
    public function __construct($host, $port, $db, $user, $pass, $charset) {
        
        if (!isset($host, $port, $db, $user, $pass, $charset)) {
            $globals['error'] = "Warning: DB connection is missing variables.";
            return false;
        }
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->dbConnection = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            $GLOBALS['errors'][] = $e->getMessage();
            return false;
        }
    }
    
    public function getConnection()
    {
        return $this->dbConnection;
    }
    
    public function query($query, $params = array()) {
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
        
        //if the first keyword in the query is select, then run this.
        if (explode(' ', $query)[0] == 'SELECT'){
        $data = $statement->fetchAll();
        return $data;
        }
    }
    
    public function viewData($table, $select = '*', $query = null, $filter_params = null)
    {
        try {
            $stmt = $this->dbConnection->prepare("SELECT $select FROM $table $query");
            if($filter_params){
                foreach($filter_params as $key => $data){
                    $key++;
                    $stmt->bindParam($key, $data['value'], $data['type']);
                }
            }

            $stmt->execute();
            return array("count" => $stmt->rowCount(), "results" => $stmt->fetchAll());
        } catch (\PDOException $e) {
            $GLOBALS['errors'][] = $e->getMessage();
            return false;
        }
    }


    

}

