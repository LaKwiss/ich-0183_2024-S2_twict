<?php

declare(strict_types=1);

/**
 * Composer
 */
require_once(dirname(__DIR__) . '/vendor/autoload.php');

/**
 * Configure PHP
 */
ini_set('xdebug.var_display_max_depth', 10);
ini_set('error_log', dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.log');
session_start();

/**
 * Error and Exception handling
 */
$errorHandler = new Core\ErrorHandler();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('{controller}/{action}');
$router->add('{controller}', ['action' => 'index']);
$router->add('', ['controller' => 'Home', 'action' => 'index']);

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI']);
