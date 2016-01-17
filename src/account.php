<?php
namespace account;

use model;
use session;

/**
 * User
 *
 * @param string $key
 *
 * @return mixed
 */
function user($key = null)
{
    static $data;

    if ($data === null) {
        $id = (int) session\data('account');
        $data = model\load('account', ['id' => $id, 'is_active' => true], false);

        if ($data) {
            $role = model\load('role', ['id' => $data['role_id'], 'is_active' => true], false);
            $data['privilege'] = $role ? $role['privilege'] : [];
        }

        if ($id <= 0 || !$data) {
            session\data('account', null, true);
        }
    }

    // If $key is null, return whole data
    if ($key === null) {
        return $data;
    }

    return isset($data[$key]) ? $data[$key] : null;
}

/**
 * Is registered
 *
 * @return bool
 */
function registered()
{
    return user('id') > 0;
}
