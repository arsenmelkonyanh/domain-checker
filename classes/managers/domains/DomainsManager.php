<?php declare(strict_types=1);

namespace DomainChecker\managers\domains;

use DomainChecker\dal\mappers\domains\DomainsMapper;

/**
 * Class DomainsManager contains domains related business logic.
 *
 * @package DomainChecker\managers\domains
 */
class DomainsManager
{
    /**
     * @var DomainsManager|null
     */
    private static ?DomainsManager $instance = null;

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
     * DomainsManager constructor.
     */
    private function __construct()
    {
    }

    /**
     * Returns mapper instance
     *
     * @return DomainsMapper
     */
    public function getMapper(): DomainsMapper
    {
        return DomainsMapper::getInstance();
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
        return $this->getMapper()->create($domainData);
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
        return $this->getMapper()->getDomainsCountByFileIds($domainFilesIds);
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
        return $this->getMapper()->getDomainsByFileId($id, $offset, $limit);
    }
}