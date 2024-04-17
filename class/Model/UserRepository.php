<?php

namespace Bieristo\Model;

class UserRepository
{
    protected static $instance;
    protected $mongo;

    protected function __construct()
    {
        $this->mongo = \Bieristo\DbConnector\Mongo::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new UserRepository();
        }

        return self::$instance;
    }

    public function getUserList($filters, $pageSize = null, $pageNumber = null, $sorts = null)
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

        $rows = $this->mongo->find('user', $filters, $options);

        $list = [];
        foreach ($rows as $userData) {
            $list[] = $this->createUser($userData);
        }

        return $list;
    }

    public function getEmptyUser()
    {
        return $this->createUser([]);
    }

    public function getUserByEmailAndLostPasswordToken($email, $lostPasswordToken)
    {
        $userData = $this->mongo->findOne('user', ['email' => $email, 'lostPasswordToken' => $lostPasswordToken]);

        if (!$userData) {
            throw new \Bieristo\Exception\NoSuchEntityException;
        }
        $user = $this->createUser($userData);

        if (!(new \Bieristo\Helper\TokenExpiration($user->getLostPasswordTokenExpiration()))->isValid()) {
            throw new \Bieristo\Exception\TokenHasExpiredException('Link has expired try again');
        }

        return $user;
    }

    public function getUserByEmail($email)
    {
        $userData = $this->mongo->findOne('user', ['email' => $email]);

        if (!$userData) {
            throw new \Bieristo\Exception\NoSuchEntityException;
        }

        return $this->createUser($userData);
    }

    public function getUserByToken($token)
    {
        $userData = $this->mongo->findOne('user', ['token' => $token]);

        if (!$userData) {
            throw new \Bieristo\Exception\NoSuchEntityException;
        }

        $user = $this->createUser($userData);

        return $user;
    }

    protected function createUser($userData)
    {
        $user = new User;

        if (isset($userData->_id)) {
            $user->setId($userData->_id);
        }

        if (isset($userData->email)) {
            $user->setEmail($userData->email);
        }

        if (isset($userData->passwordHash)) {
            $user->setPasswordHash($userData->passwordHash);
        }

        if (isset($userData->lostPasswordToken)) {
            $user->setLostPasswordToken($userData->lostPasswordToken);
        }

        if (isset($userData->lostPasswordTokenExpiration)) {
            $user->setLostPasswordTokenExpiration($userData->lostPasswordTokenExpiration->toDateTime());
        }

        if (isset($userData->token)) {
            $user->setToken($userData->token);
        }

        if (isset($userData->defaultBoard)) {
            $user->setDefaultBoard($userData->defaultBoard);
        }

        if (isset($userData->boardList)) {
            foreach ($userData->boardList as $boardId => $boardData) {
                $board = new Board;

                if (isset($boardData->name)) {
                    $board->setName($boardData->name);
                }

                if (isset($boardData->unitList)) {
                    foreach ($boardData->unitList as $key => $unitData) {
                        switch ($unitData->type) {
                            case 'HeatUnit':
                                $unit = new HeatUnit;
                                $unit->setTempSensor($unitData->tempSensor);
                                $unit->setResistor($unitData->resistor);
                                $unit->setPower($unitData->power);
                                $unit->setTarget($unitData->target);
                                $unit->setTemperature($unitData->temperature);
                                $unit->setT1($unitData->t1);
                                $unit->setT2($unitData->t2);

                                $planner = new Planner;
                                if (isset($unitData->planner)) {
                                    $planner->setCurrentTime($unitData->planner->currentTime);
                                    $planner->setCurrentStatus($unitData->planner->currentStatus);
                                    $planner->setCurrentStep($unitData->planner->currentStep);
                                    $planner->setAskStatus($unitData->planner->askStatus);
                                    $planner->setAskGoTo($unitData->planner->askGoTo);
                                    foreach ($unitData->planner->stepList as $stepData) {
                                        $step = new PlannerStep;
                                        $step->setAction($stepData->action);
                                        if (!empty($stepData->target)) {
                                            $step->setTarget($stepData->target);
                                        }
                                        if (!empty($stepData->power)) {
                                            $step->setPower($stepData->power);
                                        }
                                        $step->setDuration($stepData->duration);
                                        if (isset($stepData->onFinishGoNext)) {
                                            $step->setOnFinishGoNext($stepData->onFinishGoNext);
                                        }

                                        $planner->addStep($step);
                                    }
                                }
                                $unit->setPlanner($planner);

                                break;
                            case 'FermenterUnit':
                                $unit = new FermenterUnit;
                                $unit->setTempSensor($unitData->tempSensor);
                                $unit->setCool($unitData->cool);
                                $unit->setHeat($unitData->heat);
                                $unit->setTarget($unitData->target);
                                $unit->setTemperature($unitData->temperature);
                                $unit->setT1($unitData->t1);
                                $unit->setT2($unitData->t2);
                                $unit->setForce((int) @$unitData->force);
                                break;
                        }
                        $unit->setName($unitData->name);
                        if (isset($unitData->state)) {
                            $unit->setState($unitData->state);
                        } else {
                            $unit->setState(0);
                        }

                        $unit->setMode($unitData->mode);

                        $board->addUnit($key, $unit);
                    }
                }

                if (isset($boardData->timer)) {
                    $timer = new Timer;
                    $timer->setStart($boardData->timer->start);
                    $timer->setDuration($boardData->timer->duration);
                    $board->setTimer($timer);
                }

                $user->addBoard($boardId, $board);
            }
        }

        return $user;
    }

    public function save(User $user)
    {
        if ($user->getId()) {
            $this->mongo->update('user', ['_id' => $user->getId()], $user->getData());
        } else {
            $this->mongo->add('user', $user->getData());
        }
    }
}
