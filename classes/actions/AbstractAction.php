<?php declare(strict_types=1);

namespace DomainChecker\actions;

use DomainChecker\logger\Logger;

/**
 * Class AbstractAction used as abstract class for actions.
 * Contains abstract method action() which will be called on action call.
 *
 * @package DomainChecker\actions
 */
abstract class AbstractAction
{
    /**
     * Action method.
     * Called on action call.
     */
    abstract public function action(): void;

    /**
     * Echo json data.
     * Used to delivery action response.
     *
     * @param array $data
     */
    final public function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        try {
            echo json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $ex) {
            Logger::getInstance()->error('Unable to encode data.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'data' => $data
            ]);
        }
    }

    /**
     * After action method used to do some stuff after action response.
     */
    public function afterAction(): void
    {

    }
}