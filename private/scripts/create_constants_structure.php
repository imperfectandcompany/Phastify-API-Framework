<?php
// Define the directory structure
$directoryStructure = [
    'constants' => [
        'system_constants.php',      // System-wide constants
        'error_constants.php',       // Error constants
        'db_constants.php',          // Database constants
        'log_constants.php',         // Logging constants
    ],
    'content_constants' => [
        'en_US' => [
            'error_messages.php',        // English error messages
            'success_messages.php',      // English success messages
            'front_end_translations.php', // Frontend content translations for English
            'dev' => [
                'error_messages.php',    // English development error messages
                'success_messages.php',  // English development success messages
            ],
            'prod' => [
                'error_messages.php',    // English production error messages
                'success_messages.php',  // English production success messages
            ],
        ],
        'fr_FR' => [
            'error_messages.php',        // French error messages
            'success_messages.php',      // French success messages
            'front_end_translations.php', // Frontend content translations for French
            'dev' => [
                'error_messages.php',    // French development error messages
                'success_messages.php',  // French development success messages
            ],
            'prod' => [
                'error_messages.php',    // French production error messages
                'success_messages.php',  // French production success messages
            ],
        ],
    ],
    'features' => [
        'user' => [
            'user_constants.php',        // User-related constants
        ],
        'post' => [
            'post_constants.php',        // Post-related constants
        ],
        'comment' => [
            'comment_constants.php',    // Comment-related constants
        ],
        'integration' => [
            'integration_constants.php', // Integration-related constants
        ],
        'permission' => [
            'permission_constants.php',  // Permission-related constants
        ],
    ],
];


// Function to create directories and files recursively
function createDirectoriesAndFiles($baseDir, $structure)
{
    foreach ($structure as $key => $item) {
        if (is_string($item)) { // It's a file
            $filePath = $baseDir . '/' . $item;
            file_put_contents($filePath, "<?php\n\n");
            echo "Created file: $filePath\n"; // Debug output
        } elseif (is_array($item)) { // It's a subdirectory
            $dirPath = $baseDir . '/' . $key;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
                echo "Created directory: $dirPath\n"; // Debug output
            }
            createDirectoriesAndFiles($dirPath, $item);
        } else {
            echo "Debug: Unexpected item: $item\n"; // Debug output
        }
    }
}

// Define the base directory
$baseDirectory = $GLOBALS['config']['private_folder'] . '';

// Create the directory structure
createDirectoriesAndFiles($baseDirectory, $directoryStructure);

echo 'Directory structure and files created successfully.' . PHP_EOL;
?>