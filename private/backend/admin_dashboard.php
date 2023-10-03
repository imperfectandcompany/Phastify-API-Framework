<?php
include_once($GLOBALS['config']['private_folder'] . '/classes/class.dashboardMetrics.php');

$dashboardMetrics = new DashboardMetrics($this->prodDbConnection);

if (isset($_POST['confirm'])) {
    if (isset($_COOKIE['POSTOGONADMINID'])) {
        $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token', $_SERVER);
        try {
            if ($this->prodDbConnection->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['POSTOGONADMINID'])))) {
                $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_success', $_SERVER);
                //expire cookie
                setcookie('POSTOGONADMINID', '1', time() - 3600);
                //expire cookie
                setcookie('POSTOGONADMINID_', '1', time() - 3600);
            } else {
                $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_failure', $_SERVER);
            }
        } catch (PDOException $e) {
            $prodLogger->log($uidIsLoggedInAuthorized, 'admin_logout_deleting_token_failure', $_SERVER);
        }
    }
}

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