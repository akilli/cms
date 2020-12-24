<?php
declare(strict_types=1);

namespace attr\textarea;

use html;
use str;

function frontend(?string $val, array $attr): string
{
    return html\element('textarea', $attr['html'], str\enc($val));
}
