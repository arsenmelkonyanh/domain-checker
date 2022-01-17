<?php declare(strict_types=1);

namespace DomainChecker\logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\UidProcessor;

/**
 * Class Logger to provide monolog logger for further logging.
 *
 * @package DomainChecker\logger
 */
class Logger
{
    /**
     * @var MonologLogger|null
     */
    private static ?MonologLogger $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return MonologLogger
     */
    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = new MonologLogger(
                'domain_checker',
                [
                    new RotatingFileHandler(dirname(__DIR__, 2) . '/logs/domain_checker.log')
                ],
                [
                    new UidProcessor()
                ]
            );
        }

        return self::$instance;
    }

    private function __construct()
    {
    }
}