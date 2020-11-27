<?php
declare(strict_types=1);

namespace frontend;

use app;
use str;

/**
 * Email
 */
function email(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'email', 'value' => str\enc($val)] + $attr['html']);
}
