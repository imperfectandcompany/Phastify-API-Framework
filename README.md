# postogon-api-v2

notes: gotta add validation for param in the sense that when we add a route with a param, we should make sure the method within controller being called includes that within the function param to prevent it from continuing.

add validation for this error:
https://v2.api.postogon.com/authenticate?token=fawwew
<br />
<b>Fatal error</b>:  Uncaught Error: Call to undefined method Router::getRoutes() in /usr/www/igfastdl/postogon-api/public_html/index.php:59
Stack trace:
#0 {main}
  thrown in <b>/usr/www/igfastdl/postogon-api/public_html/index.php</b> on line <b>59</b><br />

sep18
https://v2.api.postogon.com/list-routes

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
