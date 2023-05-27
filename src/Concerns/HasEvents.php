<?php

namespace Jundayw\Sockets\Concerns;

use Closure;

trait HasEvents
{
    protected array $events = [];

    /**
     * Subscribe on event
     *
     * @param string $name
     * @param Closure $callback
     * @return static
     */
    public function on(string $name, Closure $callback): static
    {
        $this->trigger("event.on", [$name]);
        $this->events[] = [
            "name" => $name,
            "callback" => $callback,
            "type" => "on",
        ];
        return $this;
    }

    /**
     * Subscribe on event. Call only first time
     *
     * @param string $name
     * @param Closure $callback
     * @return static
     */
    public function one(string $name, Closure $callback): static
    {
        $this->trigger("event.one", [$name]);
        $this->events[] = [
            "name" => $name,
            "callback" => $callback,
            "type" => "one",
        ];
        return $this;
    }

    /**
     * Trigger event
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public function trigger(string $name, array $arguments = []): static
    {
        foreach ($this->events as $key => $event) {
            if (!array_key_exists('name', $event) || !array_key_exists('callback', $event) || !array_key_exists('type', $event)) {
                continue;
            }
            if ($event['name'] != $name) {
                continue;
            }
            call_user_func_array($event['callback'], $arguments);
            if ($event['type'] == "one") {
                unset($this->events[$key]);
            }
        }
        return $this;
    }

    /**
     * has event
     *
     * @param string $name
     * @return bool
     */
    public function hasEvent(string $name): bool
    {
        foreach ($this->events as $event) {
            if (!array_key_exists('name', $event)) {
                continue;
            }
            if ($event['name'] == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * remove event
     *
     * @param string $name
     * @return bool
     */
    public function removeEvent(string $name): bool
    {
        $length       = count($this->events);
        $this->events = array_filter($this->events, function ($event) use ($name) {
            return $event['name'] != $name;
        });
        return $length != count($this->events);
    }

}

