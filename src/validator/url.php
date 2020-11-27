<?php
declare(strict_types=1);

namespace validator;

use app;
use DomainException;

/**
 * URL
 *
 * @throws DomainException
 */
function url(string $val): string
{
    return filter_var($val, FILTER_VALIDATE_URL) ?: throw new DomainException(app\i18n('Invalid value'));
}
