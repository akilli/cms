<?php
declare(strict_types=1);

namespace contentfilter\tel;

use str;

/**
 * Converts telephone numbers to HTML entity hex format
 */
function filter(string $html): string
{
    return preg_replace_callback('#(?:tel:)\+\d+#i', fn(array $m): string => str\hex($m[0]), $html);
}
