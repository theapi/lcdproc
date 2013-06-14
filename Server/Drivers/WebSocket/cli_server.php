<?php
namespace Theapi\Lcdproc\Server\Drivers\Websocket;

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server as Reactor;

use Theapi\Lcdproc\Server\Drivers\WebSocket\Server;

require realpath(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';


$webSocketServer = new Server();


// A server for web browsers with the websocket protocol
$ioServer = IoServer::factory(new WsServer($webSocketServer), 8080);

// and listen without the websocket protocol on another port
$socket = new Reactor($ioServer->loop);
$socket->listen(8081);
$con = new IoServer($webSocketServer, $socket, $ioServer->loop);

$ioServer->run();
