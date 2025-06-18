<?php
/**
 * Comprehensive Error and Exception Handler for the application.
 *
 * This script sets up custom handlers to catch and log various types of errors,
 * ensuring that the application can handle issues gracefully without exposing
 * sensitive information to the end-user.
 */

// Prevent direct script access - commented out for now
// defined('ABSPATH') || exit;

// Enable error reporting for logging purposes
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to the user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/simple_debug.log'); // Centralized log file

/**
 * Custom error handler function.
 * Converts PHP errors into ErrorExceptions for consistent handling.
 *
 * @param int    $errno   The error level.
 * @param string $errstr  The error message.
 * @param string $errfile The file where the error occurred.
 * @param int    $errline The line number of the error.
 * @throws ErrorException
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

/**
 * Custom exception handler function.
 * Catches all uncaught exceptions and logs them.
 *
 * @param Throwable $exception The exception that was thrown.
 */
function customExceptionHandler($exception) {
    $logMessage = sprintf(
        "Uncaught Exception: %s in %s:%d\nStack trace:\n%s",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($logMessage);

    // Send a generic error response to the client
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }

    echo json_encode([
        'error' => 'A critical server error occurred. Please try again later.',
        'type' => 'exception'
    ]);
}

/**
 * Shutdown function to catch fatal errors.
 * This is the last line of defense for catching errors that are not
 * handled by the regular error handler, such as parse errors.
 */
function fatalErrorHandler() {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logMessage = sprintf(
            "Fatal Error: [%d] %s in %s on line %d",
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        error_log($logMessage);

        // Clean any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Send a generic error response
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }

        echo json_encode([
            'error' => 'A fatal server error occurred that prevented the request from completing.',
            'type' => 'fatal'
        ]);
    }
}

// Set the custom handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");
register_shutdown_function("fatalErrorHandler");

?>