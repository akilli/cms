<?php
return [
    'name' => 'Accounts',
    'actions' => ['admin', 'delete', 'edit', 'login', 'logout', 'password'],
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
            'uniq' => true,
            'searchable' => true,
            'actions' => ['admin', 'edit'],
            'maxlength' => 50,
        ],
        'password' => [
            'name' => 'Password',
            'type' => 'password',
            'required' => true,
            'actions' => ['edit'],
            'minlength' => 8,
        ],
        'role_id' => [
            'name' => 'Role',
            'type' => 'entity',
            'required' => true,
            'opt' => 'role',
            'actions' => ['admin', 'edit'],
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'checkbox',
            'actions' => ['admin', 'edit'],
        ],
        'system' => [
            'name' => 'System',
            'type' => 'checkbox',
        ],
    ],
];
