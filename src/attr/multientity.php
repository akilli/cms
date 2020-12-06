<?php
declare(strict_types=1);

namespace attr\multientity;

use app;
use entity;
use DomainException;

/**
 * @throws DomainException
 */
function validator(array $val, array $attr): array
{
    if ($val && entity\size($attr['ref'], [['id', $val]]) !== count($val)) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

function viewer(array $val, array $attr): string
{
    return implode(', ', array_column(entity\all($attr['ref'], crit: [['id', $val]], select: ['name']), 'name'));
}
