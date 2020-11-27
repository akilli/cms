<?php
declare(strict_types=1);

namespace frontend;

use app;
use str;

/**
 * Textarea
 */
function textarea(?string $val, array $attr): string
{
    return app\html('textarea', $attr['html'], str\enc($val));
}
