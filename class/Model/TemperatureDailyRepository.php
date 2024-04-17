<?php

namespace Bieristo\Model;

class TemperatureDailyRepository extends AbstractTemperatureRepository
{
    public function getMongoCollectionName()
    {
        return 'temperature_daily';
    }

    protected function formatDate(\DateTime $date)
    {
        $date->setTime(0, 0, 0, 0);

        return $date;
    }

}
