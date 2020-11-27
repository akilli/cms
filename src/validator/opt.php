<?php
declare(strict_types=1);

namespace validator;

use app;
use DomainException;

/**
 * Option
 *
 * @throws DomainException
 */
function opt(mixed $val, array $attr): mixed
{
    $opt = $attr['opt']();

    if ($val || is_scalar($val) && !is_string($val)) {
        array_map(fn(mixed $v): bool => isset($opt[$v]) ?: throw new DomainException(app\i18n('Invalid value')), (array) $val);
    }

    return $val;
}
