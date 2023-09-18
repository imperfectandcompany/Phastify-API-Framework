<?php

class Settings {
    
    private $dbObject;

    public function __construct($dbObject)
    {
        $this->dbObject = $dbObject;
        $filter_params = array();
        $filter_params[] = array("value" => $GLOBALS['user_id'], "type" => PDO::PARAM_INT);
        $query = "WHERE id = ?";
        $this->user = $this->dbObject->viewSingleData($GLOBALS['db_conf']['db_db'].".users", "*", $query, $filter_params);
        //No error should be made here since this is through a protected endpoint / error should be given
        //We're also loading the settings for our logged in user here so we can offer them to the settings page
        //Note: We're loading in our user's password too, only so we can authenticate it if they want to change it. 
    }
    
    /**
     * Undocumented function
     *
     * @param [type] $file
     * @param [type] $width
     * @param [type] $height
     * @return void
     */
    public function resizeImage($file, $width, $height)
    {
        list($w, $h) = getimagesize($file);
        /* calculate new image size with ratio */
        $ratio = max($width/$w, $height/$h);
        $h = ceil($height / $ratio);
        $x = ($w - $width / $ratio) / 2;
        $w = ceil($width / $ratio);
        /* read binary data from image file */
        $imgString = file_get_contents($file);
        /* create image from string */
        $image = imagecreatefromstring($imgString);
        $tmp = imagecreatetruecolor($width, $height);
        imagecopyresampled($tmp, $image,
        0, 0,
        $x, 0,
        $width, $height,
        $w, $h);
        imagejpeg($tmp, $file, 100);
        return $file;
        /* cleanup memory */
        imagedestroy($image);
        imagedestroy($tmp);
    }
    
    public function updateAvatar($filter_params)
    {   
        //include avatar_ts = UNIX_TIMESTAMP() in the future
        return $this->dbObject->updateData("users", "avatar = ?", "id = ?", $filter_params);
    }
}
