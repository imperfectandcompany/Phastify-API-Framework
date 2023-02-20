<?php

?>
<hr/>
<h1>DEV MODE IS ON -- Do not use in production!</h1>

<h4>Errors</h4>
<?php print_r($GLOBALS['errors']); ?>

<h4>url_loc</h4>
<?php print_r($GLOBALS['url_loc']); ?>

<h4>token</h4>
<?php isset($GLOBALS['token']) && !empty($GLOBALS['token']) ? print_r($GLOBALS['token']) : "Not available";?>

<h4>user_id</h4>
<?php echo isset($GLOBALS['user_id']) ? $GLOBALS['user_id'] : "Not available";?>
<hr/>
