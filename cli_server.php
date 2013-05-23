<?php
use Theapi\Lcdproc\Server;

// All errors
error_reporting(E_ALL);

// Allow the script to hang around waiting for connections.
set_time_limit(0);

// Turn on implicit output flushing
ob_implicit_flush();

require 'Server/Server.php';

$server = new Server();
$server->run();

