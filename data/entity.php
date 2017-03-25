<?php
return [
    'project' => [
        'name' => 'Project',
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
                'actions' => ['admin', 'edit'],
                'maxval' => 50,
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
        'name' => 'Role',
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
                'actions' => ['admin', 'edit'],
                'maxval' => 50,
            ],
            'privilege' => [
                'name' => 'Privileges',
                'type' => 'multiselect',
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
        'name' => 'Account',
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
        'name' => 'Page',
        'actions' => ['admin', 'delete', 'edit', 'index', 'view'],
        'attr' => [
            'id' => [
                'name' => 'ID',
                'auto' => true,
                'type' => 'int',
                'actions' => ['admin'],
            ],
            'uid' => [
                'name' => 'UID',
                'type' => 'text',
                'required' => true,
                'uniq' => true,
            ],
            'name' => [
                'name' => 'Name',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'actions' => ['admin', 'edit', 'form', 'index', 'view'],
                'maxval' => 100,
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
                'actions' => ['admin', 'edit'],
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
                'searchable' => true,
                'actions' => ['edit', 'view'],
            ],
            'search' => [
                'name' => 'Search',
                'type' => 'search',
            ],
            'project_id' => [
                'name' => 'Project',
                'type' => 'entity',
                'opt' => ['project'],
            ],
        ],
    ],
    'revision' => [
        'name' => 'Revision',
        'actions' => [],
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
                'maxval' => 100,
            ],
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
            ],
            'author' => [
                'name' => 'Author',
                'type' => 'text',
            ],
            'date' => [
                'name' => 'Date',
                'type' => 'datetime',
            ],
            'page_id' => [
                'name' => 'Page',
                'type' => 'entity',
                'opt' => ['page'],
            ],
        ],
    ],
    'media' => [
        'name' => 'Media',
        'model' => 'media',
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
                'actions' => ['admin', 'edit'],
            ],
            'size' => [
                'name' => 'Size',
                'type' => 'int',
                'actions' => ['admin'],
                'viewer' => 'filesize',
            ],
        ],
    ],
];
