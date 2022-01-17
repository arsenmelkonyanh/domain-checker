<?php declare(strict_types=1);

namespace DomainChecker\router;

use DomainChecker\logger\Logger;

/**
 * Class Router used to route current request uri from routes.json file.
 *
 * @package Router\router
 */
class Router
{
    /**
     * @var Router|null
     */
    public static ?Router $instance = null;

    /**
     * @var array
     */
    private array $routes;

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
     * Router constructor.
     */
    private function __construct()
    {
        $routes = file_get_contents(dirname(__DIR__, 2) . '/config/routes.json');

        try {
            $this->routes = json_decode($routes, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $ex) {
            $this->routes = [];

            Logger::getInstance()->error('Unable to decode data.', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'routes' => $routes
            ]);
        }
    }

    /**
     * Routes current request uri to route from routes.json file.
     *
     * @return array|null
     */
    public function route(): ?array
    {
        $requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];

        if (!empty($this->routes[$requestUri])) {
            return $this->routes[$requestUri];
        }

        foreach ($this->routes as $route => $info) {
            $pattern = '/' . str_replace('/', '\/', $route) . '$/';

            preg_match($pattern, $requestUri, $matches);

            if (!empty($matches)) {
                if (!isset($matches[1])) {
                    return $this->routes[$route];
                }

                $this->routes[$route]['params']['id'] = $matches[1];

                return $this->routes[$route];
            }
        }

        return null;
    }
}