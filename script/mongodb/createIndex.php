<?php

require('./config.php');
require('./vendor/autoload.php');

$mongo = \Bieristo\DbConnector\Mongo::getInstance();

$mongo->createIndex(
    'temperature',
    'temperature_userId_boardId_sensor_date',
    [
        'userId' => 1,
        'boardId' => 1,
        'sensor' => 1,
        'date' => 1,
    ],
    []
);

$mongo->createIndex(
    'temperature_hourly',
    'temperature_userId_boardId_sensor_date',
    [
        'userId' => 1,
        'boardId' => 1,
        'sensor' => 1,
        'date' => 1,
    ],
    []
);

$mongo->createIndex(
    'temperature_daily',
    'temperature_userId_boardId_sensor_date',
    [
        'userId' => 1,
        'boardId' => 1,
        'sensor' => 1,
        'date' => 1,
    ],
    []
);

$mongo->createIndex(
    'user',
    'user_token',
    ['token' => 1],
    []
);

$mongo->createIndex(
    'user',
    'user_email',
    ['email' => 1],
    []
);
