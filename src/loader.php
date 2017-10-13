<?php
declare(strict_types = 1);

namespace loader;

/**
 * JSON loader
 */
function json($val): array
{
    return $val && ($val = json_decode($val, true)) ? $val : [];
}
