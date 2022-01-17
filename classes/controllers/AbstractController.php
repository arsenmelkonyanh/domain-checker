<?php declare(strict_types=1);

namespace DomainChecker\controllers;

/**
 * Class AbstractController used as abstract class for controllers.
 * Contains abstract method load() which will be called on controller call and abstract method getTemplate()
 * which used to determine controller template.
 * Contains render method which includes controller template which corresponding data.
 *
 * @package DomainChecker\controllers
 */
abstract class AbstractController
{
    /**
     * Load method.
     * Called on request controller.
     */
    abstract public function load(): void;

    /**
     * Returns controller template.
     *
     * @return string
     */
    abstract public function getTemplate(): string;

    /**
     * Includes controller template which corresponding data.
     *
     * @param array $data
     */
    final public function render(array $data): void
    {
        ob_start();
        include(TEMPLATE_DIR . $this->getTemplate());
        echo ob_get_clean();
    }
}