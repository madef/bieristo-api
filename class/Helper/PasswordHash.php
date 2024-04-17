<?php

namespace Bieristo\Helper;

class PasswordHash
{
    protected $passwordHash;

    public function __construct($password)
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function __toString()
    {
        return $this->passwordHash;
    }
}
