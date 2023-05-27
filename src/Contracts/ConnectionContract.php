<?php

namespace Jundayw\Sockets\Contracts;

use Socket;

interface ConnectionContract
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return static
     */
    public function setId(string $id): static;

    /**
     * @return Socket
     */
    public function getSocket(): Socket;

    /**
     * @param Socket $socket
     * @return static
     */
    public function setSocket(Socket $socket): static;

    /**
     * @return string
     */
    public function getAddress(): string;

    /**
     * @param string $address
     * @return static
     */
    public function setAddress(string $address): static;

    /**
     * @return int
     */
    public function getPort(): int;

    /**
     * @param int $port
     * @return static
     */
    public function setPort(int $port): static;

    /**
     * @return int
     */
    public function getUid(): int;

    /**
     * @param int $uid
     * @return static
     */
    public function setUid(int $uid): static;

    /**
     * @return mixed
     */
    public function getAlias(): mixed;

    /**
     * @param mixed $alias
     * @return static
     */
    public function setAlias(mixed $alias): static;

    /**
     * @return bool
     */
    public function isHandshake(): bool;

    /**
     * @param bool $handshake
     * @return static
     */
    public function setHandshake(bool $handshake): static;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param array $headers
     * @return static
     */
    public function setHeaders(array $headers): static;

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string;

    /**
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setHeader(string $name, string $value): static;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     * @return static
     */
    public function setPath(string $path): static;
}
