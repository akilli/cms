<?php
return [
    'name' => 'Blocks',
    'readonly' => true,
    'action' => ['admin', 'api'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'max' => 255,
        ],
        'entity_id' => [
            'name' => 'Entity',
            'type' => 'entity_id',
            'required' => true,
        ],
        'title' => [
            'name' => 'Title',
            'type' => 'text',
            'nullable' => true,
            'max' => 255,
        ],
        'link' => [
            'name' => 'Link',
            'type' => 'urlpath',
            'nullable' => true,
            'max' => 255,
        ],
        'file' => [
            'name' => 'File',
            'type' => 'entity_file',
            'nullable' => true,
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'editor',
        ],
    ],
];
