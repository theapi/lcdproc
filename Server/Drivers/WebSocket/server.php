<?php
namespace Theapi\Lcdproc\Server\Drivers\Websocket;

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server as Reactor;

use Theapi\Lcdproc\Server\Drivers\Websocket\Browser;

require realpath(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require 'Browser.php';

$browser = new Browser();

// A server for web browsers with the websocket protocol
$server = IoServer::factory(new WsServer($browser), 8080);

// and listen without the websocket protocol on another port
$socket = new Reactor($server->loop);
$socket->listen(8081);
$con = new IoServer($browser, $socket, $server->loop);

$server->run();
