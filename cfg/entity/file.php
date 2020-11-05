<?php
return [
    'name' => 'Files',
    'readonly' => true,
    'action' => ['browser'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'max' => 100,
        ],
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'entity_id',
            'required' => true,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'file',
            'required' => true,
            'unique' => true,
        ],
        'thumb' => [
            'name' => 'Thumbnail',
            'type' => 'image',
            'nullable' => true,
            'unique' => true,
        ],
        'mime' => [
            'name' => 'MIME-Type',
            'type' => 'text',
            'required' => true,
            'max' => 255,
        ],
        'info' => [
            'name' => 'Info',
            'type' => 'textarea',
            'required' => true,
        ],
    ],
];
