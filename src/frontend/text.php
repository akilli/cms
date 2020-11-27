<?php
declare(strict_types=1);

namespace frontend;

use app;
use str;

/**
 * Text
 */
function text(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'text', 'value' => str\enc($val)] + $attr['html']);
}
