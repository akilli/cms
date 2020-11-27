<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Password
 */
function password(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'password', 'value' => false] + $attr['html']);
}
