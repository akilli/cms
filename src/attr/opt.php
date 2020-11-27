<?php
declare(strict_types=1);

namespace attr\opt;

use app;
use DomainException;

/**
 * @throws DomainException
 */
function validator(mixed $val, array $attr): mixed
{
    $opt = $attr['opt']();

    if ($val || is_scalar($val) && !is_string($val)) {
        array_map(fn(mixed $v): bool => isset($opt[$v]) ?: throw new DomainException(app\i18n('Invalid value')), (array) $val);
    }

    return $val;
}

function viewer(mixed $val, array $attr): string
{
    $val = is_array($val) ? $val : [$val];
    $opt = $attr['opt']();
    $html = '';

    foreach ($val as $v) {
        if (isset($opt[$v])) {
            $html .= ($html ? ', ' : '') . $opt[$v];
        }
    }

    return $html;
}
