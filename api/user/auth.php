<?php

/**
 * Params:
 *  - email
 *  - password
 *
 * Response:
 *  - status: true success, false error
 *  - (token)
 *  - (target)
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

    if ($request->empty('password')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing password'))->setTarget('password');
    }

    $email = $request->get('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw (new \Bieristo\Exception\InvalidFormatException('Invalid email format'))->setTarget('email');
    }

    // Validate password strength
    $password = $request->get('password');
    new \Bieristo\Helper\PasswordValidation($password);

    try {
        $user = $userRepository->getUserByEmail($email);
        $token = $user->getToken();
    } catch (\Bieristo\Exception\NoSuchEntityException $e) {
        $user = $userRepository->getEmptyUser();
        $user->setEmail($email);
        $user->setPasswordHash(new \Bieristo\Helper\PasswordHash($password));
    }

    if (!password_verify($request->get('password'), $user->getPasswordHash())) {
        throw new \Bieristo\Exception\NoSuchEntityException();
    }

    if (empty($user->getToken())) {
        $token = new \Bieristo\Helper\TokenGenerator();
        $user->setToken($token);
    }

    $userRepository->save($user);

    $response->set('token', (string) $token);
    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\InvalidFormatException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('code', 1);
    $response->set('reason', 'User do not exists or password is invalid.');
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();

