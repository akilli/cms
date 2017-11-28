<?php
return [
    'name' => 'Files',
    'act' => [
        'admin' => ['name', 'size'],
        'asset' => [],
        'browser' => ['name', 'size'],
        'delete' => [],
        'edit' => ['name', 'info'],
    ],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'int',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'file',
            'required' => true,
            'unique' => true,
            'searchable' => true,
        ],
        'info' => [
            'name' => 'Info',
            'type' => 'textarea',
            'searchable' => true,
            'val' => '',
        ],
        'size' => [
            'name' => 'Size',
            'type' => 'int',
            'viewer' => 'filesize',
        ],
    ],
];
