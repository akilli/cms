<?php
return [
    'default' => [
        'id' => null,
        'name' => null,
        'tab' => null,
        'model' => 'flat',
        'actions' => [],
        'attr' => [],
    ],
    'project' => [
        'name' => 'Projects',
        'actions' => ['admin', 'delete', 'edit', 'export', 'home', 'import', 'view'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
                'actions' => ['admin', 'edit'],
                'maxval' => 20,
                'validator' => 'qnd\validator_id',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit'],
                'maxval' => 50,
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
            'content' => [
                'name' => 'Homepage',
                'type' => 'rte',
                'actions' => ['edit', 'home'],
                'val' => '',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
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
                'maxval' => 50,
            ],
            'password' => [
                'name' => 'Password',
                'type' => 'password',
                'required' => true,
                'actions' => ['edit'],
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'checkbox',
                'backend' => 'json',
                'multiple' => true,
                'opt' => 'qnd\opt_privilege',
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
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => 'project',
            ],
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'actions' => ['admin', 'delete', 'edit', 'import', 'index', 'view'],
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
                'viewer' => 'qnd\viewer_pos',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit', 'index'],
                'maxval' => 100,
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
                'validator' => 'qnd\validator_page',
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
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => 'project',
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'media',
        'actions' => ['admin', 'browser', 'delete', 'edit', 'import', 'view'],
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
                'viewer' => 'qnd\viewer_filesize',
            ],
            'file' => [
                'name' => 'File',
                'type' => 'text',
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => 'project',
            ],
        ],
    ],
];
