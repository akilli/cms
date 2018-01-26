<?php
return [
    'role' => [
        'name' => 'Roles',
        'type' => 'db',
        'act' => [
            'admin' => ['incl' => ['name']],
            'delete' => [],
            'edit' => [],
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
        ],
    ],
    'account' => [
        'name' => 'Accounts',
        'type' => 'db',
        'act' => [
            'admin' => ['incl' => ['name', 'role']],
            'delete' => [],
            'edit' => [],
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
            'role' => [
                'name' => 'Role',
                'type' => 'ent',
                'required' => true,
                'ent' => 'role',
            ],
        ],
    ],
    'url' => [
        'name' => 'URL',
        'type' => 'db',
        'act' => [
            'admin' => [],
            'delete' => [],
            'edit' => [],
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
                'filter' => 'path',
            ],
            'target' => [
                'name' => 'Target',
                'type' => 'text',
                'required' => true,
                'searchable' => true,
                'filter' => 'path',
            ],
            'redirect' => [
                'name' => 'Redirect',
                'type' => 'int',
                'frontend' => 'select',
                'nullable' => true,
                'opt' => [301 => 301, 302 => 302, 303 => 303, 304 => 304, 305 => 305, 307 => 307, 308 => 308],
            ],
        ],
    ],
    'file' => [
        'name' => 'Files',
        'type' => 'db',
        'act' => [
            'admin' => ['incl' => ['name']],
            'asset' => [],
            'browser' => ['incl' => ['name']],
            'delete' => [],
            'edit' => ['incl' => ['name', 'info']],
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
        ],
    ],
    'page' => [
        'name' => 'Pages',
        'type' => 'db',
        'act' => [
            'index' => ['incl' => ['name', 'teaser']],
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
            ],
            'image' => [
                'name' => 'Image',
                'type' => 'ent',
                'nullable' => true,
                'ent' => 'file',
                'viewer' => 'fileopt',
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'searchable' => true,
                'val' => '',
            ],
            'body' => [
                'name' => 'Body',
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
                'auto' => true,
                'type' => 'text',
            ],
            'menu' => [
                'name' => 'Menu Entry',
                'type' => 'bool',
            ],
            'parent' => [
                'name' => 'Parent',
                'type' => 'ent',
                'nullable' => true,
                'ent' => 'page',
                'opt' => 'page',
            ],
            'sort' => [
                'name' => 'Sort',
                'type' => 'int',
                'val' => 0,
            ],
            'pos' => [
                'name' => 'Position',
                'auto' => true,
                'type' => 'text',
                'viewer' => 'pos',
            ],
            'level' => [
                'name' => 'Level',
                'auto' => true,
                'type' => 'int',
            ],
            'path' => [
                'name' => 'Path',
                'auto' => true,
                'type' => 'json',
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
            'ent' => [
                'name' => 'Page Type',
                'type' => 'select',
                'required' => true,
                'opt' => 'pagetype',
                'maxlength' => 50,
            ],
        ],
    ],
    'content' => [
        'name' => 'Content Pages',
        'type' => 'db',
        'parent' => 'page',
        'act' => [
            'admin' => ['incl' => ['name', 'pos', 'menu', 'status', 'date']],
            'delete' => [],
            'edit' => ['excl' => ['ent']],
            'index' => ['incl' => ['name', 'teaser']],
            'view' => ['incl' => ['image', 'teaser', 'body']],
        ],
    ],
    'article' => [
        'name' => 'Articles',
        'type' => 'db',
        'parent' => 'page',
        'act' => [
            'admin' => ['incl' => ['name', 'parent', 'status', 'date']],
            'delete' => [],
            'edit' => ['excl' => ['menu', 'ent']],
            'view' => ['incl' => ['image', 'teaser', 'body']],
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
            ],
            'teaser' => [
                'name' => 'Teaser',
                'type' => 'rte',
                'required' => true,
                'searchable' => true,
            ],
            'body' => [
                'name' => 'Body',
                'type' => 'rte',
                'required' => true,
                'searchable' => true,
            ],
            'status' => [
                'name' => 'Status',
                'type' => 'status',
                'required' => true,
            ],
            'date' => [
                'name' => 'Date',
                'type' => 'datetime',
                'required' => true,
            ],
            'page' => [
                'name' => 'Page',
                'type' => 'ent',
                'required' => true,
                'ent' => 'page',
            ],
        ],
    ],
];
