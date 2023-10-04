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