<?php

namespace Jundayw\Sockets\Frames;

use Closure;
use Jundayw\Sockets\Contracts\FrameContract;

class Frame implements FrameContract
{
    public ?Closure $encode = null;
    public ?Closure $decode = null;

    /**
     * 打包
     */
    public function encode($buffer)
    {
        if (is_callable($this->encode)) {
            return call_user_func_array($this->encode, func_get_args());
        }
        return $buffer;
    }

    /**
     * 解包
     */
    public function decode($buffer)
    {
        if (is_callable($this->decode)) {
            return call_user_func_array($this->decode, func_get_args());
        }
        return $buffer;
    }

}