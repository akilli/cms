<?php
declare(strict_types=1);

namespace event\account;

use entity;
use session;

function data(): array
{
    $id = (int) session\get('account');

    if ($id && ($data = entity\one('account', [['id', $id]]))) {
        $data['priv'] = entity\one('role', [['id', $data['role_id']]])['priv'];
        $data['priv'][] = '_user_';
        $data['admin'] = in_array('_all_', $data['priv']);
    } else {
        $data = entity\item('account');
        $data['priv'] = ['_guest_'];
        $data['admin'] = false;
        session\delete('account');
    }

    return $data;
}
