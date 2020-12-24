<?php
declare(strict_types=1);

namespace attr\date;

use app;
use attr;
use html;
use DomainException;

function frontend(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['date.backend'], APP['date.frontend']) : '';

    return html\element('input', ['type' => 'date', 'value' => $val] + $attr['html']);
}

/**
 * @throws DomainException
 */
function validator(string $val): string
{
    return attr\datetime($val, APP['date.frontend'], APP['date.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}

function viewer(string $val): string
{
    return attr\datetime($val, APP['date.backend'], app\cfg('app', 'date'));
}
