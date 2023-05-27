<?php

namespace Jundayw\Sockets;

use Jundayw\Sockets\Exceptions\SocketException;

class Client extends Sockets
{
    public readonly string $address;
    public readonly int $port;

    public function __construct(
        protected readonly int $domain = AF_INET,
        protected readonly int $type = SOCK_STREAM,
        protected readonly int $protocol = SOL_TCP,
    )
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->setFrame(new Frame());
    }

    /**
     * @throws SocketException
     */
    public function connect(string $address, int $port): bool
    {
        if ($this->socket_create() === false) {
            throw new SocketException($this->getLastErrorMessage());
        }

        if ($this->socket_connect($this->getSocket(), $this->address = $address, $this->port = $port) === false) {
            throw new SocketException($this->getLastErrorMessage());
        }

        return true;
    }

    public function close(): bool
    {
        return $this->socket_close($this->getSocket());
    }

}
