<?php
declare(strict_types=1);

namespace attr\urlpath;

use attr\url;
use str;

function validator(string $val): string
{
    if (preg_match('#^https?://#', $val)) {
        return url\validator($val);
    }

    return '/' . trim(preg_replace('#[^a-z0-9_\-\./]+#', '-', str\tr($val)), '-/');
}
