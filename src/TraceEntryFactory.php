<?php
namespace RobinTheHood\ExceptionMonitor;

class TraceEntryFactory
{
    public static function createFromTraceArrayEntry($index, $traceArrayEntry, $fileLanguage)
    {
        return new TraceEntry(
            $index,
            $traceArrayEntry['file'],
            $traceArrayEntry['line'],
            $traceArrayEntry['function'],
            $traceArrayEntry['class'],
            $traceArrayEntry['args'],
            $fileLanguage
        );
    }

    public static function createFromException($index, $exception, $fileLanguage)
    {
        return new TraceEntry(
            $index,
            $exception->getFile(),
            $exception->getLine(),
            '',
            '',
            '',
            $fileLanguage
        );
    }
}
