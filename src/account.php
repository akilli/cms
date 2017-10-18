<?php
declare(strict_types = 1);

namespace account;

use app;
use ent;
use session;

/**
 * Initializes account from session and stores account data in registry
 *
 * @return mixed
 */
function data(string $key = null)
{
    if (($data = & app\data('account')) === null) {
        $data = [];
        $id = (int) session\get('account');

        if ($id && ($data = ent\one('account', [['id', $id], ['active', true]]))) {
            $role = ent\one('role', [['id', $data['role_id']], ['active', true]]);
            $data['priv'] = $role ? $role['priv'] : [];
            $data['admin'] = in_array(APP['all'], $data['priv']);
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
