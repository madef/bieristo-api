<?php

/**
 * Params:
 *  - token
 *  - boardId
 *  - (newBoardId)
 *  - (name)
 *  - (timer.start)
 *  - (timer.duration)
 *  - (unitList)
 *
 * Units can be:
 *  - HeatUnit
 *  - BoitUnit
 *  - FermenterUnit
 *
 * HeatUnit params:
 *  - mode
 *  - resistor
 *  - power
 *  - target
 *  - temperature
 *  - t1
 *  - t2
 *
 * FermenterUnit params:
 *  - mode
 *  - tempSensor
 *  - heat
 *  - cool
 *  - target
 *  - temperature
 *
 * Response:
 *  - status: true success, false error
 *  - (code): 1 [token has expired]
 *  - (reason)
 *  - (data): customer data
 *
 * Response:
 *  - status: true success, false error
 *  - (code): 1 [token has expired]
 *  - (reason)
 */

require('../../../config.php');
require('../../../vendor/autoload.php');

$userRepository = \Bieristo\Model\UserRepository::getInstance();

$request = new \Bieristo\Api\Request;

$response = new \Bieristo\Api\Response();

try {
    if ($request->empty('token')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing token'))->setTarget('token');
    }

    if ($request->empty('boardId')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing board id'))->setTarget('boardId');
    }

    $token = $request->get('token');
    $boardId = $request->get('boardId');

    $user = $userRepository->getUserByToken($token);

    if (!isset($user->getBoardList()[$boardId])) {
        throw new \Bieristo\Exception\NoSuchEntityException;
    }

    $board = $user->getBoardList()[$boardId];

    if (!$request->empty('name')) {
        $board->setName($request->get('name'));
    }

    if (!$request->empty('newBoardId') && $request->get('newBoardId') != $boardId) {
        $newBoardId = $request->get('newBoardId');

        if (!preg_match('/^[a-z0-9_-]+$/i', $newBoardId)) {
            throw (
                new \Bieristo\Exception\InvalidFormatException(
                    'Board id must be a string composed with lettre, numeric, underscore (_) or hyphen (-). Spaces and other chars are forbiden.'
                )
            )->setTarget('newBoardId');
        }

        $user->removeBoard($boardId);
        $user->addBoard($newBoardId, $board);
    }

    if (!$request->empty('timer')) {
        $timerData = $request->get('timer');

        if (!$board->getTimer()) {
            $board->setTimer(new \Bieristo\Model\Timer);
        }

        $timer = $board->getTimer();

        if (isset($timerData['start'])) {
            if (false === filter_var($timerData['start'], FILTER_VALIDATE_INT) || $timerData['start'] < 0) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid date format.'))->setTarget('timer.start');
            }

            if ($timerData['start'] == 0) {
                $timer->setStart(0);
            } else {
                $timer->setStart($timerData['start']);
            }
        }

        if (isset($timerData['duration'])) {
            if (false === filter_var($timerData['duration'], FILTER_VALIDATE_INT) || $timerData['duration'] < 0 || $timerData['duration'] > 999) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid duration.'))->setTarget('timer.duration');
            }

            $timer->setDuration((int) $timerData['duration']);
        }
    }

    if ($request->isset('unitList')) {
        $unitList = $board->getUnitList();

        foreach ($request->get('unitList') as $key => $unitData) {
            if (isset($unitData['deleted']) && $unitData['deleted']) {
                unset($unitList[$key]);
                continue;
            }

            if (!isset($unitData['type']) && isset($unitList[$key])) {
                $unitData['type'] = $unitList[$key]->getType();
            }

            switch ($unitData['type']) {
                case 'HeatUnit':
                    processHeatUnit($unitList, $key, $unitData, $user, $boardId);
                    break;
                case 'FermenterUnit':
                    processFermentUnit($unitList, $key, $unitData, $user, $boardId);
                    break;
            }
        }
        $board->setUnitList($unitList);
    }

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Board do not exist.');
} catch (\Bieristo\Exception\EntityAlreadyExistsException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', 'token');
} catch (\Bieristo\Exception\InvalidFormatException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('code', 1);
    $response->set('reason', $e->getMessage());
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem'.$e->getMessage());
}

$response->send();

function processHeatUnit(&$unitList, $key, $unitData, $user, $boardId) {
    $temperatureRepository = \Bieristo\Model\TemperatureRepository::getInstance();
    if (!isset($unitList[$key])) {
        $unitList[$key] = new \Bieristo\Model\HeatUnit;
    }

    $unit = $unitList[$key];

    if (isset($unitData['name'])) {
        $unit->setName($unitData['name']);
    }

    if (!empty($unitData['mode'])) {
        if (!in_array($unitData['mode'], ['temperature', 'power', 'off'])) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid mode.'))->setTarget('unit.'.$key.'mode');
        }
        $unit->setMode($unitData['mode']);
    }

    if (!empty($unitData['tempSensor'])) {
        if (false === filter_var($unitData['tempSensor'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'.tempSensor');
        }

        $unit->setTempSensor($unitData['tempSensor']);
    }

    if (!empty($unitData['resistor'])) {
        if (false === filter_var($unitData['resistor'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'resistor');
        }

        $unit->setResistor($unitData['resistor']);
    }

    if (!empty($unitData['power'])) {
        if ($unitData['power'] == -1) {
            $unit->setPower(null);
        } else {
            if (!is_numeric($unitData['power']) || (int) $unitData['power'] != $unitData['power'] || $unitData['power'] < 0 || $unitData['power'] > 100) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'power');
            }

            $unit->setPower($unitData['power']);
        }
    }

    if (isset($unitData['state'])) {
        if (false === filter_var($unitData['state'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'state');
        }

        $unit->setState($unitData['state']);
    }

    if (isset($unitData['target'])) {
        if ($unitData['target'] == -1) {
            $unit->setTarget(null);
        } else {
            if (false === filter_var($unitData['target'], FILTER_VALIDATE_FLOAT) || $unitData['target'] < 0 || $unitData['target'] > 100) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'target');
            }

            $unit->setTarget($unitData['target']);
        }
    }

    if (!empty($unitData['temperature']) || isset($unitData['temperature']) && $unitData['temperature'] === '0') {
        if (false === filter_var($unitData['temperature'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'temperature');
        }

        $unit->setTemperature($unitData['temperature']);

        if (!empty($unit->getTempSensor())) {
            $temperatureRepository->addEntry($user->getId(), $boardId, $unit->getTempSensor(), $unitData['temperature']);
        }
    }

    if (!empty($unitData['t1']) || isset($unitData['t1']) && $unitData['t1'] === '0') {
        if (false === filter_var($unitData['t1'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'t1');
        }

        $unit->setT1($unitData['t1']);
    }

    if (!empty($unitData['t2']) || isset($unitData['t2']) && $unitData['t2'] === '0') {
        if (false === filter_var($unitData['t2'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'t2');
        }

        $unit->setT2($unitData['t2']);
    }

    if (!empty($unitData['planner']) || $unitData['planner'] === '0') {
        if (isset($unitData['planner']['askStatus'])) {
            if (false === filter_var($unitData['planner']['askStatus'], FILTER_VALIDATE_INT) || $unitData['planner']['askStatus'] > 5 || $unitData['planner']['askStatus'] < 0) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.askStatus');
            }

            $unit->getPlanner()->setAskStatus((int) $unitData['planner']['askStatus']);
        }

        if (isset($unitData['planner']['askGoTo'])) {
            if (false === filter_var($unitData['planner']['askGoTo'], FILTER_VALIDATE_INT) || $unitData['planner']['askGoTo'] < -1) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.askGoTo');
            }

            $unit->getPlanner()->setAskGoTo((int) $unitData['planner']['askGoTo']);

            if ($unit->getPlanner()->getCurrentStatus() === 0) {
                $unit->getPlanner()->setAskStatus(1);
            }
        }

        if (isset($unitData['planner']['currentTime'])) {
            if (false === filter_var($unitData['planner']['currentTime'], FILTER_VALIDATE_INT)) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.currentTime');
            }

            $unit->getPlanner()->setCurrentTime((int) $unitData['planner']['currentTime']);
        }

        if (isset($unitData['planner']['currentStep'])) {
            if (false === filter_var($unitData['planner']['currentStep'], FILTER_VALIDATE_INT)) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.currentStep');
            }

            $unit->getPlanner()->setCurrentStep((int) $unitData['planner']['currentStep']);
        }

        if (isset($unitData['planner']['currentStatus'])) {
            if (false === filter_var($unitData['planner']['currentStatus'], FILTER_VALIDATE_INT) || $unitData['planner']['currentStatus'] > 5 || $unitData['planner']['currentStatus'] < 0) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.currentStatus');
            }

            $unit->getPlanner()->setCurrentStatus((int) $unitData['planner']['currentStatus']);
        }

        if ($unit->getPlanner()->getCurrentStatus() === $unit->getPlanner()->getAskStatus()
            || $unit->getPlanner()->getCurrentStatus() === 5 && $unit->getPlanner()->getAskStatus() === 1
            || $unit->getPlanner()->getCurrentStatus() === 0 && $unit->getPlanner()->getAskStatus() === 3
        ) {
            $unit->getPlanner()->setAskStatus(0);
        }

        if ($unit->getPlanner()->getCurrentStep() === $unit->getPlanner()->getAskGoTo()) {
            $unit->getPlanner()->setAskGoTo(-1);
        }

        if (isset($unitData['planner']['stepList'])) {
            $unit->getPlanner()->dropStepList();
            if (!empty($unitData['planner']['stepList'])) {
                foreach ($unitData['planner']['stepList'] as $stepNumber => $stepData) {
                    $step = new \Bieristo\Model\PlannerStep;

                    if (false === filter_var($stepData['action'], FILTER_VALIDATE_INT)) {
                        throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.stepList.'.$stepNumber.'.action');
                    }
                    $step->setAction((int) $stepData['action']);

                    if (false === filter_var($stepData['duration'], FILTER_VALIDATE_INT)) {
                        throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.stepList.'.$stepNumber.'.duration');
                    }
                    $step->setDuration($stepData['duration']);

                    if (!empty($stepData['power']) && false === filter_var($stepData['power'], FILTER_VALIDATE_INT)) {
                        throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.stepList.'.$stepNumber.'.power');
                    }
                    if (!empty($stepData['power'])) {
                        $step->setPower($stepData['power']);
                    }

                    if (!empty($stepData['target']) && false === filter_var($stepData['target'], FILTER_VALIDATE_INT)) {
                        throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.stepList.'.$stepNumber.'.target');
                    }
                    if (!empty($stepData['target'])) {
                        $step->setTarget($stepData['target']);
                    }

                    if (false === filter_var($stepData['onFinishGoNext'], FILTER_VALIDATE_INT)) {
                        throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'planner.stepList.'.$stepNumber.'.onFinishGoNext');
                    }
                    $step->setOnFinishGoNext((int) $stepData['onFinishGoNext']);

                    $unit->getPlanner()->addStep($step);
                }
            }
        }
    }
}

function processFermentUnit(&$unitList, $key, $unitData, $user, $boardId) {
    $temperatureRepository = \Bieristo\Model\TemperatureRepository::getInstance();
    if (!isset($unitList[$key])) {
        $unitList[$key] = new \Bieristo\Model\FermenterUnit;
    }

    $unit = $unitList[$key];

    if (isset($unitData['name'])) {
        $unit->setName($unitData['name']);
    }

    if (!empty($unitData['mode'])) {
        if (!in_array($unitData['mode'], ['heat', 'cool', 'temperature', 'off'])) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid mode.'))->setTarget('unit.'.$key.'mode');
        }
        $unit->setMode($unitData['mode']);
    }

    if (!empty($unitData['tempSensor'])) {
        if (false === filter_var($unitData['tempSensor'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'tempSensor');
        }

        $unit->setTempSensor($unitData['tempSensor']);
    }

    if (!empty($unitData['cool'])) {
        if (false === filter_var($unitData['cool'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'cool');
        }

        $unit->setCool($unitData['cool']);
    }

    if (isset($unitData['state'])) {
        if (false === filter_var($unitData['state'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'state');
        }

        $unit->setState($unitData['state']);
    }

    if (!empty($unitData['heat'])) {
        if (false === filter_var($unitData['heat'], FILTER_VALIDATE_INT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Integer required.'))->setTarget('unit.'.$key.'heat');
        }

        $unit->setHeat($unitData['heat']);
    }

    if (isset($unitData['target'])) {
        if ($unitData['target'] === '-1') {
            $unit->setTarget(null);
        } else {
            if (false === filter_var($unitData['target'], FILTER_VALIDATE_FLOAT) || $unitData['target'] < 0 || $unitData['target'] > 100) {
                throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'target');
            }

            $unit->setTarget($unitData['target']);
        }
    }

    if (!empty($unitData['temperature']) || isset($unitData['temperature']) && $unitData['temperature'] === '0') {
        if (false === filter_var($unitData['temperature'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'temperature');
        }

        $unit->setTemperature($unitData['temperature']);

        if (!empty($unit->getTempSensor())) {
            $temperatureRepository->addEntry($user->getId(), $boardId, $unit->getTempSensor(), $unitData['temperature']);
        }
    }

    if (!empty($unitData['t1']) || isset($unitData['t1']) && $unitData['t1'] === '0') {
        if (false === filter_var($unitData['t1'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'t1');
        }

        $unit->setT1($unitData['t1']);
    }

    if (!empty($unitData['t2']) || isset($unitData['t2']) && $unitData['t2'] === '0') {
        if (false === filter_var($unitData['t2'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'t2');
        }

        $unit->setT2($unitData['t2']);
    }

    if (!empty($unitData['force']) || isset($unitData['force']) && $unitData['force'] === '0') {
        if (false === filter_var($unitData['force'], FILTER_VALIDATE_FLOAT)) {
            throw (new \Bieristo\Exception\InvalidFormatException('Invalid format. Numeric required.'))->setTarget('unit.'.$key.'force');
        }

        $unit->setForce($unitData['force']);
    }
}
