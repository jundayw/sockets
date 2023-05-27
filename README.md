# 环境要求

- `PHP` 8.1 或更高版本
- 安装了 `Socket` 扩展（通常在 PHP 默认安装中包含）

# 使用方法

- 命令行下, 执行 `composer` 命令安装:

```shell
composer require jundayw/sockets
```

# Socket 协议

## Server.php

```php
$server = new Server(
    '0.0.0.0',
    8080,
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);
$server->setEnableLogging();
$server->setMaxClients(1);

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

$server->start();
```

## Client.php

```php
$socket = new Client(
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);

$socket->setEnableLogging();

$socket->connect('127.0.0.1', 8080);

$socket->send($socket->getSocket(), 'data');

echo $socket->recv($socket->getSocket(), $socket->getBufferSize());

$socket->close();
```

# Http 协议

## HttpServer.php

```php
// @todo
```

## HttpClient.php

```php
// @todo
```

# WebSocket 协议

## WebSocketServer.php

```php
$server = new Server(
    '0.0.0.0',
    8080,
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);
$server->setEnableLogging();
$server->setMaxClients(10);

$server->on("connection", function (ConnectionContract $connection) {
    var_dump("connection");
    // var_dump(func_get_args());
});
//$server->on("handshake", function (ConnectionContract $connection) use ($server) {
//    $server->processClientHandshake($connection);
//    var_dump("handshake");
//    // var_dump(func_get_args());
//});
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

$server->start();
```

## WebSocketClient.php

```php
$socket = new Client(
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);

$socket->setEnableLogging();

$socket->connect('127.0.0.1', 8080);

$socket->push($socket->getSocket(), 'bye', 0x8, true);
//$socket->ping($socket->getSocket());

echo $socket->recv($socket->getSocket(), $socket->getBufferSize());

$socket->close();
```