<?php

namespace RobinTheHood\ExceptionMonitor;

use RobinTheHood\ExceptionMonitor\Handlers\BrowserHandler;
use RobinTheHood\ExceptionMonitor\Handlers\MailHandler;

class ExceptionMonitor
{
    private static $mode = 'browser';
    private static $options = [];

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

    public static function enablePhpErrors()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    public static function disablePhpErrors()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE);
    }

    private static function isEnabled($options)
    {
        $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';

        if (isset($options['ip']) && $options['ip'] != $serverIp) {
            return false;
        }

        if (isset($options['domain'])) {
            $parts = explode('.', $_SERVER['SERVER_NAME']);
            $parts = array_reverse($parts);

            if (isset($parts[1]) && isset($parts[0])) {
                $domain = $parts[1] . '.' . $parts[0];
            } elseif (isset($parts[0])) {
                $domain = $parts[0];
            } else {
                return false;
            }

            if ($options['domain'] != $domain) {
                return false;
            }
        }

        return true;
    }

    private static function setHandlers($newMode = 'browser')
    {
        self::$mode = $newMode;

        set_exception_handler('RobinTheHood\ExceptionMonitor\ExceptionMonitor::exceptionHandler');
        set_error_handler('RobinTheHood\ExceptionMonitor\ExceptionMonitor::errorToExceptionHandler');
        register_shutdown_function('RobinTheHood\ExceptionMonitor\ExceptionMonitor::syntaxErrorToExceptionHandler');
    }

    public static function exceptionHandler($exception)
    {
        if (self::$mode == 'browser') {
            BrowserHandler::init(self::$options);
            BrowserHandler::exceptionHandlerBrowser($exception);
        } elseif (self::$mode == 'mail') {
            MailHandler::init(self::$options);
            MailHandler::exceptionHandlerMail($exception);
        }
    }

    public static function errorToExceptionHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function syntaxErrorToExceptionHandler()
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
                self::exceptionHandler($exception);
        }
    }
}
