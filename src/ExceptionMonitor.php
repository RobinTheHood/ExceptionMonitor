<?php

namespace RobinTheHood\ExceptionMonitor;

use RobinTheHood\ExceptionMonitor\Handler\BrowserHandler;
use RobinTheHood\ExceptionMonitor\Handler\MailHandler;

class ExceptionMonitor
{
    /** @var string */
    private static $mode = 'browser';

    /** @var array */
    private static $options = [];

    /**
     * @param array $options
     *
     * @return void
     */
    public static function register($options = [])
    {
        self::$options = $options;

        if (self::isEnabled($options)) {
            self::enablePhpErrors();
            self::setHandlers('browser');
        } else {
            self::disablePhpErrors();
            self::setHandlers('mail');
        }
    }

    /**
     * @return void
     */
    public static function enablePhpErrors()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    /**
     * @return void
     */
    public static function disablePhpErrors()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE);
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    private static function isEnabled($options)
    {
        $server = new Server();
        $serverIp = $server->getIpAddress();
        $serverName = $server->getServerName();

        if (isset($options['ip']) && $options['ip'] != $serverIp) {
            return false;
        }

        if (isset($options['domain']) && $options['domain'] != $serverName) {
            return false;
        }

        return true;
    }

    /**
     * @param string $newMode
     *
     * @return void
     */
    private static function setHandlers($newMode = 'browser')
    {
        self::$mode = $newMode;

        set_exception_handler([__CLASS__, 'runExceptionHandler']);
        set_error_handler([__CLASS__, 'runErrorHandler']);
        register_shutdown_function([__CLASS__, 'runShutdownFunction']);
    }

    /**
     * @param Exception exception
     *
     * @return void
     */
    public static function runExceptionHandler($exception)
    {
        if (self::$mode === 'browser') {
            $browserHandler = new BrowserHandler();
            $browserHandler->init(self::$options);
            $browserHandler->handle($exception);
        } elseif (self::$mode === 'mail') {
            $mailHandler = new MailHandler();
            $mailHandler->init(self::$options);
            $mailHandler->handle($exception);
        }
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @return void
     */
    public static function runErrorHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * @return void
     */
    public static function runShutdownFunction()
    {
        $lastError = error_get_last();

        if (!$lastError) {
            return;
        }

        $exception = new \ErrorException(
            $lastError['message'],
            0,
            $lastError['type'],
            $lastError['file'],
            $lastError['line']
        );

        switch ($lastError['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                self::runExceptionHandler($exception);
        }
    }
}
