<?php
declare(strict_types=1);

return [
    'name' => 'Files',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'file',
            'required' => true,
            'unique' => true,
            'autoindex' => true,
            'max' => 255,
        ],
        'mime' => [
            'name' => 'MIME-Type',
            'type' => 'text',
            'required' => true,
            'autoedit' => false,
            'max' => 255,
        ],
        'info' => [
            'name' => 'Info',
            'type' => 'textarea',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
