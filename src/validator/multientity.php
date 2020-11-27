<?php
declare(strict_types=1);

namespace validator;

use app;
use entity;
use DomainException;

/**
 * Multi-Entity
 *
 * @throws DomainException
 */
function multientity(array $val, array $attr): array
{
    if ($val && entity\size($attr['ref'], [['id', $val]]) !== count($val)) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}
