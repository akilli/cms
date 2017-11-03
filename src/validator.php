<?php
declare(strict_types = 1);

namespace validator;

use app;
use file;
use DomainException;

/**
 * Option validator
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
 * Text validator
 */
function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));
}

/**
 * Email validator
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
 * URL validator
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
 * Rich text validator
 */
function rte(string $val): string
{
    return trim(strip_tags($val, app\cfg('filter', 'rte')));
}

/**
 * ID validator
 */
function id(string $val): string
{
    return trim(preg_replace('#[^a-z0-9]+#', '-', strtolower(strtr($val, app\cfg('filter', 'id')))), '-');
}

/**
 * Date validator
 *
 * @throws DomainException
 */
function date(string $val): string
{
    if ($val && !($val = app\datetime($val, APP['frontend.date'], APP['backend.date']))) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Datetime validator
 *
 * @throws DomainException
 */
function datetime(string $val): string
{
    if ($val && !($val = app\datetime($val, APP['frontend.datetime'], APP['backend.datetime']))) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Time validator
 *
 * @throws DomainException
 */
function time(string $val): string
{
    if ($val && !($val = app\datetime($val, APP['frontend.time'], APP['backend.time']))) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * File validator
 *
 * @throws DomainException
 */
function file(string $val): string
{
    if ($val && !file\type($val)) {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}

/**
 * Image validator
 *
 * @throws DomainException
 */
function image(string $val): string
{
    if ($val && file\type($val) !== 'image') {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}

/**
 * Audio validator
 *
 * @throws DomainException
 */
function audio(string $val): string
{
    if ($val && file\type($val) !== 'audio') {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}

/**
 * Embed validator
 *
 * @throws DomainException
 */
function embed(string $val): string
{
    if ($val && file\type($val) !== 'embed') {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}

/**
 * Video validator
 *
 * @throws DomainException
 */
function video(string $val): string
{
    if ($val && file\type($val) !== 'video') {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}
