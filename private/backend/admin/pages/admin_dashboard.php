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