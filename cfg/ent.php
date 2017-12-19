<?php
return [
    'role' => [
        'name' => 'Roles',
        'type' => 'db',
        'act' => [
            'admin' => ['name'],
            'delete' => [],
            'edit' => ['name', 'priv'],
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
                'opt' => 'priv',
            ],
            'system' => [
                'name' => 'System',
                'type' => 'bool',
            ],
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'type' => 'db',
        'act' => [
            'admin' => ['name', 'role_id'],
            'delete' => [],
            'edit' => ['name', 'password', 'role_id'],
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
            'system' => [
                'name' => 'System',
                'type' => 'bool',
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'type' => 'db',
        'act' => [
            'admin' => ['name', 'size'],
            'asset' => [],
            'browser' => ['name', 'size'],
            'delete' => [],
            'edit' => ['name', 'info'],
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
                'opt' => [
                    'aac', 'flac', 'mp3', 'oga', 'ogg', 'wav', 'weba',
                    'gif', 'jpg', 'png', 'svg', 'webp',
                    'mp4', 'ogv', 'webm',
                    'bz2', 'csv', 'doc', 'docx', 'gz', 'odg', 'odp', 'ods', 'odt', 'pdf', 'xls', 'xlsm', 'xlsx', 'zip',
                ],
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
        'type' => 'db',
        'act' => [
            'admin' => ['pos', 'name', 'status', 'date'],
            'delete' => [],
            'edit' => ['name', 'slug', 'status', 'parent_id', 'sort', 'content'],
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
            'content' => [
                'name' => 'Content',
                'type' => 'rte',
                'searchable' => true,
                'val' => '',
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
            'status' => [
                'name' => 'Status',
                'type' => 'status',
                'required' => true,
            ],
            'date' => [
                'name' => 'Date',
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
            'level' => [
                'name' => 'Level',
                'type' => 'int',
            ],
            'path' => [
                'name' => 'Path',
                'type' => 'json',
            ],
        ],
    ],
    'version' => [
        'name' => 'Versions',
        'type' => 'db',
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
            'ent' => [
                'name' => 'Entity',
                'type' => 'select',
                'required' => true,
                'searchable' => true,
                'opt' => 'ent_cfg',
                'maxlength' => 50,
            ],
            'ent_id' => [
                'name' => 'Entity-ID',
                'type' => 'int',
                'required' => true,
            ],
            'status' => [
                'name' => 'Status',
                'type' => 'status',
                'required' => true,
            ],
            'date' => [
                'name' => 'Date',
                'type' => 'datetime',
            ],
            'data' => [
                'name' => 'Data',
                'type' => 'json',
            ],
        ],
    ],
];
