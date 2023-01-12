<div class="container mx-auto">

    <h1>This is our TL object</h1>


<?php
if(isset($endpointResponse)):
?>

<?php
$result = array();
foreach ($endpointResponse as $row) {
    $temp = array(
        "PostId" => $row["id"],
        "PostBody" => $row["body"],
        "PostedBy" => $row["user_id"],
        "Likes" => $row["likes"]
    );
    array_push($result, $temp);
}

return json_response($result);

?>
    <?php foreach($endpointResponse as $key => $value): ?>

        <div class="post">
{
Profile Link: <?php echo $GLOBALS['config']['url']; ?>/profile/<?php echo $value['user_id']; ?>,
Post: <?php echo nl2br(htmlspecialchars($value['body'], ENT_QUOTES)); ?>
}

    <?php endforeach; ?>
<?php endif ?>

  </div>
