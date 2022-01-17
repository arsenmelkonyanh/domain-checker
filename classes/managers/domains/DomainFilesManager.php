<?php declare(strict_types=1);

namespace DomainChecker\managers\domains;

use DomainChecker\dal\mappers\domains\DomainFilesMapper;

/**
 * Class DomainFilesManager contains domain files related business logic.
 *
 * @package DomainChecker\managers\domains
 */
class DomainFilesManager
{
    /**
     * @var DomainFilesManager|null
     */
    private static ?DomainFilesManager $instance = null;

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
     * DomainFilesManager constructor.
     */
    private function __construct()
    {
    }

    /**
     * Returns mapper instance.
     *
     * @return DomainFilesMapper
     */
    public function getMapper(): DomainFilesMapper
    {
        return DomainFilesMapper::getInstance();
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
        return $this->getMapper()->create($filesData);
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
    public function update(int $id, int $status, int $domainsCount = 0): bool
    {
        return $this->getMapper()->update($id, $status, $domainsCount);
    }

    /**
     * Returns domain files by given status.
     *
     * @param int $status
     *
     * @return array
     */
    public function getDomainFilesByStatus(int $status = DomainFilesMapper::STATUS_NOT_STARTED): array
    {
        return $this->getMapper()->getDomainFilesByStatus($status);
    }

    /**
     * Returns all domain files with progress.
     *
     * @return array
     */
    public function getDomainFilesWithProgress(): array
    {
        $domainFiles = $this->getAllDomainFiles();

        if (!$domainFiles) {
            return [];
        }

        return $this->getCalculatedDomainsFilesProgress($domainFiles);
    }

    /**
     * Returns all domains.
     *
     * @return array
     */
    public function getAllDomainFiles(): array
    {
        return $this->getMapper()->getAllDomainFiles();
    }

    /**
     * Returns calculated domains files progress by given domain files.
     *
     * @param array $domainFiles
     *
     * @return array
     */
    public function getCalculatedDomainsFilesProgress(array $domainFiles): array
    {
        $domainFilesIds = array_column($domainFiles, 'id');

        $domainFilesDomainsCount = DomainsManager::getInstance()->getDomainsCountByFileIds($domainFilesIds);

        $domainFilesProgress = [];

        foreach ($domainFiles as $domainFile) {
            if (!isset($domainFilesDomainsCount[$domainFile['id']])) {
                $domainFilesDomainsCount[$domainFile['id']] = 0;
            }

            $domainFilesProgress[] = [
                'fileId' => $domainFile['id'],
                'name' => $domainFile['original_name'],
                'progress' => (int)floor($domainFilesDomainsCount[$domainFile['id']] / $domainFile['domains_count'] * 100)
            ];
        }

        return $domainFilesProgress;
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
        $result = $this->getMapper()->getById($id);

        if (!empty($result)) {
            return $result[0];
        }

        return [];
    }
}