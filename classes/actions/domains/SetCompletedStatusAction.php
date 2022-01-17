<?php declare(strict_types=1);

namespace DomainChecker\actions\domains;

use DomainChecker\actions\AbstractAction;
use DomainChecker\dal\mappers\domains\DomainFilesMapper;
use DomainChecker\managers\domains\DomainFilesManager;

/**
 * Class SetCompletedStatusAction used for set completed status for domain file with given id, when domain file
 * handling completed.
 *
 * @package DomainChecker\actions\domains
 */
class SetCompletedStatusAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function action(): void
    {
        $params = $this->getRequestParams();

        if (!$params['id'] || !is_numeric($params['id'])) {
            $response = [
                'success' => false
            ];

            $this->jsonResponse($response);

            return;
        }

        $success = DomainFilesManager::getInstance()->update($params['id'], DomainFilesMapper::STATUS_COMPLETED);

        $response = [
            'success' => $success,
            'fileId' => $params['id']
        ];

        $this->jsonResponse($response);
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

        return $params;
    }

}