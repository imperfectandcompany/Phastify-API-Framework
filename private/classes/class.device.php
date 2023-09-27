<?php
require_once($GLOBALS['config']['private_folder'] . '/services/device.service.php');
require_once($GLOBALS['config']['private_folder'] . '/services/token.service.php');

class Device
{

    private $dbObject;
    private $logger;

    /**
     * Constructor for the User class.
     *
     * @param DatabaseConnector $dbObject A database connection object
     */
    public function __construct($dbObject, $logger)
    {
        $this->dbObject = $dbObject;
        $this->logger = $logger;
    }

    /**
     * Get IP addresses associated with a device by device ID.
     *
     * @param int $deviceId The unique identifier of the device.
     *
     * @return array An array of associated IP addresses for the device.
     */
    public function getAssociatedIPsByDeviceId($deviceId)
    {
        // Retrieve IP addresses associated with a device
        $deviceService = new DeviceService($this->dbObject, $this->logger);
        return $deviceService->getAssociatedIPsByDeviceId($deviceId);
    }

    /**
     * Sets the database to set the device for the user with the given unique identifier.
     *
     * @param int $uid The unique identifier of the user
     *
     * @return int|false Returns the newly generated token if it was set successfully, or false otherwise
     */
    public function saveDevice($uid)
    {
        // Create an instance of the DeviceService class
        $deviceService = new DeviceService($this->dbObject, $this->logger);
        // Create an instance of the TokenService class
        $tokenService = new TokenService($this->dbObject, $this->logger);

        // Get device information
        // TODO: increase device info accuracy
        $deviceInfo = $this->getDeviceInfo();

        if (!$deviceInfo) {
            // Handle error, device information not available
            return false;
        }

        if ($this->hasPreviousLoginLogs($uid, $tokenService, $deviceService)) {
            // User has previous login activity, check if tokens are expired
            $isTokenExpired = $this->isTokenExpired($uid);
            if ($isTokenExpired) {
                // Log token expiration activity
                $this->logger->log($uid, 'token_expired', $isTokenExpired);
            }
            // Check if this user finished onboarding (based on the username)
            $isOnboardingNotComplete = $this->isUserOnboarded($uid);

            if ($isOnboardingNotComplete) {
                throwWarning('Onboarding not completed');
            } else {
                throwWarning('User has onboarding completed');
            }
        }
        // We get all the tokens associated by IP and log within the function
        $tokenService->logTokensByIp($_SERVER['REMOTE_ADDR']);

        // Let's see if this is the user's first time logging in
        $foundSuccess = false;
        $checkIfFirstTime = $this->logger->getUserLogsByAction($uid, "login_success_first_time");
        if (is_array($checkIfFirstTime) && $checkIfFirstTime["count"] > 0) {
            foreach ($checkIfFirstTime["results"] as $log) {
                if (!empty($log["action"]) && $log['action'] == 'login_success_first_time') {
                    $foundSuccess = true;
                    break; // No need to continue checking
                }
            }
        }

        if ($foundSuccess) {
            $this->logger->log($uid, 'login_success');
        } else {
            $this->logger->log($uid, 'login_success_first_time');
        }

        $this->logger->log($uid, 'device_login_save_intiated', $deviceInfo);
        try {
            // Update the device information in the devices table
            // Prepare the SQL statement to insert a new device record
            $table = 'devices';
            $columns = 'user_id, device_name, first_login, last_login, is_logged_in, expired';
            $values = '?, ?, NOW(), NOW(), 1, 0'; // Make sure placeholders match the number of values
            $filterParams = [
                ['value' => $uid, 'type' => PDO::PARAM_INT],
                // Use PDO::PARAM_INT for integer values
                ['value' => $deviceInfo['device_name'], 'type' => PDO::PARAM_STR],
                // Use PDO::PARAM_STR for string values
            ];

            // Insert the data into the "devices" table
            // Check if the insert was successful and return the result
            return $this->dbObject->insertData($table, $columns, $values, $filterParams)["insertID"];

        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'device_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }

    function associateDeviceIdWithLogin($user_id, $device_id, $device_name, $remote_ip)
    {
        // Assuming 'success' is 1 if the login is successful
        $success = 1;

        $data = [
            'user_id' => $user_id,
            'device_id' => $device_id,
            'ip_address' => $remote_ip,
            'success' => $success,
        ];

        // Build filter parameters for the data values
        $filterParams = makeFilterParams(array_values($data));
        $columnNames = implode(', ', array_keys($data));
        $whereClause = makePlaceholders(array_values($data));

        // Insert the data into the 'login_logs' table
        $insertResult = $this->dbObject->insertData('login_logs', $columnNames, $whereClause, $filterParams);

        // Check if the insert was successful
        if ($insertResult !== false) {
            // Insert IP address into 'device_ips' table
            if ($this->insertDeviceIP($device_id, $remote_ip)) {
                // Return the device_id
                throwSuccess('Login and device association successful.');
                return $device_id;
            } else {
                // Handle the case where IP address insertion failed
                throwError('Failed to insert IP address into device_ips table.');
                return false;
            }
        } else {
            // Handle the case where login data insertion failed
            throwError('Failed to insert login data into login_logs table.');
            return false;
        }
    }


    /**
     * Inserts an IP address record into the 'device_ips' table.
     *
     * @param int $device_id The ID of the device
     * @param string $ip_address The IP address to insert
     * @return array|false An array with the ID of the last inserted row or false on error
     */
    public function insertDeviceIP($device_id, $ip_address)
    {
        $rows = 'device_id, ip_address';
        $values = '?, ?';
        $paramValues = array($device_id, $ip_address);
        $filterParams = makeFilterParams($paramValues);

        return $this->dbObject->insertData('device_ips', $rows, $values, $filterParams);
    }

    /**
     * Check if there are previous login activity logs, associated devices, or login tokens for the user.
     *
     * @param int $uid The unique identifier of the user.
     *
     * @return bool True if there is evidence of a previous login, false otherwise.
     */
    private function hasPreviousLoginLogs($uid, $tokenService, $deviceService)
    {
        // Check if there are previous login activity logs
        $userLogs = $this->logger->getUserLogsByAction($uid, 'login_success_first_time');

        // Another check for default login_success just in case
        $userLogs2 = $this->logger->getUserLogsByAction($uid, 'login_success');

        // We can't get devices, just a single device because you can only have one device
        // Check if there are associated devices
        $associatedDevices = $deviceService->getDevicesByUserId($uid);

        // Check if there are login tokens for the user
        $loginTokens = $tokenService->getTokensByUserId($uid);

            // If there is evidence of previous login activity, return true
            if (
                (is_array($userLogs) && !empty($userLogs) && $userLogs["count"] > 0) ||
                (is_array($userLogs2) && !empty($userLogs2) && $userLogs2["count"] > 0) ||
                (is_array($associatedDevices) && !empty($associatedDevices)) ||
                (is_array($loginTokens) && !empty($loginTokens))
            ) {
            if (is_array($userLogs) && !empty($userLogs2)) {
                if ($userLogs["count"] > 0 || $userLogs2["count"] > 0) {
                    throwWarning('User has previous login logs');
                    echo "omgf";
                    $this->logger->log(
                        $uid,
                        'previous_login_logs',
                        'User has previous login logs'
                    );
                }
            }
            if (is_array($associatedDevices) && !empty($associatedDevices)) {
                throwWarning('User has previous associated devices');
                $this->logger->log(
                    $uid,
                    'previous_associated_devices',
                    'User has previous associated devices'
                );
            }
            if (is_array($loginTokens) && !empty($loginTokens)) {
                throwWarning('User has previous login tokens');
                $this->logger->log(
                    $uid,
                    'previous_login_tokens',
                    'User has previous login tokens'
                );
            }
        
            // Log that evidence of a previous login was found
            $this->logger->log($uid, 'previous_login_evidence', [
                'login_success_count' => is_array($userLogs) ? $userLogs["count"] : 0,
                'login_success_first_time' => is_array($userLogs2) ? $userLogs2["count"] : 0,
                'associated_devices_count' => is_array($associatedDevices) ? count($associatedDevices) : 0,
                'login_tokens_count' => is_array($loginTokens) ? count($loginTokens) : 0,
            ]);
        
            return true;
        } else {
            // Log that no evidence of a previous login was found
            $this->logger->log($uid, 'no_previous_login_evidence');
            return false;
        }

        return false;
    }



    /**
     * Check if the user's token is expired and if there are associated IPs with devices.
     *
     * @param int $uid The unique identifier of the user.
     *
     * @return bool True if the user's login is considered first-time, false otherwise.
     */
    private function isTokenExpired($uid)
    {
        // Create an instance of the DeviceService class
        $deviceService = new DeviceService($this->dbObject, $this->logger);
        // Create an instance of the TokenService class
        $tokenService = new TokenService($this->dbObject, $this->logger);
        // Check if there are previous login tokens for the user ID
        $loginTokens = $tokenService->getTokensByUserId($uid);
        // Check if there are no login tokens or if all tokens are expired
        if (empty($loginTokens)) {
            // Log that there are no valid tokens for the user
            $this->logger->log($uid, 'no__tokens');
            throwWarning('No valid tokens, we will check if any exist by ip.');
            return true;
        } else {
            // Check if all tokens are expired;
            if ($this->areAllTokensExpired($loginTokens)) {
                // Log that all tokens are expired
                $this->logger->log($uid, 'all_tokens_expired');
                // Return true to indicate the first-time login
                return true;
            } else {
                // Log that there are valid tokens for the user
                $this->logger->log($uid, 'all_tokens_not_expired');
                // TODO Log which tokens are valid on the device (lets stop here for now)
                return false;
            }
        }
    }

    /**
     * Check if all login tokens are expired.
     *
     * @param array $tokens An array of login tokens.
     *
     * @return bool True if all tokens are expired, false otherwise.
     */
    private function areAllTokensExpired($tokens)
    {
        foreach ($tokens as $key) {
            if (!$this->isTokenExpiredByTimestamp($key['expiration_time'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a token is expired based on its timestamp.
     *
     * @param string $timestamp The token's expiry timestamp.
     *
     * @return bool True if the token is expired, false otherwise.
     */
    private function isTokenExpiredByTimestamp($timestamp)
    {
        // Compare the token's timestamp with the current time
        $currentTimestamp = time();
        return strtotime($timestamp) < $currentTimestamp;
    }

    /**
     * Retrieves device information.
     *
     * @return array|false Returns device information if available, or false otherwise.
     */
    public function getDeviceInfo()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if ($this->isiPhone($userAgent)) {
            $device = "iPhone";
            $os = 'iOS'; // iPhones use iOS.
        } elseif ($this->isAndroid($userAgent)) {
            $device = "Android";
            $os = $this->detectOperatingSystem($userAgent);
        } else {
            $device = "Unknown";
            $os = $this->detectOperatingSystem($userAgent);
        }

        try {
            return [
                'device_name' => $device,
                'os' => $os,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ];
        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            throwWarning('Device Info Error');
            $this->logger->log(0, 'device_info_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }

    public function getDevice()
    {
        return $this->getDeviceInfo()["device_name"];
    }


    // TODO: Setup logs to identify if the user is being presented with the onboarding screen for the first time based on if
    // onboarding_not_completed exists in the logs
    // update i did it differently

    /**
     * Checks if it's the user's completed onboarding.
     *
     * @param int $uid The unique identifier of the user.
     *
     * @return bool|false Returns true if user hasn't completed user onboarding , false if not, or false on error.
     */
    private function isUserOnboarded($uid)
    {
        try {
            // Create filter parameters for $uid
            $uidFilterParams = makeFilterParams($uid);

            // Query the users table to check if the username is empty for the given user.
            $result = $this->dbObject->viewSingleData("users", "username", "WHERE id = ?", $uidFilterParams);
            if ($result !== false) {
                // If the query was successful, check if the username is empty.
                if (empty($result["result"]['username'])) {
                    // Check if this is the user's first time seeing onboarding
                    $onboardingLogs = $this->logger->log($uid, 'onboarding_not_completed_first_time');
                    if (!empty($onboardingLogs)) {
                        // If there are logs for onboarding not completed for the first time, we can assume this isn't their first time hitting onboarding
                        $this->logger->log($uid, 'onboarding_not_completed', $this->getDeviceInfo());
                    } else {
                        // Due to no logs on this, this might be the user's first time seeing onboarding, lets log it
                        $this->logger->log($uid, 'onboarding_not_completed_first_time', $this->getDeviceInfo());
                    }
                } else {
                    $username = $result['result']['username'];
                    $onboardingLogs = $this->logger->log($uid, 'onboarding_completed_already', "{username: " . $username . "}");
                }
                // Return true if user has onboarding completed, false if not
                // if the check for username is empty returns true, then the user has not completed onboarding
                return empty($result["result"]['username']);
            } else {
                // Handle the case where the query failed.
                $this->logger->log(0, 'first_login_error', ['error_message' => 'Database query failed']);
                return false;
            }
        } catch (Exception $e) {
            // Handle unexpected exceptions and log them
            $this->logger->log(0, 'first_login_error', ['error_message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Detect if the user agent belongs to an iPhone.
     *
     * @param string $userAgent The user agent string to check.
     *
     * @return bool True if the user agent belongs to an iPhone, false otherwise.
     */
    private function isiPhone($userAgent)
    {
        return preg_match('/iPhone/', $userAgent) || preg_match('/iPad/', $userAgent) || preg_match('/iPod/', $userAgent);
    }

    /**
     * Detect if the user agent belongs to an Android device.
     *
     * @param string $userAgent The user agent string to check.
     *
     * @return bool True if the user agent belongs to an Android device, false otherwise.
     */
    private function isAndroid($userAgent)
    {
        return preg_match('/Android/', $userAgent);
    }

    /**
     * Detect the operating system based on the user agent string.
     *
     * @param string $userAgent The user agent string to check.
     *
     * @return string|false The detected operating system or false if not found.
     */
    private function detectOperatingSystem($userAgent)
    {
        $operatingSystems = [
            'iOS' => '/\bi?OS\b [0-9._-]+/',
            'Android' => '/Android [0-9._-]+/',
            'Windows Phone' => '/Windows Phone (OS )?[0-9._-]+/',
            'Windows' => '/Windows NT [0-9._-]+/',
            'BlackBerry' => '/BlackBerry|BB10|rim[0-9]+/',
            'Symbian' => '/SymbianOS/',
            'webOS' => '/webOS|hpwOS/',
        ];

        foreach ($operatingSystems as $os => $regex) {
            if (preg_match($regex, $userAgent)) {
                return $os;
            } else {
                return 'Unknown';
            }
        }

        return false;
    }



    // TODO this is a function decoupled from the main function we need to check in order
    // to capture logs later on. too many logs fr. but this is for a purpose soo.
    private function captureLogs($deviceService, $uid)
    {
        // Get associated IPs for the user
        $associatedIPsByUserId = $deviceService->getAssociatedIPsByUserId($uid);
        // Get the user's device ID
        $userDeviceId = $deviceService->getDeviceIdByUserId($uid);

        if ($userDeviceId !== null) {
            // Get IPs associated with the user's device
            $associatedIPsByDevice = $deviceService->getAssociatedIPsByDeviceId($userDeviceId);
            // Check if there are no associated IP addresses or tokens by IP
            if (empty($associatedIPsByUserId) && empty($associatedIPsByDevice)) {
                // Log that there are no associated IP addresses or tokens by IP
                $this->logger->log($uid, 'no_associated_ips_or_tokens_by_ip');
                // Return true to indicate the first-time login
                return true;
            }
        } else {
            // Log that there is no user device associated with the user
            $this->logger->log($uid, 'no_user_device');
        }
    }

}
