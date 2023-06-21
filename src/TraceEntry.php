<?php

namespace RobinTheHood\ExceptionMonitor;

use RobinTheHood\SyntaxHighlighter\SyntaxHighlighter;

class TraceEntry
{
    /** @var int */
    private $index;

    /** @var string */
    private $filePath;

    /** @var int */
    private $line;

    /** @var string */
    private $function;

    /** @var string */
    private $class;

    /** @var array */
    private $args;

    /** @var string */
    private $fileSyntax;

    /**
     * @param int $index
     * @param string $filePath
     * @param int $line
     * @param string $function
     * @param string $class
     * @param array $args
     * @param string $fileSyntax
     */
    public function __construct($index, $filePath, $line, $function, $class, $args, $fileSyntax)
    {
        $this->index = $index;
        $this->filePath = $filePath;
        $this->line = $line;
        $this->function = $function;
        $this->class = $class;
        $this->args = $args;
        $this->fileSyntax = $fileSyntax;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getRelativeFilePath()
    {
        $rootPath = $_SERVER['DOCUMENT_ROOT'];
        $len = strlen($rootPath);

        for ($i = 0; $i < $len; $i++) {
            if (!isset($this->filePath[$i]) || $this->filePath[$i] != $rootPath[$i]) {
                break;
            }
        }

        return substr($this->filePath, $i);
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getFunctionWithArgs()
    {
        if ($this->args && $this->function) {
            $count = 0;
            $argsStr = '';

            foreach ($this->args as $arg) {
                if (is_object($arg)) {
                    $argsStr .= get_class($arg);
                } elseif ($arg === null) {
                    $argsStr .= 'null';
                } elseif ($arg === '') {
                    $argsStr .= 'EMPTY_STRING';
                } elseif ($arg === false) {
                    $argsStr .= 'FALSE';
                } elseif ($arg === true) {
                    $argsStr .= 'TRUE';
                } elseif (is_array($arg)) {
                    $argsStr .= "ARRAY";
                } elseif (is_string($arg) || is_numeric($arg)) {
                    $argsStr .= $arg;
                } else {
                    $argsStr .= 'UNKOWN_VALUE';
                }

                if (++$count < count($this->args)) {
                    $argsStr .= ', ';
                }
            }

            return $this->function . '(' . $argsStr . ')';
        } else {
            return $this->function;
        }
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return string|void
     */
    public function getCode()
    {
        if (\file_exists($this->filePath)) {
            $syntaxHl = new SyntaxHighlighter();
            $syntaxHl->setFileSyntax($this->fileSyntax);
            $syntaxHl->setFilePath($this->filePath);
            $syntaxHl->selectLine($this->line);
            return $syntaxHl->generate(['no-style' => true]);
        }
    }
}
