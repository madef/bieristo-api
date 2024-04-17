<?php

namespace Bieristo\Model;

class PlannerStep extends Unit
{
    const ACTION_OFF = 0;
    const ACTION_TARGET = 1;
    const ACTION_BOIL = 2;

    protected $action;
    protected $target;
    protected $power;
    protected $duration;
    protected $onFinishGoNext;

    public function setAction(int $action)
    {
        $this->action = $action;

        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setTarget(float $target)
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setPower(int $power)
    {
        $this->power = $power;

        return $this;
    }

    public function getPower()
    {
        return $this->power;
    }

    public function setDuration(int $duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setOnFinishGoNext(int $onFinishGoNext)
    {
        $this->onFinishGoNext = $onFinishGoNext;

        return $this;
    }

    public function getOnFinishGoNext()
    {
        return $this->onFinishGoNext;
    }

    public function getData()
    {
        return [
            'action' => $this->getAction(),
            'target' => $this->getTarget(),
            'power' => $this->getPower(),
            'duration' => $this->getDuration(),
            'onFinishGoNext' => $this->getOnFinishGoNext(),
        ];
    }
}

