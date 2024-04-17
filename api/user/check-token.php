<?php

/**
 * Params:
 *  - token
 *
 * Response:
 *  - status: true token is valid, false token not valid
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

    $token = $request->get('token');

    $user = $userRepository->getUserByToken($token);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();
