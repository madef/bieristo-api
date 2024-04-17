<?php

namespace Bieristo\Exception;

class InvalidFormatException extends \Exception
{
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
