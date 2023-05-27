<?php

namespace Jundayw\Sockets\WebSocket;

use Jundayw\Frames\WebSocket;
use Jundayw\Sockets\Contracts\FrameContract;
use Jundayw\Sockets\Http\Client as HttpClient;
use Socket;

class Client extends HttpClient
{
    protected string $path = '/';
    protected string $userAgent = 'sockets';
    protected array $headers = [];

    protected function initialize(): void
    {
        $this->setFrame(new class implements FrameContract {
            public function encode($buffer, int $opcode = 0x1, bool $finish = true, bool $mask = true): string
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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = $path;
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

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    protected function generateWebSocketKey(): string
    {
        $symbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return base64_encode(substr(str_shuffle($symbols), 0, 16));
    }

    public function connect(string $address, int $port, string $path = '/', array $headers = []): bool
    {
        parent::connect($address, $port);

        $headers = $this->setPath($path)->setHeaders($headers)->getHeaders();

        $this->headers   = array_merge($headers, [
            "GET {$this->path} HTTP/1.1",
            "User-Agent: {$this->userAgent}",
            "Upgrade: websocket",
            "Connection: Upgrade",
            "Sec-WebSocket-Key: {$this->generateWebSocketKey()}",
            "Sec-WebSocket-Version: 13",
        ]);
        $this->headers[] = "\r\n";

        if ($this->write($this->getSocket(), implode("\r\n", $this->headers)) === false) {
            return false;
        }

        if ($this->read($this->getSocket(), $this->getBufferSize()) === false) {
            return false;
        }

        return true;
    }

    public function push(Socket $socket, string $data, int $opcode = 0x1, bool $finish = true): bool
    {
        $frame = new WebSocket();
        $frame->pack($data, $opcode, $finish, false);
        return $this->send($socket, $frame->getPayload());
    }

    public function ping(Socket $socket): bool
    {
        return $this->push($socket, 'ping', 0x9, true);
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