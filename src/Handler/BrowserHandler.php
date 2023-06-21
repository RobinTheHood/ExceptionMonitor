<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use Exception;
use ErrorException;
use Throwable;
use RobinTheHood\ExceptionMonitor\Handler\HandlerInterface;
use RobinTheHood\ExceptionMonitor\TraceEntryFactory;

class BrowserHandler implements HandlerInterface
{
    /** @var string */
    private const PATH_STYLE_SYNTAX = '/vendor/robinthehood/syntax-highlighter/styles/default.css';

    /** @var string */
    private const PATH_LANGUAGE = '/vendor/robinthehood/syntax-highlighter/languages/php_lang.php';

    /** @var string */
    private $fileStyle = __DIR__ . '/../css/style.css';

    /** @var string */
    private $fileScript = __DIR__ . '/../js/script.js';

    /** @var string */
    private $fileStyleSyntax = '';

    /** @var string */
    private $fileLanguage = '';

    public function __construct()
    {
        $this->setFileStyleSyntax($this->findVendorDir() . self::PATH_STYLE_SYNTAX);
        $this->setFileLanguage($this->findVendorDir() . self::PATH_LANGUAGE);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    private function setFileStyleSyntax(string $path): void
    {
        if (!\file_exists($path)) {
            die('Error: fileStyleSyntax not exists. <br>' . $path);
        }

        $this->fileStyleSyntax = $path;
    }

    /**
     * @param string $path
     *
     * @return void
     */
    private function setFileLanguage(string $path): void
    {
        if (!\file_exists($path)) {
            die('Error: fileLanguage not exists. <br>' . $path);
        }

        $this->fileLanguage = $path;
    }

    /**
     * @param array $options
     *
     * @return void
     */
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

    /**
     * @param Throwable $exception
     *
     * @return void
     */
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
        $exeptionMonitorArgs['fullClassName'] = $classInformations['fullClassName'] ?? '';
        $exeptionMonitorArgs['class'] = $classInformations['class'] ?? '';
        $exeptionMonitorArgs['namespace'] = $classInformations['namespace'] ?? '';
        $exeptionMonitorArgs['exception'] = $exception;
        $exeptionMonitorArgs['errorType'] = $errorType;
        $exeptionMonitorArgs['traceEntries'] = $traceEntries;
        $this->show($exeptionMonitorArgs);

        // Restor saved error level
        error_reporting($savedLavel);
    }

    /**
     * @return string|false
     */
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

    /**
     * @param ErrorException $exception
     *
     * @return string
     */
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

    /**
     * @param Exception $exception
     * @param string $fileLanguage Path
     */
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

    /**
     * @param array $traceEntries
     *
     * @return array
     */
    private function filterTracEntriesArray($traceEntries)
    {
        if (
            ($traceEntries[0]['class'] ?? '') == 'ExceptionMonitor\ExceptionMonitor'
            && ($traceEntries[0]['function'] ?? '') == 'errorToExceptionHandler'
        ) {
            array_shift($traceEntries);
            array_shift($traceEntries);
        }
        return $traceEntries;
    }

    /**
     * @param ErrorException $exception
     *
     * @return array<string, string>
     */
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

    /**
     * @param array
     *
     * @return void
     */
    private function show($exeptionMonitorArgs): void
    {
        include __DIR__ . '/../Layout.php';
    }
}
