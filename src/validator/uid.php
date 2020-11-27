<?php
declare(strict_types=1);

namespace validator;

use str;

/**
 * UID
 */
function uid(string $val): string
{
    return trim(preg_replace('#[^a-z0-9_\-]+#', '-', str\tr($val)), '-');
}
