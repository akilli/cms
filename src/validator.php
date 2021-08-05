<?php
declare(strict_types=1);

namespace validator;

use DomainException;
use app;
use attr;
use entity;
use str;

/**
 * @throws DomainException
 */
function date(string $val): string
{
    if (!$val = attr\datetime($val, APP['date.frontend'], APP['date.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * @throws DomainException
 */
function datetime(string $val): string
{
    if (!$val = attr\datetime($val, APP['datetime.frontend'], APP['datetime.backend'])) {
         throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

function editor(string $val): string
{
    return trim(preg_replace('#<p>\s*</p>#', '', strip_tags($val, APP['html.tags'])));
}

/**
 * @throws DomainException
 */
function email(string $val): string
{
    return filter_var($val, FILTER_VALIDATE_EMAIL) ?: throw new DomainException(app\i18n('Invalid value'));
}

/**
 * @throws DomainException
 */
function entity(int $val, array $attr): int
{
    if ($val && !entity\size($attr['ref'], crit: [['id', $val]])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * @throws DomainException
 */
function file(string $val, array $attr): string
{
    $mime = app\data('request', 'file')[$attr['id']]['type'] ?? null;

    if ($val && (!$mime || !in_array($mime, $attr['accept']))) {
        throw new DomainException(app\i18n('Invalid file type'));
    }

    return '/' . trim(preg_replace('#[^a-z0-9_\-\./]+#', '-', str\tr($val)), '-/');
}

/**
 * @throws DomainException
 */
function multientity(array $val, array $attr): array
{
    if ($val && entity\size($attr['ref'], crit: [['id', $val]]) !== count($val)) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * @throws DomainException
 */
function opt(mixed $val, array $attr): mixed
{
    $opt = $attr['opt']();

    if ($val || is_scalar($val) && !is_string($val)) {
        array_map(
            fn(mixed $v): bool => isset($opt[$v]) ?: throw new DomainException(app\i18n('Invalid value')),
            (array) $val
        );
    }

    return $val;
}

function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}

/**
 * @throws DomainException
 */
function time(string $val): string
{
    if (!$val = attr\datetime($val, APP['time.frontend'], APP['time.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

function uid(string $val): string
{
    return str\uid($val);
}

/**
 * @throws DomainException
 */
function url(string $val): string
{
    return filter_var($val, FILTER_VALIDATE_URL) ?: throw new DomainException(app\i18n('Invalid value'));
}
