<?php
declare(strict_types=1);

return [
    'name' => 'Pages',
    'action' => ['add', 'delete', 'edit', 'index', 'view'],
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
        'content' => [
            'name' => 'Main Content',
            'type' => 'editor',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
