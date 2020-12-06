<?php
declare(strict_types=1);

namespace event\account;

use entity;
use session;

function data(): array
{
    $id = (int) session\get('account');

    if ($id && ($data = entity\one('account', crit: [['id', $id]]))) {
        $data['privilege'] = entity\one('role', crit: [['id', $data['role_id']]])['privilege'];
        $data['privilege'][] = '_user_';
        $data['admin'] = in_array('_all_', $data['privilege']);
    } else {
        $data = entity\item('account');
        $data['privilege'] = ['_guest_'];
        $data['admin'] = false;
        session\delete('account');
    }

    return $data;
}
