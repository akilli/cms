<?php
declare(strict_types=1);

namespace attr\email;

use app;
use str;
use DomainException;

function frontend(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'email', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * @throws DomainException
 */
function validator(string $val): string
{
    return filter_var($val, FILTER_VALIDATE_EMAIL) ?: throw new DomainException(app\i18n('Invalid value'));
}

function viewer(string $val): string
{
    return app\html('a', ['href' => 'mailto:' . $val], $val);
}
