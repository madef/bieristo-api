<?php

namespace Bieristo\Helper;

class TokenGenerator
{
    protected $token;

    public function __construct()
    {
        $this->token = hash('sha256', uniqid('', true));
    }

    public function __toString()
    {
        return $this->token;
    }
}
