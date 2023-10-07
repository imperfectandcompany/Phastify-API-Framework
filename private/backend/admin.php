<?php
$avatar = $this->dbConnection->viewSingleData("users", "avatar", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['avatar'] ?? $GLOBALS['config']['default_avatar'];
$avatarUrl = $GLOBALS['config']['avatar_url']."/".$avatar;
$username = $this->dbConnection->viewSingleData("users", "username", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['username'] ?? "No username found";
$email = $this->dbConnection->viewSingleData("users", "email", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['email'] ?? "No email found";


$page_include = strtolower(isset($page) ? "admin_" . $page : "admin_dashboard");
include_once($GLOBALS['config']['private_folder'] . '/backend/admin/pages/' . $page_include . '.php');
// handle logout
if (isset($_GET['confirm'])) {
    if (isset($_COOKIE['POSTOGONADMINID'])) {
        $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token', $_SERVER);
        try {
            $params = makeFilterParams(['token' => sha1($_COOKIE['POSTOGONADMINID'])]);
            $result = $prodDbConnection->deleteData('login_tokens', 'WHERE token = ?', $params);
            if ($result) {
                $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_success', $_SERVER);
                setcookie("POSTOGONADMINID", '1', time() - 3600, '/', 'admin.postogon.com', TRUE, TRUE);
                setcookie("POSTOGONADMINID_", '1', time() - 3600, '/', 'admin.postogon.com', TRUE, TRUE);
                if((!isset($_COOKIE['POSTOGONADMINID']) && !isset($_COOKIE['POSTOGONADMINID_'])) || ($_COOKIE['POSTOGONADMINID'] == '1' && $_COOKIE['POSTOGONADMINID_'] == '1')){
                    $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_success_cookie', "Successfully logged out admin panel");
                    header('Refresh: 0;');
                    return;
                }
                header('Refresh: 0;');
            } else {
                $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_failure', $_SERVER);
            }
        } catch (PDOException $e) {
            $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_failure_internal', $e->getMessage());
        }
    }
}
?>
