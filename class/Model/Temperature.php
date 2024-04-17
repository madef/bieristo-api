<?php

namespace Bieristo\Model;

class Temperature
{
    /**
     * Schema
     *
     * {
     *     value: (float),
     *     userId: (string),
     *     boardId: (string),
     *     sensor: (int),
     *     date: (datetime)
     *     entryCount: (int)
     * }
     */

    protected $id;
    protected $userId;
    protected $boardId;
    protected $sensor;
    protected $date;
    protected $value;
    protected $entryCount = 0;

    public function getId()
    {
        return $this->id;
    }

    public function setId(\MongoDB\BSON\ObjectID $id)
    {
        $this->id = $id;

        return $this;
    }

    public function setEntryCount(int $entryCount)
    {
        $this->entryCount = $entryCount;

        return $this;
    }

    public function getEntryCount()
    {
        return $this->entryCount;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId(\MongoDB\BSON\ObjectId $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(float $value)
    {
        $this->value = $value;

        return $this;
    }

    public function addEntry(float $value)
    {
        $newValue = ($value + $this->getValue() * $this->getEntryCount()) / ($this->getEntryCount() + 1);

        $this->setEntryCount($this->getEntryCount() + 1);
        $this->setValue($newValue);

        return $this;
    }

    public function getBoardId()
    {
        return $this->boardId;
    }

    public function setBoardId(string $boardId)
    {
        $this->boardId = $boardId;

        return $this;
    }

    public function getSensor()
    {
        return $this->sensor;
    }

    public function setSensor(int $sensor)
    {
        $this->sensor = $sensor;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\Datetime $date)
    {
        $this->date = $date;

        return $this;
    }

    public function getData()
    {
        return [
            'value' => $this->getValue(),
            'userId' => $this->getUserId(),
            'boardId' => $this->getBoardId(),
            'sensor' => $this->getSensor(),
            'date' => new \MongoDB\BSON\UTCDateTime($this->getDate()),
            'entryCount' => $this->getEntryCount(),
        ];
    }
}
