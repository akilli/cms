<?php
declare(strict_types=1);

namespace attr\int;

use app;

function frontend(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '1']);
}
