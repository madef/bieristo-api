<?php

namespace Bieristo\Api;

class Request
{
    protected $paramList = [];

    public function __construct()
    {
        $this->paramList = array_merge($_GET, $_POST);
    }

    public function get($param, $default = null)
    {
        return empty($this->paramList[$param]) ? $default : $this->paramList[$param];
    }

    public function isset($param)
    {
        return isset($this->paramList[$param]);
    }

    public function empty($param)
    {
        return empty($this->paramList[$param]);
    }
}
