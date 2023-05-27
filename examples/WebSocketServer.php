<?php

use Jundayw\Frames\WebSocket;
use Jundayw\Sockets\Contracts\ConnectionContract;
use Jundayw\Sockets\WebSocket\Server;

include './../../../autoload.php';

$server = new Server(
    '0.0.0.0',
    8080,
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);
$server->setEnableLogging();
$server->setMaxClients(10);

$server->on("starting", function (Server $server) {
    var_dump("starting");
    // var_dump(func_get_args());
});
$server->on("create", function (Socket $socket) {
    var_dump("create");
    // var_dump(func_get_args());
});
$server->on("bind", function (Socket $socket) {
    var_dump("bind");
    // var_dump(func_get_args());
});
$server->on("listen", function (Socket $socket) {
    var_dump("listen");
    // var_dump(func_get_args());
});
$server->on("started", function (Socket $socket, Server $server) {
    var_dump("started");
    // var_dump(func_get_args());
});

$server->on("select", function (Socket $socket) {
    var_dump("select");
    // var_dump(func_get_args());
});
$server->on("accept", function (Socket $socket) {
    var_dump("accept");
    // var_dump(func_get_args());
});

$server->on("close", function (Socket $socket, ?ConnectionContract $connection = null) use ($server) {
    var_dump("close");
    if (!is_null($connection)) {
        $server->push($connection, 'close', 0x8, true);
    }
    // var_dump(func_get_args());
});


$server->on("connection", function (ConnectionContract $connection) {
    var_dump("connection");
    // var_dump(func_get_args());
});
$server->on("handshake", function (ConnectionContract $connection) use ($server) {
    $server->processClientHandshake($connection);
    var_dump("handshake");
    // var_dump(func_get_args());
});
$server->on("message", function (ConnectionContract $connection, $data, WebSocket $frame) use ($server) {
    var_dump("message:", $data);
    $server->push($connection, $frame->getPayloadData());
    // var_dump(func_get_args());
});
$server->on("binary", function (ConnectionContract $connection, $data, WebSocket $frame) use ($server) {
    var_dump("binary:", $data);
    $server->push($connection, $frame->getPayloadData());
    // var_dump(func_get_args());
});
$server->on("ping", function (ConnectionContract $connection, $data, WebSocket $frame) use ($server) {
    var_dump("ping:", $data);
    $server->pong($connection);
    // var_dump(func_get_args());
});
$server->on("disconnection", function (ConnectionContract $connection, int $errorCode, string $errorMessage) {
    var_dump("disconnection");
    // var_dump(func_get_args());
});


$server->on("error", function (Socket $socket, ?array $phpError, string $errorMessage, int $errorCode) {
    var_dump("error");
    // var_dump(func_get_args());
});

try {
    $server->start();
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}
