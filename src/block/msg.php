<?php
declare(strict_types=1);

namespace block;

use app;

/**
 * Message
 */
function msg(): string
{
    return app\html('msg');
}
