<?php

declare(strict_types=1);

namespace Core;

/**
 * Error and exception handler
 */
class ErrorHandler
{
    function __construct()
    {
        error_reporting(E_ALL);
        set_error_handler('\Core\ErrorHandler::handleError');
        set_exception_handler('\Core\ErrorHandler::handleException');
    }

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $level  Error level
     * @param string $message  Error message
     * @param string $file  Filename the error was raised in
     * @param int $line  Line number in the file
     *
     * @return void
     */
    public static function handleError(int $level, string $message, string $file, int $line): void
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     *
     * @param Throwable $exception  The exception
     *
     * @return void
     */
    public static function handleException(\Throwable $exception): void
    {
?>
        <h1>Fatal error</h1>
        <p>Uncaught exception: <?= get_class($exception) ?></p>
        <p>Message: <?= $exception->getMessage() ?></p>
        <p>Stack trace:
        <pre><?= $exception->getTraceAsString() ?></pre>
        </p>
        <p>Thrown in <?= $exception->getFile() ?> on line <?= $exception->getLine() ?></p>
<?php

        $message = 'Uncaught exception: "' . get_class($exception) . '"' . PHP_EOL;
        $message .= 'with message "' . $exception->getMessage() . '"' . PHP_EOL;
        $message .= 'Stack trace: ' . $exception->getTraceAsString() . PHP_EOL;
        $message .= 'Thrown in "' . $exception->getFile() . '" on line ' . $exception->getLine();

        error_log($message);
    }
}
