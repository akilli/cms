<?php
declare(strict_types=1);

namespace attr\url;

use app;
use html;
use str;
use DomainException;

function frontend(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'url', 'value' => str\enc($val)] + $attr['html']);
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
    return html\element('a', ['href' => $val], $val);
}
