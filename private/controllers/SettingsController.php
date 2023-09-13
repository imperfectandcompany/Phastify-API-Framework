<?php
include($GLOBALS['config']['private_folder'].'/classes/class.settings.php');

class SettingsController {
        
    protected $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
    
    public function adjustAvatar() {
        $settings = new Settings($this->dbConnection);
            try
            {
                echo"avatar";
                print_r($_FILES);
                $target_file = basename($_FILES["avatar"]["name"]);
                if($target_file){
                    $image_extensions_allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $ext = strtolower(end(explode('.', $_FILES['avatar']['name'])));
                    
                    if(!in_array($ext, $image_extensions_allowed))
                    {	
                        throw new Exception($GLOBALS['lang']['settings']['img_invalid']);
                    }
        
                    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                    switch( $_FILES['avatar']['error'] ) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            //throw new Exception($GLOBALS['lang']['settings']['img_too_big']);
                            sendResponse('error', ['message' => "Update Error message later!"], ERROR_FORBIDDEN);
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            throw new Exception($GLOBALS['lang']['settings']['img_imcomplete']);
                            //sendResponse('error', ['message' => "Update Error message later!"], ERROR_FORBIDDEN);
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            //throw new Exception($GLOBALS['lang']['settings']['img_empty']);
                            sendResponse('error', ['message' => "Update Error message later!"], ERROR_FORBIDDEN);
                            break;
                        default:
                            //throw new Exception($GLOBALS['lang']['settings']['img_error_generic']);
                            sendResponse('error', ['message' => "Update Error message later!"], ERROR_FORBIDDEN);
                            break;
                    }
                    
                    if ($_FILES['avatar']['size'] > 4000000) {
                       // throw new Exception($GLOBALS['lang']['settings']['img_too_big']);
                    sendResponse('error', ['message' => "Your avatar has been updated!"], ERROR_FORBIDDEN);
                    }
        
                    $array = explode('.', $_FILES['avatar']['name']);
                    $ext = strtolower(end($array));
                                
                    if(file_exists($GLOBALS['config']['avatar_folder'].$GLOBALS['user_id'].".".$ext)) {
                        unlink($GLOBALS['config']['avatar_folder'].$GLOBALS['user_id'].".".$ext); //remove the file
                    }
        
                    $_POST['avatar'] = $GLOBALS['user_id'].".".$ext;
                    
                    $settings->resizeImage($_FILES["avatar"]["tmp_name"], $GLOBALS['config']['avatar_max_size'], $GLOBALS['config']['avatar_max_size']);

                    if(!is_writable($GLOBALS['config']['avatar_folder'])){ throw new Exception("Writing to the avatar folder has been denied due to a permission error!"); }
                    
                    if(!move_uploaded_file($_FILES['avatar']['tmp_name'], $GLOBALS['config']['avatar_folder']."/".$GLOBALS['user_id'].".".$ext)){ throw new Exception("Our image didn't load?"); }
                    echo $GLOBALS['config']['avatar_folder'];
                }else{
                    $_POST['avatar'] = $settings->user['result']['avatar'];
                }
        
                if($_POST['avatar'])
                {                        
                    $filter_params = array();
                    $filter_params[] = array("value" => $_POST['avatar'], "type" => PDO::PARAM_STR);
                    $filter_params[] = array("value" => $GLOBALS['user_id'], "type" => PDO::PARAM_INT);
                    $settings->updateAvatar($filter_params);
                    sendResponse('success', ['message' => "Your avatar has been updated!"], SUCCESS_OK);
                }

            } catch (Exception $e) {
                /*$GLOBALS['messages']['errors']['avatar'][] = $e->getMessage();*/ throw $e;
            }
        
    }

}
?>