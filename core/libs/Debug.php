<?php

declare(strict_types=1);

namespace Core\Libs;


function dump(mixed $value): string
{
    $code = var_export($value, true);
    $code = "<?php $code ?>";
    $code = highlight_string($code, true);
    $code = preg_replace('/<span[^>]*>(&lt;\?php\s*|\s*\?&gt;)<\/span>/i', '', $code);

    return $code;
}
