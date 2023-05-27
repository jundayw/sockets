<?php

namespace Jundayw\Sockets;

use Jundayw\Frames\WebSocket\WebsocketClose;
use Jundayw\Sockets\Concerns\HasClients;
use Jundayw\Sockets\Contracts\ConnectionContract;
use Jundayw\Sockets\Exceptions\SocketException;

class Server extends Sockets
{
    use HasClients;

    private int $maxClients = 1000;

    public function __construct(
        public readonly string $address,
        public readonly int    $port,
        protected readonly int $domain = AF_INET,
        protected readonly int $type = SOCK_STREAM,
        protected readonly int $protocol = SOL_TCP,
    )
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->setConnection(Connection::class);
        $this->setFrame(new Frame());
    }

    /**
     * @return int
     */
    public function getMaxClients(): int
    {
        return $this->maxClients;
    }

    /**
     * @param int $maxClients
     * @return static
     */
    public function setMaxClients(int $maxClients): static
    {
        $this->maxClients = $maxClients;
        return $this;
    }

    /**
     * 服务启动
     *
     * @return bool
     * @throws SocketException
     */
    public function start(): bool
    {
        $this->trigger('starting', [
            $this,
        ]);

        if ($this->socket_create() === false) {
            throw new SocketException($this->getLastErrorMessage());
        }
        $this->trigger('create', [
            $this->getSocket(),
        ]);

        if ($this->socket_set_option($this->getSocket(), SOL_SOCKET, SO_REUSEADDR, 1) === false) {
            throw new SocketException($this->getLastErrorMessage());
        }

        if ($this->socket_bind($this->getSocket(), $this->address, $this->port) === false) {
            throw new SocketException($this->getLastErrorMessage());
        }
        $this->trigger('bind', [
            $this->getSocket(),
        ]);

        if ($this->socket_listen($this->getSocket()) === false) {
            throw new SocketException($this->getLastErrorMessage());
        }
        $this->trigger('listen', [
            $this->getSocket(),
        ]);

        $this->trigger('started', [
            $this->getSocket(),
            $this,
        ]);

        while ($this->getSocket()) {
            // 多路复用
            $sockets = [
                $this->getSocket(),
            ];
            foreach ($this->getClientList() as $connection) {
                $sockets[] = $connection->getSocket();
            }

            $write = $except = null;

            if ($this->socket_select($sockets, $write, $except, null) === false) {
                $this->onError($this->getSocket());
                continue;
            }
            $this->trigger('select', [
                $this->getSocket(),
            ]);
            // 新链接
            if (in_array($this->getSocket(), $sockets)) {
                if (($socket = $this->socket_accept($this->getSocket())) === false) {
                    $this->onError($this->getSocket());
                    continue;
                }
                $this->trigger('accept', [
                    $this->getSocket(),
                ]);

                if (($client = $this->connection($socket)) === false) {
                    $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->value, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->message('Client is connection failed'));
                    continue;
                }

                if ($this->check($client) === false) {
                    continue;
                }

                $this->trigger('connection', [
                    $client,
                ]);

                continue;
            }
            // 遍历接收数据
            foreach ($sockets as $socket) {
                $client = $this->getClientInfo($socket);

                if (is_null($client)) {
                    $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->value, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->message('Client not found'));
                    continue;
                }

                if ($this->peek($client->getSocket(), $this->getBufferSize()) === false) {
                    $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_SERVER_ERROR->value, WebsocketClose::WEBSOCKET_CLOSE_SERVER_ERROR->message());
                    continue;
                }

                $buffer = $this->read($client->getSocket(), $this->getBufferSize());
                if (empty($buffer)) {
                    $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_UNSUPPORTED->value, WebsocketClose::WEBSOCKET_CLOSE_UNSUPPORTED->message());
                    continue;
                }

                $data = $this->getFrame()->decode($buffer);
                if (is_null($data) || $data === false) {
                    $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_UNSUPPORTED_DATA->value, WebsocketClose::WEBSOCKET_CLOSE_UNSUPPORTED_DATA->message());
                    continue;
                }

                $this->triggerReceiveEvents($client, $data);
            }
            // 清理
            $sockets = [];
        }

        return $this->stop();
    }

    /**
     * 连接合法性校验
     *
     * @param ConnectionContract $connection
     * @return bool
     */
    protected function check(ConnectionContract $connection): bool
    {
        if ($this->getMaxClients() == 0) {
            return true;
        }
        return $this->checkMaxClients($connection);
    }

    /**
     * 最大连接数校验
     *
     * @param ConnectionContract $connection
     * @return bool
     */
    protected function checkMaxClients(ConnectionContract $connection): bool
    {
        if (count($this->getClientList()) <= $this->getMaxClients()) {
            return true;
        }

        $this->closeClientConnection($connection, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->value, WebsocketClose::WEBSOCKET_CLOSE_MESSAGE->message('Max clients limit reached'));

        return false;
    }

    protected function triggerReceiveEvents(ConnectionContract $connection, $data)
    {
        // 接收客户端的数据
        $this->trigger('receive', [
            $connection,
            $data,
        ]);
    }

    /**
     * 服务终止
     *
     * @return bool
     */
    public function stop(): bool
    {
        if (is_null($this->getSocket())) {
            return true;
        }

        $this->trigger('stopping', [
            $this->getSocket(),
            $this,
        ]);

        foreach ($this->clients as $client) {
            $this->closeClientConnection($client, WebsocketClose::WEBSOCKET_CLOSE_NORMAL->value, WebsocketClose::WEBSOCKET_CLOSE_NORMAL->message());
        }

        $this->trigger('close', [
            $this->getSocket(),
            null,
        ]);
        $this->socket_close($this->getSocket());

        $this->trigger('stopped', [
            $this,
        ]);

        return true;
    }

    /**
     * 关闭客户端连接
     *
     * @param ConnectionContract $connection
     * @param int $errorCode
     * @param string $errorMessage
     * @return void
     */
    public function closeClientConnection(ConnectionContract $connection, int $errorCode = 0, string $errorMessage = ''): void
    {
        if (is_null($this->getClientInfo($connection->getSocket()))) {
            return;
        }

        $this->log('disconnected', $connection->getSocket());
        $this->trigger('disconnection', [
            $connection,
            $errorCode,
            $errorMessage,
        ]);

        $this->disconnection($connection->getSocket());

        $this->log('close', $connection->getSocket());
        $this->trigger('close', [
            $connection->getSocket(),
            $connection,
        ]);
        $this->socket_close($connection->getSocket());
    }

}
