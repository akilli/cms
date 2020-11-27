<?php
declare(strict_types=1);

namespace validator;

use app;
use attr;
use DomainException;

/**
 * Datetime
 *
 * @throws DomainException
 */
function datetime(string $val): string
{
    return attr\datetime($val, APP['datetime.frontend'], APP['datetime.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}
