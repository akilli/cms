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
        $data['privilege'][] = '_public_';
        $data['privilege'][] = '_user_';
    } else {
        $data = entity\item('account');
        $data['privilege'] = ['_public_', '_guest_'];
        session\delete('account');
    }

    return $data;
}
