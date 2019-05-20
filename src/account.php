<?php
declare(strict_types = 1);

namespace account;

use entity;

/**
 * Returns account if given credentials are valid and automatically rehashes password if needed
 */
function login(string $username, string $password): ?array
{
    $data = entity\one('account', [['username', $username]]);

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
