<?php

//TODO: add more extensive error handling and logging, e
// specially for scenarios where file inclusions or cache operations fail.

/**
 * LocalizationManager class handles the loading and retrieval of localized strings
 * for different regions and environments.
 */
class LocalizationManager
{
    /**
     * @var string The base directory for localization constants.
     */
    private $baseDirectory;

    /**
     * @var string The current environment ('dev' or 'prod').
     */
    private $environment;

    /**
     * @var string|null The default locale (e.g., 'en_US').
     */
    private $defaultLocale;

    /**
     * @var LocalizationCache The cache instance for storing loaded constants.
     */
    private $cache;

    /**
     * @var bool Flag for dynamic mode (used for dynamic constant definition).
     */
    private $dynamicMode = false; // Flag for dynamic mode

    /**
     * Constructs a new LocalizationManager instance.
     *
     * @param string $baseDirectory The base directory for localization constants.
     * @param string $environment The current environment ('dev' or 'prod').
     * @param string|null $defaultLocale The default locale (e.g., 'en_US').
     * @param LocalizationCache $cache The cache instance for storing loaded constants.
     */
    public function __construct(LocalizationCache $cache)
    {
        $this->baseDirectory = $GLOBALS['config']['private_folder'] . "/constants";
        $this->loadConstants('system_constants.php');
        $this->environment = devmode ? 'dev' : 'prod';
        $this->defaultLocale = region;
        $this->cache = $cache;
        $this->loadConstants('db_constants.php');
        $this->loadConstants('application_constants.php');
        $this->loadConstants('error_constants.php');
        $this->loadConstants('log_constants.php');
    }

    /**
     * Loads constants from a specified path.
     *
     * @param string $path The path to the constants file.
     */
    public function loadConstants($path)
    {
        $filePath = $this->baseDirectory . '/' . $path;
        if (file_exists($filePath)) {
            include_once($filePath);
        }
    }

    /**
     * Retrieves a localized string for the given key and locale.
     *
     * @param string $key The localization key.
     * @param string|null $locale The locale for the localized string (default is null).
     *
     * @return string The localized string or 'UNDEFINED_CONSTANT' if not found.
     *
     * @throws Exception When used in dynamic mode.
     */
    public function getLocalizedString($key, $locale = null)
    {
        if ($this->dynamicMode) {
            throw new Exception("Don't use initialize() to define specific constants dynamically.");
        }

        if ($locale === null) {
            $locale = $this->getUserRegion();
        }

        $cacheKey = "{$locale}_{$key}";
        $cachedValue = $this->cache->get($cacheKey);

        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $fileType = $this->getFileType($key);

        // Check if dev or prod folder exists
        if ($this->environment === 'dev') {
            $devPath = $this->baseDirectory . "/content_constants/$locale/dev/$fileType.php";
            if (file_exists($devPath)) {
                include_once($devPath);
                if (defined($key)) {
                    $this->cache->set($cacheKey, constant($key));
                    return constant($key);
                }
            }
        } elseif ($this->environment === 'prod') {
            $prodPath = $this->baseDirectory . "/content_constants/$locale/prod/$fileType.php";
            if (file_exists($prodPath)) {
                include_once($prodPath);
                if (defined($key)) {
                    $this->cache->set($cacheKey, constant($key));
                    return constant($key);
                }
            }
        }

        // Load constants from the main region folder
        $this->loadConstants("content_constants/$locale/$fileType.php");

        if (defined($key)) {
            $this->cache->set($cacheKey, constant($key));
            return constant($key);
        }

        return "UNDEFINED_CONSTANT";
    }

    private function getUserRegion()
    {
        // Implement logic to determine the user's region, such as checking user settings,
        // browser headers, or other user preferences.
        return 'en_US';
    }

    private function getFileType($key)
    {
        // Determine whether the key belongs to error messages or success messages.
        return strpos($key, 'ERROR_') === 0 ? 'error_messages' : 'success_messages';
    }

    /**
     * Initializes dynamic constant definition mode.
     *
     * @throws Exception If dynamic mode is not enabled.
     */
    public function initialize()
    {
        $this->dynamicMode = true;

        // Load and override constants dynamically as needed
        $this->defineDynamicConstants();
    }

    /**
     * Defines dynamic constants based on the current environment and locale.
     */
    public function defineDynamicConstants()
    {
        if (!$this->dynamicMode) {
            throw new Exception("Dynamic mode is not enabled. Cannot define constants.");
        }

        if ($this->defaultLocale === null) {
            $locale = $this->getUserRegion();
        } else {
            $locale = $this->defaultLocale;
        }

        $environmentArray = [];

        if ($this->environment === 'dev') {
            $environmentArray[] = $this->loadConstantsArray($this->baseDirectory . "/content_constants/$locale/dev/error_messages.php");
            $environmentArray[] = $this->loadConstantsArray($this->baseDirectory . "/content_constants/$locale/dev/success_messages.php");
        } elseif ($this->environment === 'prod') {
            $environmentArray[] = $this->loadConstantsArray($this->baseDirectory . "/content_constants/$locale/prod/error_messages.php");
            $environmentArray[] = $this->loadConstantsArray($this->baseDirectory . "/content_constants/$locale/prod/success_messages.php");
        }

        $mainArray = $this->loadConstantsArray($this->baseDirectory . "/content_constants/$locale/error_messages.php");

        foreach ($environmentArray as $envArray) {
            $mainArray = array_merge($mainArray, $envArray);
        }

        foreach ($mainArray as $name => $value) {
            define($name, $value);
        }

        return;
    }

    private function loadConstantsArray($filePath)
    {
        // Check if the file exists and return the constants array
        if (file_exists($filePath)) {
            include($filePath);
            return $constants ?? [];
        }
        return [];
    }

    /**
     * Retrieves a localized error string for the given key and locale.
     *
     * @param string $errorKey The error key.
     * @param string|null $locale The locale for the error string (default is null).
     *
     * @return string The localized error string or 'UNDEFINED_CONSTANT' if not found.
     */
    public function getErrorString($errorKey, $locale = null)
    {
        return $this->getLocalizedString('ERROR_' . $errorKey, $locale);
    }

    /**
     * Retrieves a localized success string for the given key and locale.
     *
     * @param string $successKey The success key.
     * @param string|null $locale The locale for the success string (default is null).
     *
     * @return string The localized success string or 'UNDEFINED_CONSTANT' if not found.
     */
    public function getSuccessString($errorKey, $locale = null)
    {
        return $this->getLocalizedString('SUCCESS_' . $errorKey, $locale);
    }

    /**
     * Defines a localized constant.
     *
     * @param string $key The key for the constant.
     * @param mixed $value The value for the constant.
     *
     * @throws Exception When used in dynamic mode.
     */
    public function defineConstant($key, $value)
    {
        if ($this->dynamicMode) {
            throw new Exception("Don't use initialize() to define specific constants dynamically.");
        }

        // Load or override constants dynamically as needed``
        // Example:
        // $this->defineConstant('ERROR_LOGIN_FAILED', 'Localized Error Message');
        if (!defined($key)) {
            define($key, $value);
        }
    }


}