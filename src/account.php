<?php
declare(strict_types = 1);

namespace cms;

/**
 * Initializes account from session and stores account data in registry
 */
function account(string $key = null)
{
    $data = & registry('account');

    if ($data === null) {
        $data = [];
        $id = (int) session_get('account');

        if ($id && ($data = one('account', [['id', $id], ['active', true]]))) {
            $role = one('role', [['id', $data['role_id']], ['active', true]]);
            $data['privilege'] = $role ? $role['privilege'] : [];
            $data['admin'] = in_array(ALL, $data['privilege']);
            unset($data['_old'], $data['_entity']);
        } else {
            session_set('account', null);
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
function account_login(string $name, string $password): ?array
{
    $data = one('account', [['name', $name], ['active', true]]);

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
 * Is not logged-in guest
 */
function account_guest(): bool
{
    return account('id') <= 0;
}

/**
 * Is logged-in user account
 */
function account_user(): bool
{
    return account('id') > 0;
}

/**
 * Check access
 */
function allowed(string $key): bool
{
    $data = cfg('privilege');
    $key = resolve($key);

    // Privilege does not exist
    if (empty($data[$key])) {
        return false;
    }

    return !empty($data[$key]['call']) && $data[$key]['call']() || account('admin') || in_array($key, account('privilege') ?? []);
}

/**
 * Check access to given URL considering rewrites
 */
function allowed_url(string $path): bool
{
    if (strpos($path, 'http') === 0) {
        return true;
    }

    $parts = explode('/', ltrim(url_rewrite($path), '/'));

    return cfg('entity', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}
