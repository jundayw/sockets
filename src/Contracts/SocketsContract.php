<?php

namespace Jundayw\Sockets\Contracts;

use Socket;

interface SocketsContract
{
    /**
     * Create a socket (endpoint for communication)
     *
     * @return Socket|false
     */
    public function socket_create(): Socket|false;

    /**
     * Sets socket options for the socket
     *
     * @param Socket $socket
     * @param int $level
     * @param int $option
     * @param $value
     * @return bool
     */
    public function socket_set_option(Socket $socket, int $level, int $option, $value): bool;

    /**
     * Initiates a connection on a socket
     *
     * @param Socket $socket
     * @param string $address
     * @param int|null $port
     * @return bool
     */
    public function socket_connect(Socket $socket, string $address, ?int $port = null): bool;

    /**
     * Binds a name to a socket
     *
     * @param Socket $socket
     * @param string $address
     * @param int $port
     * @return bool
     */
    public function socket_bind(Socket $socket, string $address, int $port = 0): bool;

    /**
     * Listens for a connection on a socket
     *
     * @param Socket $socket
     * @param int $backlog
     * @return bool
     */
    public function socket_listen(Socket $socket, int $backlog = 0): bool;

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
    public function socket_select(?array &$read, ?array &$write, ?array &$except, ?int $seconds, int $microseconds = 0): int|false;

    /**
     * Accepts a connection on a socket
     *
     * @param Socket $socket
     * @return Socket|false
     */
    public function socket_accept(Socket $socket): Socket|false;

    /**
     * Write to a socket
     *
     * @param Socket $socket
     * @param string $data
     * @return int|false
     */
    public function write(Socket $socket, string $data): int|false;

    /**
     * Reads a maximum of length bytes from a socket
     *
     * @param Socket $socket
     * @param int $length
     * @param int $mode
     * @return string|false
     */
    public function read(Socket $socket, int $length, int $mode = PHP_BINARY_READ): string|false;

    /**
     * Receives data from a connected socket
     *
     * @param Socket $socket
     * @param int $length
     * @return int|false
     */
    public function peek(Socket $socket, int $length): int|false;

    /**
     * Receives decode data from a connected socket
     *
     * @param Socket $socket
     * @param int $length
     * @param int $mode
     * @return string|false
     */
    public function recv(Socket $socket, int $length, int $mode = PHP_BINARY_READ): string|false;

    /**
     * Write encode data to a socket
     *
     * @param Socket $socket
     * @param string $data
     * @return int|false
     */
    public function send(Socket $socket, string $data): int|false;

    /**
     * Closes a socket resource
     *
     * @param Socket $socket
     * @return bool
     */
    public function socket_close(Socket $socket): bool;
}
