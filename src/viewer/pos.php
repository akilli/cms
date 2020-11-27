<?php
declare(strict_types=1);

namespace viewer;

/**
 * Position
 */
function pos(string $val): string
{
    return preg_replace('#(^|\.)0+#', '$1', $val);
}
