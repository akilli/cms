<?php
declare(strict_types=1);

return [
    'name' => 'Files',
    'readonly' => true,
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
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'entitychild',
            'required' => true,
            'max' => 50,
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
