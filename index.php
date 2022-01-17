<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use DomainChecker\router\Router;

$routedClassInfo = Router::getInstance()->route();

if (!$routedClassInfo) {
    header('location: /');
    exit;
}

include './config/constants.php';

if (!empty($routedClassInfo['params'])) {
    $_REQUEST['id'] = $routedClassInfo['params']['id'];
}

$routedClassName = $routedClassInfo['action'];
$routedClassType = $routedClassInfo['type'];

$routedClass = new $routedClassName();

$routedClass->$routedClassType();

if ($routedClassInfo['type'] === 'action') {
    $routedClass->afterAction();
}