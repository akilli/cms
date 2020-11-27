<?php
declare(strict_types=1);

namespace attr\uid;

use str;

function validator(string $val): string
{
    return trim(preg_replace('#[^a-z0-9_\-]+#', '-', str\tr($val)), '-');
}
