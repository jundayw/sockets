<?php

namespace Jundayw\Sockets\Contracts;

interface FrameContract
{
    public function encode($buffer);

    public function decode($buffer);

}