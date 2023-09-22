Type of status:

<?php if($f_header): ?>
    <?php echo htmlspecialchars($f_header, ENT_QUOTES); ?><br/>
<?php endif; ?>
<ul>
<?php foreach($feedback as $notice): ?>
    <?php if(isset($notice)): ?>
    <li>&emsp;<?php echo $notice; ?></li>
    <?php endif; ?>
<?php endforeach; ?>
</ul>

