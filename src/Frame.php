<?php

namespace Jundayw\Sockets;

use Jundayw\Sockets\Frames\Frame as Frames;

class Frame extends Frames
{
    /**
     * 打包
     */
    public function encode($buffer)
    {
        $buffer = mb_convert_encoding($buffer, 'GBK', 'utf8');
        return pack('a*', $buffer);
    }

    /**
     * 解包
     */
    public function decode($buffer)
    {
        $buffer = unpack('a*', $buffer)[1];
        return mb_convert_encoding($buffer, 'utf8', 'GBK');
    }

}