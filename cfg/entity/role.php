<?php
return [
    'name' => 'Roles',
    'actions' => ['admin', 'delete', 'edit'],
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
        'privilege' => [
            'name' => 'Privileges',
            'type' => 'checkbox',
            'backend' => 'json',
            'multiple' => true,
            'opt' => 'cms\opt_privilege',
            'actions' => ['edit'],
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
