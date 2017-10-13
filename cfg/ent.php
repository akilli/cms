<?php
return [
    'role' => [
        'name' => 'Roles',
        'act' => [
            'admin' => ['name', 'active'],
            'delete' => [],
            'edit' => ['name', 'priv', 'active'],
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
                'unique' => true,
                'searchable' => true,
                'maxlength' => 50,
            ],
            'priv' => [
                'name' => 'Privileges',
                'type' => 'checkbox',
                'backend' => 'json',
                'opt' => 'opt\priv',
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'act' => [
            'admin' => ['name', 'role_id', 'active'],
            'delete' => [],
            'edit' => ['name', 'password', 'role_id', 'active'],
            'login' => [],
            'logout' => [],
            'password' => [],
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
                'unique' => true,
                'searchable' => true,
                'maxlength' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'minlength' => 8,
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'ent',
                'required' => true,
                'opt' => 'role',
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'act' => [
            'admin' => ['pos', 'name', 'active'],
            'delete' => [],
            'edit' => ['name', 'active', 'parent_id', 'sort', 'content'],
            'index' => ['name'],
            'view' => ['content']
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
            'url' => [
                'name' => 'URL',
                'type' => 'text',
                'required' => true,
                'unique' => true,
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
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
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
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
                'type' => 'ent',
                'backend' => 'json',
                'opt' => 'page',
            ],
            'depth' => [
                'name' => 'Depth',
                'type' => 'int',
            ],
            'pos' => [
                'name' => 'Position',
                'type' => 'text',
                'viewer' => 'viewer\pos',
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'type' => 'asset',
        'act' => [
            'admin' => ['name', 'size'],
            'browser' => [],
            'delete' => [],
            'edit' => ['name'],
            'import' => [],
            'view' => []
        ],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'text',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
                'required' => true,
                'unique' => true,
                'searchable' => true,
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'viewer' => 'viewer\filesize',
            ],
            'file' => [
                'name' => 'File',
                'type' => 'text',
            ],
        ],
    ],
];
