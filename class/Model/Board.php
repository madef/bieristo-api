<?php

namespace Bieristo\Model;

class Board
{
    protected $name;
    protected $timer;
    protected $unitList = [];

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getTimer()
    {
        return $this->timer;
    }

    public function setTimer(Timer $timer)
    {
        $this->timer = $timer;

        return $this;
    }

    public function getUnitList()
    {
        return $this->unitList;
    }

    public function setUnitList(array $unitList)
    {
        $this->unitList = $unitList;

        return $this;
    }

    public function addUnit(string $key, Unit $unit)
    {
        $this->unitList[$key] = $unit;

        return $this;
    }

    public function getData()
    {
        $unitList = [];
        foreach ($this->getUnitList() as $key => $unit) {
            $unitList[$key] = $unit->getData();
        }

        return [
            'name' => $this->getName(),
            'timer' => $this->getTimer() ? $this->getTimer()->getData() : null,
            'unitList' => $unitList,
        ];
    }
}
