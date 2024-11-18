<?php
// config_loader.php

if (!function_exists('loadEnv')) {
    /**
     * Load environment variables from a .env file into an array.
     *
     * @param string $path Path to the .env file.
     * @return array Associative array of environment variables.
     * @throws Exception if the .env file does not exist.
     */
    function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at path: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $envVariables = [];

        foreach ($lines as $line) {
            // Trim whitespace from the line
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            // Split the line into name and value
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);

                // Remove any surrounding quotes from the value
                $value = trim($value, "'\"");

                // Set the environment variable
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;

                // Add to the array of variables
                $envVariables[$name] = $value;
            }
        }

        // Return the array of environment variables
        return $envVariables;
    }
}
