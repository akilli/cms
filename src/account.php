<?php
namespace qnd;

/**
 * User
 *
 * @param string $key
 *
 * @return mixed
 */
function account(string $key = null)
{
    static $data;

    if ($data === null) {
        $id = (int) session('account');
        $data = model_load('account', ['id' => $id, 'is_active' => true], false);

        if ($data) {
            $role = model_load('role', ['id' => $data['role_id'], 'is_active' => true], false);
            $data['privilege'] = $role ? $role['privilege'] : [];
        }

        if ($id <= 0 || !$data) {
            session('account', null, true);
        }
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
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
