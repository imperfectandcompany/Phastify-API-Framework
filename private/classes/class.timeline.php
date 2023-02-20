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

        return $this->dbObject->viewData("posts", '*', $query, $filter_params);
    }

    
    
}
