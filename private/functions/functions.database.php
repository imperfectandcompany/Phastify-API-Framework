<?php
/**
* Helper function for returning Filter Params
*
* Builds an array of filter parameters for use in a database query.
*
* @param mixed $paramValues The parameter values to use in the query
*
* @return array Returns an array of filter parameters with the appropriate PDO parameter type for each value

Working example in private/classes/class/user.php getPasswordFromEmail() method:

public function getPasswordFromEmail($email) {
 $table = 'users';
 $select = 'password';
 $whereClause = 'WHERE email = :email';
 $filterParams = makeFilterParams($email);

 $result = $this->dbObject->viewSingleData($table, $select, $whereClause, $filterParams)['result'];
 return $result ? $result['password'] : false;
}

Usage examples:
$singleValue = 'example';
$singleFilterParams = $this->makeFilterParams($singleValue);
Output: array(array('value' => 'example', 'type' => PDO::PARAM_STR))

$multipleValues = array('example1', 'example2', 123);
$multipleFilterParams = $this->makeFilterParams($multipleValues);
Output: array(array('value' => 'example1', 'type' => PDO::PARAM_STR), array('value' => 'example2', 'type' => PDO::PARAM_STR), array('value' => 123, 'type' => PDO::PARAM_INT))
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
