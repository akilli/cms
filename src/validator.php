<?php
declare(strict_types = 1);

namespace validator;

use app;
use file;
use filter;
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
    if (!empty($val) || is_scalar($val) && !is_string($val)) {
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
 * Password validator
 *
 * @throws DomainException
 */
function password(string $val): string
{
    if ($val && !($val = password_hash($val, PASSWORD_DEFAULT))) {
        throw new DomainException(app\i18n('Invalid password'));
    }

    return $val;
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
    return filter\html($val);
}

/**
 * Date validator
 *
 * @throws DomainException
 */
function date(string $val): string
{
    if ($val && !($val = filter\date($val, DATE['f'], DATE['b']))) {
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
    if ($val && !($val = filter\date($val, DATETIME['f'], DATETIME['b']))) {
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
    if ($val && !($val = filter\date($val, TIME['f'], TIME['b']))) {
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
    if ($val && !file\type($val, 'file')) {
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
    if ($val && !file\type($val, 'image')) {
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
    if ($val && !file\type($val, 'audio')) {
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
    if ($val && !file\type($val, 'embed')) {
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
    if ($val && !file\type($val, 'video')) {
        throw new DomainException(app\i18n('Invalid file %s', $val));
    }

    return $val;
}
