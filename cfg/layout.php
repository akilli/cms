<?php
return [
    'root' => [
        'type' => 'tpl',
        'tpl' => 'layout/root.phtml',
    ],
    'head' => [
        'type' => 'container',
    ],
    'meta' => [
        'type' => 'tpl',
        'tpl' => 'head/meta.phtml',
        'parent_id' => 'head',
        'sort' => -4,
    ],
    'link' => [
        'type' => 'tpl',
        'tpl' => 'head/link.phtml',
        'parent_id' => 'head',
        'sort' => -3,
    ],
    'user' => [
        'type' => 'tpl',
        'tpl' => 'head/user.phtml',
        'priv' => 'account-user',
        'parent_id' => 'head',
        'sort' => -2,
    ],
    'top' => [
        'type' => 'container',
    ],
    'toolbar' => [
        'type' => 'tpl',
        'tpl' => 'layout/toolbar.phtml',
        'priv' => 'account-user',
        'parent_id' => 'top',
        'sort' => -2,
    ],
    'header' => [
        'type' => 'tpl',
        'tpl' => 'layout/header.phtml',
        'parent_id' => 'top',
        'sort' => -1,
    ],
    'msg' => [
        'type' => 'msg',
        'tpl' => 'layout/msg.phtml',
    ],
    'main' => [
        'type' => 'container',
    ],
    'sidebar' => [
        'type' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    'bottom' => [
        'type' => 'container',
    ],
];
