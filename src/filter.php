<?php
declare(strict_types = 1);

namespace filter;

use app;

/**
 * ID filter
 */
function id(string $val): string
{
    return trim(preg_replace('#[^a-z0-9]+#', '-', strtolower(strtr($val, app\cfg('filter', 'id')))), '-');
}
