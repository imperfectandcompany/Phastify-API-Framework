<?php
/* 
   This is a PHP test file that demonstrates how to write tests for a custom integration controller.
   We are using TDD (Test-Driven Development) and BDD (Business-Driven Development) principles here.
   The purpose of these tests is to ensure that the integration controller functions correctly.
*/

/* other file:
function customAssert($condition, $message) {
    global $currentTest;
    if (!$condition) {
        throw new Exception($message);
    }
}  */

// Feature: As a User,
//    when I integrate a service to Postogon, I want to configure my profile with service visibility

// Scenario: The user is configuring their integration
//   Given a user wants to configure service visibility
//     | Show to Followers False | Show to Contacts True |
//   When they configure service visibility
//   Then their recent integration settings update from default


// Add a service

$serviceID;
$integrationID;

function testUserCanAddService($integrationController)
{
    // Create service data
    $serviceData = [
        "name" => "Spotify",
        "client_id" => "f2a1ed52710d4533bde25be6da03b6e3",
        "description" => "Spotify is a digital music, podcast, and video service.",
        "logo" => $GLOBALS['config']['service_url'] . "/spotify_icon.png",
        "url" => "https://www.spotify.com/",
        "available" => true
    ];

    // Convert the service data to JSON
    $serviceDataJson = json_encode($serviceData);

    // Set the input stream for service creation
    $integrationController->service->setInputStream($serviceDataJson);

    // Create the service
    global $serviceId;
    $serviceId = $integrationController->service->createService();

    // Assertions
    customAssert($serviceId !== null, "Failed: Service creation should return an ID");
}

// Add an integration
function testUserCanAddIntegration($integrationController)
{
    global $serviceId;
    $serviceId = $integrationController->service->getNewlyCreatedServiceId();
    // Create integration data
    $integrationData = [
        "service_id" => $serviceId,
        // Uses the service ID from the previous test
        "client_secret" => "yourClientSecretHere",
        "access_token" => "yourAccessTokenHere",
        "token_type" => "Bearer",
        "status" => "Active",
        "show_to_followers" => false,
        "show_to_contacts" => false
    ];

    // Convert the integration data to JSON
    $integrationDataJson = json_encode($integrationData);

    // Set the input stream for integration creation
    $integrationController->setInputStream($integrationDataJson);

    // Create the integration
    global $integrationId;
    $integrationId = $integrationController->createIntegration();

    // Assertions
    customAssert($integrationId == true, "Failed: Integration creation should return an ID");
}

// Update integration settings
function testUserCanUpdateIntegrationSettings($integrationController)
{
    // Assume the service and integration ID have been added in previous tests

    // Define the updated settings
    $updatedSettings = [
        "show_to_followers" => false,
        "show_to_contacts" => true,
    ];

    // Convert the updated settings to JSON
    $updatedSettingsJson = json_encode($updatedSettings);

    // Set the input stream for integration update
    $integrationController->setInputStream($updatedSettingsJson);

    // Update the integration with new settings
    global $integrationId;
    $integrationId = $integrationController->getNewlyCreatedIntegrationIdTest(); // Use the integration ID from the previous test
    $integrationController->updateIntegration($integrationId);

    // Retrieve the updated integration
    $updatedIntegration = $integrationController->getIntegration($integrationId);
    // Assertions
    customAssert($updatedIntegration['show_to_followers'] === 0, "Failed: Show to Followers should be false");
    customAssert($updatedIntegration['show_to_contacts'] === 1, "Failed: Show to Contacts should be true");
}

// Update integration settings
function testUserCanCleanUpScenerio($integrationController)
{
    // Cleanup: Delete the integration to leave the system in a clean state for future tests.
    global $integrationId;
    $integrationController->deleteIntegration($integrationId);
    // integration must be deleted before service can be deleted
    global $serviceId;
    $result = $integrationController->service->deleteService($serviceId);

    // Reset the input streams
    $integrationController::setInputStream('php://input');
    $integrationController->service->setInputStream('php://input');
    customAssert($result === true, "Failed: Could not delete service.");
}

function testUserCanUpdateIntegrationAfterAddingService($integrationController)
{
    // Add a service
    // Fake Spotify client_id: f2a1ed52710d4533bde25be6da03b6e3
    $serviceData = [
        "name" => "Spotify",
        "client_id" => "f2a1ed52710d4533bde25be6da03b6e3",
        // public identifier for app
        "description" => "Spotify is a digital music, podcast, and video service that gives you access to millions of songs and other content from creators all over the world.",
        "logo" => $GLOBALS['config']['service_folder'] . "/spotify_icon.png",
        "url" => "https://www.spotify.com/",
        "available" => true // Corrected the typo from Availabe to available
    ];

    // Convert the service data to JSON
    $serviceDataJson = json_encode($serviceData);

    // Set the input stream for service creation
    $integrationController->setInputStream($serviceDataJson);

    // Create the service
    $serviceId = $integrationController->service->createService();

    // Stub function to get the newly created service ID
    $serviceId = $integrationController->service->getNewlyCreatedServiceId();

    // Add an integration
    $integrationData = [
        "service_id" => $serviceId,
        "client_secret" => "yourClientSecretHere",
        "access_token" => "yourAccessTokenHere",
        "token_type" => "Bearer",
        "status" => "Active",
        "show_to_followers" => false,
        // Update to not show to followers
        "show_to_contacts" => false,
        // Update to show to contacts
    ];

    // Convert the integration data to JSON
    $integrationDataJson = json_encode($integrationData);

    // Set the input stream for integration creation
    $integrationController->setInputStream($integrationDataJson);

    // Create the integration
    $integrationId = $integrationController->createIntegration();

    // Stub function to get the newly created integration ID
    $integrationId = $integrationController->getNewlyCreatedIntegrationIdTest();

    // Define the updated settings
    $updatedSettings = [
        "show_to_followers" => false,
        // Update to not show to followers
        "show_to_contacts" => true,
        // Update to show to contacts
    ];

    // Convert the updated settings to JSON
    $updatedSettingsJson = json_encode($updatedSettings);

    // Set the input stream for integration update
    $integrationController->setInputStream($updatedSettingsJson);

    // Update the integration with new settings
    $integrationController->updateIntegration($integrationId);

    // Retrieve the updated integration
    $updatedIntegration = $integrationController->getIntegration($integrationId);

    // Assertions
    customAssert($updatedIntegration['show_to_followers'] === false, "Failed: Show to Followers should be false");
    customAssert($updatedIntegration['show_to_contacts'] === true, "Failed: Show to Contacts should be true");

    // Cleanup: Delete the integration to leave the system in a clean state for future tests.
    $integrationController->deleteIntegration($integrationId);
    $integrationController->service->deleteService($serviceId);

    // Reset the input stream
    $integrationController::setInputStream('php://input');

}

?>