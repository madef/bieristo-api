<?php

/**
 * Params:
 *  - email
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

    $email = $request->get('email');

    $token = new \Bieristo\Helper\TokenGenerator();

    $user = $userRepository->getUserByEmail($email);
    $user->setLostPasswordToken($token);
    $user->setLostPasswordTokenExpiration((new \Bieristo\Helper\TokenExpiration())->getDateTime());
    \Bieristo\Model\UserRepository::getInstance()->save($user);

    $link = DOMAIN_PREFIX . DOMAIN . '#password-recover-token='.$token;

    $mail = new \Snipworks\Smtp\Email(SMTP_HOST, SMTP_PORT);
    $mail->setProtocol(SMTP_PROTOCOL);
    $mail->setLogin(SMTP_USERNAME, SMTP_PASSWORD);
    $mail->setFrom(SMTP_SENDER_EMAIL, SMTP_SENDER_LABEL);
    $mail->addTo($email, '');
    $mail->setSubject('[Bieristo] Récupération de mot de passe'); // @TODO Manage translations

    $template = new \Bieristo\Email\RecoverPasswordTemplate();
    $template->setVar('link', $link);
    $template->setLanguage('fr'); // @TODO Manage translations
    $mail->setHtmlMessage($template->render());

    if (!$mail->send()) {
        throw new \Exception('Email cannot be sent');
    }

    $userRepository->save($user);

    $response->set('status', true);
} catch (\Bieristo\Exception\MissingParameterException $e) {
    $response->set('status', false);
    $response->set('reason', $e->getMessage());
    $response->set('target', $e->getTarget());
} catch (\Bieristo\Exception\NoSuchEntityException $e) {
    // Invisible error
    $response->set('status', true);
} catch (\Exception $e) {
    $response->set('status', false);
    $response->set('reason', 'Technical problem');
}

$response->send();
