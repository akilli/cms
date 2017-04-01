<?php
declare(strict_types = 1);

namespace qnd;

use InvalidArgumentException;

/**
 * Account
 *
 * @param string $key
 *
 * @return mixed
 */
function account(string $key = null)
{
    $data = & registry('account');

    if ($data === null) {
        $data = [];
        $id = (int) session('account');

        if ($id && ($data = one('account', ['id' => $id, 'active' => true, 'project_id' => [PROJECT, project('id')]]))) {
            $role = one('role', ['id' => $data['role_id'], 'active' => true, 'project_id' => $data['project_id']]);
            $data['privilege'] = $role ? $role['privilege'] : [];
            $data['admin'] = in_array(PRIVILEGE, $data['privilege']);
            $data['global'] = $data['project_id'] === PROJECT;
        } else {
            session('account', null, true);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Returns account if given given credentials are valid and automatically rehashes password if needed
 *
 * @param string $name
 * @param string $password
 *
 * @return array|null
 */
function account_login(string $name, string $password): ?array
{
    $data = one('account', ['name' => $name, 'active' => true]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $data['password'] = $password;
        save('account', $data);
    }

    return $data;
}

/**
 * Is registered
 *
 * @return bool
 */
function registered(): bool
{
    return account('id') > 0;
}

/**
 * Is unregistered
 *
 * @return bool
 */
function unregistered(): bool
{
    return account('id') <= 0;
}

/**
 * Is admin account
 *
 * @return bool
 */
function account_admin(): bool
{
    return !!account('admin');
}

/**
 * Is global account
 *
 * @return bool
 */
function account_global(): bool
{
    return !!account('global');
}

/**
 * Check access
 *
 * @param string $key
 *
 * @return bool
 */
function allowed(string $key = null): bool
{
    $data = data('privilege');
    $key = privilege($key);

    // Privilege does not exist
    if (empty($data[$key])) {
        return false;
    }

    return !empty($data[$key]['callback']) && $data[$key]['callback']()
        || account_admin()
        || in_array($key, account('privilege') ?? []);
}

/**
 * Retrieve full privilege id from current request
 *
 * @param string $key
 *
 * @return string
 */
function privilege(string $key = null): string
{
    if (!$key) {
        return request('entity') . '.' . request('action');
    }

    return substr_count($key, '.') === 0 ? request('entity') . '.' . $key : $key;
}

/**
 * Retrieve full privilege id from given request path considering rewrites
 *
 * @param string $path
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function privilege_url(string $path): string
{
    if (strpos($path, 'http') === 0) {
        throw new InvalidArgumentException(_('Invalid request path %s', $path));
    }

    $parts = explode('/', ltrim(url_rewrite($path), '/'));;
    $parts[1] = $parts[1] ?? 'index';

    return implode('.', array_slice($parts, 0, 2));
}
