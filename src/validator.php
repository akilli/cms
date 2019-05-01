<?php
declare(strict_types = 1);

namespace validator;

use app;
use attr;
use entity;
use request;
use DomainException;

/**
 * Text
 */
function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}

/**
 * Email
 *
 * @throws DomainException
 */
function email(string $val): string
{
    if ($val && !($val = filter_var($val, FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * URL
 *
 * @throws DomainException
 */
function url(string $val): string
{
    if ($val && !($val = filter_var($val, FILTER_VALIDATE_URL))) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * URL Path
 */
function urlpath(string $val): string
{
    if (preg_match('#^https?://#', $val)) {
        return url($val);
    }

    return '/' . trim(preg_replace('#[^a-z0-9-_/\.]+#', '-', app\tr($val)), '-/');
}

/**
 * UID
 */
function uid(string $val): string
{
    return trim(preg_replace('#[^a-z0-9-_]+#', '-', app\tr($val)), '-');
}

/**
 * Datetime
 *
 * @throws DomainException
 */
function datetime(string $val): string
{
    if (!$val = attr\datetime($val, APP['attr.datetime.frontend'], APP['attr.datetime.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Date
 *
 * @throws DomainException
 */
function date(string $val): string
{
    if (!$val = attr\datetime($val, APP['attr.date.frontend'], APP['attr.date.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Time
 *
 * @throws DomainException
 */
function time(string $val): string
{
    if (!$val = attr\datetime($val, APP['attr.time.frontend'], APP['attr.time.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Rich text
 */
function rte(string $val, array $attr): string
{
    return trim(preg_replace('#<p>\s*</p>#', '', strip_tags($val, $attr['cfg.validator'])));
}

/**
 * Option
 *
 * @return mixed
 *
 * @throws DomainException
 */
function opt($val, array $attr)
{
    $opt = $attr['opt']();

    if ($val || is_scalar($val) && !is_string($val)) {
        foreach ((array) $val as $v) {
            if (!isset($opt[$v])) {
                throw new DomainException(app\i18n('Invalid value'));
            }
        }
    }

    return $val;
}

/**
 * Entity
 *
 * @throws DomainException
 */
function entity(int $val, array $attr): int
{
    if ($val && !entity\size($attr['ref'], [['id', $val]])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Multi-Entity
 *
 * @throws DomainException
 */
function multientity(array $val, array $attr): array
{
    if ($val && entity\size($attr['ref'], [['id', $val]]) !== count($val)) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * File
 *
 * @throws DomainException
 */
function file(string $val, array $attr): string
{
    $mime = request\file($attr['id'])['type'] ?? null;

    if ($val && (!$mime || !in_array($mime, $attr['accept']))) {
        throw new DomainException(app\i18n('Invalid file type'));
    }

    return urlpath($val);
}
