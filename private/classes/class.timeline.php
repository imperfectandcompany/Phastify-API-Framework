<?php

class timeline {
    
    private $dbObject;
    /**
     * Constructor for the Timeline class.
     *
     * @param DatabaseConnector $dbObject A database connection object
     */
    public function __construct($dbObject)
    {
        $this->dbObject = $dbObject;
    }

    
    /**
     * For displaying a public timeline feed of events
     *
     * @return void
     */
    public function fetchPublicTimeline(){
        $query = 'LIMIT '.$GLOBALS['config']['max_timeline_lookup'];
        $filter_params = array();

        return $this->dbObject->viewData($GLOBALS['db_conf']['db_db'].".posts", '*', $query, $filter_params);
    }
    //For showing a private timeline
    //UserID of the person viewing it
    //User ID of the person's timeline optional
    public function fetchPrivateTimeline($user_id, $view_id = NULL){

        //If timeline post is set to 2, it means they're for contacts only. This means that we can only see it if we're a contact of this person. We need to be mutual followers, they need to set me as a contact.
        //if id = 3 and we are contact of this person, show.

    }
    
    
}
