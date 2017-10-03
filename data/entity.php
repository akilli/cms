<?php
return [
    'role' => [
        'name' => 'Roles',
        'model' => 'flat',
        'actions' => ['admin', 'delete', 'edit'],
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
                'actions' => ['admin', 'edit'],
                'maxlength' => 50,
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'checkbox',
                'backend' => 'json',
                'multiple' => true,
                'opt' => 'cms\opt_privilege',
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'model' => 'flat',
        'actions' => ['admin', 'delete', 'edit', 'login', 'logout', 'password'],
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
                'actions' => ['admin', 'edit'],
                'maxlength' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'actions' => ['edit'],
            ],
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'opt' => 'role',
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'model' => 'flat',
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
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'media',
        'actions' => ['admin', 'browser', 'delete', 'edit', 'view'],
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
                'actions' => ['admin', 'edit'],
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'actions' => ['admin'],
                'viewer' => 'cms\viewer_filesize',
            ],
            'file' => [
                'name' => 'File',
                'type' => 'text',
            ],
        ],
    ],
];
