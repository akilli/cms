<?php
return [
    'name' => 'Roles',
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
            'type' => 'toggle',
        ],
        'system' => [
            'name' => 'System',
            'type' => 'toggle',
        ],
    ],
];
