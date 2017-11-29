<?php
return [
    'root' => [
        'section' => 'tpl',
        'tpl' => 'layout/root.phtml',
    ],
    'head' => [
        'section' => 'container',
    ],
    'meta' => [
        'section' => 'tpl',
        'tpl' => 'head/meta.phtml',
        'parent_id' => 'head',
        'sort' => -4,
    ],
    'link' => [
        'section' => 'tpl',
        'tpl' => 'head/link.phtml',
        'parent_id' => 'head',
        'sort' => -3,
    ],
    'user' => [
        'section' => 'tpl',
        'tpl' => 'head/user.phtml',
        'priv' => 'account-user',
        'parent_id' => 'head',
        'sort' => -2,
    ],
    'top' => [
        'section' => 'container',
    ],
    'toolbar' => [
        'section' => 'tpl',
        'tpl' => 'layout/toolbar.phtml',
        'priv' => 'account-user',
        'parent_id' => 'top',
        'sort' => -2,
    ],
    'header' => [
        'section' => 'tpl',
        'tpl' => 'layout/header.phtml',
        'parent_id' => 'top',
        'sort' => -1,
    ],
    'msg' => [
        'section' => 'msg',
        'tpl' => 'layout/msg.phtml',
    ],
    'main' => [
        'section' => 'container',
    ],
    'sidebar' => [
        'section' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    'bottom' => [
        'section' => 'container',
    ],
];
