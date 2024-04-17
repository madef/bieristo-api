<?php

/**
 * Params:
 *  - token
 *  - boardId
 *
 * Response:
 *  - status: true success, false error
 *  - (code): 1 [token has expired]
 *  - (reason)
 */

require('../../../config.php');
require('../../../vendor/autoload.php');

$userRepository = \Bieristo\Model\UserRepository::getInstance();

$request = new \Bieristo\Api\Request;

$response = new \Bieristo\Api\Response();

try {
    if ($request->empty('token')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing token'))->setTarget('token');
    }

    if ($request->empty('boardId')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing board id'))->setTarget('boardId');
    }

    $token = $request->get('token');
    $boardId = $request->get('boardId');

    $user = $userRepository->getUserByToken($token);

    if (!isset($user->getBoardList()[$boardId])) {
        throw new \Bieristo\Exception\NoSuchEntityException;
    }

    $user->removeBoard($boardId);

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Board do not exist.');
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('code', 1);
    $response->set('reason', $e->getMessage());
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();

