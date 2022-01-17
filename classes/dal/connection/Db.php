<?php declare(strict_types=1);

namespace DomainChecker\dal\connection;

use DomainChecker\config\ConfigReader;
use DomainChecker\logger\Logger;
use PDO;
use PDOException;

/**
 * Class Db used to create PDO connection.
 *
 * @package DomainChecker\dal\connection
 */
class Db
{
    /**
     * @var Db|null
     */
    public static ?Db $instance = null;

    /**
     * @var PDO|null
     */
    public ?PDO $connection = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Db constructor.
     */
    private function __construct()
    {
        $dbConfig = ConfigReader::getInstance()->getDbConfig();

        if (!$dbConfig) {
            Logger::getInstance()->error('Unable to get db config.');

            return;
        }

        try {
            $this->connection = new PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['passwd']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $ex) {
            Logger::getInstance()->error('Unable to create connection.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'dbConfig' => $dbConfig
            ]);
        }
    }

    /**
     * Returns connection.
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
}