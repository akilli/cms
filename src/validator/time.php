<?php
declare(strict_types=1);

namespace validator;

use app;
use attr;
use DomainException;

/**
 * Time
 *
 * @throws DomainException
 */
function time(string $val): string
{
    return attr\datetime($val, APP['time.frontend'], APP['time.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}
