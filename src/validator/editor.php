<?php
declare(strict_types=1);

namespace validator;

/**
 * Editor
 */
function editor(string $val): string
{
    return trim(preg_replace('#<p>\s*</p>#', '', strip_tags($val, APP['html.tags'])));
}
