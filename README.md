# postogon-api-v2

sep18
https://v2.api.postogon.com/list-routes

# Creating and Running Tests:
This section introduces the test runner for the application, offering a systematic approach to validate features using a series of defined tests.

**Enhancements**:
- Introduced `TestDouble` to provide mocking capabilities for the input stream (simulating post body request), enabling more granular control during testing.
- Enhanced the `TestRunner` class to support categorized test execution and display comprehensive metrics post-test run
- Integrated additional tests to validate post and comment functionalities.

**Fixes**:
- Resolved issues related to global state by introducing cleanup mechanisms post-test.
- Refined error-handling mechanisms to capture both PHP `Error` and `Exception` objects.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Setting Up the Test Environment](#setting-up-the-test-environment)
- [Writing Tests](#writing-tests)
- [Grouping Tests](#grouping-tests)
- [Running Tests](#running-tests)
- [Understanding TestDouble](#understanding-testdouble)
- [Test Cleanup & Integrity](#test-cleanup--integrity)
- [Debugging](#debugging)
- [Reporting](#reporting)
- [Delving Deeper: Writing Functions with Meaningful Feedback](#delving-deeper-writing-functions-with-meaningful-feedback)
- [Conclusion](#conclusion)

## Glossary
**Group Tests**: Group tests based on functionality, and specify which controller they belong to.
**Execute Tests**: The TestRunner is now initialized with all controllers. When running tests, it refers to the specified controller for each test group.
**Initialization of Controllers**: A key-value mapping of controller names to their initialized objects is established.
**Objective**: When implementing a new test, it's crucial to focus on the objective: validating specific functionalities within the application.


## Quick Start
1. Create Controller object with $dbConnection passed in for testing.
2. Define the test function, taking into account the $objectUnderTest to access controller methods.
3. Use the customAssert function to validate outcomes.
4. Add the test name to the relevant test group.
5. Ensure the test group is associated with the right controller in the $tests array.
6. ???
7. profit
8. $$$$$$$$
<a name="overview"></a>

## 1. Overview
This enhanced testing setup provides a robust environment to validate multiple aspects of the application across various controllers. By understanding the mechanics of accessing controllers, their underlying classes, and the strategic use of TestDouble, we can craft precise, effective tests. The structure also simplifies the addition of new test cases, making maintenance and expansion streamlined.

<a name="prerequisites"></a>
## 2. Prerequisites
- Ensure you've included all necessary files in your test script.
- Familiarity with the application's logic and functionalities.

<a name="setting-up-the-test-environment"></a>
## 3. Setting Up the Test Environment
Ensure your test environment is ready:

```php
include_once($GLOBALS['config']['private_folder'].'/tests/test_case.php');
include_once($GLOBALS['config']['private_folder'].'/controllers/{ControllerBeingTested}Controller.php');
include_once($GLOBALS['config']['private_folder'].'/classes/class.testRunner.php');

${ControllerBeingTested} = new {ControllerBeingTested}Controller($dbConnection);

// Initialize the Controller object once
$controllers = [
	'post' => new PostController($dbConnection),
	'comments' => new CommentController($dbConnection),
	'integrations' => new IntegrationControllerTestDouble($dbConnection)
	// ..etc
];
```
<a name="writing-tests"></a>
## 4. Writing Tests
Tests are simple PHP functions. Here's how to define one:

```php
function testName($objectUnderTest) {
    // Test logic here
    // Use customAssert function to validate conditions
    customAssert(/* condition */, "Error Message if condition fails");
}
```

To access methods within the controller being tested, use the $objectUnderTest argument. This argument contains the initialized instance of the controller, enabling direct access to its methods.

For instance, if testing the PostController:
```
php
function testCanViewOwnPublicPost($objectUnderTest) {
    $result = $objectUnderTest->viewPublicPost($postId);
    customAssert($result, "Failed to view own public post.");
}
```
#### Accessing the Underlying Class
Controllers often utilize underlying classes to handle specific tasks. In this framework, the test environment makes these classes accessible through the controller instance.

For example, if **PostController** uses a class named **PostHandler**, it's possible to call its methods:

```
php
function anotherTestExample($objectUnderTest) {
    $handlerResult = $objectUnderTest->PostHandler->specificMethod();
    customAssert($handlerResult, "Specific method failed.");
}
```

#### Understanding getInputStream and Simulating Post Body
During testing, we need to simulate post body requests. To achieve this, functions call getInputStream, allowing us to mock or replace actual HTTP post body data with predefined testing data.


**Tips**:
- Make test names descriptive.
	- E.g., **testCanViewOwnPublicPost**.
- Use **customAssert** to verify the logic.
	- If the condition is false, the test will fail.

<a name="grouping-tests"></a>
## 5. Grouping Tests
Group tests into categories for better organization:

```php
$testCannotViewOwnPosts = [
  "testCannotViewOwnUnauthorizedPost",
];
$testCanViewOthersPosts = [
  "testCanViewPublicPostAsContact",
  "testCanViewPublicPostAsNoContact",
  "testCanViewPrivatePostAsContact"
  // ... More tests
];
```

Now define all your test categories:

```php
$tests = [
                "Can View Own Posts" => ['controller' => 'post', 'tests' => $testCanViewOwnPosts],
                "Cannot View Own Posts" => ['controller' => 'post', 'tests' => $testCannotViewOwnPosts],
                "Can View Others' Posts" => ['controller' => 'post', 'tests' => $testCanViewOthersPosts],
                "Cannot View Others' Posts" => ['controller' => 'post', 'tests' => $testCannotViewOthersPosts],
];
```

<a name="running-tests"></a>
## 6. Running Tests
Use the TestRunner class to execute tests:

```php
$runner = new TestRunner($Controllers);
$runner->runTests($tests);
```
This class ensures each test is run and provides a summary of the results.

<a name="understanding-testdouble"></a>
## 7. Understanding TestDouble
The test infrastructure uses the TestDouble pattern, which is a general strategy for replacing specific components of a system for testing purposes. Hence, controllers used in testing have names ending in "TestDouble" (e.g., CommentControllerTestDouble), making it clear that they are stand-ins or substitutes for the real controllers during testing.

Sometimes, for testing, we need to mimic the behavior of the production controller but with controlled, predictable outputs. This ensures that external factors don't interfere with our tests.

In cases where tests don't require simulation of a post body request, we directly use the actual controllers. For example, with PostController.

**By using the TestDouble pattern**:

We can avoid unintended side effects, such as database writes, while testing.
It allows for specific behaviors to be mocked, helping isolate the functionalities being tested.

**This pattern pattern ensures controlled, predictable outputs during testing**:
- Avoids unintended side effects.
- Mocks specific behaviors.
	- Introduced to simulate post requests within production method

To mock certain behaviors for testing purposes, create a class in the `/tests/controller/` following the Production Controller file name  with `TestDouble` class appended to the end before `.php`. This class will be used to extend the original `{Production}Controller` but has the added capability to override the input stream. Don't forget to include the controller we are extending.

#### Usage:

1. Set the mock input:
    ```php
    CommentControllerTestDouble::setInputStream(json_encode(['comment' => 'Mocked comment']));
    ```

2. Reset the mock input:
    ```php
    CommentControllerTestDouble::setInputStream('php://input');
    ```

<a name="test-cleanup--integrity"></a>
## 8. Test Cleanup & Integrity

Our testing strategy emphasizes cleaning up and ensuring a consistent state before and after tests. The testRunner takes care of this by:
- Resetting the user ID.
- Restoring global state changes.
- Maintaining data integrity with separate databases (coming soon)

When we mock the post body, it's essential to reset it within the test or function after use, ensuring no unexpected side-effects in subsequent tests.

<a name="debugging"></a>
## 9. Debugging
Our application works with relational data involving entities like posts, users, and contact relationships. Currently, this data is accurate and resides in our primary database. A separate testing database is in our future plans to ensure data integrity and further decouple testing from production.

When we mock the post body (using TestDouble), it's essential to reset it within the test or function after use, ensuring no unexpected side-effects in subsequent tests.

Follow these steps when facing issues:

1. Isolate the Issue
	- Comment out other tests and only run the problematic test.
2. Print Debug Information
	- Within the application logic, you can use the global **$currentTest** variable to conditionally print information.

	```php
	global $currentTest;
	if ($currentTest === 'specificTestName'){
		echo "Debugging info here";
	}
	```
3. Check Error Message
	- The test runner will display a clear error message if a test fails.

**throwWarning**: This method logs a warning message. It's particularly useful for conditions that might need attention but don't necessarily indicate a failure. For example, when a post is private but still accessible by a user, it throws a warning:
  
```php
throwWarning("Post is private");
```

- **throwError**: Similar to **throwWarning**, but used for more critical issues that indicate a test failure.

When using throwWarning within tests or application logic, it automatically logs the file and line number where the warning was thrown. This information is invaluable when trying to understand the context or source of a particular warning.

Here is an example within our code:
#### Understanding the `canViewPost` Function

The `canViewPost` function is crucial as it checks whether a given user can view a specific post. Here's its signature:

```php
/**
 * Checks if the current user can view a post.
 *
 * @param int $postId The ID of the post to check.
 * @param int $userId The ID of the user.
 * @return bool True if the user can view the post, false otherwise.
 */
public function canViewPost(int $postId, int $userId): bool { ... }
```

Within this function, there are numerous conditions that dictate if a user has the necessary permissions to view a post. In order to provide meaningful feedback during testing, the function utilizes custom methods like **throwWarning** and **throwError**. 

Both methods include contextual information from where they were invoked, such as the file and line number. This aids in debugging, especially when tests fail.

For consistency and predictability, we also ensure that every conditional path in our controllers returns true or false for our **customAssert** function. Right before these returns, we call our sendResponse function, ensuring feedback is sent to the client or caller.

*If the functions being used do not include this, you will experience issues with assertion.
*
<a name="reporting"></a>
## 10. Reporting
After tests, a summary displays metrics:
- Passed and failed tests.
- See the Delving Deeper section for more.

<a name="delving-deeper-writing-functions-with-meaningful-feedback"></a>
## 11. Delving Deeper: Writing Functions with Meaningful Feedback
A deeper look into specific functions and feedback mechanisms:
- Understand the canViewPost function.
- Utilize custom methods like throwWarning.

<a name="conclusion"></a>
## 12. Conclusion
Automated testing ensures that the application works seamlessly. By understanding various mechanics and strategies, you can craft precise, effective tests and maintain the robust application. This guide is structured to cover every aspect of the testing process, ensuring clarity, coherence, and comprehensiveness. Following this guide will assist in creating, running, and maintaining tests effectively.


## Router Class Enhancements

Here's a quick overview of the new features:

### Required Parameters

Now, we can specify required parameters for different HTTP request methods (e.g., GET, POST, PUT) for each route. This ensures that the necessary data is present when handling requests.

Example:
```php
// Update an existing integration for the authenticated user
$router->enforceParameters('/integrations/:id', 'PUT', [
    'service' => 'body',   // Service comes from the request body
    'clientname' => 'body',   // Service comes from the request body
]);
```

### Documentation
We've added support for documenting our routes comprehensively. We can include documentation for each route, describing its purpose and usage.

Example:
```php
// Add documentation to route
$router->addDocumentation('/integrations/:id', 'PUT', 'Updates an existing integration for the authenticated user.');
```

### Enforcing Required Parameters
To ensure that required parameters are always present, we introduced a function that enforces them for a specific route and request method. This helps maintain data integrity and ensures that our routes receive the necessary input.

Example:
```php
// Require 'service' and 'clientname' to be present in the request body for the PUT method
$router->enforceParameters('/integrations/:id', 'PUT', [
    'PUT:body:service,clientname',
]);
```

### Introduction of DevMode

With the implementation of a development mode (`devmode`), our RESTful Web Service is now endowed with a mode that makes it more streamlined and hassle-free for our developers during the application development phase.

### What is `devmode`?

`devmode` is a feature designed to simplify the development and testing process. When activated, it avoids the need for token-based authentication for each request, making it easier for developers to test different endpoints without having to worry about providing or refreshing authentication tokens. This can significantly speed up development, but it's essential to remember that `devmode` should **never** be activated in production environments, as it bypasses certain security checks.

### Endpoints:

1. **Get Current DevMode Status**
    - **Endpoint**: `/devmode`
    - **HTTP Method**: GET
    - **Description**: Retrieves the current status of `devmode`, returning whether it's turned on (`true`) or off (`false`).
    - **Usage**: 
      ```http
      GET /devmode
      ```

2. **Toggle DevMode**
    - **Endpoint**: `/devmode/toggle`
    - **HTTP Method**: GET
    - **Description**: Toggles the current `devmode` status. If it's on, it will be turned off and vice versa.
    - **Usage**:
      ```http
      GET /devmode/toggle
      ```

3. **Set DevMode to a Specific Value**
    - **Endpoint**: `/devmode/toggle/:value`
    - **HTTP Method**: GET
    - **Description**: Sets the `devmode` status to a specific value. The `:value` parameter should be replaced with either `true` or `false`.
    - **Usage**:
      ```http
      GET /devmode/toggle/true
      ```
      or
      ```http
      GET /devmode/toggle/false
      ```

### How to toggle `devmode`?

- To **check the current status**, use the `/devmode` endpoint.
  
- To **switch the current mode**, simply call the `/devmode/toggle` endpoint. It will invert the current setting.
  
- To **set a specific mode** (either `true` or `false`), use the `/devmode/toggle/:value` endpoint, replacing `:value` with your desired state.

---

**Important**: Always ensure that `devmode` is turned off (`false`) in production environments for security reasons.


### Implementation of Integrations in the RESTful Web Service

Postogon API V2 has been expanded to include an `integrations` feature, designed to facilitate users in connecting and managing third-party service integrations.

### What are `integrations`?

`integrations` offers a framework for users to incorporate third-party services into the application. Each integration entails vital details like the service name, client ID, client secret, access tokens, and other service-specific credentials. This structured approach ensures efficient service interaction while preserving data safety and integrity.

### Key Features:

1. **Ownership Verification**: Before any update, the system verifies if the integration pertains to the authenticated user, solidifying security.
2. **Data Validation**: Only predefined columns from the provided data are accepted. Any extraneous or unidentified columns are flagged, preventing potential mishaps.
3. **Token Handling**: Although refresh tokens aren't yet operational, there are plans to integrate them for sustained and secure third-party service connections.

### Endpoints:

1. **Retrieve Integration Details**
    - **Endpoint**: `/integrations/:id`
    - **HTTP Method**: GET
    - **Description**: Fetches integration details for the user
    - **Usage**: 
      ```http
      GET /integrations
      ```

2. **Create a New Integration**
    - **Endpoint**: `/integrations`
    - **HTTP Method**: POST
    - **Description**: Adds a new integration to the user's account.
    - **Usage**:
      ```http
      POST /integrations
      ```
      Body:
      ```json
      {
          "service": "ServiceName",
          "client_id": "ClientID",
          ...
      }
      ```

3. **Update Integration by ID**
    - **Endpoint**: `/integrations/:id`
    - **HTTP Method**: PUT
    - **Description**: Updates an integration's details using the provided ID and data. Verifies ownership prior to allowing any update.
    - **Usage**: 
      ```http
      PUT /integrations/{integration_id}
      ```
      Body:
      ```json
      {
          "service": "UpdatedServiceName",
          ...
      }
      ```
4. ### Delete Integration by ID

- **Endpoint**: `/integrations/:id`
- **HTTP Method**: DELETE
- **Description**: Removes an existing integration from the user's account based on the provided ID. The system ensures that only the owner of the integration (or an authorized user) can delete it to maintain data integrity and security.
- **Usage**: 
  ```http
  DELETE /integrations/{integration_id}
  ```
With this `DELETE` endpoint, users can efficiently manage their integrations, ensuring that obsolete or redundant connections are promptly removed from their accounts.

---


### Future Plans:

- **Refresh Token Integration**: Plans are in the pipeline to integrate refresh tokens, ensuring continued access to third-party services without frequent reauthentication. 
- **Support for Specific Services**: In the near future, we aim to have a predefined list of supported third-party services with preset URLs, simplifying user experience and reducing error margins.

**Retrieve Specific Integration Details (WIP) **
    - **Endpoint**: `/integrations/:id`
    - **HTTP Method**: GET
    - **Description**: Fetches integration details based on the provided ID. 
    - **Usage**: 
      ```http
      GET /integrations/{integration_id}
      ```

Router class:
## Function: add

Adds a new route to the router.

### Parameters:

- `$uri` - The route URI
- `$controller` - The controller name and method, separated by `@`
- `$requestMethod` - The HTTP request method

### Steps:

1. Check if the URI is empty. If it is, throw an exception.
2. Add a slash to the beginning of the URI if it is missing.
3. Check if the URI ends with a slash and remove it if it does.
4. Check if the controller name and method are separated by `@`. If not, throw an exception.
5. Split the controller name and method into separate variables.
6. Check if a method name was provided after `@`. If not, throw an exception.
7. Check if a route with the same URI already exists. If it does, throw an exception.
8. Check if the endpoint matches with an existing route. If it does and the request method is already registered, throw an exception.
9. Get the list of files in the controllers directory.
10. Check if a file with the expected name exists.
11. If the file does not exist, throw an exception.
12. Check if the specified method exists in the controller. If not, throw an exception.
13. Check if the HTTP request method is valid. If not, throw an exception.
14. Parse the URI to find any parameter placeholders and save them to an array.
15. If the URI does not already exist in the routes array, add it along with the HTTP request method and controller name and method.
16. If the URI already exists in the routes array, add the HTTP request method and controller name and method to the existing URI.


notes: gotta add validation for param in the sense that when we add a route with a param, we should make sure the method within controller being called includes that within the function param to prevent it from continuing.

add validation for this error:
https://v2.api.postogon.com/authenticate?token=fawwew
<br />
<b>Fatal error</b>:  Uncaught Error: Call to undefined method Router::getRoutes() in /usr/www/igfastdl/postogon-api/public_html/index.php:59
Stack trace:
#0 {main}
  thrown in <b>/usr/www/igfastdl/postogon-api/public_html/index.php</b> on line <b>59</b><br />
