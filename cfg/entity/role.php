<?php
return [
    'name' => 'Roles',
    'action' => ['delete', 'edit', 'index'],
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
            'type' => 'privilege',
        ],
    ],
];
