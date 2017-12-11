<?php
return [
    'name' => 'Pages',
    'type' => 'db',
    'act' => [
        'admin' => ['pos', 'name', 'active'],
        'delete' => [],
        'edit' => ['name', 'slug', 'active', 'parent_id', 'sort', 'content'],
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
        'active' => [
            'name' => 'Active',
            'type' => 'bool',
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'rte',
            'searchable' => true,
            'val' => '',
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
