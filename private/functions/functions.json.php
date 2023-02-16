<?php
/**
 * Helper function for returning json responses
 * This helper function is intended to return json responses to the frontend. It takes three parameters:
 * @param mixed $data The data to be encoded as json and returned
 * @param int $status The HTTP status code to be returned with the response. Default is 200
 * @param int $limit The maximum number of elements to show when dev_mode is on. Default is 100
 *
 * The function first sets the response's content type to application/json and sets the HTTP response code to the value passed in the status parameter
 * If dev_mode is on, the function will check if the length of the data is larger than the limit parameter. If it is, it will output the first limit elements of the data using var_dump and json_encode. Else it will output the whole data using var_dump and json_encode
 * If dev_mode is off, the function will output the json encoded data and exit the script.
 *
 * Note: This approach should be used only during the development phase and should not be used in production.
 *
 */
function json_response($data, $status = 200, $limit = 5) {
    if($GLOBALS['config']['devmode'] == 1){
        $status = 403;
        http_response_code($status);
        echo "<h2>API Response:</h2>";
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT);
        echo "</pre>";
        echo "<h3>Debug version</h3>";
        if(count($data) > $limit){
            echo "<pre>";
            var_dump(array_slice($data,0,$limit));
            echo "</pre>";
        }else{
            echo "<pre>";
            var_dump($data, true);
            echo "</pre>";
        }
    } else {
        //since we are not in dev mode, we are printing it with application/json content-type
        http_response_code($status);
        header('Content-Type: application/json');
        $json_data = json_encode(array_slice($data, 0, $limit), JSON_PRETTY_PRINT);
        echo $json_data;
    }
    exit();
}
?>
