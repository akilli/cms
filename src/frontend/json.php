<?php
declare(strict_types=1);

namespace frontend;

/**
 * JSON
 */
function json(?array $val, array $attr): string
{
    return textarea(json_encode((array) $val), $attr);
}
