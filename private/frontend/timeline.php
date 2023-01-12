<div class="container mx-auto">

    <h1>This is our TL object</h1>

    <?php foreach($timeline->fetchPublicTimeline()['results'] as $key => $value): ?>

        <div class="post">
{
Profile Link: <?php echo $GLOBALS['config']['url']; ?>/profile/<?php echo $value['user_id']; ?>,
Post: <?php echo nl2br(htmlspecialchars($value['body'], ENT_QUOTES)); ?>
}

    <?php endforeach; ?>

  </div>
