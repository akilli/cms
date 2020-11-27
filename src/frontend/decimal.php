<?php
declare(strict_types=1);

namespace frontend;

use app;

/**
 * Decimal
 */
function decimal(?float $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '0.01']);
}
