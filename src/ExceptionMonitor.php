<?php
namespace ExceptionMonitor;

class ExceptionMonitor
{
    private static $fileStyle = __DIR__ . '/css/style.css';
    private static $fileScript = __DIR__ . '/js/script.js';

    private static $fileStyleSyntax = __DIR__ . '/../../syntax-highlighter/styles/default.css';
    private static $fileLanguage = __DIR__ . '/../../syntax-highlighter/languages/php_lang.php';

    public static function register($options = [])
    {
        if (isset($options['fileStyleSyntax'])) {
            self::$fileStyleSyntax = $options['fileStyleSyntax'];
        }
        if (isset($options['fileLanguage'])) {
            self::$fileLanguage = $options['fileLanguage'];
        }
        self::setHandlers();
    }

    private static function setHandlers()
    {
        set_exception_handler('ExceptionMonitor\ExceptionMonitor::exceptionHandlerBrowser');
        set_error_handler('ExceptionMonitor\ExceptionMonitor::errorToExceptionHandler');
        register_shutdown_function('ExceptionMonitor\ExceptionMonitor::syntaxErrorToExceptionHandler');
    }

    public static function exceptionHandlerBrowser($exception)
    {
        // Save current error level
        $savedLavel = error_reporting();
        error_reporting(E_ALL ^ E_NOTICE);

        $errorType = self::createErrorType($exception);
        $classInformations = self::createClassInformations($exception);
        $traceEntries = self::createTraceEntries($exception, self::$fileLanguage);

        $exeptionMonitorArgs['fileStyle'] = self::$fileStyle;
        $exeptionMonitorArgs['fileStyleSyntax'] = self::$fileStyleSyntax;
        $exeptionMonitorArgs['fileScript'] = self::$fileScript;
        $exeptionMonitorArgs['fullClassName'] = $classInformations['fullClassName'];
        $exeptionMonitorArgs['class'] = $classInformations['class'];
        $exeptionMonitorArgs['namespace'] = $classInformations['namespace'];;
        $exeptionMonitorArgs['exception'] = $exception;
        $exeptionMonitorArgs['errorType'] = $errorType;
        $exeptionMonitorArgs['traceEntries'] = $traceEntries;
        self::show($exeptionMonitorArgs);

        // Restor saved error level
        error_reporting($savedLavel);
    }

    private static function filterTracEntriesArray($traceEntries)
    {
        if ($traceEntries[0]['class'] == 'ExceptionMonitor\ExceptionMonitor'
            && $traceEntries[0]['function'] == 'errorToExceptionHandler'
        ) {
            \array_shift($traceEntries);
            \array_shift($traceEntries);
        }
        return $traceEntries;
    }

    private static function createErrorType($exception)
    {
        if ($exception instanceof \ErrorException) {
            switch($exception->getSeverity()) {
                case E_ERROR:
                    return 'Error:';
                case E_WARNING:
                    return 'Warning';
                case E_NOTICE:
                    return 'Warning';
                case E_PARSE:
                    return 'Syntax-Error';
            }
        }
        return '';
    }

    private static function createTraceEntries($exception, $fileLanguage)
    {
        $traceArrayEntries = $exception->getTrace();
        $traceArrayEntries = self::filterTracEntriesArray($traceArrayEntries);
        $traceEntries = [];

        $index = 0;
        $traceEntries[] = TraceEntryFactory::createFromException($index++, $exception, $fileLanguage);
        foreach($traceArrayEntries as $traceArrayEntry) {
            $traceEntries[] = TraceEntryFactory::createFromTraceArrayEntry($index++, $traceArrayEntry, $fileLanguage);
        }
        $traceEntries[0]->setFunction('');

        return $traceEntries;
    }

    private static function createClassInformations($exception)
    {
        $result['fullClassName'] = get_class($exception);
        $classNameElements = explode('\\', $result['fullClassName']);
        for($i=0; $i<count($classNameElements) - 1; $i++) {
            $result['namespace'] .= $classNameElements[$i] . ' \\ ';
        }
        $result['class'] = $classNameElements[count($classNameElements) - 1];
        return $result;
    }

    public static function show($exeptionMonitorArgs)
    {
        include 'Layout.php';
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

        $exception = new \ErrorException($lastError['message'], 0, $lastError['type'], $lastError['file'], $lastError['line']);

        switch ($lastError['type'])
        {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                self::exceptionHandlerBrowser($exception);
        }
    }
}
