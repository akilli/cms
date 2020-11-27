<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Telephone
 */
function tel(string $val): string
{
    return app\html('a', ['href' => 'tel:' . $val], $val);
}
