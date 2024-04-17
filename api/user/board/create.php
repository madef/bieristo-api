<?php

/**
 * Params:
 *  - token
 *  - boardId
 *  - name
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
        throw (new \Bieristo\Exception\MissingParameterException('Missing board id'))->setTarget('bordId');
    }

    if ($request->empty('name')) {
        throw (new \Bieristo\Exception\MissingParameterException('Missing name'))->setTarget('name');
    }

    $token = $request->get('token');
    $boardId = $request->get('boardId');
    $name = $request->get('name');

    if (!preg_match('/^[a-z0-9_-]+$/i', $boardId)) {
        throw (
            new \Bieristo\Exception\InvalidFormatException(
                'Board id must be a string composed with lettre, numeric, underscore (_) or hyphen (-). Spaces and other chars are forbiden.'
            )
        )->setTarget('boardId');
    }

    $user = $userRepository->getUserByToken($token);
    $board = new \Bieristo\Model\Board;
    $board->setName($name);

    $user->addBoard($boardId, $board);

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\EntityAlreadyExistsException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', 'token');
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    $response->set('status', false);
    $response->set('reason', 'Invalid user or token.');
} catch (\Bieristo\Exception\TokenHasExpiredException $e) {
    $response->set('status', false);
    $response->set('code', 1);
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
