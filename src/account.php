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
function data(string $key)
{
    if (($data = & app\data('account')) === null) {
        $data = [];
        $id = (int) session\get('account');

        if ($id && ($data = ent\one('account', [['id', $id]]))) {
            $role = ent\one('role', [['id', $data['role_id']]]);
            $data['priv'] = $role['priv'];
            $data['priv'][] = APP['account.user'];
            $data['admin'] = in_array(APP['all'], $data['priv']);
            unset($data['_old'], $data['_ent']);
        } else {
            $data['priv'] = [APP['account.guest']];
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
    $data = ent\one('account', [['name', $name]]);

    if (!$data || !password_verify($password, $data['password'])) {
        return null;
    }

    if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
        $acc = ['id' => $data['id'], 'password' => $password];
        ent\save('account', $acc);
        $data['password'] = $acc['password'];
    }

    return $data;
}
