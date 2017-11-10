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
                'type' => 'toggle',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'toggle',
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'act' => [
            'admin' => ['name', 'size'],
            'browser' => [],
            'delete' => [],
            'edit' => ['name'],
            'view' => []
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
            'edit' => ['name', 'url', 'active', 'parent_id', 'sort', 'content'],
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
