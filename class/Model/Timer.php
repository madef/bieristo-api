<?php

namespace Bieristo\Model;

class Timer
{
    protected $start;
    protected $duration;

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(int $start)
    {
        $this->start = $start;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration(int $duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getData()
    {
        return [
            'start' => $this->getStart(),
            'duration' => $this->getDuration(),
        ];
    }
}



