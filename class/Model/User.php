<?php

namespace Bieristo\Model;

class User
{
    /**
     * Schema
     *
     * {
     *     email: (string),
     *     passwordHash: (string),
     *     lostPasswordToken: (string),
     *     token: (string),
     *     defaultBoard: (string),
     *     boardList: {
     *         (string): { // Token
     *             name: (string),
     *             sparge: {
     *                 tempSensor: (int), // Pin number
     *                 resistor: (int), // Pin number
     *                 power: (int), // 0-100
     *                 target: (float), // 0-100, 0: off
     *                 temperature: (float), // 0-100
     *                 t1: (float), // 0-100 real power, is 100% if temperature < target - t1, (target - temperature) / t1 if temperature < target - t2, 0 is other case
     *                 t2: (float), // 0-100
     *             }
     *             mashing: {
     *                 tempSensor: (int), // Pin number
     *                 resistor: (int), // Pin number
     *                 power: (int), // 0-100
     *                 target: (float), // 0-100, 0: off
     *                 temperature: (float), // 0-100
     *                 t1: (float), // 0-100 real power, is 100% if temperature < target - t1, (target - temperature) / t1 if temperature < target - t2, 0 is other case
     *                 t2: (float), // 0-100
     *             }
     *             fermenter: {
     *                 tempSensor: (int), // Pin number
     *                 cool: (int), // Pin number
     *                 heat: (int), // Pin number
     *                 target: (float), // 0-100, 0: off
     *                 temperature: (float), // 0-100
     *                 t1: (float), // 0-100 cool temperature trigger
     *                 t2: (float), // 0-100 heat temperature trigger
     *             }
     *         }
     *     }
     * }
     */

    protected $id;
    protected $email;
    protected $passwordHash;
    protected $lostPasswordToken;
    protected $lostPasswordTokenExpiration;
    protected $token;
    protected $defaultBoard;
    protected $boardList = [];

    public function getId()
    {
        return $this->id;
    }

    public function setId(\MongoDB\BSON\ObjectID $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash)
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getDefaultBoard()
    {
        return $this->defaultBoard;
    }

    public function setDefaultBoard(string $defaultBoard)
    {
        $this->defaultBoard = $defaultBoard;

        return $this;
    }

    public function getLostPasswordToken()
    {
        return $this->lostPasswordToken;
    }

    public function setLostPasswordToken(string $lostPasswordToken)
    {
        $this->lostPasswordToken = $lostPasswordToken;

        return $this;
    }

    public function getLostPasswordTokenExpiration()
    {
        return $this->lostPasswordTokenExpiration;
    }

    public function setLostPasswordTokenExpiration(\DateTime $lostPasswordTokenExpiration)
    {
        $this->lostPasswordTokenExpiration = $lostPasswordTokenExpiration;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token= $token;

        return $this;
    }

    public function addBoard(string $boardId, Board $board)
    {
        if (isset($this->boardList[$boardId])) {
            throw new \Bieristo\Exception\EntityAlreadyExistsException('A board with the save token already exists');
        }

        $this->boardList[$boardId] = $board;

        return $this;
    }

    public function removeBoard($boardId)
    {
        unset($this->boardList[$boardId]);

        return $this;
    }

    public function getBoardList()
    {
        return $this->boardList;
    }

    public function getData($secure = true)
    {
        $dataBoardList = [];

        foreach ($this->getBoardList() as $boardId => $board) {
            $dataBoardList[$boardId] = $board->getData();
        }

        $return = [
            'email' => $this->getEmail(),
            'defaultBoard' => $this->getDefaultBoard(),
            'boardList' => $dataBoardList,
        ];

        if ($secure) {
            $return['passwordHash'] = $this->getPasswordHash();
            $return['lostPasswordToken'] = $this->getLostPasswordToken();
            $return['lostPasswordTokenExpiration'] = new \MongoDB\BSON\UTCDateTime($this->getLostPasswordTokenExpiration());
            $return['token'] = $this->getToken();
        }

        return $return;
    }
}



