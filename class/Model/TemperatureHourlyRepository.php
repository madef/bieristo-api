<?php

namespace Bieristo\Model;

class TemperatureHourlyRepository extends AbstractTemperatureRepository
{
    public function getMongoCollectionName()
    {
        return 'temperature_hourly';
    }

    protected function formatDate(\DateTime $date)
    {
        $hours = $date->format('H');
        $date->setTime($hours, 0, 0, 0);

        return $date;
    }

}
