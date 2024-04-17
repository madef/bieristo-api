<?php

namespace Bieristo\Helper;

class PasswordValidation
{
    public function __construct($password, $target = 'password')
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            throw (
                new \Bieristo\Exception\InvalidFormatException(
                    'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.'
                )
            )->setTarget($target);
        }
    }
}
