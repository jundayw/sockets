<?php

use Jundayw\Sockets\Contracts\ConnectionContract;
use Jundayw\Sockets\Contracts\FrameContract;
use Jundayw\Sockets\Server;

include './../../../autoload.php';

$server = new Server(
    '0.0.0.0',
    8080,
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);
$server->setEnableLogging();
$server->setMaxClients(1);

$server->setFrame(new class implements FrameContract {
    public function encode($buffer)
    {
        $buffer = mb_convert_encoding($buffer, 'GBK', 'utf8');
        return pack('a*', $buffer);
    }

    public function decode($buffer)
    {
        $buffer = unpack('a*', $buffer)[1];
        return mb_convert_encoding($buffer, 'utf8', 'GBK');
    }
});

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

$server->on("close", function (Socket $socket, ?ConnectionContract $connection = null) {
    var_dump("close");
    // var_dump(func_get_args());
});


$server->on("connection", function (ConnectionContract $connection) {
    var_dump("connection");
    // var_dump(func_get_args());
});
$server->on("receive", function (ConnectionContract $connection, $data) use ($server) {
    var_dump("receive(hex):", bin2hex($data));
    var_dump("receive(raw):", $data);
    $server->send($connection->getSocket(), $data);
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
