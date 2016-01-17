<?php
/**
 * Error handler
 */
set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);
set_exception_handler(
    function ($e) {
        echo '<pre>' . (string) $e . '</pre>';
    }
);
register_shutdown_function(
    function () {
        $error = error_get_last();
        $errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];

        if ($error && in_array($error['type'], $errors)) {
            echo '<pre>' . print_r($error, 1) . '</pre>';
        }
    }
);

/**
 * Init and run application
 */
foreach (glob(__DIR__ . '/../src/*.php') as $file) {
    include_once $file;
}

app\run();
