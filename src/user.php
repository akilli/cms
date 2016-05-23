<?php
namespace qnd;

/**
 * User
 *
 * @param string $key
 *
 * @return mixed
 */
function user(string $key = null)
{
    $data = & registry('user');

    if ($data === null) {
        $data = [];
        $id = (int) session('user');
        $data = load('user', ['id' => $id, 'active' => true], ['one' => true]);

        if ($data) {
            $role = load('role', ['id' => $data['role_id'], 'active' => true], ['one' => true]);
            $data['privilege'] = $role ? $role['privilege'] : [];
        }

        if ($id <= 0 || !$data) {
            session('user', null, true);
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
    return user('id') > 0;
}
