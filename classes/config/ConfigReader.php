<?php declare(strict_types=1);

namespace DomainChecker\config;

use DomainChecker\logger\Logger;

/**
 * Class ConfigReader used to read configs from config.json file.
 *
 * @package DomainChecker\config
 */
class ConfigReader
{
    /**
     * @var ConfigReader|null
     */
    public static ?ConfigReader $instance = null;

    /**
     * @var array
     */
    private array $config;

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
     * ConfigReader constructor.
     */
    private function __construct()
    {
        $config = file_get_contents(dirname(__DIR__, 2) . '/config/config.json');

        try {
            $this->config = json_decode($config, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $ex) {
            $this->config = [];

            Logger::getInstance()->error('Unable to decode data.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'data' => $config
            ]);
        }
    }

    /**
     * Returns all configs.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Returns db configs.
     *
     * @return array
     */
    public function getDbConfig(): array
    {
        if (!empty($this->config['Db'])) {
            return $this->config['Db'];
        }

        return [];
    }

    /**
     * Returns rabbitMQ configs.
     *
     * @return array
     */
    public function getRabbitConfig(): array
    {
        if (!empty($this->config['Rabbit'])) {
            return $this->config['Rabbit'];
        }

        return [];
    }
}