<?php
namespace akilli;

use model;

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
        $data = model\load('account', ['id' => $id, 'is_active' => true], false);

        if ($data) {
            $role = model\load('role', ['id' => $data['role_id'], 'is_active' => true], false);
            $data['privilege'] = $role ? $role['privilege'] : [];
        }

        if ($id <= 0 || !$data) {
            session('account', null, true);
        }
    }

    // If $key is null, return whole data
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
