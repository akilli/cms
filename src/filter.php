<?php
declare(strict_types = 1);

namespace filter;

use app;
use attr;
use ent;
use DomainException;

/**
 * Text filter
 */
function text(array $attr, string $val): string
{
    return trim((string) filter_var($val, FILTER_SANITIZE_STRING));
}

/**
 * Email filter
 *
 * @throws DomainException
 */
function email(array $attr, string $val): string
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
function url(array $attr, string $val): string
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
function datetime(array $attr, string $val): string
{
    if (!$val = attr\datetime($val, $attr['cfg.frontend'], $attr['cfg.backend'])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Rich text filter
 */
function rte(array $attr, string $val): string
{
    return trim(strip_tags($val, app\cfg('filter', 'rte')));
}

/**
 * ID filter
 */
function id(array $attr, string $val): string
{
    return trim(preg_replace('#[^a-z0-9-_]+#', '-', strtr(mb_strtolower($val), app\cfg('filter', 'id'))), '-');
}

/**
 * Slug filter
 */
function slug(array $attr, string $val): string
{
    return trim(preg_replace('#[^a-z0-9-]+#', '-', strtr(mb_strtolower($val), app\cfg('filter', 'id'))), '-');
}

/**
 * Path filter
 */
function path(array $attr, string $val): string
{
    if (preg_match('#^https?://#', $val)) {
        return url($attr, $val);
    }

    return '/' . trim(preg_replace('#[^a-z0-9-/\.]+#', '-', strtr(mb_strtolower($val), app\cfg('filter', 'id'))), '-/');
}

/**
 * Spam filter
 *
 * @throws DomainException
 */
function nope(array $attr, bool $val): bool
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
function opt(array $attr, $val)
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
function ent(array $attr, int $val): int
{
    if ($val && !ent\size($attr['ref'], [['id', $val]])) {
        throw new DomainException(app\i18n('Invalid value'));
    }

    return $val;
}

/**
 * Upload filter
 *
 * @throws DomainException
 */
function upload(array $attr, string $val): string
{
    $attr['cfg.filter'] = (array) $attr['cfg.filter'];

    if ($val && !in_array(pathinfo($val, PATHINFO_EXTENSION), $attr['cfg.filter'])) {
        throw new DomainException(app\i18n('Invalid file type, allowed: %s', implode(', ', $attr['cfg.filter'])));
    }

    return path($attr, $val);
}
