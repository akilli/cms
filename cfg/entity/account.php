<?php
return [
    'name' => 'Accounts',
    'action' => ['admin', 'delete', 'edit', 'login', 'logout', 'profile'],
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
        'role_id' => [
            'name' => 'Role',
            'type' => 'entity',
            'required' => true,
            'ref' => 'role',
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
    ],
];
