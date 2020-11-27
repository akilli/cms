<?php
declare(strict_types=1);

namespace opt;

use app;

/**
 * Bool
 */
function bool(): array
{
    return [app\i18n('No'), app\i18n('Yes')];
}
