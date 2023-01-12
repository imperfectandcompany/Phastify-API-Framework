<?php if(count($GLOBALS['errors']) > 0): ?>
        <?php foreach($GLOBALS['errors'] as $error): ?>
            <?php echo $error; ?>
        <?php endforeach; ?>
<?php endif; ?>
