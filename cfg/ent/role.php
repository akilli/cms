<?php
return [
    'name' => 'Roles',
    'type' => 'db',
    'act' => [
        'admin' => ['name', 'active'],
        'delete' => [],
        'edit' => ['name', 'priv', 'active'],
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
        'priv' => [
            'name' => 'Privileges',
            'type' => 'json',
            'opt' => 'priv',
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'bool',
        ],
        'system' => [
            'name' => 'System',
            'type' => 'bool',
        ],
    ],
];
