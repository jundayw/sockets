<?php

namespace Jundayw\Sockets\WebSocket;

use Jundayw\Frames\WebSocket;
use Jundayw\Frames\WebSocket\WebsocketClose;
use Jundayw\Frames\WebSocket\WebsocketOpcode;
use Jundayw\Sockets\Connection;
use Jundayw\Sockets\Contracts\ConnectionContract;
use Jundayw\Sockets\Contracts\FrameContract;
use Jundayw\Sockets\Http\Server as HttpServer;
use Socket;

class Server extends HttpServer
{
    protected string $magic = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    private array $allowedOrigins = [];
    private string $userAgent = 'sockets';

    protected function initialize(): void
    {
        $this->setConnection(Connection::class);
        $this->setFrame(new class implements FrameContract {
            public function encode($buffer, int $opcode = 0x1, bool $finish = true, bool $mask = false): string
            {
                $webSocket = new WebSocket();
                return $webSocket->pack($buffer, $opcode, $finish, $mask)->getPayload();
            }

            public function decode($buffer)
            {
                $webSocket = new WebSocket();
                return $webSocket->unpack($buffer);
            }
        });
    }

    /**
     * @return string
     */
    public function getMagic(): string
    {
        return $this->magic;
    }

    /**
     * @return array
     */
    public function getAllowedOrigins(): array
    {
        return $this->allowedOrigins;
    }

    /**
     * @param array $allowedOrigins
     * @return static
     */
    public function setAllowedOrigins(array $allowedOrigins): static
    {
        $this->allowedOrigins = $allowedOrigins;
        return $this;
    }

    /**
     * @param string $origin
     * @return static
     */
    public function setAllowedOrigin(string $origin): static
    {
        $this->allowedOrigins[] = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return static
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    protected function triggerReceiveEvents(ConnectionContract $connection, $frame)
    {
        if (!is_object($frame)) {
            return;
        }

        if (!$frame instanceof WebSocket) {
            return;
        }

        $arguments = [
            $connection,
            $frame->getPayloadData(),
            $frame,
        ];

        if ($frame->getOpcode() == WebsocketOpcode::WEBSOCKET_OPCODE_TEXT->getValue()) {
            $this->trigger('message', $arguments);
        }

        if ($frame->getOpcode() == WebsocketOpcode::WEBSOCKET_OPCODE_BINARY->getValue()) {
            $this->trigger('binary', $arguments);
        }

        if ($frame->getOpcode() == WebsocketOpcode::WEBSOCKET_OPCODE_PING->getValue()) {
            $this->trigger('ping', $arguments);
            $this->pong($connection);
        }

        if ($frame->getOpcode() == WebsocketOpcode::WEBSOCKET_OPCODE_CLOSE->getValue()) {
            $this->closeClientConnection($connection);
        }
    }

    protected function check(ConnectionContract $connection): bool
    {
        if (parent::check($connection) === false) {
            return false;
        }

        return $this->checkHandshake($connection);
    }

    protected function checkHandshake(ConnectionContract $connection): bool
    {
        if ($this->hasEvent('handshake') === false) {
            $this->processClientHandshake($connection);
        } else {
            $this->trigger('handshake', [
                $connection,
            ]);
        }

        if ($connection->isHandshake() === false) {
            $this->log('Failed process handshake', $connection->getSocket());
            $this->closeClientConnection($connection, WebsocketClose::WEBSOCKET_CLOSE_TLS_HANDSHAKE->value, WebsocketClose::WEBSOCKET_CLOSE_TLS_HANDSHAKE->message());
            return false;
        }

        return true;
    }

    /**
     * @param ConnectionContract $connection
     * @return bool
     */
    public function processClientHandshake(ConnectionContract $connection): bool
    {
        $buffer = $this->read($connection->getSocket(), $this->getBufferSize());

        if ($buffer === false) {
            $this->log("Can't read data", $connection->getSocket());
            return false;
        }

        if ($this->parseHeaders($connection, $buffer) === false) {
            return false;
        }

        if (is_null($key = $connection->getHeader('Sec-WebSocket-Key'))) {
            $this->log('Sec-WebSocket-Key is empty', $connection->getSocket());
            return false;
        }

        $secAccept = base64_encode(pack('H*', sha1($key . $this->getMagic())));

        $upgrade = [
            'HTTP/1.1 101 Web Socket Protocol Handshake',
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Accept: ' . $secAccept,
        ];

        if (!is_null($protocol = $connection->getHeader('Sec-WebSocket-Protocol'))) {
            $upgrade[] = 'Sec-WebSocket-Protocol: ' . $protocol;
        }
        $upgrade[] = "\r\n";

        $upgrade = implode("\r\n", $upgrade);

        if ($this->write($connection->getSocket(), $upgrade) === false) {
            $this->log("Handshake can't be sent", $connection->getSocket());
            return false;
        }

        $this->log('Handshake sent', $connection->getSocket());

        $connection->setHandshake(true);

        return true;
    }

    /**
     * @param ConnectionContract $connection
     * @param string $headers
     * @return bool
     */
    protected function parseHeaders(ConnectionContract $connection, string $headers): bool
    {
        // 解析HTTP请求头
        $matches = [];
        preg_match('/GET (.*) HTTP/i', $headers, $matches);
        if (isset($matches[1])) {
            $connection->setPath(trim($matches[1]));
        }

        $lines = preg_split("/\r\n/", $headers);
        foreach ($lines as $line) {
            if (preg_match('/\A(\S+): (.*)\z/', chop($line), $matches)) {
                $connection->setHeader($matches[1], $matches[2]);
            }
        }

        return $this->checkAllowedOrigin($connection);
    }

    /**
     * @param ConnectionContract $connection
     * @return bool
     */
    protected function checkAllowedOrigin(ConnectionContract $connection): bool
    {
        if (empty($this->allowedOrigins)) {
            return true;
        }

        if (is_null($origin = $connection->getHeader('Origin'))) {
            return true;
        }

        $components = parse_url($origin);
        if (!in_array($components['host'], $this->allowedOrigins)) {
            $this->log('Invalid origin', $connection->getSocket());
            return false;
        }

        return true;
    }

    public function push(ConnectionContract $connection, string $data, int $opcode = 0x1, bool $finish = true): bool
    {
        $frame = new WebSocket();
        $frame->pack($data, $opcode, $finish, false);
        return $this->send($connection->getSocket(), $frame->getPayload());
    }

    public function pong(ConnectionContract $connection): bool
    {
        return $this->push($connection, 'pong', 0xa, true);
    }

    /**
     * Receives decode data from a connected socket
     *
     * @param Socket $socket
     * @param int $length
     * @param int $mode
     * @return string|false
     */
    public function recv(Socket $socket, int $length, int $mode = PHP_BINARY_READ): string|false
    {
        return $this->getFrame()->decode($this->read($socket, $length, $mode))->getPayloadData();
    }

    /**
     * Write encode data to a socket
     *
     * @param Socket $socket
     * @param string $data
     * @return int|false
     */
    public function send(Socket $socket, string $data): int|false
    {
        return $this->write($socket, $data);
    }

}