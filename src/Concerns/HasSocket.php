<?php

namespace Jundayw\Sockets\Concerns;

use Socket;

trait HasSocket
{
    protected ?Socket $socket = null;

    public function getSocket(): ?Socket
    {
        return $this->socket;
    }

    /**
     * Create a socket (endpoint for communication)
     *
     * @return Socket|false
     */
    public function socket_create(): Socket|false
    {
        if (($this->socket = @socket_create($this->domain, $this->type, $this->protocol)) === false) {
            $this->onError($this->socket);
        }
        return $this->socket;
    }

    /**
     * Sets socket options for the socket
     *
     * @param Socket $socket
     * @param int $level
     * @param int $option
     * @param $value
     * @return bool
     */
    public function socket_set_option(Socket $socket, int $level, int $option, $value): bool
    {
        if (($result = @socket_set_option($socket, $level, $option, $value)) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Initiates a connection on a socket
     *
     * @param Socket $socket
     * @param string $address
     * @param int|null $port
     * @return bool
     */
    public function socket_connect(Socket $socket, string $address, ?int $port = null): bool
    {
        if (($result = @socket_connect($socket, $address, $port)) === false) {
            $this->onError($this->socket);
        }
        return $result;
    }

    /**
     * Binds a name to a socket
     *
     * @param Socket $socket
     * @param string $address
     * @param int $port
     * @return bool
     */
    public function socket_bind(Socket $socket, string $address, int $port = 0): bool
    {
        if (($result = @socket_bind($socket, $address, $port)) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Listens for a connection on a socket
     *
     * @param Socket $socket
     * @param int $backlog
     * @return bool
     */
    public function socket_listen(Socket $socket, int $backlog = 0): bool
    {
        if (($result = @socket_listen($socket, $backlog)) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Runs the select() system call on the given arrays of sockets with a specified timeout
     *
     * @param array|null $read
     * @param array|null $write
     * @param array|null $except
     * @param int|null $seconds
     * @param int $microseconds
     * @return int|false
     */
    public function socket_select(?array &$read, ?array &$write, ?array &$except, ?int $seconds, int $microseconds = 0): int|false
    {
        if (($result = @socket_select($read, $write, $except, $seconds, $microseconds)) === false) {
            $this->onError($this->socket);
        }
        return $result;
    }

    /**
     * Accepts a connection on a socket
     *
     * @param Socket $socket
     * @return Socket|false
     */
    public function socket_accept(Socket $socket): Socket|false
    {
        if (($result = @socket_accept($socket)) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Write to a socket
     *
     * @param Socket $socket
     * @param string $data
     * @return int|false
     */
    public function write(Socket $socket, string $data): int|false
    {
        if (($result = @socket_write($socket, $data, strlen($data))) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Reads a maximum of length bytes from a socket
     *
     * @param Socket $socket
     * @param int $length
     * @param int $mode
     * @return string|false
     */
    public function read(Socket $socket, int $length, int $mode = PHP_BINARY_READ): string|false
    {
        if (($result = @socket_read($socket, $length, $mode)) === false) {
            $this->onError($socket);
        }
        return $result;
    }

    /**
     * Receives data from a connected socket
     *
     * @param Socket $socket
     * @param int $length
     * @return int|false
     */
    public function peek(Socket $socket, int $length): int|false
    {
        if (($result = @socket_recv($socket, $data, $length, MSG_PEEK)) === false) {
            $this->onError($socket);
        }
        if (is_null($data)) {
            $result = false;
        }
        return $result;
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
        return $this->getFrame()->decode($this->read($socket, $length, $mode));
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
        return $this->write($socket, $this->getFrame()->encode($data));
    }

    /**
     * Closes a socket resource
     *
     * @param Socket $socket
     * @return bool
     */
    public function socket_close(Socket $socket): bool
    {
        @socket_close($socket);
        return true;
    }

}
