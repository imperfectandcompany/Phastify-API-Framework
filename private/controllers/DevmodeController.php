<?php
include($GLOBALS['config']['private_folder'].'/classes/class.devmode.php');

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

    public function toggleDevModeValue(bool $status) {
        $devMode = new Devmode($this->dbConnection);

        $result = $devMode->toggleDevModeFromValue($status);
        
        if ($result) {
            sendResponse('success', ['message' => 'Devmode status updated'], SUCCESS_OK);
        } else {
            sendResponse('error', ['message' => 'Unable to update devmode status'], ERROR_INTERNAL_SERVER);
        }
    }
}
