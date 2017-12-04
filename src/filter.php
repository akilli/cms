<?php
declare(strict_types = 1);

namespace filter;

use app;
use file;
use DomainException;

/**
 * Option filter
 *
 * @return mixed
 *
 * @throws DomainException
 */
function opt($val, array $opt)
{
    if ($val || is_scalar($val) && !is_string($val)) {
        foreach ((array) $val as $v) {
            if (!isset($opt[$v])) {
                throw new DomainException(app\i18n('Invalid option'));
            }
        }
    }

    return $val;
}

/**
 * Text filter
 */
function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));
}

/**
 * Email filter
 *
 * @throws DomainException
 */
function email(string $val): string
{
    if ($val && !($val = filter_var($val, FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(app\i18n('Invalid email'));
    }

    return $val;
}

/**
 * URL filter
 *
 * @throws DomainException
 */
function url(string $val): string
{
    if ($val && !($val = filter_var($val, FILTER_VALIDATE_URL))) {
        throw new DomainException(app\i18n('Invalid URL'));
    }

    return $val;
}

/**
 * Rich text filter
 */
function rte(string $val): string
{
    return trim(strip_tags($val, app\cfg('filter', 'rte')));
}

/**
 * ID filter
 */
function id(string $val): string
{
    return trim(preg_replace('#[^a-z0-9]+#', '-', strtolower(strtr($val, app\cfg('filter', 'id')))), '-');
}

/**
 * Path filter
 */
function path(string $val): string
{
    return '/' . trim(preg_replace('#[^a-z0-9/\.]+#', '-', strtolower(strtr($val, app\cfg('filter', 'id')))), '-/');
}

/**
 * Date filter
 *
 * @throws DomainException
 */
function date(string $val): string
{
    if (!$val = app\datetime($val, APP['frontend.date'], APP['backend.date'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Datetime filter
 *
 * @throws DomainException
 */
function datetime(string $val): string
{
    if (!$val = app\datetime($val, APP['frontend.datetime'], APP['backend.datetime'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Time filter
 *
 * @throws DomainException
 */
function time(string $val): string
{
    if (!$val = app\datetime($val, APP['frontend.time'], APP['backend.time'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * File filter
 *
 * @throws DomainException
 */
function file(string $val): string
{
    if ($val && !file\type($val)) {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return path($val);
}
