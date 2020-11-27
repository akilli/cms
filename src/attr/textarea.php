<?php
declare(strict_types=1);

namespace attr\textarea;

use app;
use str;

function frontend(?string $val, array $attr): string
{
    return app\html('textarea', $attr['html'], str\enc($val));
}
