<?php

/**
 * Params:
 *  - token
 *  - password
 *  - (newPassword)
 *  - (defaultBoard)
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
    if ($request->empty('token')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing token'))->setTarget('token');
    }

    if ($request->empty('email')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing email'))->setTarget('email');
    }

    if ($request->empty('password')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing password'))->setTarget('password');
    }

    $email = $request->get('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw (new \Bieristo\Exception\InvalidFormatException('Invalid email format'))->setTarget('email');
    }

    $token = $request->get('token');

    $user = $userRepository->getUserByToken($token);

    if (!password_verify($request->get('password'), $user->getPasswordHash())) {
        throw new \Bieristo\Exception\InvalidPasswordException();
    }

    $user->setEmail($request->get('email'));

    if (!$request->empty('newPassword')) {
        $newPassword = $request->get('newPassword');
        new \Bieristo\Helper\PasswordValidation($newPassword, 'newPassword');
        $user->setPasswordHash(new \Bieristo\Helper\PasswordHash($newPassword));
    }

    if ($request->isset('defaultBoard')) {
        $user->setDefaultBoard($request->get('defaultBoard'));
    }

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\InvalidPasswordException $e) {
    $response->set('status', false);
    $response->set('reason', 'Invalid password');
    $response->set('target', 'password');
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Invalid token.');
    $response->set('code', 1);
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('code', 1);
    $response->set('reason', $e->getMessage());
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}


$response->send();
