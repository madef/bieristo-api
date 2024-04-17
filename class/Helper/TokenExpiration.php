<?php

namespace Bieristo\Helper;

class TokenExpiration
{
    const INTERVAL = 'PT1H';

    protected $datetime;

    public function __construct(\DateTime $datetime = null)
    {
        if (is_null($datetime)) {
            $datetime = new \DateTime(null, new \DateTimeZone('GMT'));
            $datetime->add(new \DateInterval(self::INTERVAL));
        }

        $this->datetime = $datetime;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function isValid()
    {
        $datetime = new \DateTime(null, new \DateTimeZone('GMT'));
        return $datetime < $this->datetime;
    }
}

