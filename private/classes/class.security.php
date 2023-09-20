<?php

class Security {
    
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        // $filter_params = array();
        // $filter_params[] = array("value" => $GLOBALS['user_id'], "type" => PDO::PARAM_INT);
        // $query = "WHERE id = ?";
        // $this->user = $this->dbConnection->viewSingleData($GLOBALS['db_conf']['db_db'].".users", "*", $query, $filter_params);
    }
    
    /**
     * Check if a user is a contact of another user.
     *
     * @param int $userid The ID of the user.
     * @param int $contactId The ID of the contact.
     * @return bool True if the user is a contact, false otherwise.
     */
    public function checkContact(int $userid, int $contactId) {
        $paramValues = makeFilterParams(array($userid, $contactId));
        $query = "WHERE id = ? AND contact_id = ?";
        
        // If the user ID matches the contact ID, they are considered a contact.
        if ($userid == $contactId) {
            return true;
        }
        
        // Check if a record exists in the 'contacts' table with the specified IDs.
        if ($this->dbConnection->viewSingleData('contacts', '*', $query, $paramValues)['result']) {
            return true;
        } else {
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
