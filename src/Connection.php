<?php

namespace Jundayw\Sockets;

use Jundayw\Sockets\Contracts\ConnectionContract;
use Socket;

class Connection implements ConnectionContract
{
    private string $id;
    private Socket $socket;
    private string $address;
    private int $port;
    private int $uid;
    private mixed $alias;

    public function __construct()
    {
        $this->id = uniqid('sc');
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Socket
     */
    public function getSocket(): Socket
    {
        return $this->socket;
    }

    /**
     * @param Socket $socket
     * @return static
     */
    public function setSocket(Socket $socket): static
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return static
     */
    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return static
     */
    public function setPort(int $port): static
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     * @return static
     */
    public function setUid(int $uid): static
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias(): mixed
    {
        return $this->alias;
    }

    /**
     * @param mixed $alias
     * @return static
     */
    public function setAlias(mixed $alias): static
    {
        $this->alias = $alias;
        return $this;
    }

    private bool $handshake = false;
    private array $headers = [];
    private string $path = "/";

    /**
     * @return bool
     */
    public function isHandshake(): bool
    {
        return $this->handshake;
    }

    /**
     * @param bool $handshake
     * @return static
     */
    public function setHandshake(bool $handshake): static
    {
        $this->handshake = $handshake;
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

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
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

}
