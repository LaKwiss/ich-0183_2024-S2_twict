<?php

declare(strict_types=1);

namespace Core;

/**
 * Base controller
 */
abstract class Controller
{
    /**
     * Parameters from the matched route
     * @var array
     */
    protected array $routeParams = [];

    /**
     * Class constructor
     *
     * @param array $routeParams  Parameters from the route
     *
     * @return void
     */
    public function __construct(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    /**
     * Invoke the $name method on this controller instance.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @return void
     */
    public function invoke(string $name, array $args): void
    {
        $methods = ["{$name}_{$_SERVER['REQUEST_METHOD']}", $name];
        $method = null;

        foreach ($methods as $m) {
            if (method_exists($this, $m)) {
                $method = $m;
                break;
            };
        }

        if ($method === null) {
            throw new \Exception("Method $name not found in controller " . get_class($this));
        }


        if ($this->before() === false) {
            return;
        }

        call_user_func_array([$this, $method], $args);

        $this->after();
    }

    /**
     * Before - called before an action method.
     *
     * @return bool
     */
    protected function before(): bool
    {
        return true;
    }

    /**
     * After - called after an action method.
     *
     * @return void
     */
    protected function after(): void
    {
    }
}
