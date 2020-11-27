<?php
declare(strict_types=1);

namespace validator;

use str;

/**
 * URL Path
 */
function urlpath(string $val): string
{
    if (preg_match('#^https?://#', $val)) {
        return url($val);
    }

    return '/' . trim(preg_replace('#[^a-z0-9_\-\./]+#', '-', str\tr($val)), '-/');
}
