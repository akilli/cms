<?php
declare(strict_types=1);

namespace validator;

/**
 * Text
 */
function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}
