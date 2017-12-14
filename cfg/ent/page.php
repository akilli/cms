<?php
return [
    'name' => 'Pages',
    'type' => 'db',
    'act' => [
        'admin' => ['pos', 'name', 'status'],
        'delete' => [],
        'edit' => ['name', 'slug', 'parent_id', 'sort', 'content', 'status'],
        'index' => ['name'],
        'view' => ['content'],
    ],
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
            'searchable' => true,
            'maxlength' => 100,
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'rte',
            'searchable' => true,
            'val' => '',
        ],
        'slug' => [
            'name' => 'Slug',
            'type' => 'text',
            'required' => true,
            'maxlength' => 50,
            'filter' => 'id',
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'filter' => 'path',
        ],
        'status' => [
            'name' => 'Status',
            'type' => 'status',
            'required' => true,
        ],
        'date' => [
            'name' => 'Date',
            'type' => 'datetime',
        ],
        'parent_id' => [
            'name' => 'Parent',
            'type' => 'ent',
            'nullable' => true,
            'opt' => 'page',
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
            'val' => 0,
        ],
        'pos' => [
            'name' => 'Position',
            'type' => 'text',
            'viewer' => 'pos',
        ],
        'level' => [
            'name' => 'Level',
            'type' => 'int',
        ],
        'path' => [
            'name' => 'Path',
            'type' => 'json',
        ],
    ],
];
