<?php

namespace Bieristo\Model;

class TemperatureRepository extends AbstractTemperatureRepository
{
    public function getMongoCollectionName()
    {
        return 'temperature';
    }

    protected function formatDate(\DateTime $date)
    {
        $hours = $date->format('H');
        $minutes = $date->format('i');
        $date->setTime($hours, $minutes, 0, 0);

        return $date;
    }

    public function addEntry(\MongoDB\BSON\ObjectId $userId, string $boardId, int $sensor, float $value)
    {
        // Add minute data
        $temperature = $this->getTemperature($userId, $boardId, $sensor);
        $temperature->addEntry($value);
        $this->save($temperature);

        // Add hourly data
        $temperatureHourlyRepository = TemperatureHourlyRepository::getInstance();
        $temperatureHourly = $temperatureHourlyRepository->getTemperature($userId, $boardId, $sensor);
        $temperatureHourly->addEntry($value);
        $temperatureHourlyRepository->save($temperatureHourly);

        // Add daily data
        $temperatureDailyRepository = TemperatureDailyRepository::getInstance();
        $temperatureDaily = $temperatureDailyRepository->getTemperature($userId, $boardId, $sensor);
        $temperatureDaily->addEntry($value);
        $temperatureDailyRepository->save($temperatureDaily);

        // Clean outdated data
        $date = new \DateTime(null, new \DateTimeZone('GMT'));
        $date->sub(new \DateInterval('P7D'));
        $filters = [
            'date' => [
                '$lt' => new \MongoDB\BSON\UTCDateTime($date),
            ]
        ];
        $this->mongo->delete($this->getMongoCollectionName(), $filters);

        $date = new \DateTime(null, new \DateTimeZone('GMT'));
        $date->sub(new \DateInterval('P31D'));
        $filters = [
            'date' => [
                '$lt' => new \MongoDB\BSON\UTCDateTime($date),
            ]
        ];
        $this->mongo->delete($temperatureDailyRepository->getMongoCollectionName(), $filters);
    }
}
