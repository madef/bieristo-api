<?php

namespace Bieristo\Model;

class Planner
{
    const STATUS_STOPPED = 0;
    const STATUS_RUNNING = 1;
    const STATUS_PAUSE = 2;
    const STATUS_RESET = 3;
    const STATUS_GO_TO = 4;
    const STATUS_WAIT_TARGET = 5;

    protected $currentTime = 0;
    protected $currentStep = 0;
    protected $currentStatus = self::STATUS_STOPPED;
    protected $askStatus = self::STATUS_STOPPED;
    protected $askGoTo = -1;
    protected $stepList = [];

    public function setCurrentTime(int $time)
    {
        $this->currentTime = $time;

        return $this;
    }

    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    public function setCurrentStep(int $step)
    {
        $this->currentStep = $step;

        return $this;
    }

    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    public function setCurrentStatus(int $status)
    {
        $this->currentStatus = $status;

        return $this;
    }

    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    public function setAskStatus(int $status)
    {
        $this->askStatus = $status;

        return $this;
    }

    public function getAskStatus()
    {
        return $this->askStatus;
    }

    public function setAskGoTo(int $goto)
    {
        $this->askGoTo= $goto;

        return $this;
    }

    public function getAskGoTo()
    {
        return $this->askGoTo;
    }

    public function addStep(PlannerStep $step)
    {
        $this->stepList[] = $step;

        return $this;
    }

    public function dropStepList()
    {
        $this->stepList = [];

        return $this;
    }

    public function getStepList()
    {
        return $this->stepList;
    }

    public function getData()
    {
        $stepList = [];
        foreach ($this->getStepList() as $step) {
            $stepList[] = $step->getData();
        }

        return [
            'currentTime' => $this->getCurrentTime(),
            'currentStep' => $this->getCurrentStep(),
            'currentStatus' => $this->getCurrentStatus(),
            'askStatus' => $this->getAskStatus(),
            'askGoTo' => $this->getAskGoTo(),
            'stepList' => $stepList,
        ];
    }
}
