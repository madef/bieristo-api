<?php

/**
 * Params:
 *  - token
 *
 * Response:
 *  - status: true success, false error
 *  - (code): 1 [token has expired]
 *  - (reason)
 *  - (data): customer data
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

    $response->set('data', $user->getData(false));

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
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
