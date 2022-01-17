<?php declare(strict_types=1);

namespace DomainChecker\controllers\domains;

use DomainChecker\controllers\AbstractController;
use DomainChecker\dal\mappers\domains\DomainFilesMapper;
use DomainChecker\managers\domains\DomainFilesManager;
use DomainChecker\managers\domains\DomainsManager;

/**
 * Class DomainCheckerResultController shows domain file result.
 *
 * @package DomainChecker\controllers
 */
class DomainCheckerResultController extends AbstractController
{

    private const DOMAINS_PER_PAGE = 1000;

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        $params = $this->getRequestParams();

        if (!$params['id'] || !is_numeric($params['id'])) {
            header('location: /');
            return;
        }

        $domainFileData = DomainFilesManager::getInstance()->getById($params['id']);

        if ((int)$domainFileData['status'] !== DomainFilesMapper::STATUS_COMPLETED) {
            header('location: /');
            return;
        }

        $offset = $this->getOffset($params['page']);

        $domains = DomainsManager::getInstance()->getDomainsByFileId($params['id'], $offset, self::DOMAINS_PER_PAGE);
        $pagesCount = ceil($domainFileData['domains_count'] / self::DOMAINS_PER_PAGE);

        $assignData = [
            'fileId' => $domainFileData['id'],
            'fileName' => $domainFileData['original_name'],
            'domains' => $domains,
            'currentPage' => $params['page'],
            'pagesCount' => $pagesCount
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
        return 'domain_checker_result.php';
    }

    /**
     * Returns request params.
     *
     * @return array
     */
    private function getRequestParams(): array
    {
        $params = [];

        $params['id'] = !empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        $params['page'] = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 1;

        return $params;
    }

    /**
     * Returns offset by given page.
     *
     * @param int $page
     *
     * @return int
     */
    private function getOffset(int $page): int
    {
        return ($page - 1) * self::DOMAINS_PER_PAGE;
    }
}