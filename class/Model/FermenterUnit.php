<?php

namespace Bieristo\Model;

class FermenterUnit extends Unit
{
    protected $tempSensor;
    protected $cool;
    protected $heat;
    protected $target;
    protected $temperature;
    protected $force;
    protected $t1 = 1;
    protected $t2 = 1;

    public function getTempSensor()
    {
        return $this->tempSensor;
    }

    public function setTempSensor(int $tempSensor = null)
    {
        $this->tempSensor = $tempSensor;

        return $this;
    }

    public function getCool()
    {
        return $this->cool;
    }

    public function setCool(int $cool = null)
    {
        $this->cool = $cool;

        return $this;
    }

    public function getHeat()
    {
        return $this->heat;
    }

    public function setHeat(int $heat = null)
    {
        $this->heat = $heat;

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

    public function getForce()
    {
        return $this->force;
    }

    public function setForce(int $force)
    {
        $this->force = (int) $force;


        return $this;
    }

    public function getData()
    {
        $data = parent::getData();
        $data['tempSensor'] = $this->getTempSensor();
        $data['cool'] = $this->getCool();
        $data['heat'] = $this->getHeat();
        $data['target'] = $this->getTarget();
        $data['temperature'] = $this->getTemperature();
        $data['t1'] = $this->getT1();
        $data['t2'] = $this->getT2();
        $data['force'] = $this->getForce();

        return $data;
    }
}


