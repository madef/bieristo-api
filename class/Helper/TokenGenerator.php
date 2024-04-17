<?php

namespace Bieristo\Helper;

class TokenGenerator
{
    protected $token;

    public function __construct()
    {
        $this->token = hash('sha256', uniqid(null, true));
    }

    public function __toString()
    {
        return $this->token;
    }
}
