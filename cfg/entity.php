<?php
return [
    'role' => [
        'name' => 'Roles',
        'act' => ['admin', 'delete', 'edit'],
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
                'uniq' => true,
                'searchable' => true,
                'act' => ['admin', 'edit'],
                'maxlength' => 50,
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'checkbox',
                'backend' => 'json',
                'multiple' => true,
                'opt' => 'opt\privilege',
                'act' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'act' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'act' => ['admin', 'delete', 'edit', 'login', 'logout', 'password'],
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
                'uniq' => true,
                'searchable' => true,
                'act' => ['admin', 'edit'],
                'maxlength' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'act' => ['edit'],
                'minlength' => 8,
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'opt' => 'role',
                'act' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'act' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'act' => ['admin', 'delete', 'edit', 'index', 'view'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'pos' => [
                'name' => 'Position',
                'type' => 'text',
                'act' => ['admin'],
                'viewer' => 'viewer\pos',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'act' => ['admin', 'edit', 'index'],
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
                'act' => ['admin', 'edit'],
            ],
            'parent_id' => [
                'name' => 'Parent',
                'type' => 'entity',
                'nullable' => true,
                'opt' => 'page',
                'act' => ['edit'],
                'validator' => 'validator\page',
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'act' => ['edit'],
                'val' => 0,
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
                'act' => ['edit', 'view'],
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
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'asset',
        'act' => ['admin', 'browser', 'delete', 'edit', 'import', 'view'],
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
                'uniq' => true,
                'searchable' => true,
                'act' => ['admin', 'edit'],
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'act' => ['admin'],
                'viewer' => 'viewer\filesize',
            ],
            'file' => [
                'name' => 'File',
                'type' => 'text',
            ],
        ],
    ],
];
