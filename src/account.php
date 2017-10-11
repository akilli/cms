<?php
declare(strict_types = 1);

namespace account;

use const app\ALL;
use app;
use entity;
use session;

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

        if ($id && ($data = entity\one('account', [['id', $id], ['active', true]]))) {
            $role = entity\one('role', [['id', $data['role_id']], ['active', true]]);
            $data['privilege'] = $role ? $role['privilege'] : [];
            $data['admin'] = in_array(ALL, $data['privilege']);
            unset($data['_old'], $data['_entity']);
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
    $data = entity\one('account', [['name', $name], ['active', true]]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $data['password'] = $password;
        entity\save('account', $data);
    }

    return $data;
}

/**
 * Is not logged-in guest
 */
function guest(): bool
{
    return data('id') <= 0;
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
    $cfg = app\cfg('privilege');
    $key = app\resolve($key);

    if (empty($cfg[$key])) {
        return false;
    }

    return !empty($cfg[$key]['call']) && $cfg[$key]['call']() || data('admin') || in_array($key, data('privilege') ?? []);
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

    return app\cfg('entity', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}
