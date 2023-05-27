<?php

namespace Jundayw\Sockets\Concerns;

use Jundayw\Sockets\Connection;
use Jundayw\Sockets\Contracts\ConnectionContract;
use Socket;

trait HasClients
{
    protected array $clients = [];
    protected ?string $connection = null;

    /**
     * @return ConnectionContract
     */
    public function getConnection(): ConnectionContract
    {
        return ($connection = (function ($connection) {
            return (is_null($connection) || !class_exists($connection)) ? null : new $connection();
        })($this->connection)) instanceof ConnectionContract ? $connection : new Connection();
    }

    /**
     * @param string|null $connection
     * @return static
     */
    public function setConnection(?string $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return ConnectionContract[]
     */
    public function getClientList(): array
    {
        return $this->clients;
    }

    /**
     * @param Socket $socket
     * @return ConnectionContract|null
     */
    public function getClientInfo(Socket $socket): ?ConnectionContract
    {
        foreach ($this->getClientList() as $connection) {
            if ($connection->getSocket() === $socket) {
                return $connection;
            }
        }
        return null;
    }

    /**
     * @param Socket $socket
     * @return ConnectionContract|false
     */
    protected function connection(Socket $socket): ConnectionContract|false
    {
        $address = $port = null;
        if (socket_getpeername($socket, $address, $port) === false) {
            $this->onError($socket);
            return false;
        }
        $this->clients[] = $client = $this->getConnection()
            ->setSocket($socket)
            ->setAddress($address)
            ->setPort($port);
        return $client;
    }

    /**
     * @param Socket $socket
     * @return ConnectionContract[]
     */
    protected function disconnection(Socket $socket): array
    {
        return $this->clients = array_filter($this->getClientList(), function ($client) use ($socket) {
            return $client->getSocket() !== $socket;
        });
    }

}
