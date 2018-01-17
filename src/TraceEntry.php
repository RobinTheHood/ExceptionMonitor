<?php
namespace RobinTheHood\ExceptionMonitor;

use RobinTheHood\SyntaxHighlighter\SyntaxHighlighter;

class TraceEntry
{
    private $index;
    private $filePath;
    private $line;
    private $fileSyntax;

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

    public function getClass()
    {
        return $this->class;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getRelativeFilePath()
    {
        $rootPath = $_SERVER['DOCUMENT_ROOT'];
        $len = strlen($rootPath);

        for($i=0; $i<$len; $i++) {
            if (!isset($this->filePath[$i]) || $this->filePath[$i] != $rootPath[$i]) {
                break;
            }
        }

        return substr($this->filePath, $i);
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function setFunction($function)
    {
        $this->function = $function;
    }

    public function getFunctionWithArgs()
    {
        if ($this->args && $this->function) {
            $count = 0;
            foreach($this->args as $arg) {
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
                } else {
                    $argsStr .= $arg;
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

    public function getArgs()
    {
        return $this->args;
    }

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
