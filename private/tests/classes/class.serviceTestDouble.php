<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.service.php');

class ServiceTestDouble extends Service
{
    protected static $inputStream;

    public function __construct($dbConnection)
    {
        parent::__construct($dbConnection);
    }

    public static function setInputStream($input = 'php://input')
    {
        static::$inputStream = $input;
    }

    protected static function getInputStream()
    {
        return static::$inputStream;
    }
}
?>