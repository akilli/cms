<?php
declare(strict_types = 1);

namespace filter;

use app;
use attr;
use entity;
use request;
use DomainException;

/**
 * Text filter
 */
function text(string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}

/**
 * Email filter
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
 * URL filter
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
 * Datetime filter
 *
 * @throws DomainException
 */
function datetime(string $val, array $attr): string
{
    if (!$val = attr\datetime($val, $attr['cfg.frontend'], $attr['cfg.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
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
    return trim(preg_replace('#[^a-z0-9-_]+#', '-', strtr(mb_strtolower($val), app\cfg('filter', 'id'))), '-');
}

/**
 * Path filter
 */
function path(string $val): string
{
    if (preg_match('#^https?://#', $val)) {
        return url($val);
    }

    return '/' . trim(preg_replace('#[^a-z0-9-_/\.]+#', '-', strtr(mb_strtolower($val), app\cfg('filter', 'id'))), '-/');
}

/**
 * Spam filter
 *
 * @throws DomainException
 */
function nope(bool $val): bool
{
    if ($val) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Option filter
 *
 * @return mixed
 *
 * @throws DomainException
 */
function opt($val, array $attr)
{
    if ($val || is_scalar($val) && !is_string($val)) {
        foreach ((array) $val as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(app\i18n('Invalid value'));
            }
        }
    }

    return $val;
}

/**
 * Entity filter
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
 * Upload filter
 *
 * @throws DomainException
 */
function upload(string $val, array $attr): string
{
    $cfg = $attr['cfg.filter'] ? app\cfg('filter', $attr['cfg.filter']) : null;
    $mime = request\get('file')[$attr['id']]['type'] ?? null;

    if ($val && (!$mime || !in_array($mime, $cfg))) {
        throw new DomainException(app\i18n('Invalid file type'));
    }

    return path($val);
}
