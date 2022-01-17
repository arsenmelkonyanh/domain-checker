<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use DomainChecker\managers\queue\QueueManager;

QueueManager::getInstance()->consumeDomainFilesQueue();

