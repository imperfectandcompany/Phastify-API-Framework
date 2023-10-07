<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

$dashboardMetrics = new DashboardMetrics($this->prodDbConnection);



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