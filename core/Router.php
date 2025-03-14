<?php

declare(strict_types=1);

namespace Core;

/**
 * Router
 */
class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected array $routes;

    /**
     * Parameters from the matched route
     * @var array
     */
    protected array $params;

    public function __construct()
    {
        $this->routes = [];
        $this->params = [];
    }

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function add($route, $params = []): void
    {
        // Remove leading and trailing slash
        $route = trim($route, '/');

        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables to expressions. Ex : {controller} to (?<controller>[a-z]+)
        $route = preg_replace('/\{([a-z]+)\}/', '(?<$1>[a-z-]+)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = "/^{$route}$/i";

        $this->routes[$route] = $params;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean true if a match found, false otherwise
     */
    public function match($url): bool
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches) !== 1) {
                continue;
            }

            // Get named capture group values
            foreach ($matches as $key => $match) {
                if (is_string($key)) {
                    $params[$key] = $match;
                }
            }

            $this->params = $params;

            return true;
        }

        return false;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public function dispatch($url): void
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url) === false) {
            throw new \Exception('No route matched.', 404);
        }


        $controller = $this->resolveController($this->params['controller']);

        if (class_exists($controller) === false) {
            throw new \Exception("Controller class $controller not found");
        }

        if (is_subclass_of($controller, 'Core\Controller') === false) {
            throw new \Exception("Controller class $controller does not extend Controller base class");
        }

        $controller = new $controller($this->params);

        
        $action = $this->params['action'];
        $action = \Core\Libs\str_lower_camel_case($action);

        $controller->invoke($action, []);
    }

    /**
     * Remove the query string variables from the URL. For example:
     *
     *   URL                           $_SERVER['REQUEST_URI']    Route
     *   -------------------------------------------------------------------
     *   localhost                     /                          ''
     *   localhost/?                   /                          ''
     *   localhost/?page=1             /?page=1                   ''
     *   localhost/posts?page=1        /posts?page=1              posts
     *   localhost/posts/index         /posts/index               posts/index
     *   localhost/posts/index?page=1  /posts/index?page=1        posts/index
     * 
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url): string
    {
        $url = parse_url($url, PHP_URL_PATH);
        $url = trim($url, '/');

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    protected function resolveController(string $controllerName): string
    {
        $controllerFullName = $this->params['namespace'] ?? 'App\Controllers';
        $controllerFullName = trim($controllerFullName,  '\\') . '\\';
        $controllerFullName .= \Core\Libs\str_upper_camel_case($controllerName);
        $controllerFullName .= 'Controller';

        return $controllerFullName;
    }
}
