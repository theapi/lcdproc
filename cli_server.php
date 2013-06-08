<?php
use Theapi\Lcdproc\Server\Server;


// All errors
error_reporting(E_ALL);

// Allow the script to hang around waiting for connections.
set_time_limit(0);

// Turn on implicit output flushing
ob_implicit_flush();

require 'Server/Server.php';

$host = '127.0.0.1';
$port = 13666;
$driver = 'piplate';
$verbosity = LOG_ERR;
$serverScreen = 1;

if (is_file('config.php')) {
    // use a local file to override the default settings
    include 'config.php';
}

$server = new Server(
    array(
        'driver' => $driver,
        'verbosity' => $verbosity,
        'serverScreen' => $serverScreen,
    )
);
$server->run($host, $port);
