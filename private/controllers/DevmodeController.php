<?php

class DevmodeController {
        
    protected $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
    
    public function getDevMode() {
        $devMode = new Devmode($this->dbConnection);
        $devModeStatus = $devMode->getDevModeStatus();
        sendResponse('success', ['devmode' => $devModeStatus], SUCCESS_OK);
    }
    
    public function toggleDevMode() {
        $devMode = new Devmode($this->dbConnection);

        $result = $devMode->toggleDevMode();
        if ($result) {
            sendResponse('success', ['message' => 'Devmode toggled'], SUCCESS_OK);
        } else {
            sendResponse('error', ['message' => 'Failed to toggle devmode'], ERROR_INTERNAL_SERVER);
        }
    }

    public function toggleDevModeValue(string $value) {
        $devMode = new Devmode($this->dbConnection);
        if($value != null){
            $bool = $value == 'true' ? true : false;
            $result = $devMode->toggleDevModeFromValue($bool);
        } else {
            $result = $devMode->toggleDevMode();
        }
        
        if ($result) {
            sendResponse('success', ['message' => 'Devmode status updated'], SUCCESS_OK);
        } else {
            sendResponse('error', ['message' => 'Unable to update devmode status'], ERROR_INTERNAL_SERVER);
        }
    }
}
?>