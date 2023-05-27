<?php

namespace Jundayw\Sockets\Contracts;

use Closure;

interface EventsContract
{
    /**
     * Subscribe on event
     *
     * @param string $name
     * @param Closure $callback
     * @return static
     */
    public function on(string $name, Closure $callback): static;

    /**
     * Subscribe on event. Call only first time
     *
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function one(string $name, Closure $callback): static;

    /**
     * Trigger event
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public function trigger(string $name, array $arguments = []): static;
}
