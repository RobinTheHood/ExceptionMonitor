<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
use RobinTheHood\ExceptionMonitor\ExceptionMonitor;
use RobinTheHood\ExceptionMonitor\FailGuard\FailGuard;

$failGuard = new FailGuard();
if ($failGuard->isBanned()) {
    die('You have been banned! Try again later.');
}

ExceptionMonitor::register([
    'ip' => '111.111.111.111',
    'mail' => 'mail@robinwieschendorf.de',
    'failGuard' => $failGuard
]);

include('FileWithError.php');
