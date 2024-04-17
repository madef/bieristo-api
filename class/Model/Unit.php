<?php

namespace Bieristo\Model;

class Unit
{
    protected $name;
    protected $mode = 'off';
    protected $state = 0;

    final public function getType()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    final public function getName()
    {
        return $this->name;
    }

    final public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode(string $mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState(int $state)
    {
        $this->state = $state;

        return $this;
    }

    public function getData()
    {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'mode' => $this->getMode(),
            'state' => $this->getState(),
        ];
    }
}


