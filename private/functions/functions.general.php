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
    if(isset($_SESSION['messages'])){ $messages = array_merge_recursive($messages, $_SESSION['messages']); unset($_SESSION['messages']); }
    foreach($messages as $key => $value)
    {
        if(count($value) > 0)
        {
            switch($key)
            {
                case"error":
                case"errors":
                    extract(array("f_type" => "danger", "f_header" => "Error"));
                break;
                case"warning":
                    extract(array("f_type" => "warning", "f_header" => "Note"));
                break;
                case"success":
                    extract(array("f_type" => "success", "f_header" => "Success"));
                break;
            }
            extract(array("feedback" => $messages[$key]));
            require $GLOBALS['config']['private_folder']."/templates/tmp_feedback.php";
        }
    }
}
