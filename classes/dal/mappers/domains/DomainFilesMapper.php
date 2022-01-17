<?php declare(strict_types=1);

namespace DomainChecker\dal\mappers\domains;

use DomainChecker\dal\mappers\AbstractMapper;
use PDO;

/**
 * Class DomainFilesMapper contains queries of `domain_files` table.
 *
 * @package DomainChecker\dal\mappers\domains
 */
class DomainFilesMapper extends AbstractMapper
{
    /**
     * Domain file handling not started.
     */
    public const STATUS_NOT_STARTED = 0;

    /**
     * Domain file handling in progress.
     */
    public const STATUS_IN_PROGRESS = 1;

    /**
     * Domain file handling completed.
     */
    public const STATUS_COMPLETED = 2;

    /**
     * @var DomainFilesMapper|null
     */
    private static ?DomainFilesMapper $instance = null;

    /**
     * @var string
     */
    public string $tableName = 'domain_files';

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
     * DomainFilesMapper constructor.
     */
    private function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates domain file row in db by given params.
     *
     * @param array $filesData
     *
     * @return bool
     */
    public function create(array $filesData): bool
    {
        $sql = 'INSERT INTO ' . $this->getTableName() . ' (`path`, `original_name`) VALUES ';

        $params = [];

        foreach ($filesData as $key => $fileData) {
            $sql .= "(?, ?)";
            $params[] = $fileData['path'];
            $params[] = $fileData['name'];

            if ($key !== count($filesData) - 1) {
                $sql .= ',';
            }
        }

        return $this->executeQuery($sql, $params);
    }

    /**
     * Updates domain file status and domains count by given params.
     * In case if $domainsCount variable is not provided updates only status.
     *
     * @param int $id
     * @param int $status
     * @param int $domainsCount
     *
     * @return bool
     */
    public function update(int $id, int $status, int $domainsCount): bool
    {
        $sql = 'UPDATE ' . $this->getTableName() . ' SET `status` = ?';

        $params = [];
        $params[] = $status;

        if ($domainsCount) {
            $sql .= ', `domains_count` = ?';
            $params[] = $domainsCount;
        }

        $sql .= ' WHERE `id` = ?;';

        $params[] = $id;

        return $this->executeQuery($sql, $params);
    }

    /**
     * Returns domain files by given status.
     *
     * @param int $status
     *
     * @return array
     */
    public function getDomainFilesByStatus(int $status): array
    {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `status` = ?;';
        $params = [$status];

        return $this->fetchRows($sql, $params);
    }

    /**
     * Returns all domains.
     *
     * @return array
     */
    public function getAllDomainFiles(): array
    {
        $sql = 'SELECT * FROM ' . $this->getTableName();

        return $this->fetchRows($sql);
    }

    /**
     * Returns domain file by given id.
     *
     * @param int $id
     *
     * @return array
     */
    public function getById(int $id): array
    {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `id` = ?;';

        return $this->fetchRows($sql, [$id]);
    }

}