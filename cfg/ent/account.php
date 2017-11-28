<?php
return [
    'name' => 'Accounts',
    'act' => [
        'admin' => ['name', 'role_id', 'active'],
        'delete' => [],
        'edit' => ['name', 'password', 'role_id', 'active'],
        'login' => [],
        'logout' => [],
        'password' => [],
    ],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'int',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'searchable' => true,
            'maxlength' => 50,
        ],
        'password' => [
            'name' => 'Password',
            'type' => 'password',
            'required' => true,
            'minlength' => 8,
        ],
        'role_id' => [
            'name' => 'Role',
            'type' => 'ent',
            'required' => true,
            'opt' => 'role',
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'toggle',
        ],
        'system' => [
            'name' => 'System',
            'type' => 'toggle',
        ],
    ],
];
