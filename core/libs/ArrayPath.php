<?php

declare(strict_types=1);

namespace Core\Libs;

/**
 * Verify if $keys exist in the $array ArrayPathCollection.
 * @param array $array the array to process
 * @param array $keys the collection of keys to search for
 * @return bool true if keys exist, or false.
 */
function array_path_exists($array, array $keys): bool
{
    if ($array === null) {
        return false;
    }

    $key = array_shift($keys);

    if ($key === null) {
        return true;
    }

    if (array_key_exists($key, $array)) {
        return array_path_exists($array[$key], $keys);
    }

    return false;
}

/**
 * Get the value stored at $keys in the $array ArrayPathCollection.
 * @param array $array the array to process
 * @param array $keys the collection of keys where value is stored.
 * @return mixed the value or null if keys are not found.
 */
function array_path_get($array, array $keys): mixed
{
    if ($array === null) {
        return null;
    }

    $key = array_shift($keys);

    if ($key === null) {
        return $array;
    }

    if (array_key_exists($key, $array)) {
        return array_path_get($array[$key], $keys);
    }

    return null;
}

/**
 * Remove the value stored at $keys in the $array ArrayPathCollection.
 * @param array $array the array to process
 * @param array $keys the collection of keys where value is stored.
 * @return mixed the value removed, or null if keys are not found.
 */
function array_path_remove(&$array, array $keys): mixed
{
    if ($array === null) {
        return null;
    }

    $key = array_shift($keys);

    if (array_key_exists($key, $array)) {
        if (count($keys) === 0) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        return array_path_remove($array[$key], $keys);
    }

    return null;
}

/**
 * Create an mutli-dimensional single entry associative array where $value is located in the tree of $keys
 * @param array $keys
 * @param mixed $value
 * @return array|mixed the array or the value if no key is provided
 */
function array_path_create(array $keys, $value)
{
    return empty($keys) ? $value : [array_shift($keys) => array_path_create($keys, $value)];
}

/**
 * Convert an $arrayPathCollection to a mutli-dimensional associative array
 * @param array $arrayPathCollection the arrayPathCollection to process
 * @return array the resulting mutli-dimensional associative array
 */
function array_path_export(array $arrayPathCollection): array
{
    $results = [];

    foreach ($arrayPathCollection as $item) {
        [$keys, $value] = $item;
        $results = array_replace_recursive($results, array_path_create($keys, $value));
    }

    return $results;
}

/**
 * Convert an associative array to an ArrayPathCollection where the key of each entry is exploded by $separator
 * @param string $separator the separator used to explode $keys
 * @param array $array the associative array to process
 * @return array the resulting ArrayPath collection
 */
function array_path_explode(string $separator, array $array): array
{
    if (empty($array)) {
        return $array;
    }

    $results = [];

    foreach ($array as $key => $value) {
        $keys = explode($separator, (string)$key);

        $results[] = [$keys, $value];
    }

    return $results;
}
