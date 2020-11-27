<?php
declare(strict_types=1);

namespace validator;

use app;
use attr;
use DomainException;

/**
 * Date
 *
 * @throws DomainException
 */
function date(string $val): string
{
    return attr\datetime($val, APP['date.frontend'], APP['date.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}
