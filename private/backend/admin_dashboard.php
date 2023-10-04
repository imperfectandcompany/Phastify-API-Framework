<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

$dashboardMetrics = new DashboardMetrics($this->prodDbConnection);

if (isset($_POST['confirm'])) {
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

$avatar = $this->dbConnection->viewSingleData("users", "avatar", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['avatar'] ?? $GLOBALS['config']['default_avatar'];
$avatarUrl = $GLOBALS['config']['avatar_url']."/".$avatar;
$username = $this->dbConnection->viewSingleData("users", "username", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['username'] ?? "No username found";
$email = $this->dbConnection->viewSingleData("users", "email", "WHERE id = ?", array(array("value" => $uidIsLoggedInAuthorized, "type" => PDO::PARAM_INT)))['result']['email'] ?? "No email found";



$metrics = [
    'totalUsers' => $dashboardMetrics->getTotalUsers(),
    'totalPosts' => $dashboardMetrics->getTotalPosts(),
    'totalArchivedPosts' => $dashboardMetrics->getTotalArchivedPosts(),
    'totalActivePosts' => $dashboardMetrics->getTotalActivePosts(),
    'mostActiveUsers' => $dashboardMetrics->getMostActiveUsers(),
    'latestPosts' => $dashboardMetrics->getLatestPosts(),
    'postsPerCategory' => $dashboardMetrics->getPostsPerCategory(),
    'totalFlaggedPosts' => $dashboardMetrics->getTotalFlaggedPosts(),
    'userRegistrations' => $dashboardMetrics->getUserRegistrations(),
    'postCreations' => $dashboardMetrics->getPostCreations(),
    'engagementRate' => $dashboardMetrics->getEngagementRate(),
    'popularPublicPosts' => $dashboardMetrics->getPopularPublicPosts('likes', 5),
    'usersWithMostArchived' => $dashboardMetrics->getUsersWithMostArchived(),
    'averagePostsPerUser' => $dashboardMetrics->getAveragePostsPerUser(),
    'totalLikes' => $dashboardMetrics->getTotalLikes(),
    'mostLikedPosts' => $dashboardMetrics->getMostLikedPosts(),
];
// Consolidated functions for service metrics
$serviceMetrics = [
    'Total Services' => $dashboardMetrics->getTotalServices(),
    'All Services' => count($dashboardMetrics->getAllServices()["results"]),
    // Convert the array to count
    'Active Services' => count($dashboardMetrics->getActiveServices()["results"]),
    'Inactive Services' => count($dashboardMetrics->getInactiveServices()["results"]),
    'Service Popularity' => $dashboardMetrics->getServicePopularity(),
    'Most Popular Services' => (count($dashboardMetrics->getMostPopularServices(5)) > 0) ? $dashboardMetrics->getMostPopularServices(5) : 'No popular services found',
    'Active Services Count' => $dashboardMetrics->getActiveServicesCount(),
    'Total Integrations Count' => $dashboardMetrics->getTotalIntegrationsCount(),
    'Metrics for All Services' => $dashboardMetrics->calculateMetricsForAllServices()
];

function getCategoryNameById($categoryId)
{
    // Define a mapping array for 'to_whom' values to category IDs
    $categoryMap = [
        1 => 'public',
        2 => 'private',
        3 => 'public archived',
        4 => 'private archived',
        5 => 'soft delete (unauthorized)',
    ];

    // Check if the category ID exists in the mapping array
    if (isset($categoryMap[$categoryId])) {
        return $categoryMap[$categoryId];
    } else {
        // Return a default value or handle the case where the category ID is not found.
        return 'Category Not Found';
    }
}



?>