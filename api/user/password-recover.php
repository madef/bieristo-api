<?php

/**
 * Params:
 *  - email
 *  - token
 *
 * Response:
 *  - status: true success, false error
 *  - (reason)
 */

require('../../config.php');
require('../../vendor/autoload.php');

$userRepository = \Bieristo\Model\UserRepository::getInstance();

$request = new \Bieristo\Api\Request;

$response = new \Bieristo\Api\Response();

try {
    if ($request->empty('email')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing email'))->setTarget('email');
    }

    if ($request->empty('token')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing token'))->setTarget('token');
    }

    if ($request->empty('password')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing password'))->setTarget('password');
    }

    $email = $request->get('email');
    $token = $request->get('token');
    $password = $request->get('password');

    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        throw (
            new \Bieristo\Exception\InvalidFormatException(
                'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.'
            )
        )->setTarget('password');
    }

    $user = $userRepository->getUserByEmailAndLostPasswordToken($email, $token);

    $user->setPasswordHash(new \Bieristo\Helper\PasswordHash($password));
    $user->setLostPasswordToken('');

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Invalid link. Ask a new password.');
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
} catch (\Bieristo\Exception\InvalidFormatException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();
