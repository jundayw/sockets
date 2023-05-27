<?php

namespace Jundayw\Sockets\Concerns;

use Socket;

trait HasLogging
{
    protected bool $enableLogging = false;

    /**
     * @param bool $enableLogging
     * @return static
     */
    public function setEnableLogging(bool $enableLogging = true): static
    {
        $this->enableLogging = $enableLogging;
        return $this;
    }

    /**
     * @param $message
     * @param Socket|null $socket
     * @return void
     */
    public function log($message, ?Socket $socket = null): void
    {
        if ($this->enableLogging === false) {
            return;
        }
        $log = [
            date("Y-m-d H:i:s"),
            ":",
        ];
        if (!is_null($socket)) {
            if (@socket_getpeername($socket, $address, $port)) {
                $log[] = '[';
                $log[] = $address;
                $log[] = ':';
                $log[] = $port;
                $log[] = ']';
            }
        }
        $log[] = $message;
        echo implode(' ', $log) . "\n";
    }

}
