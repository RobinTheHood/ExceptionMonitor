<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use ErrorException;
use RobinTheHood\ExceptionMonitor\Handler\HandlerInterface;
use RobinTheHood\ExceptionMonitor\TraceEntryFactory;
use Throwable;

class BrowserHandler implements HandlerInterface
{
    private const PATH_STYLE_SYNTAX = '/vendor/robinthehood/syntax-highlighter/styles/default.css';
    private const PATH_LANGUAGE = '/vendor/robinthehood/syntax-highlighter/languages/php_lang.php';

    private $fileStyle = __DIR__ . '/../css/style.css';
    private $fileScript = __DIR__ . '/../js/script.js';

    private $fileStyleSyntax = '';
    private $fileLanguage = '';

    public function __construct()
    {
        $this->setFileStyleSyntax($this->findVendorDir() . self::PATH_STYLE_SYNTAX);
        $this->setFileLanguage($this->findVendorDir() . self::PATH_LANGUAGE);
    }

    private function setFileStyleSyntax(string $path): void
    {
        if (!\file_exists($path)) {
            die('Error: fileStyleSyntax not exists. <br>' . $path);
        }

        $this->fileStyleSyntax = $path;
    }

    private function setFileLanguage(string $path): void
    {
        if (!\file_exists($path)) {
            die('Error: fileLanguage not exists. <br>' . $path);
        }

        $this->fileLanguage = $path;
    }

    public function init($options)
    {
        if (isset($options['fileStyleSyntax'])) {
            $this->setFileStyleSyntax($options['fileStyleSyntax']);
            // self::$fileStyleSyntax = $options['fileStyleSyntax'];
        }

        if (isset($options['fileLanguage'])) {
            $this->setFileLanguage($options['fileLanguage']);
            //self::$fileLanguage = $options['fileLanguage'];
        }
    }

    public function handle(Throwable $exception): void
    {
        // Save current error level
        $savedLavel = error_reporting();
        error_reporting(E_ALL ^ E_NOTICE);

        $errorType = $this->createErrorType($exception);
        $classInformations = $this->createClassInformations($exception);
        $traceEntries = $this->createTraceEntries($exception, $this->fileLanguage);

        $exeptionMonitorArgs['fileStyle'] = $this->fileStyle;
        $exeptionMonitorArgs['fileStyleSyntax'] = $this->fileStyleSyntax;
        $exeptionMonitorArgs['fileScript'] = $this->fileScript;
        $exeptionMonitorArgs['fullClassName'] = $classInformations['fullClassName'];
        $exeptionMonitorArgs['class'] = $classInformations['class'];
        $exeptionMonitorArgs['namespace'] = $classInformations['namespace'];
        $exeptionMonitorArgs['exception'] = $exception;
        $exeptionMonitorArgs['errorType'] = $errorType;
        $exeptionMonitorArgs['traceEntries'] = $traceEntries;
        $this->show($exeptionMonitorArgs);

        // Restor saved error level
        error_reporting($savedLavel);
    }

    private function findVendorDir()
    {
        $directory = dirname(__FILE__);
        while ($directory != '/') {
            $composerFile = $directory . '/composer.json';
            $vendorDir = $directory . '/vendor';
            if (file_exists($composerFile) && file_exists($vendorDir)) {
                return $directory;
            }
            $directory = dirname($directory);
        }
        return false;
    }

    private function createErrorType($exception)
    {
        if ($exception instanceof ErrorException) {
            switch ($exception->getSeverity()) {
                case E_ERROR:
                    return 'Error:';
                case E_WARNING:
                    return 'Warning';
                case E_NOTICE:
                    return 'Notice';
                case E_PARSE:
                    return 'Syntax-Error';
            }
        }
        return '';
    }

    private function createTraceEntries($exception, $fileLanguage)
    {
        $traceArrayEntries = $exception->getTrace();
        $traceArrayEntries = $this->filterTracEntriesArray($traceArrayEntries);
        $traceEntries = [];

        $index = 0;
        $traceEntries[] = TraceEntryFactory::createFromException($index++, $exception, $fileLanguage);
        foreach ($traceArrayEntries as $traceArrayEntry) {
            $traceEntries[] = TraceEntryFactory::createFromTraceArrayEntry($index++, $traceArrayEntry, $fileLanguage);
        }
        $traceEntries[0]->setFunction('');

        return $traceEntries;
    }

    private function filterTracEntriesArray($traceEntries)
    {
        if (
            $traceEntries[0]['class'] == 'ExceptionMonitor\ExceptionMonitor'
            && $traceEntries[0]['function'] == 'errorToExceptionHandler'
        ) {
            array_shift($traceEntries);
            array_shift($traceEntries);
        }
        return $traceEntries;
    }

    private function createClassInformations($exception)
    {
        $result['fullClassName'] = get_class($exception);
        $classNameElements = explode('\\', $result['fullClassName']);
        for ($i = 0; $i < count($classNameElements) - 1; $i++) {
            $result['namespace'] .= $classNameElements[$i] . ' \\ ';
        }
        $result['class'] = $classNameElements[count($classNameElements) - 1];
        return $result;
    }

    private function show($exeptionMonitorArgs): void
    {
        include __DIR__ . '/../Layout.php';
    }
}
