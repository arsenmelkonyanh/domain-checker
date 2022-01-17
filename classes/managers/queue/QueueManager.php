<?php declare(strict_types=1);

namespace DomainChecker\managers\queue;

use DomainChecker\config\ConfigReader;
use DomainChecker\dal\mappers\domains\DomainFilesMapper;
use DomainChecker\handlers\FileReader;
use DomainChecker\logger\Logger;
use DomainChecker\managers\domains\DomainFilesManager;
use DomainChecker\managers\domains\DomainsManager;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class QueueManager used to manage rabbitMQ queue.
 * Contains create queue methods and queue handling methods.
 *
 * @package DomainChecker\managers\queue
 */
class QueueManager
{
    public const DOMAIN_FILES_QUEUE_NAME = 'domain_files_queue';
    public const DOMAINS_QUEUE_NAME = 'domains_queue';

    /**
     * @var QueueManager|null
     */
    private static ?QueueManager $instance = null;

    /**
     * @var array
     */
    private array $rabbitConfig;

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
     * QueueManager constructor.
     */
    private function __construct()
    {
        $this->rabbitConfig = ConfigReader::getInstance()->getRabbitConfig();

        if (!$this->rabbitConfig) {
            Logger::getInstance()->error('Unable to get rabbitMQ config.');

            return;
        }
    }

    /**
     * Creates files queue.
     * Send as message domain file id, path and name to use in further handling.
     *
     * @param array $domainFiles
     *
     * @return bool
     */
    public function createFilesQueue(array $domainFiles): bool
    {
        try {
            $connection = new AMQPStreamConnection($this->rabbitConfig['host'], $this->rabbitConfig['port'], $this->rabbitConfig['user'], $this->rabbitConfig['passwd']);
            $channel = $connection->channel();

            $channel->queue_declare(self::DOMAIN_FILES_QUEUE_NAME, false, true, false, false);

            foreach ($domainFiles as $domainFile) {
                $messageBody = [
                    'id' => $domainFile['id'],
                    'path' => $domainFile['path'],
                    'name' => $domainFile['original_name']
                ];

                $msg = new AMQPMessage(
                    json_encode($messageBody, JSON_THROW_ON_ERROR),
                    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
                );

                $channel->basic_publish($msg, '', self::DOMAIN_FILES_QUEUE_NAME);
            }

            $channel->close();
            $connection->close();

            return true;
        } catch (\Exception $ex) {
            Logger::getInstance()->error('Unable to create files queue.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'domainFiles' => $domainFiles
            ]);

            return false;
        }
    }

    /**
     * Consume domain files queue.
     * Send message to handleDomainFiles() method for further handling.
     *
     * @return bool
     */
    public function consumeDomainFilesQueue(): bool
    {
        try {
            $connection = new AMQPStreamConnection($this->rabbitConfig['host'], $this->rabbitConfig['port'], $this->rabbitConfig['user'], $this->rabbitConfig['passwd']);
            $channel = $connection->channel();

            $channel->queue_declare(self::DOMAIN_FILES_QUEUE_NAME, false, true, false, false);

            echo "########## Waiting for domain files. ##########\n";

            $channel->basic_qos(null, 1, null);
            $channel->basic_consume(self::DOMAIN_FILES_QUEUE_NAME, '', false, false, false, false, [$this, 'handleDomainFile']);

            while ($channel->is_open()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();

            return true;
        } catch (\Exception $ex) {
            Logger::getInstance()->error('Unable to consume files queue.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);

            return false;
        }
    }

    /**
     * Handles domain file.
     * In case if domain file doesn't contains any domain set domain file status as completed,
     * otherwise set domain file status as in progress and creates domains queue from file.
     *
     * @param AMQPMessage $message
     */
    public function handleDomainFile(AMQPMessage $message): void
    {
        $domainFileData = json_decode($message->body, true);

        $domainFileName = $domainFileData['name'];

        echo "########## Handle domain file $domainFileName started ##########\n";

        $domains = FileReader::getInstance()->getDataFromFile($domainFileData['path']);

        $domainsCount = count($domains);
        $status = DomainFilesMapper::STATUS_IN_PROGRESS;

        // empty file
        if (!$domainsCount) {
            $status = DomainFilesMapper::STATUS_COMPLETED;
        }

        $domainFileId = (int)$domainFileData['id'];

        DomainFilesManager::getInstance()->update($domainFileId, $status, $domainsCount);

        $this->createDomainsQueue($domains, $domainFileId);

        $message->ack();

        echo "########## Handle domain file $domainFileName completed ##########\n";
    }

    /**
     * Creates domains queue.
     * Send as message domain file id and domain to use in further handling.
     *
     * @param array $domains
     * @param int $domainFileId
     *
     * @return bool
     */
    public function createDomainsQueue(array $domains, int $domainFileId): bool
    {
        try {
            $connection = new AMQPStreamConnection($this->rabbitConfig['host'], $this->rabbitConfig['port'], $this->rabbitConfig['user'], $this->rabbitConfig['passwd']);
            $channel = $connection->channel();

            $channel->queue_declare(self::DOMAINS_QUEUE_NAME, false, true, false, false);

            foreach ($domains as $domain) {
                $messageBody = [
                    'fileId' => $domainFileId,
                    'domain' => $domain
                ];

                $msg = new AMQPMessage(
                    json_encode($messageBody, JSON_THROW_ON_ERROR),
                    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
                );

                $channel->basic_publish($msg, '', self::DOMAINS_QUEUE_NAME);
            }

            $channel->close();
            $connection->close();

            return true;
        } catch (\Exception $ex) {
            Logger::getInstance()->error('Unable to create domains queue.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);

            return false;
        }
    }

    /**
     * Consume domains queue.
     * Send message to handleDomain() method for further handling.
     *
     * @return bool
     */
    public function consumeDomainsQueue(): bool
    {
        try {
            $connection = new AMQPStreamConnection($this->rabbitConfig['host'], $this->rabbitConfig['port'], $this->rabbitConfig['user'], $this->rabbitConfig['passwd']);
            $channel = $connection->channel();

            $channel->queue_declare(self::DOMAINS_QUEUE_NAME, false, true, false, false);

            echo "########## Waiting for domain. ##########\n";

            $channel->basic_qos(null, 1, null);
            $channel->basic_consume(self::DOMAINS_QUEUE_NAME, '', false, false, false, false, [$this, 'handleDomain']);

            while ($channel->is_open()) {

                $channel->wait();
            }

            $channel->close();
            $connection->close();

            return true;
        } catch (\Exception $ex) {
            Logger::getInstance()->error('Unable to consume domains queue.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);

            return false;
        }
    }

    /**
     * Handles domains.
     * Checks domain using shell_exec to perform linux command whois.
     * From command response checks if domain have expiry date.
     * In case if domain have expiry date update domain in db, set expire date and valid as true,
     * otherwise set valid as false.
     *
     * @param AMQPMessage $message
     */
    public function handleDomain(AMQPMessage $message): void
    {
        $domainData = json_decode($message->body, true);

        $fileId = $domainData['fileId'];
        $domain = $domainData['domain'];

        echo "########## Handle domain $domain started ##########\n";

        $domainInfo = shell_exec("whois $domain");
        $domainInfoExpiresDatePart = explode('Expires:', $domainInfo);

        $isValid = true;
        if (!isset($domainInfoExpiresDatePart[1])) {
            $domainInfoExpiresDatePart = explode('Registry Expiry Date:', $domainInfo);
            if (!isset($domainInfoExpiresDatePart[1])) {
                $isValid = false;
            }
        }

        $expires = null;
        if ($isValid) {
            $expires = substr(trim($domainInfoExpiresDatePart[1]), 0, 10);
        }

        $domainData = [
            'fileId' => $fileId,
            'domain' => $domain,
            'isValid' => $isValid,
            'expires' => $expires
        ];

        DomainsManager::getInstance()->create($domainData);

        $message->ack();

        echo "########## Handle domain $domain completed ##########\n";
    }
}