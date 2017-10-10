<?php
return [
    'name' => 'Pages',
    'actions' => ['admin', 'delete', 'edit', 'index', 'view'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'int',
        ],
        'pos' => [
            'name' => 'Position',
            'type' => 'text',
            'actions' => ['admin'],
            'viewer' => 'cms\viewer_pos',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'searchable' => true,
            'actions' => ['admin', 'edit', 'index'],
            'maxlength' => 100,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'text',
            'required' => true,
            'uniq' => true,
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'checkbox',
            'actions' => ['admin', 'edit'],
        ],
        'parent_id' => [
            'name' => 'Parent',
            'type' => 'entity',
            'nullable' => true,
            'opt' => 'page',
            'actions' => ['edit'],
            'validator' => 'cms\validator_page',
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
            'actions' => ['edit'],
            'val' => 0,
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'rte',
            'actions' => ['edit', 'view'],
            'val' => '',
        ],
        'search' => [
            'name' => 'Search',
            'type' => 'search',
            'searchable' => true,
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
        ],
        'modified' => [
            'name' => 'Modified',
            'type' => 'datetime',
        ],
        'path' => [
            'name' => 'Path',
            'type' => 'entity',
            'backend' => 'json',
            'multiple' => true,
            'opt' => 'page',
        ],
        'depth' => [
            'name' => 'Depth',
            'type' => 'int',
        ],
    ],
];
