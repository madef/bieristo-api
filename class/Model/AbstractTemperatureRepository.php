<?php

namespace Bieristo\Model;

abstract class AbstractTemperatureRepository
{
    protected static $instance = [];
    protected $mongo;

    public abstract function getMongoCollectionName();

    protected abstract function formatDate(\DateTime $date);

    protected function __construct()
    {
        $this->mongo = \Bieristo\DbConnector\Mongo::getInstance();
    }

    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }

        return self::$instance[$class];
    }


    public function getTemperatureList($filters, $pageSize = null, $pageNumber = null, $sorts = null)
    {
        $options = [];
        if (!is_null($sorts)) {
            $options['sort'] = $sorts;
        }
        if (!is_null($pageSize)) {
            $options['limit'] = (int) $pageSize;

            if (!is_null($pageNumber)) {
                $pageNumber = $pageNumber ?: 1;
                $options['skip'] = $pageSize * ($pageNumber - 1);
            }
        }

        $rows = $this->mongo->find($this->getMongoCollectionName(), $filters, $options);

        $list = [];
        foreach ($rows as $temperatureData) {
            $list[] = $this->createTemperature($temperatureData);
        }

        return $list;
    }

    public function getEmptyTemperature()
    {
        return $this->createTemperature([]);
    }

    public function getTemperature(\MongoDB\BSON\ObjectId $userId, string $boardId, int $sensor, \DateTime $date = null)
    {
        if (is_null($date)) {
            $date = new \DateTime(null, new \DateTimeZone('GMT'));
        }

        $filters = [
            'boardId' => $boardId,
            'userId' => $userId,
            'sensor' => $sensor,
            'date' => new \MongoDB\BSON\UTCDateTime($this->formatDate($date)),
        ];

        $list = $this->getTemperatureList($filters, 1, 1);


        if (empty($list)) {
            return $this->getEmptyTemperature()
                ->setUserId($userId)
                ->setDate($date)
                ->setBoardId($boardId)
                ->setSensor($sensor);

            return $temperature;
        }

        return current($list);
    }

    protected function createTemperature($data)
    {
        $temperature = new Temperature;

        if (isset($data->_id)) {
            $temperature->setId($data->_id);
        }

        if (isset($data->value)) {
            $temperature->setValue($data->value);
        }

        if (isset($data->userId)) {
            $temperature->setUserId($data->userId);
        }

        if (isset($data->boardId)) {
            $temperature->setBoardId($data->boardId);
        }

        if (isset($data->sensor)) {
            $temperature->setSensor((int) $data->sensor);
        }

        if (isset($data->entryCount)) {
            $temperature->setEntryCount((int) $data->entryCount);
        }

        if (isset($data->date)) {
            $temperature->setDate($this->formatDate($data->date->toDateTime()));
        } else {
            $temperature->setDate($this->formatDate(new \DateTime(null, new \DateTimeZone('GMT'))));
        }

        return $temperature;
    }

    public function save(Temperature $temperature)
    {
        if ($temperature->getId()) {
            $this->mongo->update($this->getMongoCollectionName(), ['_id' => $temperature->getId()], $temperature->getData());
        } else {
            $this->mongo->add($this->getMongoCollectionName(), $temperature->getData());
        }
    }
}
