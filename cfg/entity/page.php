<?php
declare(strict_types=1);

return [
    'name' => 'Pages',
    'readonly' => true,
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
            'type' => 'entitychild',
            'required' => true,
            'max' => 255,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'urlpath',
            'required' => true,
            'unique' => true,
        ],
        'title' => [
            'name' => 'Title',
            'type' => 'text',
            'max' => 100,
        ],
        'content' => [
            'name' => 'Main Content',
            'type' => 'editor',
        ],
        'aside' => [
            'name' => 'Additional Information',
            'type' => 'editor',
        ],
        'meta_title' => [
            'name' => 'Meta Title',
            'type' => 'text',
            'max' => 80,
        ],
        'meta_description' => [
            'name' => 'Meta Description',
            'type' => 'text',
            'max' => 300,
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
