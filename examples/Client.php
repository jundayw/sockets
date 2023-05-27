<?php

use Jundayw\Sockets\Client;
use Jundayw\Sockets\Contracts\FrameContract;

include './../../../autoload.php';

$socket = new Client(
    AF_INET,
    SOCK_STREAM,
    SOL_TCP
);

$socket->setEnableLogging();

$socket->setFrame(new class implements FrameContract {
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
            break;
        }

        $bytes = $message;
        // 发送消息
        $socket->send($socket->getSocket(), $bytes);
        // 接收服务器应答消息
        $data = $socket->recv($socket->getSocket(), $socket->getBufferSize());
        if ($data === false) {
            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        // 打印应答消息内容
        echo "Received reply message:" . PHP_EOL;
        echo $data . PHP_EOL;
    }
    $socket->close();
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}
