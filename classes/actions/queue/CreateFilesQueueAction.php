<?php declare(strict_types=1);

namespace DomainChecker\actions\queue;

use DomainChecker\actions\AbstractAction;
use DomainChecker\handlers\FileUploader;
use DomainChecker\handlers\FileValidator;
use DomainChecker\managers\domains\DomainFilesManager;
use DomainChecker\managers\queue\QueueManager;

/**
 * Class CreateFilesQueueAction upload given files to project files path, inserts files info to files table and
 * after action creates files queue.
 *
 * @package DomainChecker\actions\queue
 */
class CreateFilesQueueAction extends AbstractAction
{
    /**
     * @var array
     */
    private array $domainFiles = [];

    /**
     * @inheritDoc
     */
    public function action(): void
    {
        $files = FileValidator::getInstance()->getValidatedFiles($_FILES);

        if (!$files) {
            $response = [
                'success' => false
            ];

            $this->jsonResponse($response);
            return;
        }

        // Uploaded files data which need to be inserted into db
        $filesData = FileUploader::getInstance()->upload($files);

        $domainFilesManager = DomainFilesManager::getInstance();
        $result = $domainFilesManager->create($filesData);

        if (!$result) {
            $response = [
                'success' => false
            ];

            $this->jsonResponse($response);
            return;
        }

        // Domain files which should be shown in frontend and added to queue
        $this->domainFiles = $domainFilesManager->getDomainFilesByStatus();

        $this->jsonResponse([
            'success' => true,
            'domainFiles' => $this->domainFiles
        ]);
    }

    /**
     * @inheritDoc
     */
    public function afterAction(): void
    {
        QueueManager::getInstance()->createFilesQueue($this->domainFiles);
    }
}