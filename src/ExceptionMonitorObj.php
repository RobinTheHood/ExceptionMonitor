<?php

namespace RobinTheHood\ExceptionMonitor;

use ErrorException;
use RobinTheHood\ExceptionMonitor\Handler\HandlerCollection;
use RobinTheHood\ExceptionMonitor\Handler\HandlerInterface;
use Throwable;

class ExceptionMonitorObj
{
    /** @var HandlerCollection */
    private $handlerCollection;

    public function __construct()
    {
        $this->handlerCollection = new HandlerCollection();
    }

    public function addHandler(HandlerInterface $handler)
    {
        $this->handlerCollection->add($handler);
    }

    public function register()
    {
        set_exception_handler(function (Throwable $exception) {
            $this->runExceptionHandler($exception);
        });

        set_error_handler(function ($severity, $message, $file, $line) {
            $this->runErrorHandler($severity, $message, $file, $line);
        });


        register_shutdown_function(function () {
            $this->runShutdownFunction();
        });
    }

    private function runExceptionHandler(Throwable $exception): void
    {
        foreach ($this->handlerCollection as $handler) {
            $handler->handle($exception);
        }
    }

    private function runErrorHandler($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        $exception = new ErrorException($message, 0, $severity, $file, $line);
        $this->runExceptionHandler($exception);
        return true;
        //throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    private function runShutdownFunction(): void
    {
        $exception = $this->convertSyntaxErrorToException();
        if (!$exception) {
            return;
        }
        $this->runExceptionHandler($exception);
    }

    private function convertSyntaxErrorToException(): ?Throwable
    {
        $lastError = error_get_last();

        if (!$lastError) {
            return null;
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
                return $exception;
        }

        return null;
    }
}
