<?php

namespace Jundayw\Sockets;

use Jundayw\Sockets\Concerns\HasEvents;
use Jundayw\Sockets\Concerns\HasFrame;
use Jundayw\Sockets\Concerns\HasLogging;
use Jundayw\Sockets\Concerns\HasSocket;
use Jundayw\Sockets\Contracts\EventsContract;
use Jundayw\Sockets\Contracts\LoggingContract;
use Jundayw\Sockets\Contracts\SocketsContract;
use Socket;

abstract class Sockets implements EventsContract, LoggingContract, SocketsContract
{
    use HasSocket;
    use HasFrame;
    use HasLogging;
    use HasEvents;

    private int $bufferSize = 8192;

    /**
     * @return int
     */
    public function getBufferSize(): int
    {
        return $this->bufferSize;
    }

    /**
     * @param int $bufferSize
     * @return self
     */
    public function setBufferSize(int $bufferSize): self
    {
        $this->bufferSize = $bufferSize;
        return $this;
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function onError(Socket $socket): void
    {
        $this->log("Error: " . $this->getLastErrorMessage(), $socket);
        $this->trigger("error", [
            $socket,
            error_get_last(),
            $this->getLastErrorMessage(),
            $this->getLastErrorCode(),
        ]);
        socket_clear_error($socket);
    }

    /**
     * Returns the last error on the socket
     *
     * @return int
     */
    public function getLastErrorCode(): int
    {
        return socket_last_error();
    }

    /**
     * Return a string describing a socket error
     *
     * @return string
     */
    public function getLastErrorMessage(): string
    {
        return socket_strerror($this->getLastErrorCode());
    }

}
