<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use request;
use session;

/**
 * Login Form
 */
function login(array $block): string
{
    if ($data = app\data('request', 'post')) {
        if (!empty($data['username'])
            && !empty($data['password'])
            && ($account = app\login($data['username'], $data['password']))
        ) {
            session\regenerate();
            session\save('account', $account['id']);
            request\redirect();
            return '';
        }

        app\msg('Invalid name and password combination');
    }

    $entity = app\cfg('entity', 'account');
    $a = [
        'username' => ['unique' => false, 'min' => 0, 'max' => 0],
        'password' => ['min' => 0, 'max' => 0, 'autocomplete' => null],
    ];
    $attrs = arr\extend(arr\extract($entity['attr'], ['username', 'password']), $a);

    return app\tpl($block['tpl'], ['attr' => $attrs, 'data' => [], 'multipart' => false]);
}
