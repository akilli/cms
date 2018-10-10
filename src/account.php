<?php
declare(strict_types = 1);

namespace account;

use app;
use entity;
use session;

/**
 * Initializes account from session and stores account data in registry
 *
 * @return mixed
 */
function get(string $key)
{
    if (($data = & app\registry('account')) === null) {
        $data = [];
        $id = (int) session\get('account');

        if ($id && ($data = entity\one('account', [['id', $id]]))) {
            $role = entity\one('role', [['id', $data['role']]]);
            $data['priv'] = $role['priv'];
            $data['priv'][] = '_user_';
            $data['admin'] = in_array('_all_', $data['priv']);
            unset($data['_old'], $data['_entity']);
        } else {
            $data['priv'] = ['_guest_'];
            session\set('account', null);
        }
    }

    return $data[$key] ?? null;
}

/**
 * Returns account if given credentials are valid and automatically rehashes password if needed
 */
function login(string $name, string $password): ?array
{
    $data = entity\one('account', [['name', $name]]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $acc = ['id' => $data['id'], 'password' => $password];
        entity\save('account', $acc);
        $data['password'] = $acc['password'];
    }

    return $data;
}
