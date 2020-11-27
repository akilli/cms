<?php
declare(strict_types=1);

namespace frontend;

use app;
use str;

/**
 * Telephone
 */
function tel(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'tel', 'value' => str\enc($val)] + $attr['html']);
}
