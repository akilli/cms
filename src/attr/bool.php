<?php
declare(strict_types=1);

namespace attr\bool;

use app;
use html;

function frontend(?bool $val, array $attr): string
{
    $html = html\element('input', ['name' => $attr['html']['name'], 'type' => 'hidden']);

    return $html . html\element('input', ['type' => 'checkbox', 'value' => 1, 'checked' => !!$val] + $attr['html']);
}

function opt(): array
{
    return [app\i18n('No'), app\i18n('Yes')];
}
