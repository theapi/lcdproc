<?php
namespace Theapi\Lcdproc\Server\Drivers\Websocket;

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

use Theapi\Lcdproc\Server\Drivers\Websocket\Browser;

require realpath(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require 'Browser.php';

// A server for web browsers
$server = IoServer::factory(
    new WsServer(
        new Browser()
    ),
    8080
);

//$server = IoServer::factory(new Lcd(), 8080);

$server->run();
