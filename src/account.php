<?php
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

        if ($id && ($data = one('account', ['id' => $id, 'active' => true, 'project_id' => project('ids')]))) {
            $role = one('role', ['id' => $data['role_id'], 'active' => true, 'project_id' => $data['project_id']]);
            $data['privilege'] = $role ? $role['privilege'] : [];
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
    $item = one('account', ['name' => $name, 'active' => true, 'project_id' => project('ids')]);

    if (!$item || !password_verify($password, $item['password'])) {
        return null;
    }

    if (!password_needs_rehash($item['password'], PASSWORD_DEFAULT)) {
        return $item;
    }

    $data = [$item['id'] => array_replace($item, ['password' => $password])];

    return save('account', $data) ? $data[$item['id']] : $item;
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

    return empty($data[$key]['active'])
        || !empty($data[$key]['callback']) && $data[$key]['callback']()
        || array_intersect([PRIVILEGE, $key], account('privilege') ?? []);
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

    $parts = explode('/', ltrim(url_rewrite($path), '/'));
    $parts[1] = $parts[1] ?? 'index';

    return implode('.', array_slice($parts, 0, 2));
}
