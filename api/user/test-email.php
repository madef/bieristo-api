<?php

/**
 * Params:
 *  - email
 *  - password
 *
 * Response:
 *  - token
 */

require('../../config.php');
require('../../vendor/autoload.php');

$response = new \Bieristo\Api\Response();

$mail = new \Snipworks\Smtp\Email(SMTP_HOST, SMTP_PORT);
$mail->setProtocol(SMTP_PROTOCOL);
$mail->setLogin(SMTP_USERNAME, SMTP_PASSWORD);
$mail->setFrom(SMTP_SENDER_EMAIL, SMTP_SENDER_LABEL);
$mail->addTo('maxence@deflotte.fr', '');
$mail->setSubject('Example subject');
$mail->setHtmlMessage('<b>Example message</b>...');

if ($mail->send()) {
    $response->set('success', true);
} else {
    $response->set('success', false);
}

$response->send();
