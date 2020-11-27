<?php
declare(strict_types=1);

namespace attr\decimal;

use app;

function frontend(?float $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '0.01']);
}
