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
        return "Yo we made it";
    }

    
    
}
