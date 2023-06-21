<?php

namespace RobinTheHood\ExceptionMonitor;

use Exception;

class TraceEntryFactory
{
    /**
     * @param int $index
     * @param array $traceArrayEntry
     * @param string $fileLanguage Path
     *
     * @return TraceEntry
     */
    public static function createFromTraceArrayEntry($index, $traceArrayEntry, $fileLanguage)
    {
        return new TraceEntry(
            $index,
            $traceArrayEntry['file'] ?? '',
            $traceArrayEntry['line'] ?? 0,
            $traceArrayEntry['function'] ?? '',
            $traceArrayEntry['class'] ?? '',
            $traceArrayEntry['args'] ?? [],
            $fileLanguage
        );
    }

    /**
     * @param Exception $exception
     * @param string $fileLanguage Path
     *
     * @return TraceEntry
     */
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
