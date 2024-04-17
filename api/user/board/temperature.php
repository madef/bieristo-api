<?php

/**
 * Params:
 *  - token
 *  - boardId
 *  - sensor
 *  - from
 *  - to
 *
 * Response:
 *  - status: true success, false error
 *  - (pointList): List of points
 *  - (code): 1 [token has expired]
 *  - (reason)
 *  - (data): board data
 *
 *  Point:
 *  - time: YYYY-MM-DD HH:MM
 *  - value
 */

require('../../../config.php');
require('../../../vendor/autoload.php');

$userRepository = \Bieristo\Model\UserRepository::getInstance();

$request = new \Bieristo\Api\Request;

$response = new \Bieristo\Api\Response();

define('LIMIT_MINUTES', 7 * 24 * 60);
define('LIMIT_HOURS', 31 * 24 * 60);
define('MAX_INTERVAL', 12 * 31 * 24 * 60);

try {
    if ($request->empty('token')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing token'))->setTarget('token');
    }

    if ($request->empty('boardId')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing board id'))->setTarget('boardId');
    }

    if ($request->empty('sensor')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing sensor'))->setTarget('sensor');
    }

    if ($request->empty('from')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing from'))->setTarget('from');
    }

    if ($request->empty('to')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing to'))->setTarget('to');
    }

    $token = $request->get('token');
    $boardId = $request->get('boardId');
    $sensor = $request->get('sensor');
    $from = $request->get('from');
    $to = $request->get('to');

    $user = $userRepository->getUserByToken($token);

    if (!isset($user->getBoardList()[$boardId])) {
        throw new \Bieristo\Exception\NoSuchEntityException;
    }

    try {
        $dateTimeFrom = new \DateTime($from, new \DateTimeZone('GMT'));
    } catch (\Exception $e) {
        $exception = new \Bieristo\Exception\InvalidFormatException($e->getMessage());
        $exception->setTarget('from');

        throw $exception;
    }

    try {
        $dateTimeTo = new \DateTime($to, new \DateTimeZone('GMT'));
    } catch (\Exception $e) {
        $exception = new \Bieristo\Exception\InvalidFormatException($e->getMessage());
        $exception->setTarget('from');

        throw $exception;
    }
    $currentDate = new \DateTime(null, new \DateTimeZone('GMT'));


    if ($dateTimeFrom > $dateTimeTo) {
        $exception = new \Bieristo\Exception\InvalidFormatException('The interval between the two dates must be lower than one year.');
        $exception->setTarget('from');

        throw $exception;
    }

    $interval = $dateTimeTo->diff($dateTimeFrom);
    $diffWithBorne = $interval->format("%y") * 365 * 24 * 60; // years
    $diffWithBorne += $interval->format("%m") * 31 * 24 * 60; // months of 31 days
    $diffWithBorne += $interval->format("%d") * 24 * 60; // days
    $diffWithBorne += $interval->format("%h") * 60; // hours
    $diffWithBorne += $interval->format("%i");

    if ($diffWithBorne > MAX_INTERVAL) {
        $exception = new \Bieristo\Exception\InvalidFormatException('Interval between from and to must be lower than one year.');
        $exception->setTarget('from');

        throw $exception;
    }

    $interval = $dateTimeFrom->diff($currentDate);
    $diffWithCurrent = $interval->format("%y") * 365 * 24 * 60; // years
    $diffWithCurrent += $interval->format("%m") * 31 * 24 * 60; // months of 31 days
    $diffWithCurrent += $interval->format("%d") * 24 * 60; // days
    $diffWithCurrent += $interval->format("%h") * 60; // hours
    $diffWithCurrent += $interval->format("%i");

    if ($diffWithCurrent > LIMIT_HOURS || $diffWithBorne > LIMIT_HOURS) {
        $temperatureRepository = \Bieristo\Model\TemperatureDailyRepository::getInstance();
    } else if ($diffWithCurrent > LIMIT_MINUTES || $diffWithBorne > LIMIT_MINUTES) {
        $temperatureRepository = \Bieristo\Model\TemperatureHourlyRepository::getInstance();
    } else {
        $temperatureRepository = \Bieristo\Model\TemperatureRepository::getInstance();
    }

    $filters = [
        'userId' => $user->getId(),
        'boardId' => $boardId,
        'sensor' => (int) $sensor,
        'date' => [
            '$gte' => new \MongoDB\BSON\UTCDateTime($dateTimeFrom),
            '$lte' => new \MongoDB\BSON\UTCDateTime($dateTimeTo),
        ]
    ];

    $temperatureList = $temperatureRepository->getTemperatureList($filters, null, null, ['date' => 1]);
    $pointList = [];
    foreach ($temperatureList as $temperature) {
        $pointList[] = [
            'time' => $temperature->getDate()->format('c'),
            'value' => $temperature->getValue(),
        ];
    }

    $response->set('pointList', $pointList);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\InvalidFormatException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Board do not exist.');
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('code', 1);
    $response->set('reason', $e->getMessage());
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();
