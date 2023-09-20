<?php
/**
 * Helper function for returning JSON responses
 *
 * This function is intended to return JSON responses to the frontend. It takes three parameters:
 *
 * @param mixed $data The data to be encoded as JSON and returned
 * @param int $status The HTTP status code to be returned with the response. Default is 200
 * @param int $limit The maximum number of elements to show when dev_mode is on. Default is 5
 *
 * The function first sets the response's content type to application/json and sets the HTTP response code to the value passed
 * in the $status parameter. If dev_mode is on, the function will output debugging information in addition to the JSON-encoded data.
 *
 * If dev_mode is on, the function will check if the length of the $data is larger than the $limit parameter. If it is, it will
 * output the first $limit elements of the data using var_dump and json_encode. Otherwise, it will output the whole data using var_dump
 * and json_encode.
 *
 * If dev_mode is off, the function will output the JSON-encoded data and exit the script.
 *
 * Note: This approach should be used only during the development phase and should not be used in production.
 *
 * @param $data
 * @param int $status
 * @param int $limit
 */
function json_response($data, $status = 200, $limit = 5)
{
    $is_dev_mode = $GLOBALS['config']['devmode'] === 1;

    if ($is_dev_mode) {
        // In dev mode, output debugging information
        $status = 403;
        http_response_code($status);

        echo "<h2>API Response:</h2>";
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT);
        echo "</pre>";
        echo "<h3>Debug version</h3>";

        if (count($data) > $limit) {
            echo "<pre>";
            var_dump(array_slice($data, 0, $limit));
            echo "</pre>";
        } else {
            echo "<pre>";
            var_dump($data, true);
            echo "</pre>";
        }
    } else {
        // In production mode, output the JSON-encoded data and exit the script
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(array_slice($data, 0, $limit), JSON_PRETTY_PRINT);
    }

    exit();
}

function parse($text)
{
    // Damn pesky carriage returns...
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);

    // JSON requires new line characters be escaped
    $text = str_replace("\n", "\\n", $text);
    return $text;
}