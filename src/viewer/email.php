<?php
declare(strict_types=1);

namespace viewer;

use app;

/**
 * Email
 */
function email(string $val): string
{
    return app\html('a', ['href' => 'mailto:' . $val], $val);
}
