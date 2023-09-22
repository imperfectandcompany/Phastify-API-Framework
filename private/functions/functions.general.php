<?php

/**
 * Displays a series of feedback messages when given an array of message types
 *
 * If no specific value is provided uses the default messages global
 *
 * If our active session contains messages (such as confirmations) we display and kill these here too
 *
 * @param array $messages
 * @return void
 */
function display_feedback($messages = null)
{
    if(is_null($messages)){ $messages = $GLOBALS['messages']; }
    foreach($messages as $key => $value)
    {
        if(count($value) > 0)
        {
            switch($key)
            {
                case"error":
                    extract(array("f_type" => "danger", "f_header" => "Error"));                  
                case"errors":
                    extract(array("f_type" => "danger", "f_header" => "Error"));
                break;
                case"warning":
                    extract(array("f_type" => "warning", "f_header" => "Warning"));
                break;
                case"test":
                break;                
                case"success":
                    extract(array("f_type" => "success", "f_header" => "Success"));
                break;
                default:
                break;
               
            }
            extract(array("feedback" => $messages[$key]));
            require($GLOBALS['config']['private_folder'].'/templates/tmp_feedback.php');
        }
    }
}

function trimSlash($file){
    // Split the path into segments
    $segments = explode('/', $file);

    // Get the last two segments
    $lastTwoSegments = array_slice($segments, -2);

    // Combine the last two segments back into a path
    $trimmedPath = '/' . implode('/', $lastTwoSegments);
    return $trimmedPath;
}

function isTest(){
    return !isset($GLOBALS['config']['testmode']) && $GLOBALS['config']['testmode'] != true;
}

function throwWarning($message){
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $file = $caller['file'];
    $line =  $caller['line'];
    $message = $message . " - Thrown in file " . trimSlash($file) . " on line " . $line;
    if (isTest()) {
    $GLOBALS['messages']["warning"][] = $message;
    } else {
        global $currentTest;
        $GLOBALS['logs'][$currentTest]["warning"][] = $message;  // Store the message with the test name
    }
}

function throwError($message){
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $file = $caller['file'];
    $line =  $caller['line'];
    $message = $message . " - Thrown in file " . trimSlash($file) . " on line " . $line;
    if (isTest()) {
    $GLOBALS['messages']["error"][] = $message;
    } else {
        global $currentTest;
        $GLOBALS['logs'][$currentTest]["error"][] = $message;  // Store the message with the test name
    }
}

function throwSuccess($message){
    
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $file = $caller['file'];
    $line =  $caller['line'];
    $message = $message . " - Thrown in file " . trimSlash($file) . " on line " . $line;
    if (isTest()) {
        $GLOBALS['messages']["success"][] = $message;
    } else {
        global $currentTest;
        $GLOBALS['logs'][$currentTest]["success"][] = $message;  // Store the message with the test name
    }
}

function sendResponse($status, $data, $httpCode) {
    if (!isset($GLOBALS['config']['testmode']) && $GLOBALS['config']['testmode'] != true) {
        echo json_response(['status' => $status] + $data);
        $GLOBALS['messages'][$status][] = $data && isset($data['message']) ? $data['message'] : null;
        http_response_code($httpCode);
    } else {
        global $currentTest;
        if($data && isset($data['message'])){
            $GLOBALS['logs'][$currentTest][$status][] = $data['message'];  // Store the message with the test name
        }
    }
}


/**
 * Utility function to check if the given input fields are set and not empty.
 * Returns an error message if any of the fields are missing.
 */
function checkInputFields($inputFields, $postBody) {
    foreach ($inputFields as $field) {
        if (!isset($postBody->{$field}) || empty($postBody->{$field})) {
            $error = "Error: " . ucfirst($field) . " field is required";
            echo json_encode(array('status' => 'error', 'message' => $error));
            http_response_code(400);  // Bad Request
            exit;
        }
    }
}