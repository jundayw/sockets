<?php

namespace Jundayw\Sockets\Concerns;

use Jundayw\Sockets\Contracts\FrameContract;
use Jundayw\Sockets\Frames\Frame;

trait HasFrame
{
    protected ?FrameContract $frame = null;

    /**
     * @return FrameContract
     */
    public function getFrame(): FrameContract
    {
        if (!$this->frame instanceof FrameContract) {
            return new Frame();
        }
        return $this->frame;
    }

    /**
     * @param FrameContract $frame
     * @return static
     */
    public function setFrame(FrameContract $frame): static
    {
        $this->frame = $frame;
        return $this;
    }

}