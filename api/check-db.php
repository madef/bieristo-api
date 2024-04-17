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


$mongo = \Bieristo\DbConnector\Mongo::getInstance();

$response = new \Bieristo\Api\Response();
$response->set('success', $mongo->checkConnection());
$response->send();

