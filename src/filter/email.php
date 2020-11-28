<?php
declare(strict_types=1);

namespace filter\email;

use str;

/**
 * Converts email addresses to HTML entity hex format
 */
function filter(string $html): string
{
    return preg_replace_callback(
        '#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im',
        fn(array $m): string => str\hex($m[0]),
        $html
    );
}
