<?php
declare(strict_types=1);

return [
    'name' => 'Accounts',
    'action' => ['add', 'delete', 'edit', 'index', 'login', 'logout', 'profile', 'view'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'max' => 50,
        ],
        'uid' => [
            'name' => 'UID',
            'type' => 'uid',
            'required' => true,
            'unique' => true,
            'autoedit' => false,
            'max' => 100,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'urlpath',
            'auto' => true,
            'unique' => true,
            'max' => 102,
        ],
        'role_id' => [
            'name' => 'Role',
            'type' => 'entity',
            'ref' => 'role',
            'required' => true,
        ],
        'username' => [
            'name' => 'Username',
            'type' => 'uid',
            'required' => true,
            'unique' => true,
            'max' => 50,
        ],
        'password' => [
            'name' => 'Password',
            'type' => 'password',
            'required' => true,
            'min' => 8,
            'max' => 255,
        ],
        'email' => [
            'name' => 'Email',
            'type' => 'email',
            'nullable' => true,
            'unique' => true,
            'max' => 50,
        ],
        'image' => [
            'name' => 'Image',
            'type' => 'image',
            'nullable' => true,
            'unique' => true,
            'max' => 255,
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'bool',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
