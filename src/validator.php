<?php
declare(strict_types=1);

namespace validator;

use app;
use DomainException;
use entity;
use str;

/**
 * @throws DomainException
 */
function date(string $val): string
{
    if (!$val = app\datetime($val, APP['date.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * @throws DomainException
 */
function datetime(string $val): string
{
    if (!$val = app\datetime($val, APP['datetime.backend'])) {
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
            (array)$val
        );
    }

    return $val;
}

function text(string $val): string
{
    return trim(strip_tags($val));
}

/**
 * @throws DomainException
 */
function time(string $val): string
{
    if (!$val = app\datetime($val, APP['time.backend'])) {
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

function urlpath(string $val): string
{
    $parts = explode('/', $val);
    $key = array_key_last($parts);
    $info = pathinfo($parts[$key]);
    $parts[$key] = $info['filename'];
    $parts = array_map('str\uid', $parts);
    $parts[$key] .= $parts[$key] && $info['extension'] ? '.' . $info['extension'] : '';

    return '/' . implode('/', array_filter($parts));
}
