<?php
class DashboardMetrics
{
    private $dbConnection;

    public function __construct(DatabaseConnector $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getTotalUsers()
    {
        return $this->dbConnection->viewCount('users');
    }

    public function getTotalPosts()
    {
        return $this->dbConnection->viewCount('posts');
    }

    public function getTotalArchivedPosts()
    {
        return $this->dbConnection->viewCount('posts', null, 'WHERE to_whom = 3 OR to_whom = 4');
    }

    public function getTotalActivePosts()
    {
        return $this->getTotalPosts() - $this->getTotalArchivedPosts();
    }

    public function getMostActiveUsers()
    {
        return $this->dbConnection->viewData('posts', 'user_id, COUNT(*) as post_count', 'GROUP BY user_id ORDER BY post_count DESC LIMIT 5');
    }

    public function getLatestPosts()
    {
        return $this->dbConnection->viewData('posts', '*', 'ORDER BY posted_on DESC LIMIT 5');
    }

    public function getPostsPerCategory()
    {
        return $this->dbConnection->viewData('posts', 'to_whom, COUNT(*) as post_count', 'GROUP BY to_whom');
    }

    public function getTotalFlaggedPosts()
    {
        return $this->dbConnection->viewCount('posts', null, 'WHERE flagged_content IS NOT NULL');
    }

    public function getUserRegistrations()
    {
        return $this->dbConnection->viewData('users', 'MONTH(createdAt) as reg_month, YEAR(createdAt) as reg_year, COUNT(*) as user_count', 'GROUP BY MONTH(createdAt), YEAR(createdAt)');
    }

    public function getPostCreations()
    {
        $query = 'SELECT MONTH(FROM_UNIXTIME(posted_on)) as post_month, YEAR(FROM_UNIXTIME(posted_on)) as post_year, COUNT(*) as post_count FROM posts GROUP BY MONTH(FROM_UNIXTIME(posted_on)), YEAR(FROM_UNIXTIME(posted_on))';
        $data = $this->dbConnection->query($query);
        return $data;
    }

    public function getUsersWithMostArchived()
    {
        return $this->dbConnection->viewData('posts', 'user_id, COUNT(*) as post_count', 'WHERE to_whom = 3 OR to_whom = 4 GROUP BY user_id ORDER BY post_count DESC LIMIT 5');
    }

    public function getAveragePostsPerUser()
    {
        return $this->getTotalPosts() / $this->getTotalUsers();
    }

    public function getTotalLikes()
    {
        // Using the generic query method for this
        $data = $this->dbConnection->query("SELECT SUM(likes) as totalLikes FROM posts");
        return $data[0]['totalLikes'] ?? 0;
    }

    public function getMostLikedPosts()
    {
        return $this->dbConnection->viewData('posts', '*', 'ORDER BY likes DESC LIMIT 5');
    }

    public function getTotalComments()
    {
        return $this->dbConnection->viewCount('comments');
    }


    public function getTotalReplies()
    {
        return $this->dbConnection->viewCount('replies');
    }

    public function getEngagementRate()
    {
        $totalLikes = $this->getTotalLikes();
        $totalComments = $this->getTotalComments();
        $totalReplies = $this->getTotalReplies();
        $totalPosts = $this->getTotalPosts();

        if ($totalPosts > 0) {
            $engagementRate = (($totalLikes + $totalComments + $totalReplies) / $totalPosts) * 100;
        } else {
            $engagementRate = 0;
        }

        return $engagementRate;
    }

    public function getPopularPublicPosts($orderBy = 'likes', $limit = 5)
    {
        // Count the number of comments for each post
        $commentsQuery = "SELECT post_id, COUNT(*) as comments_count FROM comments GROUP BY post_id";

        // Count the number of replies for each comment
        $repliesQuery = "SELECT c.post_id, COUNT(*) as replies_count
                         FROM comments c
                         JOIN replies r ON c.id = r.comment_id
                         GROUP BY c.post_id";

        // Join the posts table with the comments and replies counts
        $query = "SELECT p.*, COALESCE(c.comments_count, 0) as comments, COALESCE(r.replies_count, 0) as replies
                  FROM posts p
                  LEFT JOIN ($commentsQuery) c ON p.id = c.post_id
                  LEFT JOIN ($repliesQuery) r ON p.id = r.post_id
                  WHERE to_whom = 1
                  ORDER BY $orderBy DESC
                  LIMIT $limit";

        return $this->dbConnection->query($query);
    }

    public function getTotalIntegrations()
    {
        return $this->dbConnection->viewCount('integrations');
    }

    public function getTotalActiveIntegrations()
    {
        return $this->dbConnection->viewCount('integrations', null, 'WHERE status = "active"');
    }

    public function getTotalInactiveIntegrations()
    {
        return $this->dbConnection->viewCount('integrations', null, 'WHERE status = "inactive"');
    }


    public function getTotalAdminUsers()
    {
        return $this->dbConnection->viewCount('user_roles', null, 'WHERE role_id = 1');
    }

    private function getUsersWithPermission($permissionId)
    {
        return $this->dbConnection->viewCount('user_permissions', null, 'WHERE permission_id = ' . $permissionId);
    }

    public function getTotalServices()
    {
        return $this->dbConnection->viewCount('services');
    }

    public function getAllServices()
    {
        return $this->dbConnection->viewData('services', '*');
    }

    public function getActiveServices()
    {
        return $this->dbConnection->viewData('services', '*', 'WHERE available = 1');
    }

    public function getInactiveServices()
    {
        return $this->dbConnection->viewData('services', '*', 'WHERE available = 0');
    }

    public function getServicePopularity()
    {
        // Collect metrics for service popularity based on active integrations
        $activeServicesCount = $this->getActiveServicesCount();
        $totalIntegrationsCount = $this->getTotalIntegrationsCount();

        // Calculate popularity based on the ratio of active integrations to total integrations
        $servicePopularity = ($totalIntegrationsCount > 0) ? ($activeServicesCount / $totalIntegrationsCount) : 0;

        return $servicePopularity;
    }

    public function getMostPopularServices($limit = 5)
    {
        $query = "SELECT s.name, COUNT(i.id) as integration_count
                  FROM services s
                  LEFT JOIN integrations i ON s.id = i.service_id
                  WHERE i.status = 'Active'
                  GROUP BY s.id
                  ORDER BY integration_count DESC
                  LIMIT $limit";
        return $this->dbConnection->query($query);
    }

    public function getActiveServicesCount()
    {
        // Count the number of active services
        $query = "SELECT COUNT(*) as active_services_count
                  FROM services
                  WHERE status = 'Active'";
        return $this->dbConnection->query($query)[0]['active_services_count'] ?? 0;
    }

    public function getTotalIntegrationsCount()
    {
        // Count the total number of integrations
        $query = "SELECT COUNT(*) as total_integrations_count
                  FROM integrations";
        return $this->dbConnection->query($query)[0]['total_integrations_count'] ?? 0;
    }


    // Function to calculate total integrations for a service
    public function calculateTotalIntegrations($serviceId)
    {
        $query = "SELECT COUNT(*) as total_integrations FROM integrations WHERE service_id = :serviceId";
        $params = array(':serviceId' => $serviceId);

        // Execute the query and fetch the result
        $result = $this->dbConnection->query($query, $params);
        // Return the total integrations count
        return $result[0]['total_integrations'];
    }

    // Function to calculate active integrations for a service
    public function calculateActiveIntegrations($serviceId)
    {
        // Query your database to count the active integrations for a specific service
        // Replace this with your database query logic
        $query = "SELECT COUNT(*) as active_integrations FROM integrations WHERE service_id = :serviceId AND status = 'Active'";
        $params = array(':serviceId' => $serviceId);

        // Execute the query and fetch the result
        $result = $this->dbConnection->query($query, $params);

        // Return the active integrations count
        return $result[0]['active_integrations'];
    }

    // Function to calculate disabled integrations for a service
    public function calculateDisabledIntegrations($serviceId)
    {
        // Query your database to count the disabled integrations for a specific service
        // Replace this with your database query logic
        $query = "SELECT COUNT(*) as disabled_integrations FROM integrations WHERE service_id = :serviceId AND status = 'Inactive'";
        $params = array(':serviceId' => $serviceId);

        // Execute the query and fetch the result
        $result = $this->dbConnection->query($query, $params);

        // Return the disabled integrations count
        return $result[0]['disabled_integrations'];
    }

    // Function to calculate visibility settings for a service
    public function calculateVisibilitySettings($serviceId)
    {
        // Query your database to retrieve the visibility settings for a specific service
        // Replace this with your database query logic
        $query = "SELECT 
                    SUM(CASE WHEN show_to_followers = 1 AND show_to_contacts = 1 THEN 1 ELSE 0 END) as show_to_both,
                    SUM(CASE WHEN show_to_followers = 1 AND show_to_contacts = 0 THEN 1 ELSE 0 END) as show_to_followers_only,
                    SUM(CASE WHEN show_to_followers = 0 AND show_to_contacts = 1 THEN 1 ELSE 0 END) as show_to_contacts_only,
                    SUM(CASE WHEN show_to_followers = 0 AND show_to_contacts = 0 THEN 1 ELSE 0 END) as show_to_none
                  FROM integrations WHERE service_id = :serviceId";
        $params = array(':serviceId' => $serviceId);

        // Execute the query and fetch the result
        $result = $this->dbConnection->query($query, $params);

        // Return an array of visibility settings counts
        return array(
            'show_to_both' => $result[0]['show_to_both'],
            'show_to_followers_only' => $result[0]['show_to_followers_only'],
            'show_to_contacts_only' => $result[0]['show_to_contacts_only'],
            'show_to_none' => $result[0]['show_to_none']
        );
    }

    // Function to calculate metrics for all available services
    public function calculateMetricsForAllServices()
    {
        // Assuming you have a function to fetch all services from the database
        $allServices = $this->getAllServices();
        $results = $allServices['results']; // Get the data rows



        $metrics = array();

        foreach ($results as $service) {
            // Check if the service is available (available column is 1)
            if ($service['available'] == 1) {
                $serviceId = $service['id'];

                $totalIntegrations = $this->calculateTotalIntegrations($serviceId);
                $activeIntegrations = $this->calculateActiveIntegrations($serviceId);
                $disabledIntegrations = $this->calculateDisabledIntegrations($serviceId);
                $visibilitySettings = $this->calculateVisibilitySettings($serviceId);

                // Create an array to store metrics for this service
                $serviceMetrics = array(
                    'service_name' => $service['name'],
                    'total_integrations' => $totalIntegrations,
                    'active_integrations' => $activeIntegrations,
                    'disabled_integrations' => $disabledIntegrations,
                    'visibility_settings' => $visibilitySettings,
                );

                // Add the service-specific metrics to the overall metrics array
                $metrics[$serviceId] = $serviceMetrics;
            }
        }

        return $metrics;
    }

}
?>