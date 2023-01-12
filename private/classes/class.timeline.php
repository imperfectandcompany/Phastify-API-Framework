<?php

class timeline {
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

    
    
}
