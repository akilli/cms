<?php
return [
    'project' => [
        'name' => 'Projects',
        'actions' => ['admin', 'delete', 'edit', 'export', 'import', 'switch'],
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
                'validator' => 'id',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit'],
                'maxval' => 50,
            ],
            'exported' => [
                'name' => 'Exported',
                'type' => 'date',
                'nullable' => true,
                'actions' => ['admin'],
                'val' => null,
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
        ],
    ],
    'role' => [
        'name' => 'Roles',
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
                'maxval' => 50,
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multicheckbox',
                'opt' => ['opt_privilege'],
                'actions' => ['edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
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
            'role_id' => [
                'name' => 'Role',
                'type' => 'entity',
                'required' => true,
                'opt' => ['role'],
                'actions' => ['admin', 'edit'],
            ],
            'active' => [
                'name' => 'Active',
                'type' => 'checkbox',
                'actions' => ['admin', 'edit'],
                'val' => false,
            ],
            'system' => [
                'name' => 'System',
                'type' => 'checkbox',
                'val' => false,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
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
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit', 'index', 'view'],
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
                'val' => false,
            ],
            'parent_id' => [
                'name' => 'Parent',
                'type' => 'entity',
                'nullable' => true,
                'opt' => ['page'],
                'actions' => ['edit'],
                'validator' => 'page',
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
            ],
            'search' => [
                'name' => 'Search',
                'type' => 'search',
                'searchable' => true,
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'tree' => [
        'name' => 'Page Tree',
        'attr' => [
            'id' => [
                'name' => 'ID',
                'type' => 'int',
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
            ],
            'url' => [
                'name' => 'URL',
                'type' => 'text',
            ],
            'parent_id' => [
                'name' => 'Parent',
                'type' => 'entity',
                'opt' => ['page'],
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
            ],
            'path' => [
                'name' => 'Path',
                'type' => 'entity',
                'backend' => 'json',
                'multiple' => true,
                'opt' => ['page'],
            ],
            'depth' => [
                'name' => 'Depth',
                'type' => 'int',
            ],
            'pos' => [
                'name' => 'Position',
                'type' => 'text',
                'viewer' => 'pos',
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'media',
        'actions' => ['admin', 'delete', 'edit', 'import', 'view'],
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
                'viewer' => 'filesize',
            ],
            'file' => [
                'name' => 'File',
                'type' => 'text',
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
];
