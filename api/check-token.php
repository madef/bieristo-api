<?php

/**
 * Params:
 *  - email
 *  - password
 *
 * Response:
 *  - token
 */

require('../config.php');
require('../vendor/autoload.php');

$response = new \Bieristo\Api\Response();
$response->set('success', false);
$response->send();

