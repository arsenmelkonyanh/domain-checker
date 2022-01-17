<?php declare(strict_types=1);

namespace DomainChecker\dal\mappers\domains;

use DomainChecker\dal\mappers\AbstractMapper;
use PDO;

/**
 * Class DomainsMapper contains queries of `domains` table.
 *
 * @package DomainChecker\dal\mappers\domains
 */
class DomainsMapper extends AbstractMapper
{
    /**
     * @var DomainsMapper|null
     */
    private static ?DomainsMapper $instance = null;

    /**
     * @var string
     */
    public string $tableName = 'domains';

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
     * DomainsMapper constructor.
     */
    private function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates domain row in db by given params.
     *
     * @param array $domainData
     *
     * @return bool
     */
    public function create(array $domainData): bool
    {
        $sql = 'INSERT INTO ' . $this->getTableName() . ' (`file_id`, `domain`, `is_valid`, `expire_date`) VALUES (?, ?, ?, ?)';

        $params = [
            $domainData['fileId'],
            $domainData['domain'],
            $domainData['isValid'],
            $domainData['expires']
        ];

        return $this->executeQuery($sql, $params);
    }

    /**
     * Returns domains count by given domain files ids.
     *
     * return format:
     * [
     *  {domainFileId} => {domainsCount}
     * ]
     *
     * @param array $domainFilesIds
     *
     * @return array
     */
    public function getDomainsCountByFileIds(array $domainFilesIds): array
    {
        $in  = str_repeat('?,', count($domainFilesIds) - 1) . '?';

        $sql = "SELECT `file_id`, COUNT(`id`) FROM " . $this->getTableName() . " WHERE `file_id` IN ($in) GROUP BY `file_id`;";

        return $this->fetchRows($sql, $domainFilesIds, PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns domains by given file id.
     *
     * @param int $id
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getDomainsByFileId(int $id, int $offset, int $limit): array
    {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE `file_id` = $id LIMIT $offset, $limit;";

        return $this->fetchRows($sql);
    }
}