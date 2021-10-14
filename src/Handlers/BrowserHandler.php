<?php

namespace RobinTheHood\ExceptionMonitor\Handlers;

use RobinTheHood\ExceptionMonitor\TraceEntryFactory;

class BrowserHandler
{
    private static $fileStyle = __DIR__ . '/../css/style.css';
    private static $fileScript = __DIR__ . '/../js/script.js';

    private static $fileStyleSyntax = '';
    private static $fileLanguage = '';

    public static function init($options)
    {
        self::$fileStyleSyntax = self::findVendorDir() . '/vendor/robinthehood/syntax-highlighter/styles/default.css';
        self::$fileLanguage = self::findVendorDir() . '/vendor/robinthehood/syntax-highlighter/languages/php_lang.php';

        if (isset($options['fileStyleSyntax'])) {
            self::$fileStyleSyntax = $options['fileStyleSyntax'];
        }
        if (isset($options['fileLanguage'])) {
            self::$fileLanguage = $options['fileLanguage'];
        }

        if (!\file_exists(self::$fileStyleSyntax)) {
            die('Error: fileStyleSyntax not exists. <br>' . self::$fileStyleSyntax);
        }

        if (!\file_exists(self::$fileLanguage)) {
            die('Error: fileLanguage not exists. <br>' . self::$fileLanguage);
        }
    }

    private static function findVendorDir()
    {
        $directory = dirname(__FILE__);
        while ($directory != '/') {
            $composerFile = $directory . '/composer.json';
            if (file_exists($composerFile)) {
                return $directory;
            }
            $directory = dirname($directory);
        }
        return false;
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
        $exeptionMonitorArgs['namespace'] = $classInformations['namespace'];
        $exeptionMonitorArgs['exception'] = $exception;
        $exeptionMonitorArgs['errorType'] = $errorType;
        $exeptionMonitorArgs['traceEntries'] = $traceEntries;
        self::show($exeptionMonitorArgs);

        // Restor saved error level
        error_reporting($savedLavel);
    }

    private static function createErrorType($exception)
    {
        if ($exception instanceof \ErrorException) {
            switch ($exception->getSeverity()) {
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
        foreach ($traceArrayEntries as $traceArrayEntry) {
            $traceEntries[] = TraceEntryFactory::createFromTraceArrayEntry($index++, $traceArrayEntry, $fileLanguage);
        }
        $traceEntries[0]->setFunction('');

        return $traceEntries;
    }

    private static function filterTracEntriesArray($traceEntries)
    {
        if (
            $traceEntries[0]['class'] == 'ExceptionMonitor\ExceptionMonitor'
            && $traceEntries[0]['function'] == 'errorToExceptionHandler'
        ) {
            \array_shift($traceEntries);
            \array_shift($traceEntries);
        }
        return $traceEntries;
    }

    private static function createClassInformations($exception)
    {
        $result['fullClassName'] = get_class($exception);
        $classNameElements = explode('\\', $result['fullClassName']);
        for ($i = 0; $i < count($classNameElements) - 1; $i++) {
            $result['namespace'] .= $classNameElements[$i] . ' \\ ';
        }
        $result['class'] = $classNameElements[count($classNameElements) - 1];
        return $result;
    }

    public static function show($exeptionMonitorArgs)
    {
        include __DIR__ . '/../Layout.php';
    }
}
