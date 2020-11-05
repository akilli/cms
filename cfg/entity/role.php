<?php
return [
    'name' => 'Roles',
    'action' => ['admin', 'delete', 'edit'],
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
        'priv' => [
            'name' => 'Privileges',
            'type' => 'text[]',
            'opt' => 'opt\priv',
        ],
    ],
];
