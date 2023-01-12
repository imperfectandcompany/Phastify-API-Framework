<?php
/**
 * Helper function for returning json responses
 *
 * @param mixed $data The data to be encoded as json and returned
 * @param int $status The HTTP status code to be returned with the response. Default is 200
 * @return void
 */
function json_response($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit();
}

?>
