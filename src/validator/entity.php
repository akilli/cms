<?php
declare(strict_types=1);

namespace validator;

use app;
use entity;
use DomainException;

/**
 * Entity
 *
 * @throws DomainException
 */
function entity(int $val, array $attr): int
{
    if ($val && !entity\size($attr['ref'], [['id', $val]])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}
