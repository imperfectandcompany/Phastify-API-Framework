<?php

class Devmode {
    
    private $dbObject;

    public function __construct($dbObject)
    {
        $this->dbObject = $dbObject;
    }
    
    public function toggleDevMode(){
        $currentStatus = $this->getDevModeStatus();
        return $this->setDevModeStatus(!$currentStatus);
    }
    
    public function toggleDevModeFromValue(bool $value){
        return $this->setDevModeStatus($value);
    }

    public function getDevModeStatus(){
        $result = $this->dbObject->viewSingleData("app_settings", 'devmode')['result'];
        return $result ? $result['devmode'] : false;
    }

    private function setDevModeStatus($status){
        // Assuming your database class has an update function
        return $this->dbObject->updateData("app_settings", "devmode = :devmode", "WHERE id = 1", [
            [ 'value' => $status, 'type' => PDO::PARAM_BOOL ]
        ]);
    }
}
