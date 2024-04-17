<?php

namespace Bieristo\Model;

class HeatUnit extends Unit
{
    protected $tempSensor;
    protected $resistor;
    protected $power;
    protected $target;
    protected $temperature;
    protected $t1 = 5;
    protected $t2 = 0.5;
    protected $planner;

    public function getTempSensor()
    {
        return $this->tempSensor;
    }

    public function setTempSensor(int $tempSensor = null)
    {
        $this->tempSensor = $tempSensor;

        return $this;
    }

    public function getResistor()
    {
        return $this->resistor;
    }

    public function setResistor(int $resistor = null)
    {
        $this->resistor = $resistor;

        return $this;
    }

    public function getPower()
    {
        return $this->power;
    }

    public function setPower(int $power = null)
    {
        $this->power = $power;

        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget(float $target = null)
    {
        $this->target = $target;

        return $this;
    }

    public function getTemperature()
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature = null)
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getT1()
    {
        return $this->t1;
    }

    public function setT1(float $t1 = null)
    {
        $this->t1 = (float) $t1;


        return $this;
    }

    public function getT2()
    {
        return $this->t2;
    }

    public function setT2(float $t2 = null)
    {
        $this->t2 = (float) $t2;


        return $this;
    }

    public function getPlanner()
    {
        return $this->planner;
    }

    public function setPlanner(Planner $planner)
    {
        $this->planner = $planner;

        return $this;
    }

    public function getData()
    {
        $data = parent::getData();
        $data['tempSensor'] = $this->getTempSensor();
        $data['power'] = $this->getPower();
        $data['resistor'] = $this->getResistor();
        $data['target'] = $this->getTarget();
        $data['temperature'] = $this->getTemperature();
        $data['t1'] = $this->getT1();
        $data['t2'] = $this->getT2();
        $data['planner'] = $this->getPlanner() ? $this->getPlanner()->getData() : null;

        return $data;
    }
}

