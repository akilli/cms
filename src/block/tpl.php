<?php
declare(strict_types=1);

namespace block\tpl;

use app;

function render(array $block): string
{
    return app\tpl($block['cfg']['tpl']);
}
