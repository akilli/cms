<?php
declare(strict_types=1);

namespace attr\range;

use app;

function frontend(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'range', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
