<?php
declare(strict_types=1);

return [
    'name' => 'Roles',
    'action' => ['add', 'delete', 'edit', 'index'],
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
        'privilege' => [
            'name' => 'Privileges',
            'type' => 'multitext',
            'opt' => 'privilege',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
