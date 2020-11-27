<?php
declare(strict_types=1);

namespace attr\password;

use app;

function frontend(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'password', 'value' => false] + $attr['html']);
}
