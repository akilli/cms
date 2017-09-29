<?php
declare(strict_types = 1);

namespace cms;

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
        $id = (int) session_get('account');

        if ($id && ($data = one('account', [['id', $id], ['active', true], ['project_id', [ALL['project'], project('id')]]]))) {
            $data['admin'] = in_array(ALL['privilege'], $data['privilege']);
            $data['global'] = $data['project_id'] === ALL['project'];
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
 *
 * @param string $name
 * @param string $password
 *
 * @return array|null
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
 *
 * @return bool
 */
function account_guest(): bool
{
    return account('id') <= 0;
}

/**
 * Is logged-in user account
 *
 * @return bool
 */
function account_user(): bool
{
    return account('id') > 0;
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
function allowed(string $key): bool
{
    $data = data('privilege');
    $key = resolve($key);

    // Privilege does not exist
    if (empty($data[$key])) {
        return false;
    }

    return !empty($data[$key]['call']) && $data[$key]['call']() || account_admin() || in_array($key, account('privilege') ?? []);
}

/**
 * Check access to given URL considering rewrites
 *
 * @param string $path
 *
 * @return bool
 */
function allowed_url(string $path): bool
{
    if (strpos($path, 'http') === 0) {
        return true;
    }

    $parts = explode('/', ltrim(url_rewrite($path), '/'));

    return data('entity', $parts[0]) && !empty($parts[1]) && allowed($parts[0] . '/' . $parts[1]);
}
