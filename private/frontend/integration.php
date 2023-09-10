<?php
if ($GLOBALS['config']['devmode'] == 1): ?>
    <div class="container mx-auto">

        <h1>[DEV] This is our Integration object</h1>
<?php endif; ?>

<?php
if (isset($endpointResponse)):
    $result = array();
    foreach ($endpointResponse as $row) {
        $temp = array(
            "IntegrationId" => $row["id"],
            "Service" => $row["service"],
            "ClientId" => $row["client_id"],
            // ... add more fields as necessary
        );
        array_push($result, $temp);
    }
?>

    <?php foreach($endpointResponse as $key => $value): ?>
        <div class="integration">
            Service: <?php echo nl2br(htmlspecialchars($value['service'], ENT_QUOTES)); ?>,
            Client Id: <?php echo nl2br(htmlspecialchars($value['client_id'], ENT_QUOTES)); ?>
            <!-- You can add more fields to display as needed -->
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<?php
if ($GLOBALS['config']['devmode'] == 1): ?>
    </div>
<?php endif; ?>
