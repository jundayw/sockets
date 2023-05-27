<?php

namespace Jundayw\Sockets\Contracts;

use Socket;

interface LoggingContract
{
    /**
     * @param bool $enableLogging
     * @return static
     */
    public function setEnableLogging(bool $enableLogging = true): static;

    /**
     * @param $message
     * @param Socket|null $socket
     * @return void
     */
    public function log($message, Socket $socket = null): void;
}
