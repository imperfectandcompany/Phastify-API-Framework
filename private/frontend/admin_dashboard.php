<script src="https://flowbite-admin-dashboard.vercel.app/app.css"></script>
<link rel="stylesheet" href="https://flowbite-admin-dashboard.vercel.app//app.css">

<body class="bg-gray-50 ">
    <?php
    include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/admin_navbar.php');
    ?>
    <div class="flex pt-16 overflow-hidden bg-gray-50 dark:bg-gray-900">
        <?php
        include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/admin_sidebar.php');
        ?>
        <div class="relative w-full h-full overflow-y-auto bg-gray-50 lg:ml-64 ">
            <main>
                <div class="px-4 pt-6">
                    <div class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
                        <?php
                        include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/pages/admin_dashboard.php');
                        ?>
                    </div>
                </div>
            </main>

            <h1 class="text-3xl mb-4">Admin Dashboard</h1>

            <form method="get" class="mb-8">
                <div class="flex items-center">
                    <input type="text" name="query" id="query" placeholder="Search..."
                        class="w-full p-2 border border-gray-300 rounded-l focus:outline-none"
                        value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" />
                    <select name="category" id="category"
                        class="py-2 px-4 border border-gray-300 rounded-r focus:outline-none">
                        <option value="all" <?php echo isset($_GET['category']) && $_GET['category'] === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="post" <?php echo isset($_GET['category']) && $_GET['category'] === 'post' ? 'selected' : ''; ?>>Posts</option>
                        <option value="comment" <?php echo isset($_GET['category']) && $_GET['category'] === 'comment' ? 'selected' : ''; ?>>Comments</option>
                        <option value="user" <?php echo isset($_GET['category']) && $_GET['category'] === 'user' ? 'selected' : ''; ?>>Users</option>
                        <option value="integration" <?php echo isset($_GET['category']) && $_GET['category'] === 'integration' ? 'selected' : ''; ?>>Integrations</option>
                        <option value="reply" <?php echo isset($_GET['category']) && $_GET['category'] === 'reply' ? 'selected' : ''; ?>>Replies</option>
                    </select>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Search</button>
                </div>
            </form>


            <!-- Search Results Section -->
            <div class="mt-8">
                <?php
                // Check if the form has been submitted
                if (isset($_GET['query'])) {
                    include_once($GLOBALS['config']['private_folder'] . '/classes/class.search.php');
                    $search = new Search($this->prodDbConnection, $prodLogger);

                    try {
                        // Perform the search and get the results
                        $searchResults = $search->performSearch($_GET['query'], $_GET['category']);

                        // Display search results
                        if (is_array($searchResults)) {
                            echo '<h2 class="text-xl mb-4">Search Results</h2>';
                            echo '<ul>';
                            foreach ($searchResults as $result) {
                                // Display 'Type'
                                echo '<li>Type: ' . $result['type'] . ', ';

                                // Switch content based on type
                                switch ($result['type']) {
                                    case 'post':
                                        echo 'Post content: ' . ($result['content'] ?? '');
                                        break;
                                    case 'comment':
                                        echo 'Comment content: ' . ($result['content'] ?? '');
                                        break;
                                    case 'user':
                                        echo 'Username: ' . ($result['content'] ?? '');
                                        break;
                                    case 'integration':
                                        echo 'Service Name: ' . ($result['content'] ?? '');
                                        break;
                                    case 'reply':
                                        echo 'Reply content: ' . ($result['content'] ?? '');
                                        break;
                                    default:
                                        echo 'Unknown Type';
                                }

                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>No results found.</p>';
                        }
                    } catch (PDOException $e) {
                        // Handle the database query error (e.g., log or show an error message)
                        echo 'Database Error: ' . $e->getMessage();
                    }
                }
                ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Cards for Single statistics -->
                <?php
                $singleStats = ['totalUsers', 'totalPosts', 'totalArchivedPosts', 'totalActivePosts', 'totalFlaggedPosts', 'averagePostsPerUser', 'totalLikes', 'engagementRate'];
                foreach ($singleStats as $stat): ?>
                    <div class="bg-white p-6 rounded shadow">
                        <h2 class="text-xl mb-4">
                            <?= ucwords(str_replace('_', ' ', $stat)); ?>
                        </h2>
                        <p class="text-2xl">
                            <?= $metrics[$stat] ?? 0; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tables/Lists for Detailed Metrics -->
            <h2 class="text-2xl mt-10 mb-4">Detailed Metrics</h2>

            <!-- Most Active Users -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Most Active Users</h3>
                <ul>
                    <?php foreach ($metrics['mostActiveUsers']['results'] as $user): ?>
                        <li>User ID:
                            <?= $user['user_id']; ?>, Posts:
                            <?= $user['post_count']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Latest Posts -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Latest Posts</h3>
                <ul>
                    <?php foreach ($metrics['latestPosts']['results'] as $post): ?>
                        <li>Post ID:
                            <?= $post['id']; ?>, Post:
                            <?= $post['body']; ?>, Date:
                            <?= date('Y-m-d H:i:s', $post['posted_on']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Posts Per Category -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Posts Per Category</h3>
                <ul>
                    <?php foreach ($metrics['postsPerCategory']['results'] as $postCat): ?>
                        <li>
                            Category ID:
                            <?= getCategoryNameById($postCat['to_whom']); ?>, Posts:
                            <?= $postCat['post_count']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- User Registrations -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">User Registrations</h3>
                <ul>
                    <?php
                    // Sort User Registrations data by year and month
                    if (isset($metrics['userRegistrations']['results'])) {
                        usort($metrics['userRegistrations']['results'], function ($a, $b) {
                            // Compare years first
                            $yearComparison = $a['reg_year'] <=> $b['reg_year'];

                            if ($yearComparison === 0) {
                                // If years are the same, compare months
                                return $a['reg_month'] <=> $b['reg_month'];
                            }

                            return $yearComparison;
                        });
                    }
                    foreach ($metrics['userRegistrations']['results'] as $reg): ?>
                        <?php if (!empty($reg['reg_month']) && !empty($reg['reg_year'])): ?>
                            <li>Month:
                                <?= $reg['reg_month']; ?>, Year:
                                <?= $reg['reg_year']; ?>, Users Registered:
                                <?= $reg['user_count']; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Popular Public Posts -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h2 class="text-xl mb-4">Popular Public Posts</h2>
                <?php if (isset($metrics['popularPublicPosts'])): ?>
                    <ul>
                        <?php foreach ($metrics['popularPublicPosts'] as $post): ?>
                            <li>
                                Post ID:
                                <?= $post['id']; ?>, Likes:
                                <?= $post['likes']; ?>, Comments:
                                <?= $post['comments']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No data available for popular public posts.</p>
                <?php endif; ?>
            </div>

            <!-- Post Creations -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Post Creations</h3>
                <?php if (isset($metrics['postCreations'])): ?>
                    <ul>
                        <?php foreach ($metrics['postCreations'] as $creation): ?>
                            <li>Month:
                                <?= $creation['post_month']; ?>, Year:
                                <?= $creation['post_year']; ?>, Posts Created:
                                <?= $creation['post_count']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No data available for post creations.</p>
                <?php endif; ?>
            </div>

            <!-- Users With Most Archived Posts -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Users With Most Archived Posts</h3>
                <ul>
                    <?php foreach ($metrics['usersWithMostArchived']['results'] as $archive): ?>
                        <li>User ID:
                            <?= $archive['user_id']; ?>, Archived Posts:
                            <?= $archive['post_count']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Most Liked Posts -->
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl mb-4">Most Liked Posts</h3>
                <ul>
                    <?php foreach ($metrics['mostLikedPosts']['results'] as $likedPost): ?>
                        <li>Post ID:
                            <?= $likedPost['id']; ?>, Post:
                            <?= $likedPost['body']; ?>, Likes:
                            <?= $likedPost['likes']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="container mx-auto p-8">
                <h1 class="text-3xl font-semibold mb-4">Service Metrics</h1>

                <!-- Service Metrics Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700">
                                <th class="py-2 px-4">Metric</th>
                                <th class="py-2 px-4">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviceMetrics as $metricName => $metricValue): ?>
                                <tr class="border-t border-gray-300">
                                    <td class="py-2 px-4 font-medium">
                                        <?= $metricName ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <?php
                                        if ($metricName === 'Metrics for All Services') {
                                            // Handle displaying metrics for all services
                                            foreach ($metricValue as $serviceId => $serviceMetrics) {
                                                echo '<strong>' . $serviceMetrics['service_name'] . '</strong><br>';
                                                echo 'Total Integrations: ' . $serviceMetrics['total_integrations'] . '<br>';
                                                echo 'Active Integrations: ' . $serviceMetrics['active_integrations'] . '<br>';
                                                echo 'Disabled Integrations: ' . $serviceMetrics['disabled_integrations'] . '<br>';
                                                // Handle visibility settings here if needed
                                                echo '<br>';
                                            }
                                        } else {
                                            // Display other metrics as usual
                                            echo $metricValue;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://flowbite-admin-dashboard.vercel.app//app.bundle.js"></script>
</body>

<?php die(); ?>