<?php 

function testUserCanUpdateIntegrationAfterAddingService($integrationController) {

    // Add a service
    $serviceData = [
        "service" => "SomeServiceName",
        "client_id" => "yourClientIDHere",
        "client_secret" => "yourClientSecretHere",
        "access_token" => "yourAccessTokenHere",
        "token_type" => "Bearer",
        "status" => "Active"
    ];

    // Convert the service data to JSON
    $serviceDataJson = json_encode($serviceData);

    // Set the input stream for integration creation
    $integrationController::setInputStream($serviceDataJson);

    // Create the integration
    $integrationController->createIntegration();

    // Assuming you have a function to get the newly created integration ID
    $integrationId = getNewlyCreatedIntegrationId();

    // Define the updated settings
    $updatedSettings = [
        "Show to Followers" => false,
        "Show to Contacts" => true
    ];

    // Convert the updated settings to JSON
    $updatedSettingsJson = json_encode($updatedSettings);

    // Set the input stream for integration update
    $integrationController::setInputStream($updatedSettingsJson);

    // Update the integration with new settings
    $integrationController->updateIntegration($integrationId);

    // Retrieve the updated integration
    $updatedIntegration = $integrationController->getIntegration($integrationId);

    // Assertions
    $this->assertEquals(false, $updatedIntegration['Show to Followers']);
    $this->assertEquals(true, $updatedIntegration['Show to Contacts']);

    // Cleanup: Delete the integration (if needed) to leave the system in a clean state for future tests.
    $integrationController->deleteIntegration($integrationId);
}





?>