<?php
declare(strict_types=1);

namespace block\msg;

use app;

function render(): string
{
    return app\html('msg');
}
