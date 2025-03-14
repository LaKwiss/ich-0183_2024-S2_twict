<?php

declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Base model
 */
abstract class Model
{
    /**
     * Get the PDO database connection
     *
     * @return PDO
     */
    protected static function getDB(): PDO
    {
        static $db = null;

        if ($db === null) {
            $db = new PDO('mysql:host=db;port=3306;dbname=twict;charset=utf8', 'root', '123456', [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }

        return $db;
    }
}
