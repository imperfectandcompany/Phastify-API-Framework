<?php

class Security {
    
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $filter_params = array();
        $filter_params[] = array("value" => $GLOBALS['user_id'], "type" => PDO::PARAM_INT);
        $query = "WHERE id = ?";
        $this->user = $this->dbConnection->viewSingleData($GLOBALS['db_conf']['db_db'].".users", "*", $query, $filter_params);
    }
    
    public function checkContact(int $userid, int $contactId) {
        $paramValues = makeFilterParams(array($userid, $contactId));
        $query = "WHERE id = ? AND contact_id = ?";
        if($userid == $contactId){
            return true;
        }
        if ($this->dbConnection->viewSingleData('contacts', '*', $query, $paramValues)['result'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function checkUser(int $userid) {
        $paramValues = makeFilterParams(array($userid));
        $query = "WHERE id = ?";
        if ($this->dbConnection->viewSingleData('users', '*', $query, $paramValues)['result'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    
}
