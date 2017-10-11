<?php
declare(strict_types = 1);

namespace validator;

use const attr\{DATE, DATETIME, TIME};
use function app\_;
use app;
use entity;
use filter;
use DomainException;

/**
 * Option validator
 *
 * @throws DomainException
 */
function opt(array $attr, array $data): array
{
    if (!empty($data[$attr['id']]) || is_scalar($data[$attr['id']]) && !is_string($data[$attr['id']])) {
        foreach ((array) $data[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(_('Invalid option for attribute %s', $attr['name']));
            }
        }
    }

    return $data;
}

/**
 * Page validator
 *
 * @throws DomainException
 */
function page(array $attr, array $data): array
{
    $old = $data['_old']['id'] ?? null;

    if ($data[$attr['id']] && $old && in_array($old, entity\one('page', [['id', $data[$attr['id']]]])['path'])) {
        throw new DomainException(_('Cannot assign the page itself or a child page as parent'));
    }

    return $data;
}

/**
 * Text validator
 */
function text(array $attr, array $data): array
{
    $data[$attr['id']] = trim((string) filter_var($data[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $data;
}

/**
 * Password validator
 *
 * @throws DomainException
 */
function password(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = password_hash($data[$attr['id']], PASSWORD_DEFAULT))) {
        throw new DomainException(_('Invalid password'));
    }

    return $data;
}

/**
 * ID validator
 */
function id(array $attr, array $data): array
{
    $data = text($attr, $data);
    $data[$attr['id']] = filter\id($data[$attr['id']]);

    return $data;
}

/**
 * Email validator
 *
 * @throws DomainException
 */
function email(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(_('Invalid email'));
    }

    return $data;
}

/**
 * URL validator
 *
 * @throws DomainException
 */
function url(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_URL))) {
        throw new DomainException(_('Invalid URL'));
    }

    return $data;
}

/**
 * JSON validator
 *
 * @throws DomainException
 */
function json(array $attr, array $data): array
{
    if ($data[$attr['id']] && json_decode($data[$attr['id']], true) === null) {
        throw new DomainException(_('Invalid JSON notation'));
    }

    if (!$data[$attr['id']]) {
        $data[$attr['id']] = '[]';
    }

    return $data;
}

/**
 * Rich text validator
 *
 * @return array
 */
function rte(array $attr, array $data): array
{
    $data[$attr['id']] = filter\html($data[$attr['id']]);

    return $data;
}

/**
 * Date validator
 *
 * @throws DomainException
 */
function date(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter\date($data[$attr['id']], DATE['f'], DATE['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Datetime validator
 *
 * @throws DomainException
 */
function datetime(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter\date($data[$attr['id']], DATETIME['f'], DATETIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Time validator
 *
 * @throws DomainException
 */
function time(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter\date($data[$attr['id']], TIME['f'], TIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * File validator
 *
 * @throws DomainException
 */
function file(array $attr, array $data): array
{
    if ($data[$attr['id']]) {
        if (!in_array($attr['type'], app\cfg('file', pathinfo($data[$attr['id']], PATHINFO_EXTENSION)) ?? [])) {
            throw new DomainException(_('Invalid file %s', $data[$attr['id']]));
        }

        if (is_file(app\path('data', $data[$attr['id']])) && ($data['_old'][$attr['id']] ?? null) !== $data[$attr['id']]) {
            throw new DomainException(_('File %s already exists', $data[$attr['id']]));
        }
    }

    return $data;
}
