<?php declare(strict_types=1);

namespace DomainChecker\actions\queue;

use DomainChecker\actions\AbstractAction;
use DomainChecker\dal\mappers\domains\DomainFilesMapper;
use DomainChecker\managers\domains\DomainFilesManager;

/**
 * Class QueueProgressAction used for polling files progress and show in frontend current progress with percent.
 *
 * @package DomainChecker\actions\queue
 */
class QueueProgressAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function action(): void
    {
        $domainFilesManager = DomainFilesManager::getInstance();

        $domainFiles = $domainFilesManager->getDomainFilesByStatus(DomainFilesMapper::STATUS_IN_PROGRESS);

        if (!$domainFiles) {
            $this->jsonResponse([
                'filesProgress' => []
            ]);
            return;
        }

        $domainFilesProgress = $domainFilesManager->getCalculatedDomainsFilesProgress($domainFiles);

        $this->jsonResponse([
            'filesProgress' => $domainFilesProgress
        ]);
    }
}