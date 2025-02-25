<?php

namespace Tests\Support;

/**
 * Class HeadersMock
 *
 * Mocks the header() function for testing.
 * Captures headers set by the code under test without sending them to the browser.
 */
class HeadersMock {
    /**
     * @var array Holds captured headers
     */
    private static array $headers = [];

    /**
     * Captures headers for testing purposes.
     * 
     * @param string $header The header string
     * @param bool $replace Whether to replace a previous similar header
     * @param int|null $http_response_code The HTTP status code
     */
    public static function capture($header, $replace = true, $http_response_code = null): void
    {
        if ($replace) {
            // Remove existing header with the same name
            self::$headers = array_filter(self::$headers, function($existingHeader) use ($header) {
                return stripos($existingHeader, strtok($header, ':')) !== 0;
            });
        }
        
        // Capture the header
        self::$headers[] = $header;

        // Capture the status code, if provided
        if ($http_response_code !== null) {
            self::$headers[] = "HTTP/1.1 {$http_response_code}";
        }
    }

    /**
     * Returns all captured headers.
     *
     * @return array
     */
    public static function getHeaders(): array
    {
        return self::$headers;
    }

    /**
     * Resets the captured headers.
     */
    public static function reset(): void
    {
        self::$headers = [];
    }

    /**
     * Checks if a specific header has been captured.
     *
     * @param string $needle The header to search for
     * @return bool
     */
    public static function hasHeader(string $needle): bool
    {
        return in_array($needle, self::$headers);
    }
}