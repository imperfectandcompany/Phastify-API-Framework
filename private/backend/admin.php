<?php
$avatar = $this->dbConnection->viewSingleData("users", "avatar", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['avatar'] ?? $GLOBALS['config']['default_avatar'];
$avatarUrl = $GLOBALS['config']['avatar_url']."/".$avatar;
$username = $this->dbConnection->viewSingleData("users", "username", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['username'] ?? "No username found";
$email = $this->dbConnection->viewSingleData("users", "email", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['email'] ?? "No email found";


$page_include = strtolower(isset($page) ? "admin_" . $page : "admin_dashboard");
include_once($GLOBALS['config']['private_folder'] . '/backend/admin/pages/' . $page_include . '.php');
?>
