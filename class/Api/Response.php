<?php

namespace Bieristo\Api;

class Response
{
    protected $data = [];

    public function __construct()
    {
    }

    public function set($param, $value)
    {
        $this->data[$param] = $value;

        return $this;
    }

    public function send()
    {
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($this->data);
        exit;
    }
}

