        <!-- Metrics Section -->
        <section>
            <!-- Total Users -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold">Total Users</h2>
                <p><?= $metrics['totalUsers'] ?></p>
            </div>

            <!-- Total Posts -->
            <div class="bg-white p-4 rounded shadow mt-4">
                <h2 class="text-lg font-semibold">Total Posts</h2>
                <p><?= $metrics['totalPosts'] ?></p>
            </div>

            <!-- Total Archived Posts -->
            <div class="bg-white p-4 rounded shadow mt-4">
                <h2 class="text-lg font-semibold">Total Archived Posts</h2>
                <p><?= $metrics['totalArchivedPosts'] ?></p>
            </div>

            <!-- Total Active Posts -->
            <div class="bg-white p-4 rounded shadow mt-4">
                <h2 class="text-lg font-semibold">Total Active Posts</h2>
                <p><?= $metrics['totalActivePosts'] ?></p>
            </div>

            <!-- Post Creations Per Month Chart -->
            <div class="bg-white p-6 rounded shadow mt-4">
                <h2 class="text-lg font-semibold mb-4">Post Creations Per Month</h2>
                <div id="postCreationsChart"></div>
            </div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

            <script>
                var postCreationsOptions = {
                    series: [{
                        name: 'Post Creations',
                        data: <?php echo json_encode(array_column($metrics['postCreations'], 'post_count'));  ?>
                    }],
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    xaxis: {
                        categories: <?php 
                        // Extract month-year and echo as a JSON array
                        $months = array_map(function($item) {
                            return $item['post_month'] . '-' . $item['post_year'];
                        }, $metrics['postCreations']);
                        echo json_encode($months);
                     ?>
                    }
                };

                var postCreationsChart = new ApexCharts(document.querySelector("#postCreationsChart"), postCreationsOptions);
                postCreationsChart.render();
            </script>

            <!-- More sections can be added below -->
        </section>


<!-- Total Users and Posts -->
<div class="grid grid-cols-2 gap-4 mt-4">
    <!-- Total Users -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold">Total Users</h2>
        <p><?= $metrics['totalUsers'] ?></p>
    </div>

    <!-- Total Posts -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold">Total Posts</h2>
        <p><?= $metrics['totalPosts'] ?></p>
    </div>
</div>
<!-- Total Archived Posts vs. Total Active Posts Chart -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Total Archived Posts vs. Total Active Posts</h2>
    <div id="archivedActivePieChart"></div>
</div>

<script>
    var archivedActiveOptions = {
        series: [<?= $metrics['totalArchivedPosts'] ?>, <?= $metrics['totalActivePosts'] ?>],
        labels: ['Archived Posts', 'Active Posts'],
        chart: {
            type: 'donut',
            height: 350,
        },
    };

    var archivedActiveChart = new ApexCharts(document.querySelector("#archivedActivePieChart"), archivedActiveOptions);
    archivedActiveChart.render();
</script>


<!-- Most Active Users Chart -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Most Active Users</h2>
    <div id="mostActiveUsersChart"></div>
</div>

<script>
    var mostActiveUsersOptions = {
        series: [{
            name: 'Post Count',
            data: [
                <?= $metrics['mostActiveUsers']['results'][0]['post_count'] ?>,
                <?= $metrics['mostActiveUsers']['results'][1]['post_count'] ?>,
                <?= $metrics['mostActiveUsers']['results'][2]['post_count'] ?>,
                <?= $metrics['mostActiveUsers']['results'][3]['post_count'] ?>,
                <?= $metrics['mostActiveUsers']['results'][4]['post_count'] ?>
            ],
        }],
        chart: {
            type: 'bar',
            height: 350,
        },
        xaxis: {
            categories: [
                'User <?= $metrics['mostActiveUsers']['results'][0]['user_id'] ?>',
                'User <?= $metrics['mostActiveUsers']['results'][1]['user_id'] ?>',
                'User <?= $metrics['mostActiveUsers']['results'][2]['user_id'] ?>',
                'User <?= $metrics['mostActiveUsers']['results'][3]['user_id'] ?>',
                'User <?= $metrics['mostActiveUsers']['results'][4]['user_id'] ?>'
            ],
        },
    };

    var mostActiveUsersChart = new ApexCharts(document.querySelector("#mostActiveUsersChart"), mostActiveUsersOptions);
    mostActiveUsersChart.render();
</script>
<!-- Posts Per Category Chart -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Posts Per Category</h2>
    <div id="postsPerCategoryChart"></div>
</div>

<script>
    var postsPerCategoryOptions = {
        series: [{
            name: 'Post Count',
            data: [
                <?= $metrics['postsPerCategory']['results'][0]['post_count'] ?>,
                <?= $metrics['postsPerCategory']['results'][1]['post_count'] ?>,
                <?= $metrics['postsPerCategory']['results'][2]['post_count'] ?>,
                <?= $metrics['postsPerCategory']['results'][3]['post_count'] ?>,
                <?= $metrics['postsPerCategory']['results'][4]['post_count'] ?>
            ],
        }],
        chart: {
            type: 'bar',
            height: 350,
        },
        xaxis: {
            categories: [
                '<?= getCategoryNameById($metrics['postsPerCategory']['results'][0]['to_whom']) ?>',
                '<?= getCategoryNameById($metrics['postsPerCategory']['results'][1]['to_whom']) ?>',
                '<?= getCategoryNameById($metrics['postsPerCategory']['results'][2]['to_whom']) ?>',
                '<?= getCategoryNameById($metrics['postsPerCategory']['results'][3]['to_whom']) ?>',
                '<?= getCategoryNameById($metrics['postsPerCategory']['results'][4]['to_whom']) ?>',
            ],
        },
    };

    var postsPerCategoryChart = new ApexCharts(document.querySelector("#postsPerCategoryChart"), postsPerCategoryOptions);
    postsPerCategoryChart.render();
</script>


<!-- Engagement Rate -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Engagement Rate</h2>
    <p><?= number_format($metrics['engagementRate'], 2) ?>%</p>
</div>
<!-- Popular Public Posts -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Popular Public Posts</h2>
    <ul>
        <?php foreach ($metrics['popularPublicPosts'] as $post): ?>
            <li>
                <strong>User <?= $post['user_id'] ?>:</strong> <?= $post['body'] ?> (Likes: <?= $post['likes'] ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<!-- Average Posts Per User -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Average Posts Per User</h2>
    <p><?= number_format($metrics['averagePostsPerUser'], 2) ?></p>
</div>
<!-- Total Likes -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold mb-4">Total Likes</h2>
    <p><?= $metrics['totalLikes'] ?></p>
</div>


<!-- Total Flagged Posts -->
<div class="bg-white p-4 rounded shadow mt-4">
    <h2 class="text-lg font-semibold">Total Flagged Posts</h2>
    <p><?= $metrics['totalFlaggedPosts'] ?></p>
</div>



