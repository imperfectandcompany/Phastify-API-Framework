<?php
include($GLOBALS['config']['private_folder'] . '/classes/class.service.php');

class ServiceController
{

    protected $dbConnection;
    protected $service;
    protected $logger;
    protected $roles;


    public function __construct($dbConnection, $logger)
    {
        $this->dbConnection = $dbConnection;
        $service = new Service($this->dbConnection);
        $roles = new Roles($dbConnection);
        $this->roles = $roles;
        $this->logger = $logger;
        $this->service = $service;
    }

    // Create a new service (POST request)
    public function createService()
    {
        $postBody = json_decode(static::getInputStream(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON Decode Error: ' . json_last_error_msg();
        }

        // @role guard - only admins can create a service (TODO: future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to create a service'], ERROR_FORBIDDEN);
            return;
        }

        try {
            $missingFields = missingFields(['name', 'description'], $postBody);

            if (!empty($missingFields)) {
                $error = 'Error: ' . implode(', ', $missingFields) . ' field(s) is/are required';
                sendResponse('error', ['message' => $error], ERROR_INVALID_INPUT);
                return;
            }

            // Check if the service with the same name already exists
            if ($this->service->doesServiceNameExist($postBody["name"])) {
                throwError("Service with the same name already exists");
            }

            // Check if the service with the same URL already exists
            if ($this->service->doesServiceUrlExist($postBody["url"])) {
                throwError('Service with the same URL already exists');
            }

            $result = $this->service->insertServiceData($postBody);

            if ($result) {
                sendResponse('success', ['message' => 'Service Added'], SUCCESS_CREATED);
                return true;
            } else {
                sendResponse('error', ['message' => 'Unable to create service'], ERROR_INTERNAL_SERVER);
                return;
            }

        } catch (Exception $e) {
            sendResponse('error', ['message' => $e->getMessage()], ERROR_BAD_REQUEST);
        }
    }

    public function getAllServices()
    {
        // Check if the current user is an admin
        $isAdmin = $this->roles->isUserAdmin($GLOBALS["user_id"]);

        $result = $this->service->getAllServices();
        if ($result) {
            // Filter services based on availability for non-admins
            $filteredResult = array_filter($result, function ($service) use ($isAdmin) {
                if (!$isAdmin) {
                    throwWarning("User is not admin, filtering to show only available services");
                    return $service['available'] == 1; // Show only available services for non-admins
                }
                return true; // Show all services for admins
            });

            // Remove 'client_id' for non-admins
            if (!$isAdmin) {
                throwWarning("User is not admin, removing client_id from services");
                $filteredResult = array_map(function ($service) {
                    unset($service['client_id']);
                    return $service;
                }, $filteredResult);
            }

            sendResponse('success', ['services' => array_values($filteredResult)], SUCCESS_OK);
            return true;
        } else {
            sendResponse('error', ['message' => 'Unable to get services'], ERROR_INTERNAL_SERVER);
            return false;
        }
    }

    public function getServiceById(int $id)
    {
        // Check if the current user is an admin
        $isAdmin = $this->roles->isUserAdmin($GLOBALS["user_id"]);

        // Get the service details by ID
        $result = $this->service->getServiceById($id);

        if (!$result) {
            sendResponse('error', ['message' => 'Service not found'], ERROR_NOT_FOUND);
            return;
        }

        // Filter out 'client_id' for non-admins
        if (!$isAdmin) {
            unset($result['client_id']);
        }

        sendResponse('success', ['service' => $result], SUCCESS_OK);
    }

    public function deleteServiceById($id)
    {
        // TODO: ACCOMODATE FOR CONSTRAINT WHERE SERVICE HAS INTEGRATIONS

        // @role guard - only admins can delete a service (TODO: future update, create decorators for functions)
        if (!$this->roles->isUserAdmin($GLOBALS["user_id"])) {
            sendResponse('error', ['message' => 'Unauthorized to delete a service'], ERROR_FORBIDDEN);
            return false;
        }

        // Check if service exists before authorization check.
        if (!$this->service->doesServiceIdExist($id)) {
            sendResponse('error', ['message' => 'Service ID does not exist'], ERROR_NOT_FOUND);
            return false;
        }

        $result = $this->service->deleteServiceById($id);
        if ($result) {
            sendResponse('success', ['message' => 'Service deleted'], SUCCESS_OK);
            return true;
        } else {
            sendResponse('error', ['message' => 'Unable to delete service'], ERROR_INTERNAL_SERVER);
            return false;
        }
    }

    protected static function getInputStream()
    {
        return file_get_contents('php://input');
    }
}

?>