<?php
declare(strict_types=1);

namespace attr\url;

use app;
use DomainException;
use str;

function frontend(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'url', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * @throws DomainException
 */
function validator(string $val): string
{
    return filter_var($val, FILTER_VALIDATE_URL) ?: throw new DomainException(app\i18n('Invalid value'));
}

function viewer(string $val): string
{
    return app\html('a', ['href' => $val], $val);
}
