<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
use RobinTheHood\ExceptionMonitor\ExceptionMonitorObj;
use RobinTheHood\ExceptionMonitor\Handler\CallbackHandler;

$exceptionMonitor = new ExceptionMonitorObj();
$exceptionMonitor->addHandler(new CallbackHandler(function ($exception) {
    var_dump($exception);
}));
$exceptionMonitor->register();

include('FileWithError.php');
