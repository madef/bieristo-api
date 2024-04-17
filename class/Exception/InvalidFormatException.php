<?php

namespace Bieristo\Exception;

class InvalidFormatException extends \Exception
{
    protected $target;

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
