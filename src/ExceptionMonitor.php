<?php
namespace RobinTheHood\ExceptionMonitor;

class ExceptionMonitor
{
    private static $fileStyle = __DIR__ . '/css/style.css';
    private static $fileScript = __DIR__ . '/js/script.js';

    private static $fileStyleSyntax = __DIR__ . '/../../syntax-highlighter/styles/default.css';
    private static $fileLanguage = __DIR__ . '/../../syntax-highlighter/languages/php_lang.php';

    private static $mode = 'browser';
    private static $mailAddress = '';

    public static function register($options = [])
    {
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

        if (self::isEnabled($options)) {
            self::enablePhpErrors();
            self::setHandlers('browser');
        } else {
            self::disablePhpErrors();
            if (isset($options['mail'])) {
                self::$mailAddress = $options['mail'];
                self::setHandlers('mail');
            }
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
        error_reporting(0);
    }

    private static function isEnabled($options)
    {
        if (isset($options['ip']) && $options['ip'] != $_SERVER['SERVER_ADDR']) {
            return false;
        }

        if (isset($options['domain'])) {
            $parts = explode('.', $_SERVER['SERVER_NAME']);
            $parts = array_reverse($parts);
            $domain = $parts[1] . '.' . $parts[0];

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
            self::exceptionHandlerBrowser($exception);
        } elseif(self::$mode == 'mail') {
            self::exceptionHandlerMail($exception);
        }
    }

    public static function exceptionHandlerMail($exception)
    {
        $subject = 'ErrorReport: ' . $_SERVER['SERVER_NAME'];
        //$content = $exception->getMessage();
        $content = self::exceptionToString($exception);

        self::sendMail(self::$mailAddress, $subject, $content);
    }

    public static function exceptionToString($exception)
    {
        $str = '';

        $str .= 'Message: ' . $exception->getMessage() . "\n";
        $str .= 'File: ' . $exception->getFile() . "\n";
        $str .= 'Line: ' . $exception->getLine() . "\n";

        $str .= "\n" . '---------- TRACE ----------' . "\n\n";

        foreach ($exception->getTrace() as $entry) {
            $str .= 'File: ' . $entry['file'] . "\n";
            $str .= 'Line: ' . $entry['line'] . "\n";
            $str .= 'Func: ' . $entry['function'] . "\n";
            $str .= 'Class: ' . $entry['class'] . "\n";
            $str .= '---------------------------' . "\n\n";
        }

        return $str;
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
                self::exceptionHandler($exception);
        }
    }

    private static function sendMail($toAddress, $subject, $content)
    {
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-type: text/plain; charset=UTF-8';
        $header[] = 'Content-Transfer-Encoding: quoted-printable';
        //$header[] = 'From: '           . $encodedMeta['from'];
        //$header[] = 'Reply-To: '       . $encodedMeta['replyTo'];
        $header[] = 'X-Mailer: PHP/'   . phpversion();
        $header = implode("\r\n", $header);

        // Set bounce - option
        $options =  '-f ' . $toAddress;

        // Send mail
        mail($toAddress, $subject, quoted_printable_encode($content), $header, $options);
    }
}
