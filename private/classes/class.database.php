<?php
/**
 * DatabaseConnector class provides methods for connecting to a MySQL database and performing common database operations.
 *
 * This class provides a wrapper around PHP's PDO database driver,
 * allowing you to easily connect to and interact with your database.
 * It provides methods for executing queries, inserting, updating,
 * and deleting data, as well as for fetching rows and counts.
 *
 * Usage:
 *
 * Instantiate the class by providing the required database connection
 * details: host, port, database name, username, password, and charset.
 * Once instantiated, you can use the provided methods to interact with
 * the database. For example:
 *
 * $db = new DatabaseConnector('localhost', 3306, 'my_database', 'root', '', 'utf8mb4');
 * $result = $db->viewData('my_table', '*', 'WHERE id = :id', array(':id' => array(123, PDO::PARAM_INT)));
 *
 * Methods:
 *
 * - __construct($host, $port, $db, $user, $pass, $charset) - creates a new database connection object using the provided connection details
 * - getConnection() - returns the database connection object
 * - viewCount($table, $filter_params = null, $query = null) - returns the count of rows matching the specified criteria in a given table
 * - query($query, $params = array()) - runs a specified query against the database and returns the resulting data (if applicable)
 * - viewData($table, $select = '*', $query = null, $filter_params = null) - returns an array of data from a specified table, filtered and/or ordered as specified
 * - viewSingleData($table, $select = '*', $query = null, $filter_params = null) - returns a single row of data from a specified table
 * - insertData($table, $rows, $values, $filter_params = null) - inserts a new row into a specified table with the given column names and values
 * - insertDataUnique($table, $rows, $values, $update_values, $filter_params = null) - inserts a new row into a specified table with the given column names and values, or updates an existing row with the specified values if a duplicate key is found
 * - updateData($table, $setClause, $whereClause = null, $filter_params = null) - updates a row or rows in a specified table with the given values, filtered as specified
 * - deleteData($table, $rows, $filter_params = null) - deletes a row or rows from a specified table, filtered as specified
 *
 * @package Postogon
 */
class DatabaseConnector
{
    private $dbConnection = null;

    /**
     * DatabaseConnector constructor.
     *
     * @param string $host The hostname of the database server
     * @param string $port The port number to use for the database connection
     * @param string $db The name of the database to connect to
     * @param string $user The username to use for the database connection
     * @param string $pass The password to use for the database connection
     * @param string $charset The character set to use for the database connection
     *
     * @return bool Returns false if any of the required connection parameters are missing
     */
    public function __construct($host, $port, $db, $user, $pass, $charset)
    {
        if (!isset($host, $port, $db, $user, $pass, $charset)) {
            $GLOBALS['messages']['errors'][] = "Warning: DB connection is missing variables.";
            return false;
        }
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,];
        try {
            $this->dbConnection = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            $GLOBALS['messages']['errors'][] = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the database connection object.
     *
     * @return PDO The PDO database connection object
     */
    public function getConnection()
    {
        return $this->dbConnection;
    }

    /**
     * Returns the count of rows in a table
     *
     * @param string $table The name of the table to count rows for
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     * @param string|null $query An optional WHERE clause to use in the query
     *
     * @return int|false Returns the count of rows or false on error
     */
    public function viewCount($table, $filter_params = null, $query = null)
    {
        try {
            $stmt = $this->dbConnection->prepare("SELECT * FROM $table $query");
            if ($filter_params) {
                foreach ($filter_params as $key => $data) {
                    $key++;
                    $stmt->bindParam($key, $data['value'], $data['type']);
                }
            }
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $GLOBALS['messages']['errors'][] = $e->getMessage();
            return false;
        }
    }

    /**
     * Executes a query
     *
     * @param string $query The query to execute
     * @param array $params An optional array of parameters to bind to the query
     *
     * @return array|false Returns an array of rows on success or false on error
     */
    public function query($query, $params = array())
    {

        // Convert a single value to an array
        if (!is_array($params)) {
            $params = array($params);
        }

        $filterParams = array();

        foreach ($params as $value) {
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

        try {
            $statement = $this->dbConnection->prepare($query);
            if ($filterParams) {
                foreach ($filterParams as $key => $data) {
                    $key++;
                    $statement->bindParam($key, $data['value'], $data['type']);
                }
            }
            $statement->execute();


            //if the first keyword in the query is select, then run this.
            if (explode(' ', $query)[0] == 'SELECT' && explode(' ', $query)[1] != 'count(*)') {

                $data = $statement->fetchAll();
                return $data;
            }

            //if the second keyword in the query is select, then run this.
            if (explode(' ', $query)[0] == 'SELECT' && explode(' ', $query)[1] == 'count(*)') {
                $data = $statement->fetch();
                $data = $data['total'];
                return $data;
            }

        } catch (Exception $e) {
            // Log the executed query and parameters (for debugging)
            throwWarning('Executed query: ' . $query);
            throwWarning('Parameters: ' . print_r($params, true));
            $GLOBALS['messages']['errors'][] = '<b>Error: </b>' . $e->getMessage();
            return false;
        } catch (\PDOException $e) {
            // Log the executed query and parameters (for debugging)
            throwWarning('Executed query: ' . $query);
            throwWarning('Parameters: ' . print_r($params, true));
            if ($e->getCode() === '23000') {
                $GLOBALS['messages']['errors'][] = '<b>UNIQUE CONSTRAINT: </b>' . $e->getMessage();
            } else {
                $GLOBALS['messages']['errors'][] = '<b>INTERNAL ERROR: </b>' . $e->getMessage();
            }
            return false;
        } catch (PDOException $e) {
            // Log the executed query and parameters (for debugging)
            throwWarning('Executed query: ' . $query);
            throwWarning('Parameters: ' . print_r($params, true));
            if ($e->getCode() === '23000') {
                $GLOBALS['messages']['errors'][] = '<b>UNIQUE CONSTRAINT: </b>' . $e->getMessage();
            } else {
                $GLOBALS['messages']['errors'][] = '<b>INTERNAL ERROR: </b>' . $e->getMessage();
            }
            return false;
        }
    }


    /**
     * Retrieves rows from a specified table.
     *
     * @param string $table The name of the table to retrieve data from
     * @param string $select The columns to select (default is '*')
     * @param string|null $query An optional WHERE clause to use in the query
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return array|false An array of database rows that match the query parameters or false on error
     */
    public function viewData($table, $select = '*', $query = null, $filter_params = null)
    {
        try {
            $stmt = $this->dbConnection->prepare("SELECT $select FROM $table $query");
            if ($filter_params) {
                foreach ($filter_params as $key => $data) {
                    $key++;
                    $stmt->bindParam($key, $data['value'], $data['type']);
                }
            }
            $stmt->execute();
            return array("count" => $stmt->rowCount(), "results" => $stmt->fetchAll());
        } catch (Exception $e) {
            echo $e->getMessage();

            $GLOBALS['messages']['errors'][] = '<b>Error: </b>' . $e->getMessage();
            return false;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $GLOBALS['messages']['errors'][] = '<b>UNIQUE CONSTRAINT: </b>' . $e->getMessage();
            } else {
                $GLOBALS['messages']['errors'][] = '<b>INTERNAL ERROR: </b>' . $e->getMessage();
            }
            return false;
        }
    }

    /**
     * Retrieves a single row from a specified table.
     *
     * @param string $table The name of the table to retrieve data from
     * @param string $select The columns to select (default is '*')
     * @param string|null $query An optional WHERE clause to use in the query
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return array|false An array with a single database row that matches the query parameters or false on error
     */
    public function viewSingleData($table, $select = '*', $where = null, $filter_params = null)
    {
        return $this->runQuery("single", 'SELECT ' . $select . ' FROM ' . $table . ' ' . $where . ' LIMIT 1', $filter_params);
    }

    /**
     * Inserts a new row into a specified table with the given column names and values.
     *
     * @param string $table The name of the table to insert data into
     * @param string $rows The columns to insert data into
     * @param string $values The values to insert
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return array|false An array with the ID of the last inserted row or false on error
     */
    public function insertData($table, $rows, $values, $filter_params = null)
    {
        return $this->runQuery("insert", 'INSERT INTO ' . $table . ' (' . $rows . ') VALUES (' . $values . ')', $filter_params);
    }

    /**
     * Inserts a new row into a specified table with the given column names and values,
     * or updates an existing row with the specified values if a duplicate key is found.
     *
     * @param string $table The name of the table to insert data into
     * @param string $rows The columns to insert data into
     * @param string $values The values to insert
     * @param string $update_values The values to update in case of a duplicate key
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return array|false An array with the ID of the last inserted row or an array with the ID of the row that was updated, or false on error
     */
    public function insertDataUnique($table, $rows, $values, $update_values, $filter_params = null)
    {
        return $this->runQuery("insert", 'INSERT INTO ' . $table . ' (' . $rows . ') VALUES (' . $values . ') ON DUPLICATE KEY UPDATE ' . $update_values, $filter_params);
        /*INSERT INTO t1 (a,b,c) VALUES (1,2,3),(4,5,6)  ON DUPLICATE KEY UPDATE c=VALUES(a)+VALUES(b);*/
    }

    /**
     * Updates data in a specified table with the given values, filtered as specified.
     *
     * @param string $table The name of the table to update
     * @param string $setClause The SET clause specifying the columns and values to update
     * @param string|null $whereClause An optional WHERE clause to specify the rows to update
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return true|false True if the data was updated successfully, false otherwise
     */
    public function updateData($table, $setClause, $whereClause = null, $filter_params = null)
    {
        $whereClause = $whereClause !== null ? ' WHERE ' . $whereClause : null;
        $query = 'UPDATE ' . $table . ' SET ' . $setClause . $whereClause;
        return $this->runQuery("update", $query, $filter_params);
    }

    /**
     * Deletes data from a specified table, filtered as specified.
     *
     * @param string $table The name of the table to delete data from
     * @param string $rows The rows to delete (e.g., 'WHERE id = :id')
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return true|false True if the data was deleted successfully, false otherwise
     */
    public function deleteData($table, $rows, $filter_params = null)
    {
        return $this->runQuery("delete", 'DELETE FROM ' . $table . ' ' . $rows, $filter_params);
    }

    /**
     * Executes a specified SQL query and returns the result.
     *
     * @param string $type The type of query (insert, update, single, delete)
     * @param string $query The SQL query to execute
     * @param array|null $filter_params An optional array of filter parameters to use in the query
     *
     * @return array|true|false An array with the ID of the last inserted row, or an array with the ID of the row that was updated, or an array with a single database row that matches the query parameters, or true/false depending on the query type
     */
    public function runQuery($type, $query, $filter_params)
    {
        try {
            $stmt = $this->dbConnection->prepare($query);
            if ($filter_params) {
                foreach ($filter_params as $key => $data) {
                    $key++;
                    $stmt->bindParam($key, $data['value'], $data['type']);
                }
            }
            $stmt->execute();
            switch ($type) {
                case "single":
                    return array("count" => $stmt->rowCount(), "result" => $stmt->fetch());
                    break;
                case "insert": //insert
                    return array("insertID" => $this->dbConnection->lastInsertId());
                    break;
                case "update": //insert
                    return array("insertID" => $this->dbConnection->lastInsertId());
                    return true;
                    break;
                case "delete": //insert
                    return array("insertID" => $this->dbConnection->lastInsertId());
                    return true;
                    break;
                default:
                    throw new Exception('No query type was specified.');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $GLOBALS['messages']['errors'][] = '<b>Error: </b>' . $e->getMessage();
            return false;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $GLOBALS['messages']['errors'][] = '<b>UNIQUE CONSTRAINT: </b>' . $e->getMessage();
            } else {
                $GLOBALS['messages']['errors'][] = '<b>INTERNAL ERROR: </b>' . $e->getMessage();
            }
            return false;
        }
    }
}