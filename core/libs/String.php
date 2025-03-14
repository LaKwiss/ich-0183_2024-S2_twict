<?php

declare(strict_types=1);

namespace Core\Libs;

/**
 * Convert the string with hyphens to upper camel case (PascalCase),
 * e.g. post-authors => PostAuthors
 *
 * @param string $string The string to convert
 *
 * @return string
 */
function str_upper_camel_case($string)
{
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
}

/**
 * Convert the string with hyphens to camelCase,
 * e.g. add-new => addNew
 *
 * @param string $string The string to convert
 *
 * @return string
 */
function str_lower_camel_case($string)
{
    return lcfirst(str_upper_camel_case($string));
}
