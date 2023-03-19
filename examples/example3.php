<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
use RobinTheHood\ExceptionMonitor\ExceptionMonitorObj;
use RobinTheHood\ExceptionMonitor\Handler\BrowserHandler;

$exceptionMonitor = new ExceptionMonitorObj();
$exceptionMonitor->addHandler(new BrowserHandler());
$exceptionMonitor->register();

include('FileWithError.php');
