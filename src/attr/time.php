<?php
declare(strict_types=1);

namespace attr\time;

use app;
use attr;
use html;
use DomainException;

function frontend(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['time.backend'], APP['time.frontend']) : '';

    return html\element('input', ['type' => 'time', 'value' => $val] + $attr['html']);
}

/**
 * @throws DomainException
 */
function validator(string $val): string
{
    return attr\datetime($val, APP['time.frontend'], APP['time.backend']) ?: throw new DomainException(app\i18n('Invalid value'));
}

function viewer(string $val): string
{
    return attr\datetime($val, APP['time.backend'], app\cfg('app', 'time'));
}
