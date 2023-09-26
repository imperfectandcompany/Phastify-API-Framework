<?php
/**
 * Helper function for returning Filter Params
 *
 * Builds an array of filter parameters for use in a database query.
 *
 * @param mixed $paramValues The parameter values to use in the query
 *
 * @return array Returns an array of filter parameters with the appropriate PDO parameter type for each value
 *
 * @example Usage examples:
 *   $singleValue = 'example';
 *   $singleFilterParams = $this->makeFilterParams($singleValue);
 *   Output: array(array('value' => 'example', 'type' => PDO::PARAM_STR))
 *
 *   $multipleValues = array('example1', 'example2', 123);
 *   $multipleFilterParams = $this->makeFilterParams($multipleValues);
 *   Output: array(
 *     array('value' => 'example1', 'type' => PDO::PARAM_STR),
 *     array('value' => 'example2', 'type' => PDO::PARAM_STR),
 *     array('value' => 123, 'type' => PDO::PARAM_INT)
 *   )
 */
function makeFilterParams($paramValues) {
    // Convert a single value to an array
    if (!is_array($paramValues)) {
        $paramValues = array($paramValues);
    }
    
    $filterParams = array();
    
    foreach ($paramValues as $value) {
        $type = PDO::PARAM_STR; // Default to string type
        
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        }
        
        $filterParams[] = array('value' => $value, 'type' => $type);
    }
    
    return $filterParams;
}

/**
 * Generate placeholders for an array of keys.
 *
 * @param array $keys An array of keys for which to generate placeholders.
 * @param string $placeholder The placeholder to use (e.g., '?').
 * 
 * @return string The generated placeholders joined by commas.
 */
function makePlaceholders($keys, $placeholder = '?') {
    // Convert a single value to an array
    if (!is_array($keys)) {
        $keys = array($keys);
    }
    
    return implode(', ', array_fill(0, count($keys), $placeholder));
}