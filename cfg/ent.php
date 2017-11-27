<?php
return [
    'role' => [
        'name' => 'Roles',
        'act' => [
            'admin' => ['name', 'active'],
            'delete' => [],
            'create' => ['name', 'priv', 'active'],
            'update' => ['name', 'priv', 'active'],
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
                'type' => 'json',
                'opt' => 'priv',
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'toggle',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'toggle',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'act' => [
            'admin' => ['name', 'role_id', 'active'],
            'delete' => [],
            'create' => ['name', 'password', 'role_id', 'active'],
            'update' => ['name', 'password', 'role_id', 'active'],
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
                'type' => 'toggle',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'toggle',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'act' => [
            'admin' => ['name', 'size'],
            'asset' => [],
            'browser' => ['name', 'size'],
            'delete' => [],
            'create' => ['name', 'info'],
            'update' => ['name', 'info'],
        ],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'file',
                'required' => true,
                'unique' => true,
                'searchable' => true,
            ],
            'info' => [
                'name' => 'Info',
                'type' => 'textarea',
                'searchable' => true,
                'val' => '',
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'viewer' => 'filesize',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'act' => [
            'admin' => ['pos', 'name', 'url', 'active'],
            'delete' => [],
            'create' => ['name', 'url', 'active', 'parent_id', 'sort', 'content'],
            'update' => ['name', 'url', 'active', 'parent_id', 'sort', 'content'],
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
                'filter' => 'path',
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'toggle',
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
                'searchable' => true,
                'val' => '',
            ],
            'created' => [
                'name' => 'Created',
                'type' => 'datetime',
            ],
            'modified' => [
                'name' => 'Modified',
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
            'depth' => [
                'name' => 'Depth',
                'type' => 'int',
            ],
            'path' => [
                'name' => 'Path',
                'type' => 'json',
                'opt' => 'page',
            ],
        ],
    ],
];
