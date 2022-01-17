<?php declare(strict_types=1);

namespace DomainChecker\controllers\domains;

use DomainChecker\controllers\AbstractController;
use DomainChecker\managers\domains\DomainFilesManager;

/**
 * Class DomainCheckerController is our home page.
 * Here user can upload files to check domains and see already uploaded files with progress.
 *
 * @package DomainChecker\controllers
 */
class DomainCheckerController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function load(): void
    {
        $domainFiles = DomainFilesManager::getInstance()->getDomainFilesWithProgress();

        $assignData = [
            'domainFiles' => $domainFiles
        ];

        $this->render($assignData);
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return 'domain_checker.php';
    }
}