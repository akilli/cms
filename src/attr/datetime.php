<?php
declare(strict_types=1);

namespace attr\datetime;

use app;
use attr;
use html;
use DomainException;

function frontend(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['datetime.backend'], APP['datetime.frontend']) : '';

    return html\element('input', ['type' => 'datetime-local', 'value' => $val] + $attr['html']);
}

/**
 * @throws DomainException
 */
function validator(string $val): string
{
    return attr\datetime($val, APP['datetime.frontend'], APP['datetime.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}

function viewer(string $val): string
{
    return attr\datetime($val, APP['datetime.backend'], app\cfg('app', 'datetime'));
}
