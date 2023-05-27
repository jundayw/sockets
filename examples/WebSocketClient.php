<?php

use Jundayw\Frames\WebSocket;
use Jundayw\Sockets\WebSocket\Client;

include './../../../autoload.php';

$socket = new Client(
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);

$socket->setEnableLogging();


$socket->on("message", function (ConnectionContract $connection, $data, WebSocket $frame) use ($socket) {
    var_dump("message:", $data);
    $socket->push($connection, $data);
    $socket->push($connection, $frame->getPayloadData());
    // var_dump(func_get_args());
});
try {
    $socket->connect('127.0.0.1', 8080);
    while (true) {
        echo PHP_EOL;
        echo "input:";
        $message = fgets(STDIN);
        $message = str_replace(PHP_EOL, '', $message);

        if (empty($message)) {
            continue;
        }

        if ($message == 'q') {
            $socket->push($socket->getSocket(), 'bye', 0x8, true);
        } elseif ($message == 'p') {
            $socket->ping($socket->getSocket());
        } else {
            // 发送消息
            $socket->push($socket->getSocket(), $message);
        }
        // 接收服务器应答消息
        $data = $socket->recv($socket->getSocket(), $socket->getBufferSize());
        if ($data === false) {
            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        // 打印应答消息内容
        var_dump("Received reply message:", $data);
        echo PHP_EOL;
    }
    $socket->close();
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}
