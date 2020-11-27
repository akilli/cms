<?php
declare(strict_types=1);

namespace frontend;

use app;
use str;

/**
 * URL
 */
function url(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'url', 'value' => str\enc($val)] + $attr['html']);
}
