<?php
declare(strict_types = 1);

namespace account;

use const app\ALL;
use app;
use ent;
use session;

const PRIV = ['name' => null, 'call' => null, 'active' => true, 'sort' => 0];

/**
 * Initializes account from session and stores account data in registry
 *
 * @return mixed
 */
function data(string $key = null)
{
    $data = & app\data('account');

    if ($data === null) {
        $data = [];
        $id = (int) session\get('account');

        if ($id && ($data = ent\one('account', [['id', $id], ['active', true]]))) {
            $role = ent\one('role', [['id', $data['role_id']], ['active', true]]);
            $data['priv'] = $role ? $role['priv'] : [];
            $data['admin'] = in_array(ALL, $data['priv']);
            unset($data['_old'], $data['_ent']);
        } else {
            session\set('account', null);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Returns account if given credentials are valid and automatically rehashes password if needed
 */
function login(string $name, string $password): ?array
{
    $data = ent\one('account', [['name', $name], ['active', true]]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $data['password'] = $password;
        ent\save('account', $data);
    }

    return $data;
}

/**
 * Is logged-in user account
 */
function user(): bool
{
    return data('id') > 0;
}

/**
 * Check access
 */
function allowed(string $key): bool
{
    $key = app\resolve($key);

    if (!$cfg = app\cfg('priv', $key)) {
        return false;
    }

    return !$cfg['active'] || $cfg['call'] && $cfg['call']() || data('admin') || in_array($key, data('priv') ?? []);
}

/**
 * Check access to given URL considering rewrites
 */
function allowed_url(string $path): bool
{
    if (strpos($path, 'http') === 0) {
        return true;
    }

    $parts = explode('/', ltrim(app\rewrite($path), '/'));

    return app\cfg('ent', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}
